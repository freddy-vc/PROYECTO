<?php
require_once __DIR__ . '/../database/connection.php';

/**
 * Clase Partido
 * 
 * Maneja todas las operaciones relacionadas con partidos
 */
class Partido
{
    // Conexión PDO
    private $conexion;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // Obtener la conexión a la base de datos
        $this->conexion = Conexion::getConexion();
    }
    
    /**
     * Obtener todos los partidos
     */
    public function obtenerTodos($filtro = null)
    {
        try {
            // Consulta base
            $sql = "SELECT p.cod_par, p.fecha, p.hora, p.estado,
                    e1.cod_equ as local_id, e1.nombre as local_nombre, e1.escudo as local_escudo,
                    e2.cod_equ as visitante_id, e2.nombre as visitante_nombre, e2.escudo as visitante_escudo,
                    c.nombre as cancha
                    FROM Partidos p
                    JOIN Equipos e1 ON p.equ_local = e1.cod_equ
                    JOIN Equipos e2 ON p.equ_visitante = e2.cod_equ
                    JOIN Canchas c ON p.cod_cancha = c.cod_cancha";
            
            // Aplicar filtros si hay
            if ($filtro === 'programados') {
                $sql .= " WHERE p.estado = 'programado'";
            } elseif ($filtro === 'finalizados') {
                $sql .= " WHERE p.estado = 'finalizado'";
            }
            
            // Ordenar por fecha y hora
            $sql .= " ORDER BY p.fecha, p.hora";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            $partidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Para cada partido, obtener los goles si está finalizado
            foreach ($partidos as &$partido) {
                if ($partido['estado'] === 'finalizado') {
                    $goles_local = $this->contarGoles($partido['cod_par'], $partido['local_id']);
                    $goles_visitante = $this->contarGoles($partido['cod_par'], $partido['visitante_id']);
                    
                    $partido['goles_local'] = $goles_local;
                    $partido['goles_visitante'] = $goles_visitante;
                }
            }
            
            return $partidos;
            
        } catch (PDOException $e) {
            error_log('Error al obtener partidos: ' . $e->getMessage());
            return [];
        } catch (Exception $e) {
            error_log('Error general al obtener partidos: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener partido por ID
     */
    public function obtenerPorId($cod_par)
    {
        try {
            $sql = "SELECT p.cod_par, p.fecha, p.hora, p.estado,
                    e1.cod_equ as local_id, e1.nombre as local_nombre, e1.escudo as local_escudo,
                    e2.cod_equ as visitante_id, e2.nombre as visitante_nombre, e2.escudo as visitante_escudo,
                    c.nombre as cancha, c.direccion as cancha_direccion
                    FROM Partidos p
                    JOIN Equipos e1 ON p.equ_local = e1.cod_equ
                    JOIN Equipos e2 ON p.equ_visitante = e2.cod_equ
                    JOIN Canchas c ON p.cod_cancha = c.cod_cancha
                    WHERE p.cod_par = :cod_par";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->execute();
            
            $partido = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($partido) {
                // Obtener goles y más detalles si está finalizado
                if ($partido['estado'] === 'finalizado') {
                    $goles_local = $this->contarGoles($partido['cod_par'], $partido['local_id']);
                    $goles_visitante = $this->contarGoles($partido['cod_par'], $partido['visitante_id']);
                    
                    $partido['goles_local'] = $goles_local;
                    $partido['goles_visitante'] = $goles_visitante;
                    
                    // Obtener detalles de goles, asistencias y faltas
                    $partido['detalle_goles'] = $this->obtenerDetalleGoles($partido['cod_par']);
                    $partido['detalle_asistencias'] = $this->obtenerDetalleAsistencias($partido['cod_par']);
                    $partido['detalle_faltas'] = $this->obtenerDetalleFaltas($partido['cod_par']);
                }
            }
            
            return $partido;
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Contar goles de un equipo en un partido
     */
    public function contarGoles($cod_par, $cod_equ)
    {
        try {
            // Contar goles normales y de penal (a favor)
            $sql = "SELECT COUNT(*) FROM Goles g
                    JOIN Jugadores j ON g.cod_jug = j.cod_jug
                    WHERE g.cod_par = :cod_par 
                    AND j.cod_equ = :cod_equ
                    AND g.tipo IN ('normal', 'penal')";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->bindParam(':cod_equ', $cod_equ);
            $stmt->execute();
            
            $goles_favor = $stmt->fetchColumn();
            
            // Contar autogoles (en contra)
            $sql = "SELECT COUNT(*) FROM Goles g
                    JOIN Jugadores j ON g.cod_jug = j.cod_jug
                    WHERE g.cod_par = :cod_par 
                    AND j.cod_equ != :cod_equ
                    AND g.tipo = 'autogol'";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->bindParam(':cod_equ', $cod_equ);
            $stmt->execute();
            
            $autogoles_contra = $stmt->fetchColumn();
            
            // Contar autogoles propios
            $sql = "SELECT COUNT(*) FROM Goles g
                    JOIN Jugadores j ON g.cod_jug = j.cod_jug
                    WHERE g.cod_par = :cod_par 
                    AND j.cod_equ = :cod_equ
                    AND g.tipo = 'autogol'";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->bindParam(':cod_equ', $cod_equ);
            $stmt->execute();
            
            $autogoles_propios = $stmt->fetchColumn();
            
            // Total de goles = goles a favor + autogoles contra - autogoles propios
            return $goles_favor + $autogoles_contra - $autogoles_propios;
            
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Obtener detalle de goles de un partido
     */
    public function obtenerDetalleGoles($cod_par)
    {
        try {
            $sql = "SELECT g.cod_gol, g.minuto, g.tipo,
                    j.cod_jug, j.nombres, j.apellidos, j.dorsal,
                    e.cod_equ, e.nombre as equipo
                    FROM Goles g
                    JOIN Jugadores j ON g.cod_jug = j.cod_jug
                    JOIN Equipos e ON j.cod_equ = e.cod_equ
                    WHERE g.cod_par = :cod_par
                    ORDER BY g.minuto";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener detalle de asistencias de un partido
     */
    public function obtenerDetalleAsistencias($cod_par)
    {
        try {
            $sql = "SELECT a.cod_asis, a.minuto,
                    j.cod_jug, j.nombres, j.apellidos, j.dorsal,
                    e.cod_equ, e.nombre as equipo
                    FROM Asistencias a
                    JOIN Jugadores j ON a.cod_jug = j.cod_jug
                    JOIN Equipos e ON j.cod_equ = e.cod_equ
                    WHERE a.cod_par = :cod_par
                    ORDER BY a.minuto";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener detalle de faltas de un partido
     */
    public function obtenerDetalleFaltas($cod_par)
    {
        try {
            $sql = "SELECT f.cod_falta, f.minuto, f.tipo_falta,
                    j.cod_jug, j.nombres, j.apellidos, j.dorsal,
                    e.cod_equ, e.nombre as equipo
                    FROM Faltas f
                    JOIN Jugadores j ON f.cod_jug = j.cod_jug
                    JOIN Equipos e ON j.cod_equ = e.cod_equ
                    WHERE f.cod_par = :cod_par
                    ORDER BY f.minuto";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Crear un nuevo partido
     */
    public function crear($fecha, $hora, $cod_cancha, $equ_local, $equ_visitante)
    {
        try {
            // Verificar que los equipos sean diferentes
            if ($equ_local === $equ_visitante) {
                return [
                    'estado' => false,
                    'mensaje' => 'El equipo local y visitante no pueden ser el mismo'
                ];
            }
            
            $sql = "INSERT INTO Partidos (fecha, hora, cod_cancha, equ_local, equ_visitante, estado) 
                    VALUES (:fecha, :hora, :cod_cancha, :equ_local, :equ_visitante, 'programado')";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':hora', $hora);
            $stmt->bindParam(':cod_cancha', $cod_cancha);
            $stmt->bindParam(':equ_local', $equ_local);
            $stmt->bindParam(':equ_visitante', $equ_visitante);
            
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Partido creado correctamente',
                'id' => $this->conexion->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al crear el partido: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar partido
     */
    public function actualizar($cod_par, $fecha, $hora, $cod_cancha, $estado)
    {
        try {
            $sql = "UPDATE Partidos 
                    SET fecha = :fecha, hora = :hora, 
                    cod_cancha = :cod_cancha, estado = :estado
                    WHERE cod_par = :cod_par";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':hora', $hora);
            $stmt->bindParam(':cod_cancha', $cod_cancha);
            $stmt->bindParam(':estado', $estado);
            
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Partido actualizado correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al actualizar el partido: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Registrar un gol
     */
    public function registrarGol($cod_par, $cod_jug, $minuto, $tipo)
    {
        try {
            $sql = "INSERT INTO Goles (cod_par, cod_jug, minuto, tipo) 
                    VALUES (:cod_par, :cod_jug, :minuto, :tipo)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->bindParam(':cod_jug', $cod_jug);
            $stmt->bindParam(':minuto', $minuto);
            $stmt->bindParam(':tipo', $tipo);
            
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Gol registrado correctamente',
                'id' => $this->conexion->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al registrar el gol: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Registrar una asistencia
     */
    public function registrarAsistencia($cod_par, $cod_jug, $minuto)
    {
        try {
            $sql = "INSERT INTO Asistencias (cod_par, cod_jug, minuto) 
                    VALUES (:cod_par, :cod_jug, :minuto)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->bindParam(':cod_jug', $cod_jug);
            $stmt->bindParam(':minuto', $minuto);
            
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Asistencia registrada correctamente',
                'id' => $this->conexion->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al registrar la asistencia: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Registrar una falta
     */
    public function registrarFalta($cod_par, $cod_jug, $minuto, $tipo_falta)
    {
        try {
            $sql = "INSERT INTO Faltas (cod_par, cod_jug, minuto, tipo_falta) 
                    VALUES (:cod_par, :cod_jug, :minuto, :tipo_falta)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->bindParam(':cod_jug', $cod_jug);
            $stmt->bindParam(':minuto', $minuto);
            $stmt->bindParam(':tipo_falta', $tipo_falta);
            
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Falta registrada correctamente',
                'id' => $this->conexion->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al registrar la falta: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar partido
     */
    public function eliminar($cod_par)
    {
        try {
            $sql = "DELETE FROM Partidos WHERE cod_par = :cod_par";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Partido eliminado correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al eliminar el partido: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener los últimos partidos finalizados
     */
    public function obtenerUltimosFinalizados($limite = 3) 
    {
        try {
            $sql = "SELECT p.cod_par, p.fecha, p.hora, p.estado,
                    e1.cod_equ as local_id, e1.nombre as local_nombre, e1.escudo as local_escudo,
                    e2.cod_equ as visitante_id, e2.nombre as visitante_nombre, e2.escudo as visitante_escudo,
                    c.nombre as cancha
                    FROM Partidos p
                    JOIN Equipos e1 ON p.equ_local = e1.cod_equ
                    JOIN Equipos e2 ON p.equ_visitante = e2.cod_equ
                    JOIN Canchas c ON p.cod_cancha = c.cod_cancha
                    WHERE p.estado = 'finalizado'
                    ORDER BY p.fecha DESC, p.hora DESC
                    LIMIT :limite";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            $partidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar los escudos y añadir conteo de goles
            foreach ($partidos as &$partido) {
                // Procesar escudos
                if ($partido['local_escudo']) {
                    $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']);
                } else {
                    $partido['local_escudo_base64'] = './frontend/assets/images/default-team.png';
                }
                
                if ($partido['visitante_escudo']) {
                    $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']);
                } else {
                    $partido['visitante_escudo_base64'] = './frontend/assets/images/default-team.png';
                }
                
                // Obtener los goles
                $partido['goles_local'] = $this->contarGoles($partido['cod_par'], $partido['local_id']);
                $partido['goles_visitante'] = $this->contarGoles($partido['cod_par'], $partido['visitante_id']);
                
                // Formatear fecha
                $fecha = new DateTime($partido['fecha']);
                $hora = new DateTime($partido['hora']);
                $partido['fecha_formateada'] = $fecha->format('d M, Y') . ' - ' . $hora->format('H:i');
            }
            
            return $partidos;
            
        } catch (PDOException $e) {
            error_log('Error al obtener últimos partidos: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener partidos por fases (para el cuadro del torneo)
     */
    public function obtenerPartidosPorFases($fases) 
    {
        try {
            // Convertir el array de fases a un string para la consulta SQL
            $fasesStr = "'" . implode("','", $fases) . "'";
            
            $sql = "SELECT 
                    p.cod_par, p.fecha, p.hora, p.estado,
                    e1.cod_equ as equ_local, e1.nombre as local_nombre, e1.escudo as local_escudo,
                    e2.cod_equ as equ_visitante, e2.nombre as visitante_nombre, e2.escudo as visitante_escudo,
                    c.nombre as cancha,
                    fe.fase,
                    CASE 
                        WHEN fe.fase = 'cuartos' THEN
                            CASE
                                WHEN (SELECT COUNT(*) FROM Partidos p2
                                      JOIN FaseEquipo fe2 ON (p2.equ_local = fe2.cod_equ OR p2.equ_visitante = fe2.cod_equ)
                                      WHERE fe2.fase = 'cuartos' AND fe2.fase = fe.fase
                                      AND p2.fecha <= p.fecha AND p2.cod_par <= p.cod_par)
                                THEN (SELECT COUNT(*) FROM Partidos p2
                                      JOIN FaseEquipo fe2 ON (p2.equ_local = fe2.cod_equ OR p2.equ_visitante = fe2.cod_equ)
                                      WHERE fe2.fase = 'cuartos' AND fe2.fase = fe.fase
                                      AND p2.fecha <= p.fecha AND p2.cod_par <= p.cod_par)
                                ELSE 0
                            END
                        WHEN fe.fase = 'semis' THEN
                            CASE
                                WHEN (SELECT COUNT(*) FROM Partidos p2
                                      JOIN FaseEquipo fe2 ON (p2.equ_local = fe2.cod_equ OR p2.equ_visitante = fe2.cod_equ)
                                      WHERE fe2.fase = 'semis' AND fe2.fase = fe.fase
                                      AND p2.fecha <= p.fecha AND p2.cod_par <= p.cod_par)
                                THEN (SELECT COUNT(*) FROM Partidos p2
                                      JOIN FaseEquipo fe2 ON (p2.equ_local = fe2.cod_equ OR p2.equ_visitante = fe2.cod_equ)
                                      WHERE fe2.fase = 'semis' AND fe2.fase = fe.fase
                                      AND p2.fecha <= p.fecha AND p2.cod_par <= p.cod_par)
                                ELSE 0
                            END
                        ELSE 1
                    END as orden
                FROM Partidos p
                JOIN Equipos e1 ON p.equ_local = e1.cod_equ
                JOIN Equipos e2 ON p.equ_visitante = e2.cod_equ
                JOIN Canchas c ON p.cod_cancha = c.cod_cancha
                JOIN FaseEquipo fe ON (p.equ_local = fe.cod_equ OR p.equ_visitante = fe.cod_equ)
                WHERE fe.fase IN ($fasesStr)
                GROUP BY p.cod_par, e1.cod_equ, e1.nombre, e1.escudo,
                         e2.cod_equ, e2.nombre, e2.escudo,
                         c.nombre, fe.fase
                ORDER BY 
                    CASE fe.fase
                        WHEN 'final' THEN 1
                        WHEN 'semis' THEN 2
                        WHEN 'cuartos' THEN 3
                        ELSE 4
                    END,
                   p.fecha, p.hora";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            $partidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar los escudos y añadir conteo de goles para partidos finalizados
            foreach ($partidos as &$partido) {
                // Procesar escudos
                if ($partido['local_escudo']) {
                    $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']);
                } else {
                    $partido['local_escudo_base64'] = '../assets/images/default-team.png';
                }
                
                if ($partido['visitante_escudo']) {
                    $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']);
                } else {
                    $partido['visitante_escudo_base64'] = '../assets/images/default-team.png';
                }
                
                // Si el partido está finalizado, añadir conteo de goles
                if ($partido['estado'] === 'finalizado') {
                    $partido['goles_local'] = $this->contarGoles($partido['cod_par'], $partido['equ_local']);
                    $partido['goles_visitante'] = $this->contarGoles($partido['cod_par'], $partido['equ_visitante']);
                }
                
                // Formatear fecha
                $partido['fecha_formateada'] = date('d/m/Y', strtotime($partido['fecha']));
            }
            
            return $partidos;
            
        } catch (PDOException $e) {
            error_log('Error al obtener partidos por fases: ' . $e->getMessage());
            return [];
        }
    }
}
?> 
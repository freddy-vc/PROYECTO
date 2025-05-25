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
            $sql = "SELECT p.cod_par, p.fecha, p.hora, p.estado, p.fase,
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
            
            // Ordenar por ID
            $sql .= " ORDER BY p.cod_par";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            $partidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Para cada partido, procesar escudos y obtener los goles si está finalizado
            foreach ($partidos as &$partido) {
                // Procesar escudo del equipo local
                if (!empty($partido['local_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['local_escudo'])) {
                        $content = stream_get_contents($partido['local_escudo']);
                        rewind($partido['local_escudo']);
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['local_escudo']);
                    } else {
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['local_escudo']);
                    }
                } else {
                    $partido['local_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['local_escudo']);
                }
                
                // Procesar escudo del equipo visitante
                if (!empty($partido['visitante_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['visitante_escudo'])) {
                        $content = stream_get_contents($partido['visitante_escudo']);
                        rewind($partido['visitante_escudo']);
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['visitante_escudo']);
                    } else {
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['visitante_escudo']);
                    }
                } else {
                    $partido['visitante_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['visitante_escudo']);
                }
                
                // Calcular marcador si el partido está finalizado
                $marcador = $this->calcularMarcadorPorDetalle($partido['cod_par'], $partido['local_nombre'], $partido['visitante_nombre']);
                $partido['goles_local'] = $marcador['goles_local'];
                $partido['goles_visitante'] = $marcador['goles_visitante'];
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
            $sql = "SELECT p.cod_par, p.fecha, p.hora, p.estado, p.fase, p.cod_cancha,
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
                // Procesar escudo del equipo local
                if (!empty($partido['local_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['local_escudo'])) {
                        $content = stream_get_contents($partido['local_escudo']);
                        rewind($partido['local_escudo']);
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['local_escudo']);
                    } else {
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['local_escudo']);
                    }
                } else {
                    $partido['local_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['local_escudo']);
                }
                
                // Procesar escudo del equipo visitante
                if (!empty($partido['visitante_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['visitante_escudo'])) {
                        $content = stream_get_contents($partido['visitante_escudo']);
                        rewind($partido['visitante_escudo']);
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['visitante_escudo']);
                    } else {
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['visitante_escudo']);
                    }
                } else {
                    $partido['visitante_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['visitante_escudo']);
                }
                
                // Obtener goles y más detalles si está finalizado
                if ($partido['estado'] === 'finalizado') {
                    $marcador = $this->calcularMarcadorPorDetalle($partido['cod_par'], $partido['local_nombre'], $partido['visitante_nombre']);
                    
                    $partido['goles_local'] = $marcador['goles_local'];
                    $partido['goles_visitante'] = $marcador['goles_visitante'];
                    
                    // Obtener detalles de goles, asistencias y faltas
                    $partido['detalle_goles'] = $this->obtenerDetalleGoles($partido['cod_par']);
                    $partido['detalle_asistencias'] = $this->obtenerDetalleAsistencias($partido['cod_par']);
                    $partido['detalle_faltas'] = $this->obtenerDetalleFaltas($partido['cod_par']);
                }
            }
            
            return $partido;
            
        } catch (PDOException $e) {
            error_log('Error al obtener partido por ID: ' . $e->getMessage());
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
    public function crear($fecha, $hora, $cod_cancha, $equ_local, $equ_visitante, $fase)
    {
        try {
            // Verificar que los equipos sean diferentes
            if ($equ_local === $equ_visitante) {
                return [
                    'estado' => false,
                    'mensaje' => 'El equipo local y visitante no pueden ser el mismo'
                ];
            }
            
            $sql = "INSERT INTO Partidos (fecha, hora, cod_cancha, equ_local, equ_visitante, estado, fase) 
                    VALUES (:fecha, :hora, :cod_cancha, :equ_local, :equ_visitante, 'programado', :fase)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':hora', $hora);
            $stmt->bindParam(':cod_cancha', $cod_cancha);
            $stmt->bindParam(':equ_local', $equ_local);
            $stmt->bindParam(':equ_visitante', $equ_visitante);
            $stmt->bindParam(':fase', $fase);
            
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
    public function actualizar($cod_par, $fecha, $hora, $cod_cancha, $estado, $fase)
    {
        try {
            $sql = "UPDATE Partidos 
                    SET fecha = :fecha, hora = :hora, 
                    cod_cancha = :cod_cancha, estado = :estado, fase = :fase
                    WHERE cod_par = :cod_par";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':hora', $hora);
            $stmt->bindParam(':cod_cancha', $cod_cancha);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':fase', $fase);
            
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
            // Verificar primero si el partido existe
            $query = "SELECT * FROM Partidos WHERE cod_par = :cod_par";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se pudo eliminar porque el partido no existe o ya fue eliminado'
                ];
            }
            
            // Verificar si hay estadísticas asociadas al partido
            $queryGoles = "SELECT COUNT(*) as total FROM Goles WHERE cod_par = :cod_par";
            $stmtGoles = $this->conexion->prepare($queryGoles);
            $stmtGoles->bindParam(':cod_par', $cod_par);
            $stmtGoles->execute();
            $goles = $stmtGoles->fetch(PDO::FETCH_ASSOC)['total'];
            
            $queryAsistencias = "SELECT COUNT(*) as total FROM Asistencias WHERE cod_par = :cod_par";
            $stmtAsistencias = $this->conexion->prepare($queryAsistencias);
            $stmtAsistencias->bindParam(':cod_par', $cod_par);
            $stmtAsistencias->execute();
            $asistencias = $stmtAsistencias->fetch(PDO::FETCH_ASSOC)['total'];
            
            $queryFaltas = "SELECT COUNT(*) as total FROM Faltas WHERE cod_par = :cod_par";
            $stmtFaltas = $this->conexion->prepare($queryFaltas);
            $stmtFaltas->bindParam(':cod_par', $cod_par);
            $stmtFaltas->execute();
            $faltas = $stmtFaltas->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($goles > 0 || $asistencias > 0 || $faltas > 0) {
                $detalles = [];
                if ($goles > 0) $detalles[] = $goles . ' gol(es)';
                if ($asistencias > 0) $detalles[] = $asistencias . ' asistencia(s)';
                if ($faltas > 0) $detalles[] = $faltas . ' tarjeta(s)';
                
                return [
                    'estado' => false,
                    'mensaje' => 'No se puede eliminar el partido porque tiene estadísticas asociadas: ' . implode(', ', $detalles) . '. Elimine primero estas estadísticas o considere marcar el partido como cancelado en lugar de eliminarlo.'
                ];
            }
            
            // Eliminar el partido
            $sql = "DELETE FROM Partidos WHERE cod_par = :cod_par";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se pudo eliminar el partido. Por favor, inténtelo de nuevo más tarde.'
                ];
            }
            
            return [
                'estado' => true,
                'mensaje' => 'Partido eliminado correctamente'
            ];
            
        } catch (PDOException $e) {
            $errorMessage = 'Error al eliminar el partido';
            
            // Verificar si es error de clave foránea
            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                $errorMessage = 'No se puede eliminar el partido porque está siendo referenciado por otros registros del sistema';
            }
            
            return [
                'estado' => false,
                'mensaje' => $errorMessage
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
                // Procesar escudo del equipo local
                if (!empty($partido['local_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['local_escudo'])) {
                        $content = stream_get_contents($partido['local_escudo']);
                        rewind($partido['local_escudo']);
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['local_escudo']);
                    } else {
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['local_escudo']);
                    }
                } else {
                    $partido['local_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['local_escudo']);
                }
                
                // Procesar escudo del equipo visitante
                if (!empty($partido['visitante_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['visitante_escudo'])) {
                        $content = stream_get_contents($partido['visitante_escudo']);
                        rewind($partido['visitante_escudo']);
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['visitante_escudo']);
                    } else {
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['visitante_escudo']);
                    }
                } else {
                    $partido['visitante_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['visitante_escudo']);
                }
                
                // Obtener los goles
                $marcador = $this->calcularMarcadorPorDetalle($partido['cod_par'], $partido['local_nombre'], $partido['visitante_nombre']);
                $partido['goles_local'] = $marcador['goles_local'];
                $partido['goles_visitante'] = $marcador['goles_visitante'];
                
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
                    p.cod_par, p.fecha, p.hora, p.estado, p.fase,
                    e1.cod_equ as equ_local, e1.nombre as local_nombre, e1.escudo as local_escudo,
                    e2.cod_equ as equ_visitante, e2.nombre as visitante_nombre, e2.escudo as visitante_escudo,
                    c.nombre as cancha,
                    CASE 
                        WHEN p.fase = 'cuartos' THEN
                            (SELECT COUNT(*) FROM Partidos p2
                            WHERE p2.fase = 'cuartos'
                            AND p2.fecha <= p.fecha AND p2.cod_par <= p.cod_par)
                        WHEN p.fase = 'semis' THEN
                            (SELECT COUNT(*) FROM Partidos p2
                            WHERE p2.fase = 'semis'
                            AND p2.fecha <= p.fecha AND p2.cod_par <= p.cod_par)
                        ELSE 1
                    END as orden
                FROM Partidos p
                JOIN Equipos e1 ON p.equ_local = e1.cod_equ
                JOIN Equipos e2 ON p.equ_visitante = e2.cod_equ
                JOIN Canchas c ON p.cod_cancha = c.cod_cancha
                WHERE p.fase IN ($fasesStr)
                ORDER BY 
                    CASE p.fase
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
                // Procesar escudo del equipo local
                if (!empty($partido['local_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['local_escudo'])) {
                        $content = stream_get_contents($partido['local_escudo']);
                        rewind($partido['local_escudo']);
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['local_escudo']);
                    } else {
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['local_escudo']);
                    }
                } else {
                    $partido['local_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['local_escudo']);
                }
                
                // Procesar escudo del equipo visitante
                if (!empty($partido['visitante_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['visitante_escudo'])) {
                        $content = stream_get_contents($partido['visitante_escudo']);
                        rewind($partido['visitante_escudo']);
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['visitante_escudo']);
                    } else {
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['visitante_escudo']);
                    }
                } else {
                    $partido['visitante_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['visitante_escudo']);
                }
                
                // Si el partido está finalizado, añadir conteo de goles
                if ($partido['estado'] === 'finalizado') {
                    $marcador = $this->calcularMarcadorPorDetalle($partido['cod_par'], $partido['local_nombre'], $partido['visitante_nombre']);
                    $partido['goles_local'] = $marcador['goles_local'];
                    $partido['goles_visitante'] = $marcador['goles_visitante'];
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

    /**
     * Obtener partidos por fase (cuartos, semis, final)
     */
    public function obtenerPorFase($fase) {
        try {
            $sql = "SELECT p.*, e1.nombre as local_nombre, e2.nombre as visitante_nombre
                    FROM Partidos p
                    JOIN Equipos e1 ON p.equ_local = e1.cod_equ
                    JOIN Equipos e2 ON p.equ_visitante = e2.cod_equ
                    WHERE p.fase = :fase
                    ORDER BY p.cod_par ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fase', $fase);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Actualizar un gol
     */
    public function actualizarGol($cod_gol, $cod_par, $cod_jug, $minuto, $tipo)
    {
        try {
            $sql = "UPDATE Goles SET cod_par = :cod_par, cod_jug = :cod_jug, minuto = :minuto, tipo = :tipo WHERE cod_gol = :cod_gol";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->bindParam(':cod_jug', $cod_jug);
            $stmt->bindParam(':minuto', $minuto);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':cod_gol', $cod_gol);
            $stmt->execute();
            return ['estado' => true, 'mensaje' => 'Gol actualizado correctamente'];
        } catch (PDOException $e) {
            return ['estado' => false, 'mensaje' => 'Error al actualizar el gol: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar un gol
     */
    public function eliminarGol($cod_gol)
    {
        try {
            $sql = "DELETE FROM Goles WHERE cod_gol = :cod_gol";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_gol', $cod_gol);
            $stmt->execute();
            return ['estado' => true, 'mensaje' => 'Gol eliminado correctamente'];
        } catch (PDOException $e) {
            return ['estado' => false, 'mensaje' => 'Error al eliminar el gol: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar una asistencia
     */
    public function actualizarAsistencia($cod_asis, $cod_par, $cod_jug, $minuto)
    {
        try {
            $sql = "UPDATE Asistencias SET cod_par = :cod_par, cod_jug = :cod_jug, minuto = :minuto WHERE cod_asis = :cod_asis";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->bindParam(':cod_jug', $cod_jug);
            $stmt->bindParam(':minuto', $minuto);
            $stmt->bindParam(':cod_asis', $cod_asis);
            $stmt->execute();
            return ['estado' => true, 'mensaje' => 'Asistencia actualizada correctamente'];
        } catch (PDOException $e) {
            return ['estado' => false, 'mensaje' => 'Error al actualizar la asistencia: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar una asistencia
     */
    public function eliminarAsistencia($cod_asis)
    {
        try {
            $sql = "DELETE FROM Asistencias WHERE cod_asis = :cod_asis";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_asis', $cod_asis);
            $stmt->execute();
            return ['estado' => true, 'mensaje' => 'Asistencia eliminada correctamente'];
        } catch (PDOException $e) {
            return ['estado' => false, 'mensaje' => 'Error al eliminar la asistencia: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar una falta
     */
    public function actualizarFalta($cod_falta, $cod_par, $cod_jug, $minuto, $tipo_falta)
    {
        try {
            $sql = "UPDATE Faltas SET cod_par = :cod_par, cod_jug = :cod_jug, minuto = :minuto, tipo_falta = :tipo_falta WHERE cod_falta = :cod_falta";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_par', $cod_par);
            $stmt->bindParam(':cod_jug', $cod_jug);
            $stmt->bindParam(':minuto', $minuto);
            $stmt->bindParam(':tipo_falta', $tipo_falta);
            $stmt->bindParam(':cod_falta', $cod_falta);
            $stmt->execute();
            return ['estado' => true, 'mensaje' => 'Falta actualizada correctamente'];
        } catch (PDOException $e) {
            return ['estado' => false, 'mensaje' => 'Error al actualizar la falta: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar una falta
     */
    public function eliminarFalta($cod_falta)
    {
        try {
            $sql = "DELETE FROM Faltas WHERE cod_falta = :cod_falta";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_falta', $cod_falta);
            $stmt->execute();
            return ['estado' => true, 'mensaje' => 'Falta eliminada correctamente'];
        } catch (PDOException $e) {
            return ['estado' => false, 'mensaje' => 'Error al eliminar la falta: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener partidos donde participa un equipo
     */
    public function obtenerPorEquipo($cod_equ) {
        try {
            $sql = "SELECT p.cod_par, p.fecha, p.hora, p.estado,
                       e1.cod_equ as local_id, e1.nombre as local_nombre, e1.escudo as local_escudo,
                       e2.cod_equ as visitante_id, e2.nombre as visitante_nombre, e2.escudo as visitante_escudo,
                       c.nombre as cancha
                FROM Partidos p
                JOIN Equipos e1 ON p.equ_local = e1.cod_equ
                JOIN Equipos e2 ON p.equ_visitante = e2.cod_equ
                JOIN Canchas c ON p.cod_cancha = c.cod_cancha
                WHERE p.equ_local = :cod_equ OR p.equ_visitante = :cod_equ
                ORDER BY p.fecha, p.hora";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_equ', $cod_equ, PDO::PARAM_INT);
            $stmt->execute();
            
            $partidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar los escudos y añadir conteo de goles para partidos finalizados
            foreach ($partidos as &$partido) {
                // Procesar escudo del equipo local
                if (!empty($partido['local_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['local_escudo'])) {
                        $content = stream_get_contents($partido['local_escudo']);
                        rewind($partido['local_escudo']);
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['local_escudo']);
                    } else {
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['local_escudo']);
                    }
                } else {
                    $partido['local_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['local_escudo']);
                }
                
                // Procesar escudo del equipo visitante
                if (!empty($partido['visitante_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['visitante_escudo'])) {
                        $content = stream_get_contents($partido['visitante_escudo']);
                        rewind($partido['visitante_escudo']);
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['visitante_escudo']);
                    } else {
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['visitante_escudo']);
                    }
                } else {
                    $partido['visitante_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['visitante_escudo']);
                }
                
                // Si el partido está finalizado, añadir conteo de goles
                if ($partido['estado'] === 'finalizado') {
                    $marcador = $this->calcularMarcadorPorDetalle($partido['cod_par'], $partido['local_nombre'], $partido['visitante_nombre']);
                    $partido['goles_local'] = $marcador['goles_local'];
                    $partido['goles_visitante'] = $marcador['goles_visitante'];
                }
                
                // Formatear fecha
                $partido['fecha_formateada'] = date('d/m/Y', strtotime($partido['fecha']));
            }
            
            return $partidos;
        } catch (PDOException $e) {
            error_log('Error al obtener partidos por equipo: ' . $e->getMessage());
            return [];
        }
    }

    public function calcularMarcadorPorDetalle($cod_par, $local_nombre, $visitante_nombre) {
        $sql = "SELECT g.tipo, j.cod_jug, j.nombres, j.apellidos, j.dorsal, e.nombre as equipo
                FROM Goles g
                JOIN Jugadores j ON g.cod_jug = j.cod_jug
                JOIN Equipos e ON j.cod_equ = e.cod_equ
                WHERE g.cod_par = :cod_par";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':cod_par', $cod_par);
        $stmt->execute();
        $goles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $marcadorLocal = 0;
        $marcadorVisitante = 0;
        foreach ($goles as $gol) {
            $equipoJugador = $gol['equipo'];
            if ($gol['tipo'] === 'autogol') {
                if ($equipoJugador === $local_nombre) {
                    $marcadorVisitante++;
                } elseif ($equipoJugador === $visitante_nombre) {
                    $marcadorLocal++;
                }
            } else {
                if ($equipoJugador === $local_nombre) {
                    $marcadorLocal++;
                } elseif ($equipoJugador === $visitante_nombre) {
                    $marcadorVisitante++;
                }
            }
        }
        return [
            'goles_local' => $marcadorLocal,
            'goles_visitante' => $marcadorVisitante
        ];
    }

    /**
     * Contar partidos por fase
     * @param string $fase Fase a contar (cuartos, semis, final)
     * @param int|null $excluir_partido_id ID de partido a excluir del conteo (útil al actualizar)
     * @return int Número de partidos en la fase
     */
    public function contarPartidosPorFase($fase, $excluir_partido_id = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM Partidos WHERE fase = :fase";
            
            // Si se proporciona un ID para excluir, añadirlo a la consulta
            if ($excluir_partido_id !== null) {
                $sql .= " AND cod_par != :excluir_partido_id";
            }
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fase', $fase);
            
            if ($excluir_partido_id !== null) {
                $stmt->bindParam(':excluir_partido_id', $excluir_partido_id, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$resultado['total'];
            
        } catch (PDOException $e) {
            error_log("Error al contar partidos por fase: " . $e->getMessage());
            return 0;
        }
    }
}
?> 
<?php
// Incluir la conexión a la base de datos
require_once __DIR__ . '/../database/connection.php';

class Jugador {
    private $db;
    
    public function __construct() {
        $this->db = Conexion::getConexion();
    }
    
    /**
     * Obtiene todos los jugadores con sus datos básicos
     */
    public function obtenerTodos() {
        try {
            $query = "SELECT j.cod_jug, j.nombres, j.apellidos, j.dorsal, j.posicion, j.foto, j.cod_equ, 
                             e.nombre as nombre_equipo, e.escudo as escudo_equipo 
                     FROM Jugadores j
                     LEFT JOIN Equipos e ON j.cod_equ = e.cod_equ
                     ORDER BY j.cod_jug";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar las fotos y escudos
            foreach ($jugadores as &$jugador) {
                // Procesar la foto del jugador
                $jugador = $this->procesarFotoJugador($jugador);
                
                // Procesar el escudo del equipo
                if (!empty($jugador['escudo_equipo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($jugador['escudo_equipo'])) {
                        $content = stream_get_contents($jugador['escudo_equipo']);
                        rewind($jugador['escudo_equipo']);
                        $jugador['escudo_equipo'] = 'data:image/jpeg;base64,' . base64_encode($content);
                    } else {
                        $jugador['escudo_equipo'] = 'data:image/jpeg;base64,' . base64_encode($jugador['escudo_equipo']);
                    }
                } else {
                    $jugador['escudo_equipo'] = '../assets/images/team.png';
                }
            }
            
            return $jugadores;
        } catch (PDOException $e) {
            throw new Exception("Error al obtener jugadores: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene todos los jugadores con sus estadísticas (goles, asistencias, tarjetas)
     */
    public function obtenerTodosConEstadisticas() {
        try {
            $query = "SELECT j.cod_jug, j.nombres, j.apellidos, j.dorsal, j.posicion, j.foto, j.cod_equ, 
                             e.nombre as nombre_equipo, e.escudo as escudo_equipo 
                     FROM Jugadores j
                     LEFT JOIN Equipos e ON j.cod_equ = e.cod_equ
                     ORDER BY j.cod_jug";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar las fotos y escudos
            foreach ($jugadores as &$jugador) {
                // Procesar la foto del jugador
                $jugador = $this->procesarFotoJugador($jugador);
                
                // Procesar el escudo del equipo
                if (!empty($jugador['escudo_equipo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($jugador['escudo_equipo'])) {
                        $content = stream_get_contents($jugador['escudo_equipo']);
                        rewind($jugador['escudo_equipo']);
                        $jugador['escudo_equipo'] = 'data:image/jpeg;base64,' . base64_encode($content);
                    } else {
                        $jugador['escudo_equipo'] = 'data:image/jpeg;base64,' . base64_encode($jugador['escudo_equipo']);
                    }
                } else {
                    $jugador['escudo_equipo'] = '../assets/images/team.png';
                }
                
                // Obtener estadísticas
                $jugador['goles'] = $this->contarGoles($jugador['cod_jug']);
                $jugador['asistencias'] = $this->contarAsistencias($jugador['cod_jug']);
                $jugador['tarjetas_amarillas'] = $this->contarTarjetas($jugador['cod_jug'], 'amarilla');
                $jugador['tarjetas_rojas'] = $this->contarTarjetas($jugador['cod_jug'], 'roja');
            }
            
            return $jugadores;
        } catch (Exception $e) {
            throw new Exception("Error al obtener jugadores con estadísticas: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene los jugadores de un equipo específico
     */
    public function obtenerPorEquipo($equipoId) {
        try {
            $query = "SELECT j.cod_jug, j.nombres, j.apellidos, j.dorsal, j.posicion, j.foto, j.cod_equ, 
                             e.nombre as nombre_equipo, e.escudo as escudo_equipo 
                     FROM Jugadores j
                     LEFT JOIN Equipos e ON j.cod_equ = e.cod_equ
                     WHERE j.cod_equ = :equipo_id
                     ORDER BY j.cod_jug";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':equipo_id', $equipoId, PDO::PARAM_INT);
            $stmt->execute();
            
            $jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar las fotos y escudos
            foreach ($jugadores as &$jugador) {
                // Procesar la foto del jugador
                $jugador = $this->procesarFotoJugador($jugador);
                
                // Procesar el escudo del equipo
                if (!empty($jugador['escudo_equipo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($jugador['escudo_equipo'])) {
                        $content = stream_get_contents($jugador['escudo_equipo']);
                        rewind($jugador['escudo_equipo']);
                        $jugador['escudo_equipo'] = 'data:image/jpeg;base64,' . base64_encode($content);
                    } else {
                        $jugador['escudo_equipo'] = 'data:image/jpeg;base64,' . base64_encode($jugador['escudo_equipo']);
                    }
                } else {
                    $jugador['escudo_equipo'] = '../assets/images/team.png';
                }
                
                // Obtener estadísticas
                $jugador['goles'] = $this->contarGoles($jugador['cod_jug']);
                $jugador['asistencias'] = $this->contarAsistencias($jugador['cod_jug']);
                $jugador['tarjetas_amarillas'] = $this->contarTarjetas($jugador['cod_jug'], 'amarilla');
                $jugador['tarjetas_rojas'] = $this->contarTarjetas($jugador['cod_jug'], 'roja');
            }
            
            return $jugadores;
        } catch (PDOException $e) {
            throw new Exception("Error al obtener jugadores del equipo: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene un jugador específico por su ID con todos sus detalles
     */
    public function obtenerDetalleCompleto($jugadorId) {
        try {
            // Obtener datos básicos del jugador
            $query = "SELECT j.*, e.nombre as nombre_equipo, e.escudo as escudo_equipo, 
                             e.cod_ciu, c.nombre as ciudad_equipo, dt.nombres as dt_nombres, dt.apellidos as dt_apellidos
                     FROM Jugadores j
                     LEFT JOIN Equipos e ON j.cod_equ = e.cod_equ
                     LEFT JOIN Ciudades c ON e.cod_ciu = c.cod_ciu
                     LEFT JOIN Directores dt ON e.cod_dt = dt.cod_dt
                     WHERE j.cod_jug = :jugador_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            $jugador = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$jugador) {
                return null;
            }
            
            // Procesar la foto del jugador
            $jugador = $this->procesarFotoJugador($jugador);
            
            // Procesar el escudo del equipo
            if (!empty($jugador['escudo_equipo'])) {
                // Verificar si es un recurso o un string
                if (is_resource($jugador['escudo_equipo'])) {
                    $content = stream_get_contents($jugador['escudo_equipo']);
                    rewind($jugador['escudo_equipo']);
                    $jugador['escudo_equipo'] = 'data:image/jpeg;base64,' . base64_encode($content);
                } else {
                    $jugador['escudo_equipo'] = 'data:image/jpeg;base64,' . base64_encode($jugador['escudo_equipo']);
                }
            } else {
                $jugador['escudo_equipo'] = '../assets/images/team.png';
            }
            
            // Obtener las estadísticas del jugador
            $jugador['goles'] = $this->contarGoles($jugadorId);
            $jugador['asistencias'] = $this->contarAsistencias($jugadorId);
            $jugador['tarjetas_amarillas'] = $this->contarTarjetas($jugadorId, 'amarilla');
            $jugador['tarjetas_rojas'] = $this->contarTarjetas($jugadorId, 'roja');
            
            // Obtener los partidos en los que ha participado
            $jugador['partidos'] = $this->obtenerPartidosJugador($jugadorId);
            
            // Obtener detalles de los goles
            $jugador['detalle_goles'] = $this->obtenerDetalleGoles($jugadorId);
            
            // Obtener detalles de las asistencias
            $jugador['detalle_asistencias'] = $this->obtenerDetalleAsistencias($jugadorId);
            
            // Obtener detalles de las faltas
            $jugador['detalle_faltas'] = $this->obtenerDetalleFaltas($jugadorId);
            
            return $jugador;
        } catch (PDOException $e) {
            throw new Exception("Error al obtener detalle del jugador: " . $e->getMessage());
        }
    }
    
    /**
     * Cuenta el número de goles de un jugador
     */
    public function contarGoles($jugadorId) {
        try {
            $query = "SELECT COUNT(*) as total FROM Goles WHERE cod_jug = :jugador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Obtiene el detalle de los goles de un jugador
     */
    public function obtenerDetalleGoles($jugadorId) {
        try {
            $query = "SELECT g.*, p.fecha, p.hora, e_local.nombre as equipo_local, e_visit.nombre as equipo_visitante
                     FROM Goles g
                     JOIN Partidos p ON g.cod_par = p.cod_par
                     JOIN Equipos e_local ON p.equ_local = e_local.cod_equ
                     JOIN Equipos e_visit ON p.equ_visitante = e_visit.cod_equ
                     WHERE g.cod_jug = :jugador_id
                     ORDER BY p.fecha DESC, g.minuto";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Cuenta el número de asistencias de un jugador
     */
    public function contarAsistencias($jugadorId) {
        try {
            $query = "SELECT COUNT(*) as total FROM Asistencias WHERE cod_jug = :jugador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Obtiene el detalle de las asistencias de un jugador
     */
    public function obtenerDetalleAsistencias($jugadorId) {
        try {
            $query = "SELECT a.*, p.fecha, p.hora, e_local.nombre as equipo_local, e_visit.nombre as equipo_visitante
                     FROM Asistencias a
                     JOIN Partidos p ON a.cod_par = p.cod_par
                     JOIN Equipos e_local ON p.equ_local = e_local.cod_equ
                     JOIN Equipos e_visit ON p.equ_visitante = e_visit.cod_equ
                     WHERE a.cod_jug = :jugador_id
                     ORDER BY p.fecha DESC, a.minuto";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Cuenta el número de tarjetas (amarillas o rojas) de un jugador
     */
    public function contarTarjetas($jugadorId, $tipoTarjeta) {
        try {
            $query = "SELECT COUNT(*) as total FROM Faltas 
                     WHERE cod_jug = :jugador_id AND tipo_falta = :tipo_tarjeta";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_tarjeta', $tipoTarjeta, PDO::PARAM_STR);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Obtiene el detalle de las faltas de un jugador
     */
    public function obtenerDetalleFaltas($jugadorId) {
        try {
            $query = "SELECT f.*, p.fecha, p.hora, e_local.nombre as equipo_local, e_visit.nombre as equipo_visitante
                     FROM Faltas f
                     JOIN Partidos p ON f.cod_par = p.cod_par
                     JOIN Equipos e_local ON p.equ_local = e_local.cod_equ
                     JOIN Equipos e_visit ON p.equ_visitante = e_visit.cod_equ
                     WHERE f.cod_jug = :jugador_id
                     ORDER BY p.fecha DESC, f.minuto";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtiene los partidos en los que ha participado un jugador
     */
    public function obtenerPartidosJugador($jugadorId) {
        try {
            $query = "SELECT DISTINCT p.*, 
                            e_local.nombre as local_nombre, 
                            e_visit.nombre as visitante_nombre,
                            e_local.escudo as escudo_local,
                            e_visit.escudo as escudo_visitante,
                            c.nombre as cancha
                     FROM Partidos p
                     JOIN Equipos e_local ON p.equ_local = e_local.cod_equ
                     JOIN Equipos e_visit ON p.equ_visitante = e_visit.cod_equ
                     JOIN Canchas c ON p.cod_cancha = c.cod_cancha
                     LEFT JOIN Goles g ON p.cod_par = g.cod_par
                     LEFT JOIN Asistencias a ON p.cod_par = a.cod_par
                     LEFT JOIN Faltas f ON p.cod_par = f.cod_par
                     WHERE (g.cod_jug = :jugador_id OR a.cod_jug = :jugador_id2 OR f.cod_jug = :jugador_id3)
                     ORDER BY p.fecha DESC, p.hora";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->bindParam(':jugador_id2', $jugadorId, PDO::PARAM_INT);
            $stmt->bindParam(':jugador_id3', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            $partidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar los escudos y añadir conteo de goles para partidos finalizados
            foreach ($partidos as &$partido) {
                // Procesar escudos
                if ($partido['escudo_local']) {
                    $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['escudo_local']);
                } else {
                    // Usar una imagen base64 por defecto en lugar de una ruta relativa
                    $default_image_path = __DIR__ . '/../../frontend/assets/images/team.png';
                    if (file_exists($default_image_path)) {
                        $partido['local_escudo_base64'] = 'data:image/png;base64,' . base64_encode(file_get_contents($default_image_path));
                    } else {
                        // Si no existe el archivo, usamos una URL absoluta como fallback
                        $partido['local_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    }
                }
                
                if ($partido['escudo_visitante']) {
                    $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['escudo_visitante']);
                } else {
                    // Usar una imagen base64 por defecto en lugar de una ruta relativa
                    $default_image_path = __DIR__ . '/../../frontend/assets/images/team.png';
                    if (file_exists($default_image_path)) {
                        $partido['visitante_escudo_base64'] = 'data:image/png;base64,' . base64_encode(file_get_contents($default_image_path));
                    } else {
                        // Si no existe el archivo, usamos una URL absoluta como fallback
                        $partido['visitante_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    }
                }
                
                // Formatear fecha
                $partido['fecha_formateada'] = date('d/m/Y', strtotime($partido['fecha']));
                
                // Si el partido está finalizado, añadir conteo de goles
                if ($partido['estado'] === 'finalizado') {
                    $partido['goles_local'] = $this->contarGolesEquipo($partido['cod_par'], $partido['equ_local']);
                    $partido['goles_visitante'] = $this->contarGolesEquipo($partido['cod_par'], $partido['equ_visitante']);
                }
            }
            
            return $partidos;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Cuenta los goles de un equipo en un partido específico
     */
    private function contarGolesEquipo($partidoId, $equipoId) {
        try {
            $query = "SELECT COUNT(*) as total 
                     FROM Goles g
                     JOIN Jugadores j ON g.cod_jug = j.cod_jug
                     WHERE g.cod_par = :partido_id AND j.cod_equ = :equipo_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':partido_id', $partidoId, PDO::PARAM_INT);
            $stmt->bindParam(':equipo_id', $equipoId, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Obtiene al jugador con más goles (goleador del torneo)
     */
    public function obtenerGoleador() {
        try {
            $query = "SELECT j.cod_jug, j.nombres, j.apellidos, j.dorsal, j.posicion, j.foto,
                           e.nombre as nombre_equipo, e.escudo as escudo_equipo,
                           COUNT(g.cod_gol) as total_goles
                     FROM Jugadores j
                     JOIN Equipos e ON j.cod_equ = e.cod_equ
                     JOIN Goles g ON j.cod_jug = g.cod_jug
                     WHERE g.tipo IN ('normal', 'penal')
                     GROUP BY j.cod_jug, j.nombres, j.apellidos, j.dorsal, j.posicion, j.foto, e.nombre, e.escudo
                     ORDER BY total_goles DESC
                     LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $goleador = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$goleador) {
                return null;
            }
            
            // Procesar la foto del jugador
            $goleador = $this->procesarFotoJugador($goleador);
            
            // Procesar el escudo del equipo
            if ($goleador['escudo_equipo']) {
                $goleador['escudo_equipo_base64'] = 'data:image/jpeg;base64,' . base64_encode($goleador['escudo_equipo']);
            } else {
                // Usar ruta absoluta para la imagen por defecto
                $goleador['escudo_equipo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
            }
            
            // Añadir el campo 'goles' para que coincida con lo que espera el frontend
            $goleador['goles'] = $goleador['total_goles'];
            
            return $goleador;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Obtiene al jugador con más asistencias
     */
    public function obtenerMaximoAsistidor() {
        try {
            $query = "SELECT j.cod_jug, j.nombres, j.apellidos, j.dorsal, j.posicion, j.foto,
                           e.nombre as nombre_equipo, e.escudo as escudo_equipo,
                           COUNT(a.cod_asis) as total_asistencias
                     FROM Jugadores j
                     JOIN Equipos e ON j.cod_equ = e.cod_equ
                     JOIN Asistencias a ON j.cod_jug = a.cod_jug
                     GROUP BY j.cod_jug, j.nombres, j.apellidos, j.dorsal, j.posicion, j.foto, e.nombre, e.escudo
                     ORDER BY total_asistencias DESC
                     LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $asistidor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$asistidor) {
                return null;
            }
            
            // Procesar la foto del jugador
            $asistidor = $this->procesarFotoJugador($asistidor);
            
            // Procesar el escudo del equipo
            if ($asistidor['escudo_equipo']) {
                $asistidor['escudo_equipo_base64'] = 'data:image/jpeg;base64,' . base64_encode($asistidor['escudo_equipo']);
            } else {
                // Usar ruta absoluta para la imagen por defecto
                $asistidor['escudo_equipo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
            }
            
            // Añadir el campo 'asistencias' para que coincida con lo que espera el frontend
            $asistidor['asistencias'] = $asistidor['total_asistencias'];
            
            return $asistidor;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Crea un nuevo jugador en la base de datos
     */
    public function crear($datos) {
        try {
            // Verificar si el número de camiseta ya está asignado a otro jugador en el mismo equipo
            $query = "SELECT * FROM Jugadores WHERE dorsal = :dorsal AND cod_equ = :cod_equ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dorsal', $datos['dorsal'], PDO::PARAM_INT);
            $stmt->bindParam(':cod_equ', $datos['cod_equ'], PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $jugadorExistente = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'estado' => false,
                    'mensaje' => 'El número de camiseta ' . $datos['dorsal'] . ' ya está asignado a otro jugador en el mismo equipo: ' . $jugadorExistente['nombres'] . ' ' . $jugadorExistente['apellidos']
                ];
            }
            
            // Verificar si ya existe un jugador con el mismo nombre y apellido en el equipo
            $query = "SELECT * FROM Jugadores WHERE nombres = :nombres AND apellidos = :apellidos AND cod_equ = :cod_equ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombres', $datos['nombres']);
            $stmt->bindParam(':apellidos', $datos['apellidos']);
            $stmt->bindParam(':cod_equ', $datos['cod_equ'], PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'Ya existe un jugador con el mismo nombre y apellido en este equipo'
                ];
            }
            
            // Preparar la consulta SQL
            $query = "INSERT INTO Jugadores (nombres, apellidos, posicion, dorsal, cod_equ, foto) 
                     VALUES (:nombres, :apellidos, :posicion, :dorsal, :cod_equ, :foto)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombres', $datos['nombres']);
            $stmt->bindParam(':apellidos', $datos['apellidos']);
            $stmt->bindParam(':posicion', $datos['posicion']);
            $stmt->bindParam(':dorsal', $datos['dorsal'], PDO::PARAM_INT);
            $stmt->bindParam(':cod_equ', $datos['cod_equ'], PDO::PARAM_INT);
            $stmt->bindParam(':foto', $datos['foto'], PDO::PARAM_LOB);
            $stmt->execute();
            $jugadorId = $this->db->lastInsertId();
            
            if (!$jugadorId) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se pudo crear el jugador. Por favor, inténtelo de nuevo más tarde.'
                ];
            }
            
            return [
                'estado' => true,
                'mensaje' => 'Jugador creado correctamente',
                'id' => $jugadorId
            ];
        } catch (PDOException $e) {
            $errorMessage = 'Error al crear el jugador';
            
            // Verificar el tipo de error específico
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'dorsal') !== false) {
                    $errorMessage = 'El número de camiseta ya está siendo utilizado por otro jugador en el mismo equipo';
                } else {
                    $errorMessage = 'Ya existe un jugador con datos similares';
                }
            } else if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                $errorMessage = 'El equipo seleccionado no existe o no está disponible';
            }
            
            return [
                'estado' => false,
                'mensaje' => $errorMessage
            ];
        }
    }
    
    /**
     * Actualiza un jugador existente en la base de datos
     */
    public function actualizar($datos) {
        try {
            // Verificar primero si el jugador existe
            $query = "SELECT * FROM Jugadores WHERE cod_jug = :cod_jug";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':cod_jug', $datos['cod_jug'], PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se pudo actualizar porque el jugador no existe o ya fue eliminado'
                ];
            }
            
            // Verificar si el número de camiseta ya está asignado a otro jugador en el mismo equipo
            $query = "SELECT * FROM Jugadores WHERE dorsal = :dorsal AND cod_equ = :cod_equ AND cod_jug != :cod_jug";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dorsal', $datos['dorsal'], PDO::PARAM_INT);
            $stmt->bindParam(':cod_equ', $datos['cod_equ'], PDO::PARAM_INT);
            $stmt->bindParam(':cod_jug', $datos['cod_jug'], PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $jugadorExistente = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'estado' => false,
                    'mensaje' => 'El número de camiseta ' . $datos['dorsal'] . ' ya está asignado a otro jugador en el mismo equipo: ' . $jugadorExistente['nombres'] . ' ' . $jugadorExistente['apellidos']
                ];
            }
            
            if ($datos['actualizar_foto']) {
                $query = "UPDATE Jugadores SET nombres = :nombres, apellidos = :apellidos, 
                         posicion = :posicion, dorsal = :dorsal, cod_equ = :cod_equ, foto = :foto 
                         WHERE cod_jug = :cod_jug";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':foto', $datos['foto'], PDO::PARAM_LOB);
            } else {
                $query = "UPDATE Jugadores SET nombres = :nombres, apellidos = :apellidos, 
                         posicion = :posicion, dorsal = :dorsal, cod_equ = :cod_equ 
                         WHERE cod_jug = :cod_jug";
                $stmt = $this->db->prepare($query);
            }
            $stmt->bindParam(':nombres', $datos['nombres']);
            $stmt->bindParam(':apellidos', $datos['apellidos']);
            $stmt->bindParam(':posicion', $datos['posicion']);
            $stmt->bindParam(':dorsal', $datos['dorsal'], PDO::PARAM_INT);
            $stmt->bindParam(':cod_equ', $datos['cod_equ'], PDO::PARAM_INT);
            $stmt->bindParam(':cod_jug', $datos['cod_jug'], PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return [
                    'estado' => true,
                    'mensaje' => 'No se detectaron cambios en los datos del jugador'
                ];
            }
            
            return [
                'estado' => true,
                'mensaje' => 'Jugador actualizado correctamente'
            ];
        } catch (PDOException $e) {
            $errorMessage = 'Error al actualizar el jugador';
            
            // Verificar el tipo de error específico
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'dorsal') !== false) {
                    $errorMessage = 'El número de camiseta ya está siendo utilizado por otro jugador en el mismo equipo';
                } else {
                    $errorMessage = 'Existe un conflicto con datos duplicados';
                }
            } else if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                $errorMessage = 'El equipo seleccionado no existe o no está disponible';
            }
            
            return [
                'estado' => false,
                'mensaje' => $errorMessage
            ];
        }
    }
    
    /**
     * Elimina un jugador de la base de datos
     */
    public function eliminar($jugadorId) {
        try {
            // Primero verificamos si el jugador existe
            $query = "SELECT * FROM Jugadores WHERE cod_jug = :jugador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se pudo eliminar porque el jugador no existe o ya fue eliminado'
                ];
            }
            
            // Verificar si el jugador tiene registros relacionados
            // Verificar goles
            $query = "SELECT COUNT(*) as total FROM Goles WHERE cod_jug = :jugador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            $goles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Verificar asistencias
            $query = "SELECT COUNT(*) as total FROM Asistencias WHERE cod_jug = :jugador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            $asistencias = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Verificar faltas
            $query = "SELECT COUNT(*) as total FROM Faltas WHERE cod_jug = :jugador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            $faltas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($goles > 0 || $asistencias > 0 || $faltas > 0) {
                $detalles = [];
                if ($goles > 0) $detalles[] = $goles . ' gol(es)';
                if ($asistencias > 0) $detalles[] = $asistencias . ' asistencia(s)';
                if ($faltas > 0) $detalles[] = $faltas . ' tarjeta(s)';
                
                return [
                    'estado' => false,
                    'mensaje' => 'No se puede eliminar el jugador porque tiene registros asociados: ' . implode(', ', $detalles) . '. Elimine primero estos registros o considere inactivar al jugador en lugar de eliminarlo.'
                ];
            }
            
            // Eliminar el jugador
            $query = "DELETE FROM Jugadores WHERE cod_jug = :jugador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se pudo eliminar el jugador. Por favor, inténtelo de nuevo más tarde.'
                ];
            }
            
            return [
                'estado' => true,
                'mensaje' => 'Jugador eliminado correctamente'
            ];
        } catch (PDOException $e) {
            $errorMessage = 'Error al eliminar el jugador';
            
            // Verificar si es error de clave foránea
            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                $errorMessage = 'No se puede eliminar el jugador porque está siendo utilizado en otros registros del sistema';
            }
            
            return [
                'estado' => false,
                'mensaje' => $errorMessage
            ];
        }
    }
    
    /**
     * Procesa la foto de un jugador (codificación base64)
     */
    private function procesarFotoJugador($jugador)
    {
        if (!empty($jugador) && array_key_exists('foto', $jugador)) {
            if (!empty($jugador['foto'])) {
                // Verificar si es un recurso o un string
                if (is_resource($jugador['foto'])) {
                    $content = stream_get_contents($jugador['foto']);
                    rewind($jugador['foto']);
                    $jugador['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                } else {
                    $jugador['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($jugador['foto']);
                }
            } else {
                // Usar una ruta absoluta para la imagen por defecto
                $jugador['foto_base64'] = '/PROYECTO/frontend/assets/images/player.png';
            }
        }
        return $jugador;
    }
    
    /**
     * Eliminar la foto de un jugador
     */
    public function eliminarFoto($jugadorId)
    {
        try {
            // Verificar si el jugador existe
            $query = "SELECT * FROM Jugadores WHERE cod_jug = :jugador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se pudo eliminar la foto porque el jugador no existe o ya fue eliminado'
                ];
            }
            
            // Verificar si el jugador tiene foto
            $jugador = $stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($jugador['foto'])) {
                return [
                    'estado' => false,
                    'mensaje' => 'El jugador no tiene foto asignada actualmente'
                ];
            }
            
            // Establecer la foto como NULL
            $query = "UPDATE Jugadores SET foto = NULL WHERE cod_jug = :jugador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':jugador_id', $jugadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se pudo eliminar la foto. Por favor, inténtelo de nuevo más tarde.'
                ];
            }
            
            return [
                'estado' => true,
                'mensaje' => 'Foto del jugador eliminada correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al eliminar la foto: ocurrió un problema con la base de datos'
            ];
        }
    }
} 
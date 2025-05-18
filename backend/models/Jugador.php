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
            $query = "SELECT j.*, e.nombre as nombre_equipo, e.escudo as escudo_equipo 
                     FROM Jugadores j
                     LEFT JOIN Equipos e ON j.cod_equ = e.cod_equ
                     ORDER BY j.apellidos, j.nombres";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar las fotos y escudos
            foreach ($jugadores as &$jugador) {
                // Procesar la foto del jugador
                if ($jugador['foto']) {
                    $jugador['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($jugador['foto']);
                } else {
                    $jugador['foto_base64'] = '../assets/images/player.png';
                }
                
                // Procesar el escudo del equipo
                if ($jugador['escudo_equipo']) {
                    $jugador['escudo_equipo'] = 'data:image/jpeg;base64,' . base64_encode($jugador['escudo_equipo']);
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
            // Primero obtenemos todos los jugadores con sus datos básicos
            $jugadores = $this->obtenerTodos();
            
            // Para cada jugador, obtenemos sus estadísticas
            foreach ($jugadores as &$jugador) {
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
            $query = "SELECT j.*, e.nombre as nombre_equipo, e.escudo as escudo_equipo 
                     FROM Jugadores j
                     LEFT JOIN Equipos e ON j.cod_equ = e.cod_equ
                     WHERE j.cod_equ = :equipo_id
                     ORDER BY j.apellidos, j.nombres";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':equipo_id', $equipoId, PDO::PARAM_INT);
            $stmt->execute();
            
            $jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar las fotos y escudos
            foreach ($jugadores as &$jugador) {
                // Procesar la foto del jugador
                if ($jugador['foto']) {
                    $jugador['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($jugador['foto']);
                } else {
                    $jugador['foto_base64'] = '../assets/images/player.png';
                }
                
                // Procesar el escudo del equipo
                if ($jugador['escudo_equipo']) {
                    $jugador['escudo_equipo'] = 'data:image/jpeg;base64,' . base64_encode($jugador['escudo_equipo']);
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
            if ($jugador['foto']) {
                $jugador['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($jugador['foto']);
            } else {
                $jugador['foto_base64'] = '../assets/images/player.png';
            }
            
            // Procesar el escudo del equipo
            if ($jugador['escudo_equipo']) {
                $jugador['escudo_equipo'] = 'data:image/jpeg;base64,' . base64_encode($jugador['escudo_equipo']);
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
                            e_local.nombre as equipo_local, 
                            e_visit.nombre as equipo_visitante,
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
                    $partido['escudo_local_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['escudo_local']);
                } else {
                    $partido['escudo_local_base64'] = '../assets/images/team.png';
                }
                
                if ($partido['escudo_visitante']) {
                    $partido['escudo_visitante_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['escudo_visitante']);
                } else {
                    $partido['escudo_visitante_base64'] = '../assets/images/team.png';
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
            if ($goleador['foto']) {
                $goleador['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($goleador['foto']);
            } else {
                $goleador['foto_base64'] = './frontend/assets/images/player.png';
            }
            
            // Procesar el escudo del equipo
            if ($goleador['escudo_equipo']) {
                $goleador['escudo_equipo_base64'] = 'data:image/jpeg;base64,' . base64_encode($goleador['escudo_equipo']);
            } else {
                $goleador['escudo_equipo_base64'] = './frontend/assets/images/team.png';
            }
            
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
            if ($asistidor['foto']) {
                $asistidor['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($asistidor['foto']);
            } else {
                $asistidor['foto_base64'] = './frontend/assets/images/player.png';
            }
            
            // Procesar el escudo del equipo
            if ($asistidor['escudo_equipo']) {
                $asistidor['escudo_equipo_base64'] = 'data:image/jpeg;base64,' . base64_encode($asistidor['escudo_equipo']);
            } else {
                $asistidor['escudo_equipo_base64'] = './frontend/assets/images/team.png';
            }
            
            return $asistidor;
        } catch (PDOException $e) {
            return null;
        }
    }
} 
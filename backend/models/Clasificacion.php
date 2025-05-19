<?php
// Incluir la conexión a la base de datos
require_once __DIR__ . '/../database/connection.php';

/**
 * Clase Clasificacion
 * 
 * Maneja todas las operaciones relacionadas con la clasificación de equipos a fases eliminatorias
 */
class Clasificacion {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Obtener la conexión a la base de datos
        $this->db = Conexion::getConexion();
    }
    
    /**
     * Obtener todas las clasificaciones
     */
    public function obtenerTodas() {
        try {
            $query = "SELECT fe.*, e.nombre as nombre_equipo, e.escudo 
                     FROM faseequipo fe
                     JOIN equipos e ON fe.cod_equ = e.cod_equ
                     ORDER BY 
                        CASE 
                            WHEN fe.fase = 'cuartos' THEN 1
                            WHEN fe.fase = 'semis' THEN 2
                            WHEN fe.fase = 'final' THEN 3
                            ELSE 4
                        END, 
                        fe.posicion";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $clasificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar las imágenes de los escudos
            foreach ($clasificaciones as &$clasificacion) {
                if (!empty($clasificacion['escudo'])) {
                    $clasificacion['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($clasificacion['escudo']);
                } else {
                    $clasificacion['escudo_base64'] = '../../assets/images/team.png';
                }
            }
            
            return $clasificaciones;
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener clasificaciones por fase
     */
    public function obtenerPorFase($fase) {
        try {
            $query = "SELECT fe.*, e.nombre as nombre_equipo, e.escudo 
                     FROM faseequipo fe
                     JOIN equipos e ON fe.cod_equ = e.cod_equ
                     WHERE fe.fase = :fase
                     ORDER BY fe.posicion";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':fase', $fase, PDO::PARAM_STR);
            $stmt->execute();
            
            $clasificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar las imágenes de los escudos
            foreach ($clasificaciones as &$clasificacion) {
                if (!empty($clasificacion['escudo'])) {
                    $clasificacion['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($clasificacion['escudo']);
                } else {
                    $clasificacion['escudo_base64'] = '../../assets/images/team.png';
                }
            }
            
            return $clasificaciones;
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener una clasificación por ID
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT fe.*, e.nombre as nombre_equipo, e.escudo 
                     FROM faseequipo fe
                     JOIN equipos e ON fe.cod_equ = e.cod_equ
                     WHERE fe.id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $clasificacion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Procesar la imagen del escudo
            if ($clasificacion && !empty($clasificacion['escudo'])) {
                $clasificacion['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($clasificacion['escudo']);
            } else if ($clasificacion) {
                $clasificacion['escudo_base64'] = '../../assets/images/team.png';
            }
            
            return $clasificacion;
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Obtener partidos por fase
     */
    public function obtenerPartidosPorFase($fase) {
        try {
            $query = "SELECT p.*, 
                     e1.nombre as local_nombre, e1.escudo as local_escudo,
                     e2.nombre as visitante_nombre, e2.escudo as visitante_escudo,
                     c.nombre as cancha_nombre
                     FROM partidos p
                     JOIN equipos e1 ON p.cod_equ_local = e1.cod_equ
                     JOIN equipos e2 ON p.cod_equ_visitante = e2.cod_equ
                     LEFT JOIN canchas c ON p.cod_cancha = c.cod_cancha
                     WHERE p.fase = :fase
                     ORDER BY p.fecha, p.hora";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':fase', $fase, PDO::PARAM_STR);
            $stmt->execute();
            
            $partidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar las imágenes de los escudos
            foreach ($partidos as &$partido) {
                if (!empty($partido['local_escudo'])) {
                    $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']);
                } else {
                    $partido['local_escudo_base64'] = '../../assets/images/team.png';
                }
                
                if (!empty($partido['visitante_escudo'])) {
                    $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']);
                } else {
                    $partido['visitante_escudo_base64'] = '../../assets/images/team.png';
                }
            }
            
            return $partidos;
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Crear una nueva clasificación
     */
    public function crear($datos) {
        try {
            // Verificar si ya existe una clasificación para este equipo en esta fase
            $existeQuery = "SELECT COUNT(*) FROM faseequipo 
                          WHERE cod_equ = :cod_equ AND fase = :fase";
            
            $existeStmt = $this->db->prepare($existeQuery);
            $existeStmt->bindParam(':cod_equ', $datos['cod_equ'], PDO::PARAM_INT);
            $existeStmt->bindParam(':fase', $datos['fase'], PDO::PARAM_STR);
            $existeStmt->execute();
            
            if ($existeStmt->fetchColumn() > 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'Este equipo ya está clasificado para esta fase'
                ];
            }
            
            // Insertar la nueva clasificación
            $query = "INSERT INTO faseequipo (cod_equ, fase, posicion, fecha_clasificacion, comentario) 
                     VALUES (:cod_equ, :fase, :posicion, :fecha_clasificacion, :comentario)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':cod_equ', $datos['cod_equ'], PDO::PARAM_INT);
            $stmt->bindParam(':fase', $datos['fase'], PDO::PARAM_STR);
            $stmt->bindParam(':posicion', $datos['posicion'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_clasificacion', $datos['fecha_clasificacion']);
            $stmt->bindParam(':comentario', $datos['comentario']);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Clasificación creada correctamente',
                'id' => $this->db->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al crear la clasificación: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar una clasificación existente
     */
    public function actualizar($datos) {
        try {
            // Verificar si ya existe otra clasificación para este equipo en esta fase
            $existeQuery = "SELECT COUNT(*) FROM faseequipo 
                          WHERE cod_equ = :cod_equ AND fase = :fase AND id != :id";
            
            $existeStmt = $this->db->prepare($existeQuery);
            $existeStmt->bindParam(':cod_equ', $datos['cod_equ'], PDO::PARAM_INT);
            $existeStmt->bindParam(':fase', $datos['fase'], PDO::PARAM_STR);
            $existeStmt->bindParam(':id', $datos['id'], PDO::PARAM_INT);
            $existeStmt->execute();
            
            if ($existeStmt->fetchColumn() > 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'Este equipo ya está clasificado para esta fase'
                ];
            }
            
            // Actualizar la clasificación
            $query = "UPDATE faseequipo 
                     SET cod_equ = :cod_equ, fase = :fase, posicion = :posicion, 
                         fecha_clasificacion = :fecha_clasificacion, comentario = :comentario 
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $datos['id'], PDO::PARAM_INT);
            $stmt->bindParam(':cod_equ', $datos['cod_equ'], PDO::PARAM_INT);
            $stmt->bindParam(':fase', $datos['fase'], PDO::PARAM_STR);
            $stmt->bindParam(':posicion', $datos['posicion'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_clasificacion', $datos['fecha_clasificacion']);
            $stmt->bindParam(':comentario', $datos['comentario']);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Clasificación actualizada correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al actualizar la clasificación: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar una clasificación
     */
    public function eliminar($id) {
        try {
            $query = "DELETE FROM faseequipo WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Clasificación eliminada correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al eliminar la clasificación: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener fases disponibles
     */
    public function obtenerFasesDisponibles() {
        return [
            'cuartos' => 'Cuartos de Final',
            'semis' => 'Semifinales',
            'final' => 'Final'
        ];
    }
    
    /**
     * Obtener estructura del torneo
     */
    public function obtenerEstructuraTorneo() {
        $estructuraTorneo = [
            'cuartos' => $this->obtenerPorFase('cuartos'),
            'semis' => $this->obtenerPorFase('semis'),
            'final' => $this->obtenerPorFase('final')
        ];
        
        $partidosTorneo = [
            'cuartos' => $this->obtenerPartidosPorFase('cuartos'),
            'semis' => $this->obtenerPartidosPorFase('semis'),
            'final' => $this->obtenerPartidosPorFase('final')
        ];
        
        return [
            'equipos' => $estructuraTorneo,
            'partidos' => $partidosTorneo
        ];
    }
}
?> 
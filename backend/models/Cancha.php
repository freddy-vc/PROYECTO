<?php
// Incluir la conexión a la base de datos
require_once __DIR__ . '/../database/connection.php';

/**
 * Clase Cancha
 * 
 * Maneja todas las operaciones relacionadas con canchas de juego
 */
class Cancha {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Obtener la conexión a la base de datos
        $this->db = Conexion::getConexion();
    }
    
    /**
     * Obtener todas las canchas
     */
    public function obtenerTodas() {
        try {
            $query = "SELECT * FROM canchas ORDER BY nombre";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener una cancha por su ID
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM canchas WHERE cod_cancha = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Crear una nueva cancha
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO canchas (nombre, direccion, capacidad) 
                     VALUES (:nombre, :direccion, :capacidad)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':direccion', $datos['direccion']);
            $stmt->bindParam(':capacidad', $datos['capacidad'], PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Cancha creada correctamente',
                'id' => $this->db->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al crear la cancha: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar una cancha existente
     */
    public function actualizar($datos) {
        try {
            $query = "UPDATE canchas 
                     SET nombre = :nombre, direccion = :direccion, capacidad = :capacidad
                     WHERE cod_cancha = :id";
                     
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':direccion', $datos['direccion']);
            $stmt->bindParam(':capacidad', $datos['capacidad'], PDO::PARAM_INT);
            $stmt->bindParam(':id', $datos['cod_cancha'], PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Cancha actualizada correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al actualizar la cancha: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar una cancha
     */
    public function eliminar($id) {
        try {
            // Verificar si hay partidos asociados a la cancha
            $query_partidos = "SELECT COUNT(*) FROM partidos WHERE cod_cancha = :id";
            $stmt_partidos = $this->db->prepare($query_partidos);
            $stmt_partidos->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_partidos->execute();
            
            if ($stmt_partidos->fetchColumn() > 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se puede eliminar la cancha porque tiene partidos asociados'
                ];
            }
            
            // Eliminar la cancha
            $query = "DELETE FROM canchas WHERE cod_cancha = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Cancha eliminada correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al eliminar la cancha: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener canchas por ciudad
     */
    public function obtenerPorCiudad($ciudadId) {
        try {
            $query = "SELECT c.*, ci.nombre as ciudad_nombre 
                     FROM Canchas c
                     LEFT JOIN Ciudades ci ON c.cod_ciu = ci.cod_ciu
                     WHERE c.cod_ciu = :ciudad_id
                     ORDER BY c.nombre";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':ciudad_id', $ciudadId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Procesar las imágenes de las canchas para mostrarlas en frontend
     */
    public function procesarImagenes($canchas) {
        if (!is_array($canchas)) {
            return $canchas;
        }
        
        foreach ($canchas as &$cancha) {
            // Usar una imagen por defecto para todas las canchas
            $cancha['foto_base64'] = '../../assets/images/field.png';
        }
        
        return $canchas;
    }
} 
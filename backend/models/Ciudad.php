<?php
// Incluir la conexión a la base de datos
require_once __DIR__ . '/../database/connection.php';

/**
 * Clase Ciudad
 * 
 * Maneja todas las operaciones relacionadas con ciudades
 */
class Ciudad {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Obtener la conexión a la base de datos
        $this->db = Conexion::getConexion();
    }
    
    /**
     * Obtener todas las ciudades
     */
    public function obtenerTodas() {
        try {
            $query = "SELECT * FROM Ciudades ORDER BY nombre";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener una ciudad por su ID
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM Ciudades WHERE cod_ciu = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Crear una nueva ciudad
     */
    public function crear($nombre) {
        try {
            $query = "INSERT INTO Ciudades (nombre) VALUES (:nombre)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Ciudad creada correctamente',
                'id' => $this->db->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al crear la ciudad: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar una ciudad existente
     */
    public function actualizar($id, $nombre) {
        try {
            $query = "UPDATE Ciudades SET nombre = :nombre WHERE cod_ciu = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Ciudad actualizada correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al actualizar la ciudad: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar una ciudad
     */
    public function eliminar($id) {
        try {
            // Verificar si hay equipos asociados a la ciudad
            $query_equipos = "SELECT COUNT(*) FROM Equipos WHERE cod_ciu = :id";
            $stmt_equipos = $this->db->prepare($query_equipos);
            $stmt_equipos->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_equipos->execute();
            
            if ($stmt_equipos->fetchColumn() > 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se puede eliminar la ciudad porque tiene equipos asociados'
                ];
            }
            
            // Eliminar la ciudad
            $query = "DELETE FROM Ciudades WHERE cod_ciu = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Ciudad eliminada correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al eliminar la ciudad: ' . $e->getMessage()
            ];
        }
    }
} 
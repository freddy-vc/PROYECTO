<?php
// Incluir la conexión a la base de datos
require_once __DIR__ . '/../database/connection.php';

/**
 * Clase Director
 * 
 * Maneja todas las operaciones relacionadas con directores técnicos
 */
class Director {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Obtener la conexión a la base de datos
        $this->db = Conexion::getConexion();
    }
    
    /**
     * Obtener todos los directores técnicos
     */
    public function obtenerTodos() {
        try {
            $query = "SELECT * FROM Directores ORDER BY cod_dt";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener un director técnico por su ID
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM Directores WHERE cod_dt = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Crear un nuevo director técnico
     */
    public function crear($nombres, $apellidos) {
        try {
            $query = "INSERT INTO Directores (nombres, apellidos) VALUES (:nombres, :apellidos)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombres', $nombres);
            $stmt->bindParam(':apellidos', $apellidos);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Director técnico creado correctamente',
                'id' => $this->db->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al crear el director técnico: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar un director técnico existente
     */
    public function actualizar($id, $nombres, $apellidos) {
        try {
            $query = "UPDATE Directores SET nombres = :nombres, apellidos = :apellidos WHERE cod_dt = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombres', $nombres);
            $stmt->bindParam(':apellidos', $apellidos);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Director técnico actualizado correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al actualizar el director técnico: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar un director técnico
     */
    public function eliminar($id) {
        try {
            // Verificar si hay equipos asociados al director técnico
            $query_equipos = "SELECT COUNT(*) FROM Equipos WHERE cod_dt = :id";
            $stmt_equipos = $this->db->prepare($query_equipos);
            $stmt_equipos->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_equipos->execute();
            
            if ($stmt_equipos->fetchColumn() > 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se puede eliminar el director técnico porque tiene equipos asociados'
                ];
            }
            
            // Eliminar el director técnico
            $query = "DELETE FROM Directores WHERE cod_dt = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Director técnico eliminado correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al eliminar el director técnico: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener equipos dirigidos por un director técnico
     */
    public function obtenerEquipos($id = null) {
        try {
            $query = "SELECT e.* FROM Equipos e";
            $params = [];
            
            if ($id !== null) {
                $query .= " WHERE e.cod_dt = :id";
                $params[':id'] = $id;
            }
            
            $query .= " ORDER BY e.nombre";
            
            $stmt = $this->db->prepare($query);
            
            if (!empty($params)) {
                foreach ($params as $param => $value) {
                    $stmt->bindValue($param, $value, PDO::PARAM_INT);
                }
            }
            
            $stmt->execute();
            
            $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar los escudos para mostrarlos como imágenes
            foreach ($equipos as &$equipo) {
                if (!empty($equipo['escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($equipo['escudo'])) {
                        $content = stream_get_contents($equipo['escudo']);
                        rewind($equipo['escudo']);
                        $equipo['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($equipo['escudo']);
                    } else {
                        $equipo['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($equipo['escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($equipo['escudo']);
                    }
                } else {
                    $equipo['escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($equipo['escudo']);
                }
            }
            
            return $equipos;
            
        } catch (PDOException $e) {
            return [];
        }
    }
} 
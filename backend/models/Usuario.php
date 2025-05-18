<?php
require_once __DIR__ . '/../database/connection.php';

/**
 * Clase Usuario
 * 
 * Maneja todas las operaciones relacionadas con usuarios
 */
class Usuario
{
    // Propiedades que corresponden a los campos de la tabla Usuarios
    private $id_usuario;
    private $nombre;
    private $email;
    private $contraseña;
    private $rol;
    private $foto_perfil;
    
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
     * Registrar un nuevo usuario
     */
    public function registrar($nombre, $email, $contraseña, $foto_perfil = null)
    {
        try {
            // Verificar si el email ya está registrado
            if ($this->emailExiste($email)) {
                return [
                    'estado' => false,
                    'mensaje' => 'El correo electrónico ya está registrado'
                ];
            }
            
            // Hashear la contraseña
            $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);
            
            // Preparar la consulta SQL
            $sql = "INSERT INTO Usuarios (nombre, email, contraseña, rol, foto_perfil) 
                    VALUES (:nombre, :email, :contraseña, :rol, :foto_perfil)";
            
            $stmt = $this->conexion->prepare($sql);
            
            // Asignar valores a los parámetros
            $rol = 'usuario'; // Por defecto todos son usuarios normales
            
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':contraseña', $contraseña_hash);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':foto_perfil', $foto_perfil, PDO::PARAM_LOB);
            
            // Ejecutar la consulta
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Usuario registrado correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al registrar usuario: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar si un email ya está registrado
     */
    public function emailExiste($email)
    {
        try {
            $sql = "SELECT COUNT(*) FROM Usuarios WHERE email = :email";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Iniciar sesión de usuario
     */
    public function login($email, $contraseña)
    {
        try {
            $sql = "SELECT * FROM Usuarios WHERE email = :email";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si existe el usuario y la contraseña es correcta
            if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
                return [
                    'estado' => true,
                    'usuario' => $usuario
                ];
            } else {
                return [
                    'estado' => false,
                    'mensaje' => 'Correo electrónico o contraseña incorrectos'
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al iniciar sesión: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener un usuario por su ID
     */
    public function obtenerPorId($id_usuario)
    {
        try {
            $sql = "SELECT id_usuario, nombre, email, rol, foto_perfil FROM Usuarios WHERE id_usuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Actualizar datos del usuario
     */
    public function actualizar($id_usuario, $nombre, $email, $foto_perfil = null)
    {
        try {
            // Verificar si estamos actualizando el email y si ya existe
            $usuario = $this->obtenerPorId($id_usuario);
            
            if ($usuario['email'] !== $email && $this->emailExiste($email)) {
                return [
                    'estado' => false,
                    'mensaje' => 'El correo electrónico ya está registrado por otro usuario'
                ];
            }
            
            // SQL base
            $sql = "UPDATE Usuarios SET nombre = :nombre, email = :email";
            $params = [
                ':id_usuario' => $id_usuario,
                ':nombre' => $nombre,
                ':email' => $email
            ];
            
            // Si se actualiza la foto de perfil
            if ($foto_perfil !== null) {
                $sql .= ", foto_perfil = :foto_perfil";
                $params[':foto_perfil'] = $foto_perfil;
            }
            
            $sql .= " WHERE id_usuario = :id_usuario";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            
            return [
                'estado' => true,
                'mensaje' => 'Datos actualizados correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al actualizar datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cambiar contraseña del usuario
     */
    public function cambiarContraseña($id_usuario, $contraseña_actual, $nueva_contraseña)
    {
        try {
            // Obtener contraseña actual del usuario
            $sql = "SELECT contraseña FROM Usuarios WHERE id_usuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si la contraseña actual es correcta
            if (!password_verify($contraseña_actual, $usuario['contraseña'])) {
                return [
                    'estado' => false,
                    'mensaje' => 'La contraseña actual es incorrecta'
                ];
            }
            
            // Hashear la nueva contraseña
            $contraseña_hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);
            
            // Actualizar la contraseña
            $sql = "UPDATE Usuarios SET contraseña = :contraseña WHERE id_usuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':contraseña', $contraseña_hash);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Contraseña actualizada correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al cambiar contraseña: ' . $e->getMessage()
            ];
        }
    }
}
?> 
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
    private $cod_user;
    private $username;
    private $email;
    private $password;
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
    public function registrar($username, $email, $password, $foto_perfil = null)
    {
        try {
            // Verificar si el username ya está registrado
            if ($this->usernameExiste($username)) {
                return [
                    'estado' => false,
                    'mensaje' => 'El nombre de usuario ya está registrado'
                ];
            }
            
            // Verificar si el email ya está registrado
            if ($this->emailExiste($email)) {
                return [
                    'estado' => false,
                    'mensaje' => 'El correo electrónico ya está registrado'
                ];
            }
            
            // Hashear la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Preparar la consulta SQL
            $sql = "INSERT INTO Usuarios (username, email, password, rol, foto_perfil) 
                    VALUES (:username, :email, :password, :rol, :foto_perfil)";
            
            $stmt = $this->conexion->prepare($sql);
            
            // Asignar valores a los parámetros
            $rol = 'usuario'; // Por defecto todos son usuarios normales
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password_hash);
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
     * Verificar si un username ya está registrado
     */
    public function usernameExiste($username)
    {
        try {
            $sql = "SELECT COUNT(*) FROM Usuarios WHERE username = :username";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
            
        } catch (PDOException $e) {
            return false;
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
    public function login($username, $password)
    {
        try {
            $sql = "SELECT * FROM Usuarios WHERE username = :username";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si existe el usuario y la contraseña es correcta
            if ($usuario && password_verify($password, $usuario['password'])) {
                return [
                    'estado' => true,
                    'usuario' => $usuario
                ];
            } else {
                return [
                    'estado' => false,
                    'mensaje' => 'Nombre de usuario o contraseña incorrectos'
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
    public function obtenerPorId($cod_user)
    {
        try {
            $sql = "SELECT cod_user, username, email, rol, foto_perfil FROM Usuarios WHERE cod_user = :cod_user";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_user', $cod_user);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Actualizar datos del usuario
     */
    public function actualizar($cod_user, $username, $email, $foto_perfil = null)
    {
        try {
            // Verificar si estamos actualizando el username y si ya existe
            $usuario = $this->obtenerPorId($cod_user);
            
            if ($usuario['username'] !== $username && $this->usernameExiste($username)) {
                return [
                    'estado' => false,
                    'mensaje' => 'El nombre de usuario ya está registrado por otro usuario'
                ];
            }
            
            // Verificar si estamos actualizando el email y si ya existe
            if ($usuario['email'] !== $email && $this->emailExiste($email)) {
                return [
                    'estado' => false,
                    'mensaje' => 'El correo electrónico ya está registrado por otro usuario'
                ];
            }
            
            // SQL base
            $sql = "UPDATE Usuarios SET username = :username, email = :email";
            $params = [
                ':cod_user' => $cod_user,
                ':username' => $username,
                ':email' => $email
            ];
            
            // Si se actualiza la foto de perfil
            if ($foto_perfil !== null) {
                $sql .= ", foto_perfil = :foto_perfil";
                $params[':foto_perfil'] = $foto_perfil;
            }
            
            $sql .= " WHERE cod_user = :cod_user";
            
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
    public function cambiarContraseña($cod_user, $password_actual, $nueva_password)
    {
        try {
            // Obtener contraseña actual del usuario
            $sql = "SELECT password FROM Usuarios WHERE cod_user = :cod_user";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cod_user', $cod_user);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si la contraseña actual es correcta
            if (!password_verify($password_actual, $usuario['password'])) {
                return [
                    'estado' => false,
                    'mensaje' => 'La contraseña actual es incorrecta'
                ];
            }
            
            // Hashear la nueva contraseña
            $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            
            // Actualizar la contraseña
            $sql = "UPDATE Usuarios SET password = :password WHERE cod_user = :cod_user";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':cod_user', $cod_user);
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
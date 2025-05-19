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
            
            // Preparar la consulta SQL
            $sql = "INSERT INTO Usuarios (username, email, password, rol, foto_perfil) 
                    VALUES (:username, :email, :password, :rol, :foto_perfil)";
            
            $stmt = $this->conexion->prepare($sql);
            
            // Asignar valores a los parámetros
            $rol = 'usuario'; // Por defecto todos son usuarios normales
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':foto_perfil', $foto_perfil, PDO::PARAM_LOB);
            
            // Ejecutar la consulta
            $stmt->execute();
            
            // Obtener el ID del usuario recién creado
            $cod_user = $this->conexion->lastInsertId();
            
            // Devolver el usuario recién creado para iniciar sesión automáticamente
            $usuario = $this->obtenerPorId($cod_user);
            
            return [
                'estado' => true,
                'mensaje' => 'Usuario registrado correctamente',
                'usuario' => $usuario
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
            $sql = "SELECT * FROM Usuarios WHERE username = :username AND password = :password";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si existe el usuario
            if ($usuario) {
                // Procesar la foto de perfil
                $usuario = $this->procesarFotoPerfil($usuario);
                
                return [
                    'estado' => true,
                    'usuario' => $usuario,
                    'mensaje' => 'Inicio de sesión exitoso'
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
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si existe el usuario, procesar la foto de perfil
            if ($usuario) {
                $usuario = $this->procesarFotoPerfil($usuario);
            }
            
            return $usuario;
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Procesar la foto de perfil
     */
    private function procesarFotoPerfil($usuario)
    {
        if (isset($usuario['foto_perfil']) && $usuario['foto_perfil']) {
            $usuario['foto_perfil_base64'] = 'data:image/jpeg;base64,' . base64_encode($usuario['foto_perfil']);
        } else {
            // No establecemos ninguna ruta, el header.php se encargará de determinarla según el contexto
            $usuario['foto_perfil_base64'] = '';
        }
        
        return $usuario;
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
            if ($password_actual !== $usuario['password']) {
                return [
                    'estado' => false,
                    'mensaje' => 'La contraseña actual es incorrecta'
                ];
            }
            
            // Actualizar la contraseña
            $sql = "UPDATE Usuarios SET password = :password WHERE cod_user = :cod_user";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':password', $nueva_password);
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
    
    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos() {
        try {
            $sql = "SELECT cod_user, username, email, rol, foto_perfil FROM Usuarios ORDER BY username";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar las fotos de perfil
            foreach ($usuarios as &$usuario) {
                $usuario = $this->procesarFotoPerfil($usuario);
            }
            
            return $usuarios;
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Registrar un nuevo usuario desde el panel admin
     */
    public function registrarAdmin($username, $email, $password, $rol, $foto_perfil = null) {
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
            
            // Preparar la consulta SQL
            $sql = "INSERT INTO Usuarios (username, email, password, rol, foto_perfil) 
                    VALUES (:username, :email, :password, :rol, :foto_perfil)";
            
            $stmt = $this->conexion->prepare($sql);
            
            // Asignar valores a los parámetros
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':foto_perfil', $foto_perfil, PDO::PARAM_LOB);
            
            // Ejecutar la consulta
            $stmt->execute();
            
            // Obtener el ID del usuario recién creado
            $cod_user = $this->conexion->lastInsertId();
            
            // Devolver el usuario recién creado
            return [
                'estado' => true,
                'mensaje' => 'Usuario registrado correctamente',
                'id' => $cod_user
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al registrar usuario: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar un usuario desde el panel admin
     */
    public function actualizarAdmin($id, $username, $email, $password = null, $rol = null, $foto_perfil = null, $actualizar_foto = false) {
        try {
            // Verificar si estamos actualizando el username y si ya existe
            $usuario = $this->obtenerPorId($id);
            
            if (!$usuario) {
                return [
                    'estado' => false,
                    'mensaje' => 'El usuario no existe'
                ];
            }
            
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
                ':id' => $id,
                ':username' => $username,
                ':email' => $email
            ];
            
            // Si se actualiza el rol
            if ($rol !== null) {
                $sql .= ", rol = :rol";
                $params[':rol'] = $rol;
            }
            
            // Si se actualiza la contraseña
            if ($password !== null && $password !== '') {
                $sql .= ", password = :password";
                $params[':password'] = $password;
            }
            
            // Si se actualiza la foto de perfil
            if ($actualizar_foto) {
                $sql .= ", foto_perfil = :foto_perfil";
                $params[':foto_perfil'] = $foto_perfil;
            }
            
            $sql .= " WHERE cod_user = :id";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            
            return [
                'estado' => true,
                'mensaje' => 'Usuario actualizado correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al actualizar el usuario: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar un usuario
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM Usuarios WHERE cod_user = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Usuario eliminado correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al eliminar el usuario: ' . $e->getMessage()
            ];
        }
    }
}
?> 
<?php
// Iniciar la sesión
session_start();

// Incluir el modelo de Usuario
require_once '../models/Usuario.php';

// Verificar si se envió el formulario por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtener los datos del formulario
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validar que los campos no estén vacíos
    if (empty($username) || empty($password)) {
        $_SESSION['error_login'] = 'Todos los campos son obligatorios';
        header('Location: ../../frontend/pages/login.php');
        exit;
    }
    
    // Intentar iniciar sesión
    $usuario = new Usuario();
    $resultado = $usuario->login($username, $password);
    
    if ($resultado['estado']) {
        // Guardar datos del usuario en la sesión
        $_SESSION['usuario_id'] = $resultado['usuario']['cod_user'];
        $_SESSION['usuario_nombre'] = $resultado['usuario']['username'];
        $_SESSION['usuario_email'] = $resultado['usuario']['email'];
        $_SESSION['usuario_rol'] = $resultado['usuario']['rol'];
        
        // Guardar la foto de perfil en la sesión si existe
        if (isset($resultado['usuario']['foto_perfil']) && !empty($resultado['usuario']['foto_perfil']) && isset($resultado['usuario']['foto_perfil_base64']) && !empty($resultado['usuario']['foto_perfil_base64'])) {
            $_SESSION['usuario_foto'] = $resultado['usuario']['foto_perfil_base64'];
        } else {
            $_SESSION['usuario_foto'] = '';
        }
        
        // Guardar mensaje de éxito en la sesión
        $_SESSION['exito_login'] = '¡Sesión iniciada correctamente! Bienvenido/a ' . $username;
        
        // Redireccionar según el rol del usuario
        if ($resultado['usuario']['rol'] === 'admin') {
            header('Location: ../../frontend/pages/admin/index.php');
        } else {
            header('Location: ../../index.php');
        }
        exit;
    } else {
        // Guardar mensaje de error en la sesión
        $_SESSION['error_login'] = $resultado['mensaje'];
        
        // Redireccionar al formulario de login
        header('Location: ../../frontend/pages/login.php');
        exit;
    }
} else {
    // Si no es POST, redireccionar al formulario de login
    header('Location: ../../frontend/pages/login.php');
    exit;
}
?> 
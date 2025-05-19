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
        // Login exitoso, guardar datos en la sesión
        $_SESSION['usuario_id'] = $resultado['usuario']['cod_user'];
        $_SESSION['usuario_nombre'] = $resultado['usuario']['username'];
        $_SESSION['usuario_email'] = $resultado['usuario']['email'];
        $_SESSION['usuario_rol'] = $resultado['usuario']['rol'];
        
        // Si tiene foto de perfil, usar la versión base64 ya procesada por el modelo
        if (isset($resultado['usuario']['foto_perfil_base64'])) {
            $_SESSION['usuario_foto'] = $resultado['usuario']['foto_perfil_base64'];
        } else if ($resultado['usuario']['foto_perfil']) {
            $_SESSION['usuario_foto'] = 'data:image/jpeg;base64,' . base64_encode($resultado['usuario']['foto_perfil']);
        } else {
            // Dejamos vacío para que el componente header determine la ruta correcta según la ubicación
            $_SESSION['usuario_foto'] = '';
        }
        
        // Guardar mensaje de éxito en la sesión
        $_SESSION['exito_login'] = '¡Sesión iniciada correctamente! Bienvenido/a ' . $username;
        
        // Redireccionar al inicio
        header('Location: ../../index.php');
        exit;
    } else {
        // Login fallido, mostrar mensaje de error
        $_SESSION['error_login'] = $resultado['mensaje'];
        header('Location: ../../frontend/pages/login.php');
        exit;
    }
} else {
    // Si no es POST, redireccionar al formulario de login
    header('Location: ../../frontend/pages/login.php');
    exit;
}
?> 
<?php
// Iniciar la sesión
session_start();

// Calcular la ruta base relativa
$current_dir = dirname(__FILE__);
$project_root = dirname(dirname(dirname($current_dir)));
$relative_path = '';

// Si estamos en un subdirectorio del servidor (localhost/proyecto)
if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
    $project_folder = basename(dirname(dirname(dirname(__FILE__))));
    $relative_path = '';
    
    // Determinar si estamos en un subdirectorio o en la raíz
    if ($project_folder != 'www' && $project_folder != 'htdocs') {
        $relative_path = '/' . $project_folder;
    }
}

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
        header('Location: ' . $relative_path . '/frontend/pages/login.php');
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
        
        // Si tiene foto de perfil, convertir el BLOB a base64 para mostrarlo
        if ($resultado['usuario']['foto_perfil']) {
            $_SESSION['usuario_foto'] = 'data:image/jpeg;base64,' . base64_encode($resultado['usuario']['foto_perfil']);
        } else {
            $_SESSION['usuario_foto'] = null;
        }
        
        // Redireccionar al inicio
        header('Location: ' . $relative_path . '/index.php');
        exit;
    } else {
        // Login fallido, mostrar mensaje de error
        $_SESSION['error_login'] = $resultado['mensaje'];
        header('Location: ' . $relative_path . '/frontend/pages/login.php');
        exit;
    }
} else {
    // Si no es POST, redireccionar al formulario de login
    header('Location: ' . $relative_path . '/frontend/pages/login.php');
    exit;
}
?> 
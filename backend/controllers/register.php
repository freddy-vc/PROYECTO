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
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmar_password = isset($_POST['confirmar_password']) ? $_POST['confirmar_password'] : '';
    
    // Validar que los campos obligatorios no estén vacíos
    if (empty($nombre) || empty($email) || empty($password) || empty($confirmar_password)) {
        $_SESSION['error_registro'] = 'Todos los campos obligatorios deben ser completados';
        header('Location: ' . $relative_path . '/frontend/pages/registro.php');
        exit;
    }
    
    // Validar que las contraseñas coincidan
    if ($password !== $confirmar_password) {
        $_SESSION['error_registro'] = 'Las contraseñas no coinciden';
        header('Location: ' . $relative_path . '/frontend/pages/registro.php');
        exit;
    }
    
    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_registro'] = 'El formato del correo electrónico es inválido';
        header('Location: ' . $relative_path . '/frontend/pages/registro.php');
        exit;
    }
    
    // Procesar la foto de perfil si se ha subido
    $foto_perfil = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['size'] > 0) {
        // Procesar la imagen (puedes agregar más validaciones)
        $foto_perfil = file_get_contents($_FILES['foto_perfil']['tmp_name']);
    }
    
    // Intentar registrar al usuario
    $usuario = new Usuario();
    $resultado = $usuario->registrar($nombre, $email, $password, $foto_perfil);
    
    if ($resultado['estado']) {
        // Registro exitoso, guardar mensaje y redireccionar a login
        $_SESSION['exito_registro'] = $resultado['mensaje'];
        header('Location: ' . $relative_path . '/frontend/pages/login.php');
        exit;
    } else {
        // Registro fallido, mostrar mensaje de error
        $_SESSION['error_registro'] = $resultado['mensaje'];
        header('Location: ' . $relative_path . '/frontend/pages/registro.php');
        exit;
    }
} else {
    // Si no es POST, redireccionar al formulario de registro
    header('Location: ' . $relative_path . '/frontend/pages/registro.php');
    exit;
}
?> 
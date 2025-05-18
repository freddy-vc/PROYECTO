<?php
// Iniciar la sesión
session_start();

// Incluir el modelo de Usuario
require_once '../models/Usuario.php';

// Verificar si se envió el formulario por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtener los datos del formulario
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmar_password = isset($_POST['confirmar_password']) ? $_POST['confirmar_password'] : '';
    
    // Validar que los campos no estén vacíos
    if (empty($nombre) || empty($email) || empty($password) || empty($confirmar_password)) {
        $_SESSION['error_registro'] = 'Todos los campos son obligatorios';
        header('Location: /frontend/pages/registro.php');
        exit;
    }
    
    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_registro'] = 'El formato del correo electrónico es inválido';
        header('Location: /frontend/pages/registro.php');
        exit;
    }
    
    // Validar que las contraseñas coincidan
    if ($password !== $confirmar_password) {
        $_SESSION['error_registro'] = 'Las contraseñas no coinciden';
        header('Location: /frontend/pages/registro.php');
        exit;
    }
    
    // Validar longitud mínima de la contraseña
    if (strlen($password) < 6) {
        $_SESSION['error_registro'] = 'La contraseña debe tener al menos 6 caracteres';
        header('Location: /frontend/pages/registro.php');
        exit;
    }
    
    // Procesar la imagen si se subió
    $foto_perfil = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        $tamaño_maximo = 2 * 1024 * 1024; // 2MB
        
        // Validar tipo de archivo
        if (!in_array($_FILES['foto_perfil']['type'], $tipos_permitidos)) {
            $_SESSION['error_registro'] = 'El formato de la imagen no es válido. Sólo se permiten JPG, PNG y GIF';
            header('Location: /frontend/pages/registro.php');
            exit;
        }
        
        // Validar tamaño
        if ($_FILES['foto_perfil']['size'] > $tamaño_maximo) {
            $_SESSION['error_registro'] = 'La imagen es demasiado grande. El tamaño máximo es 2MB';
            header('Location: /frontend/pages/registro.php');
            exit;
        }
        
        // Leer el contenido del archivo
        $foto_perfil = file_get_contents($_FILES['foto_perfil']['tmp_name']);
    }
    
    // Registrar al usuario
    $usuario = new Usuario();
    $resultado = $usuario->registrar($nombre, $email, $password, $foto_perfil);
    
    if ($resultado['estado']) {
        // Registro exitoso
        $_SESSION['exito_login'] = 'Registro exitoso. Ahora puedes iniciar sesión';
        header('Location: /frontend/pages/login.php');
        exit;
    } else {
        // Registro fallido
        $_SESSION['error_registro'] = $resultado['mensaje'];
        header('Location: /frontend/pages/registro.php');
        exit;
    }
} else {
    // Si no es POST, redireccionar al formulario de registro
    header('Location: /frontend/pages/registro.php');
    exit;
}
?> 
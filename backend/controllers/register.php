<?php
// Iniciar la sesión
session_start();

// Incluir el modelo de Usuario
require_once '../models/Usuario.php';

// Verificar si se envió el formulario por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtener los datos del formulario
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmar_password = isset($_POST['confirmar_password']) ? $_POST['confirmar_password'] : '';
    
    // Validar que los campos obligatorios no estén vacíos
    if (empty($username) || empty($email) || empty($password) || empty($confirmar_password)) {
        $_SESSION['error_registro'] = 'Todos los campos obligatorios deben ser completados';
        header('Location: ../../frontend/pages/registro.php');
        exit;
    }
    
    // Validar longitud del nombre de usuario
    if (strlen($username) < 3) {
        $_SESSION['error_registro'] = 'El nombre de usuario debe tener al menos 3 caracteres';
        header('Location: ../../frontend/pages/registro.php');
        exit;
    }
    
    // Validar que las contraseñas coincidan
    if ($password !== $confirmar_password) {
        $_SESSION['error_registro'] = 'Las contraseñas no coinciden';
        header('Location: ../../frontend/pages/registro.php');
        exit;
    }
    
    // Validar longitud de la contraseña
    if (strlen($password) < 6) {
        $_SESSION['error_registro'] = 'La contraseña debe tener al menos 6 caracteres';
        header('Location: ../../frontend/pages/registro.php');
        exit;
    }
    
    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_registro'] = 'El formato del correo electrónico es inválido. Debe tener el formato usuario@dominio.com';
        header('Location: ../../frontend/pages/registro.php');
        exit;
    }
    
    // Procesar la foto de perfil si se ha subido
    $foto_perfil = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['size'] > 0) {
        // Validar el tipo de archivo
        $tipo_archivo = $_FILES['foto_perfil']['type'];
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($tipo_archivo, $tipos_permitidos)) {
            $_SESSION['error_registro'] = 'El formato de la imagen no es válido. Solo se permiten JPG, PNG y GIF';
            header('Location: ../../frontend/pages/registro.php');
            exit;
        }
        
        // Validar el tamaño del archivo (máximo 2MB)
        $tamano_archivo = $_FILES['foto_perfil']['size'] / (1024 * 1024); // Tamaño en MB
        if ($tamano_archivo > 2) {
            $_SESSION['error_registro'] = 'La imagen es demasiado grande. El tamaño máximo permitido es 2MB';
            header('Location: ../../frontend/pages/registro.php');
            exit;
        }
        
        // Procesar la imagen
        $foto_perfil = file_get_contents($_FILES['foto_perfil']['tmp_name']);
    }
    
    // Intentar registrar al usuario
    $usuario = new Usuario();
    $resultado = $usuario->registrar($username, $email, $password, $foto_perfil);
    
    if ($resultado['estado']) {
        // Registro exitoso, iniciar sesión automáticamente
        $_SESSION['usuario_id'] = $resultado['usuario']['cod_user'];
        $_SESSION['usuario_nombre'] = $resultado['usuario']['username'];
        $_SESSION['usuario_email'] = $resultado['usuario']['email'];
        $_SESSION['usuario_rol'] = $resultado['usuario']['rol'];
        
        // Si hay foto_perfil_base64, usarla, de lo contrario usar imagen predeterminada
        if(isset($resultado['usuario']['foto_perfil_base64'])) {
            $_SESSION['usuario_foto'] = $resultado['usuario']['foto_perfil_base64'];
        } else {
            $_SESSION['usuario_foto'] = './frontend/assets/images/user.png';
        }

        // Guardar mensaje de éxito en la sesión
        $_SESSION['exito_login'] = '¡Registro exitoso! Bienvenido/a ' . $username;
        
        // Redireccionar al inicio
        header('Location: ../../index.php');
        exit;
    } else {
        // Registro fallido, mostrar mensaje de error
        $_SESSION['error_registro'] = $resultado['mensaje'];
        header('Location: ../../frontend/pages/registro.php');
        exit;
    }
} else {
    // Si no es POST, redireccionar al formulario de registro
    header('Location: ../../frontend/pages/registro.php');
    exit;
}
?> 
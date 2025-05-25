<?php
// Iniciar la sesión
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    // Redireccionar a login si no hay sesión
    header('Location: ../../frontend/pages/login.php');
    exit;
}

// Incluir el modelo de Usuario
require_once '../models/Usuario.php';

// Verificar si se envió el formulario por POST y si se subió una foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['size'] > 0) {
    
    // Obtener el ID del usuario de la sesión
    $cod_user = $_SESSION['usuario_id'];
    
    // Obtener el username y email de la sesión (necesarios para actualizar)
    $username = $_SESSION['usuario_nombre'];
    $email = $_SESSION['usuario_email'];
    
    // Procesar la foto
    $foto_perfil = file_get_contents($_FILES['foto_perfil']['tmp_name']);
    
    // Verificar que el contenido de la imagen no esté vacío
    if (empty($foto_perfil)) {
        $_SESSION['error_perfil'] = 'Error al procesar la imagen: contenido vacío';
        header('Location: ../../frontend/pages/perfil.php');
        exit;
    }

    // Verificar el tipo de archivo
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->buffer($foto_perfil);

    if (!in_array($mime_type, $allowed_types)) {
        $_SESSION['error_perfil'] = 'El formato de la imagen no es válido. Use JPG, PNG o GIF.';
        header('Location: ../../frontend/pages/perfil.php');
        exit;
    }
    
    // Actualizar la foto de perfil
    $usuario = new Usuario();
    $resultado = $usuario->actualizar($cod_user, $username, $email, $foto_perfil);
    
    if ($resultado['estado']) {
        // Obtener el usuario actualizado para tener la foto procesada correctamente
        $usuario_actualizado = $usuario->obtenerPorId($cod_user);
        
        // Actualizar la foto en la sesión si existe
        if (isset($usuario_actualizado['foto_perfil_base64']) && !empty($usuario_actualizado['foto_perfil_base64'])) {
            $_SESSION['usuario_foto'] = $usuario_actualizado['foto_perfil_base64'];
        } else {
            $_SESSION['usuario_foto'] = '';
        }
        
        // Guardar mensaje de éxito
        $_SESSION['exito_perfil'] = 'Foto de perfil actualizada correctamente';
    } else {
        // Guardar mensaje de error
        $_SESSION['error_perfil'] = $resultado['mensaje'];
    }
    
    // Redireccionar de vuelta al perfil
    header('Location: ../../frontend/pages/perfil.php');
    exit;
} else {
    // Si no se envió una foto, mostrar error
    $_SESSION['error_perfil'] = 'No se ha seleccionado ninguna imagen';
    header('Location: ../../frontend/pages/perfil.php');
    exit;
} 
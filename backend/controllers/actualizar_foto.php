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
    
    // Actualizar la foto de perfil
    $usuario = new Usuario();
    $resultado = $usuario->actualizar($cod_user, $username, $email, $foto_perfil);
    
    if ($resultado['estado']) {
        // Actualizar la foto en la sesión
        $_SESSION['usuario_foto'] = 'data:image/jpeg;base64,' . base64_encode($foto_perfil);
        
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
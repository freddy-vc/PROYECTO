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

try {
    // Obtener el ID del usuario de la sesión
    $cod_user = $_SESSION['usuario_id'];
    
    // Obtener el username y email de la sesión (necesarios para actualizar)
    $username = $_SESSION['usuario_nombre'];
    $email = $_SESSION['usuario_email'];
    
    // Crear una instancia del modelo Usuario
    $usuario = new Usuario();
    
    // Llamar al método para eliminar la foto
    $resultado = $usuario->eliminarFoto($cod_user, $username, $email);
    
    if ($resultado['estado']) {
        // Actualizar la foto en la sesión
        $_SESSION['usuario_foto'] = '';
        
        // Guardar mensaje de éxito
        $_SESSION['exito_perfil'] = 'Foto de perfil eliminada correctamente';
    } else {
        // Guardar mensaje de error
        $_SESSION['error_perfil'] = $resultado['mensaje'];
        error_log('ERROR AL ELIMINAR FOTO: ' . $resultado['mensaje']);
    }
    
} catch (Exception $e) {
    // Capturar cualquier excepción y registrarla
    error_log('ERROR EN ELIMINAR_FOTO: ' . $e->getMessage());
    $_SESSION['error_perfil'] = 'Error al eliminar la imagen: ' . $e->getMessage();
}

// Redireccionar de vuelta al perfil
header('Location: ../../frontend/pages/perfil.php');
exit; 
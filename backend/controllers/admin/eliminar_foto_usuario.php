<?php
// Iniciar la sesión
session_start();

// Verificar si hay sesión activa y si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Redireccionar a login si no hay sesión o no es admin
    header('Location: ../../../frontend/pages/login.php');
    exit;
}

// Incluir el modelo de Usuario
require_once '../../models/Usuario.php';

// Verificar si se recibió el ID del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'])) {
    
    // Obtener el ID del usuario
    $usuario_id = intval($_POST['usuario_id']);
    
    try {
        // Crear una instancia del modelo Usuario
        $usuario = new Usuario();
        
        // Obtener datos del usuario para mantener username y email
        $datos_usuario = $usuario->obtenerPorId($usuario_id);
        
        if (!$datos_usuario) {
            $_SESSION['error_usuario'] = 'El usuario no existe';
            header('Location: ../../../frontend/pages/admin/usuarios.php');
            exit;
        }
        
        // Llamar al método para eliminar la foto
        $resultado = $usuario->eliminarFoto($usuario_id, $datos_usuario['username'], $datos_usuario['email']);
        
        if ($resultado['estado']) {
            // Guardar mensaje de éxito
            $_SESSION['exito_usuario'] = 'Foto eliminada correctamente';
        } else {
            // Guardar mensaje de error
            $_SESSION['error_usuario'] = $resultado['mensaje'];
            error_log('ERROR AL ELIMINAR FOTO DE USUARIO: ' . $resultado['mensaje']);
        }
        
    } catch (Exception $e) {
        // Capturar cualquier excepción y registrarla
        error_log('ERROR EN ELIMINAR_FOTO_USUARIO: ' . $e->getMessage());
        $_SESSION['error_usuario'] = 'Error al eliminar la foto: ' . $e->getMessage();
    }
    
} else {
    // Si no se recibió el ID del usuario, mostrar error
    $_SESSION['error_usuario'] = 'No se ha especificado el usuario';
}

// Redireccionar de vuelta a la página de usuarios
header('Location: ../../../frontend/pages/admin/usuarios.php');
exit; 
<?php
// Iniciar la sesión
session_start();

// Verificar si hay sesión activa y si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Redireccionar a login si no hay sesión o no es admin
    header('Location: ../../../frontend/pages/login.php');
    exit;
}

// Incluir el modelo de Jugador
require_once '../../models/Jugador.php';

// Verificar si se recibió el ID del jugador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jugador_id'])) {
    
    // Obtener el ID del jugador
    $jugador_id = intval($_POST['jugador_id']);
    
    try {
        // Crear una instancia del modelo Jugador
        $jugador = new Jugador();
        
        // Llamar al método para eliminar la foto
        $resultado = $jugador->eliminarFoto($jugador_id);
        
        if ($resultado['estado']) {
            // Guardar mensaje de éxito
            $_SESSION['exito_jugador'] = 'Foto eliminada correctamente';
        } else {
            // Guardar mensaje de error
            $_SESSION['error_jugador'] = $resultado['mensaje'];
            error_log('ERROR AL ELIMINAR FOTO DE JUGADOR: ' . $resultado['mensaje']);
        }
        
    } catch (Exception $e) {
        // Capturar cualquier excepción y registrarla
        error_log('ERROR EN ELIMINAR_FOTO_JUGADOR: ' . $e->getMessage());
        $_SESSION['error_jugador'] = 'Error al eliminar la foto: ' . $e->getMessage();
    }
    
} else {
    // Si no se recibió el ID del jugador, mostrar error
    $_SESSION['error_jugador'] = 'No se ha especificado el jugador';
}

// Redireccionar de vuelta a la página de jugadores
header('Location: ../../../frontend/pages/admin/jugadores.php');
exit; 
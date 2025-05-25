<?php
// Iniciar la sesión
session_start();

// Verificar si hay sesión activa y si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Redireccionar a login si no hay sesión o no es admin
    header('Location: ../../../frontend/pages/login.php');
    exit;
}

// Incluir el modelo de Equipo
require_once '../../models/Equipo.php';

// Verificar si se recibió el ID del equipo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipo_id'])) {
    
    // Obtener el ID del equipo
    $equipo_id = intval($_POST['equipo_id']);
    
    try {
        // Crear una instancia del modelo Equipo
        $equipo = new Equipo();
        
        // Llamar al método para eliminar el escudo
        $resultado = $equipo->eliminarEscudo($equipo_id);
        
        if ($resultado['estado']) {
            // Guardar mensaje de éxito
            $_SESSION['exito_equipo'] = 'Escudo eliminado correctamente';
        } else {
            // Guardar mensaje de error
            $_SESSION['error_equipo'] = $resultado['mensaje'];
            error_log('ERROR AL ELIMINAR ESCUDO: ' . $resultado['mensaje']);
        }
        
    } catch (Exception $e) {
        // Capturar cualquier excepción y registrarla
        error_log('ERROR EN ELIMINAR_ESCUDO: ' . $e->getMessage());
        $_SESSION['error_equipo'] = 'Error al eliminar el escudo: ' . $e->getMessage();
    }
    
} else {
    // Si no se recibió el ID del equipo, mostrar error
    $_SESSION['error_equipo'] = 'No se ha especificado el equipo';
}

// Redireccionar de vuelta a la página de equipos
header('Location: ../../../frontend/pages/admin/equipos.php');
exit; 
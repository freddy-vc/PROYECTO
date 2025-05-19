<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Redireccionar a la página de inicio si no es administrador
    header('Location: ../../../index.php');
    exit;
}

// Incluir los modelos necesarios
require_once __DIR__ . '/../../models/Ciudad.php';

// Verificar que se ha enviado una acción
if (!isset($_REQUEST['accion'])) {
    $_SESSION['error_ciudades'] = 'No se especificó ninguna acción';
    header('Location: ../../../frontend/pages/admin/ciudades.php');
    exit;
}

// Procesar la acción solicitada
$accion = $_REQUEST['accion'];

switch ($accion) {
    case 'crear':
        crearCiudad();
        break;
    
    case 'actualizar':
        actualizarCiudad();
        break;
    
    case 'eliminar':
        eliminarCiudad();
        break;
    
    case 'listar':
        listarCiudades();
        break;
    
    default:
        $_SESSION['error_ciudades'] = 'Acción no reconocida';
        header('Location: ../../../frontend/pages/admin/ciudades.php');
        break;
}

/**
 * Función para crear una nueva ciudad
 */
function crearCiudad() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['nombre']) || trim($_POST['nombre']) === '') {
        $_SESSION['error_ciudades'] = 'El nombre de la ciudad es obligatorio';
        header('Location: ../../../frontend/pages/admin/ciudades_form.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $nombre = trim($_POST['nombre']);
    
    // Crear la ciudad en la base de datos
    $ciudadModel = new Ciudad();
    $resultado = $ciudadModel->crear($nombre);
    
    if ($resultado['estado']) {
        // Éxito al crear la ciudad
        $_SESSION['exito_ciudades'] = 'Ciudad creada correctamente';
        header('Location: ../../../frontend/pages/admin/ciudades.php');
        exit;
    } else {
        // Error al crear la ciudad
        $_SESSION['error_ciudades'] = 'Error al crear la ciudad: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/ciudades_form.php');
        exit;
    }
}

/**
 * Función para actualizar una ciudad existente
 */
function actualizarCiudad() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['id']) || !isset($_POST['nombre']) || trim($_POST['nombre']) === '') {
        $_SESSION['error_ciudades'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/ciudades.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    
    // Actualizar la ciudad en la base de datos
    $ciudadModel = new Ciudad();
    $resultado = $ciudadModel->actualizar($id, $nombre);
    
    if ($resultado['estado']) {
        // Éxito al actualizar la ciudad
        $_SESSION['exito_ciudades'] = 'Ciudad actualizada correctamente';
        header('Location: ../../../frontend/pages/admin/ciudades.php');
        exit;
    } else {
        // Error al actualizar la ciudad
        $_SESSION['error_ciudades'] = 'Error al actualizar la ciudad: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/ciudades_form.php?id=' . $id);
        exit;
    }
}

/**
 * Función para eliminar una ciudad
 */
function eliminarCiudad() {
    // Verificar que se ha enviado el ID de la ciudad
    if (!isset($_POST['id'])) {
        $_SESSION['error_ciudades'] = 'No se especificó la ciudad a eliminar';
        header('Location: ../../../frontend/pages/admin/ciudades.php');
        exit;
    }
    
    // Obtener el ID de la ciudad
    $id = intval($_POST['id']);
    
    // Eliminar la ciudad de la base de datos
    $ciudadModel = new Ciudad();
    $resultado = $ciudadModel->eliminar($id);
    
    if ($resultado['estado']) {
        // Éxito al eliminar la ciudad
        $_SESSION['exito_ciudades'] = 'Ciudad eliminada correctamente';
    } else {
        // Error al eliminar la ciudad
        $_SESSION['error_ciudades'] = 'Error al eliminar la ciudad: ' . $resultado['mensaje'];
    }
    
    // Redireccionar a la lista de ciudades
    header('Location: ../../../frontend/pages/admin/ciudades.php');
    exit;
}

/**
 * Función para listar todas las ciudades (respuesta JSON)
 */
function listarCiudades() {
    // Obtener todas las ciudades
    $ciudadModel = new Ciudad();
    $ciudades = $ciudadModel->obtenerTodas();
    
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'estado' => true,
        'ciudades' => $ciudades
    ]);
    exit;
} 
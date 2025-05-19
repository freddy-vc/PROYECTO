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
require_once __DIR__ . '/../../models/Clasificacion.php';
require_once __DIR__ . '/../../models/Equipo.php';

// Verificar que se ha enviado una acción
if (!isset($_REQUEST['accion'])) {
    $_SESSION['error_clasificaciones'] = 'No se especificó ninguna acción';
    header('Location: ../../../frontend/pages/admin/clasificaciones.php');
    exit;
}

// Procesar la acción solicitada
$accion = $_REQUEST['accion'];

switch ($accion) {
    case 'crear':
        crearClasificacion();
        break;
    
    case 'actualizar':
        actualizarClasificacion();
        break;
    
    case 'eliminar':
        eliminarClasificacion();
        break;
    
    case 'listar':
        listarClasificaciones();
        break;
    
    default:
        $_SESSION['error_clasificaciones'] = 'Acción no reconocida';
        header('Location: ../../../frontend/pages/admin/clasificaciones.php');
        break;
}

/**
 * Función para crear una nueva clasificación
 */
function crearClasificacion() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['equipo_id']) || !isset($_POST['fase']) || !isset($_POST['posicion'])) {
        $_SESSION['error_clasificaciones'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/clasificaciones_form.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $equipo_id = intval($_POST['equipo_id']);
    $fase = trim($_POST['fase']);
    $posicion = intval($_POST['posicion']);
    $fecha_clasificacion = isset($_POST['fecha_clasificacion']) ? trim($_POST['fecha_clasificacion']) : date('Y-m-d');
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
    
    // Crear la clasificación en la base de datos
    $clasificacionModel = new Clasificacion();
    $resultado = $clasificacionModel->crear([
        'cod_equ' => $equipo_id,
        'fase' => $fase,
        'posicion' => $posicion,
        'fecha_clasificacion' => $fecha_clasificacion,
        'comentario' => $comentario
    ]);
    
    if ($resultado['estado']) {
        // Éxito al crear la clasificación
        $_SESSION['exito_clasificaciones'] = 'Clasificación creada correctamente';
        header('Location: ../../../frontend/pages/admin/clasificaciones.php');
        exit;
    } else {
        // Error al crear la clasificación
        $_SESSION['error_clasificaciones'] = 'Error al crear la clasificación: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/clasificaciones_form.php');
        exit;
    }
}

/**
 * Función para actualizar una clasificación existente
 */
function actualizarClasificacion() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['id']) || !isset($_POST['equipo_id']) || !isset($_POST['fase']) || !isset($_POST['posicion'])) {
        $_SESSION['error_clasificaciones'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/clasificaciones.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $id = intval($_POST['id']);
    $equipo_id = intval($_POST['equipo_id']);
    $fase = trim($_POST['fase']);
    $posicion = intval($_POST['posicion']);
    $fecha_clasificacion = isset($_POST['fecha_clasificacion']) ? trim($_POST['fecha_clasificacion']) : date('Y-m-d');
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
    
    // Actualizar la clasificación en la base de datos
    $clasificacionModel = new Clasificacion();
    $resultado = $clasificacionModel->actualizar([
        'id' => $id,
        'cod_equ' => $equipo_id,
        'fase' => $fase,
        'posicion' => $posicion,
        'fecha_clasificacion' => $fecha_clasificacion,
        'comentario' => $comentario
    ]);
    
    if ($resultado['estado']) {
        // Éxito al actualizar la clasificación
        $_SESSION['exito_clasificaciones'] = 'Clasificación actualizada correctamente';
        header('Location: ../../../frontend/pages/admin/clasificaciones.php');
        exit;
    } else {
        // Error al actualizar la clasificación
        $_SESSION['error_clasificaciones'] = 'Error al actualizar la clasificación: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/clasificaciones_form.php?id=' . $id);
        exit;
    }
}

/**
 * Función para eliminar una clasificación
 */
function eliminarClasificacion() {
    // Verificar que se ha enviado el ID de la clasificación
    if (!isset($_POST['id'])) {
        $_SESSION['error_clasificaciones'] = 'No se especificó la clasificación a eliminar';
        header('Location: ../../../frontend/pages/admin/clasificaciones.php');
        exit;
    }
    
    // Obtener el ID de la clasificación
    $id = intval($_POST['id']);
    
    // Eliminar la clasificación de la base de datos
    $clasificacionModel = new Clasificacion();
    $resultado = $clasificacionModel->eliminar($id);
    
    if ($resultado['estado']) {
        // Éxito al eliminar la clasificación
        $_SESSION['exito_clasificaciones'] = 'Clasificación eliminada correctamente';
    } else {
        // Error al eliminar la clasificación
        $_SESSION['error_clasificaciones'] = 'Error al eliminar la clasificación: ' . $resultado['mensaje'];
    }
    
    // Redireccionar a la lista de clasificaciones
    header('Location: ../../../frontend/pages/admin/clasificaciones.php');
    exit;
}

/**
 * Función para listar todas las clasificaciones (respuesta JSON)
 */
function listarClasificaciones() {
    // Obtener el filtro si existe
    $filtro = isset($_GET['fase']) ? $_GET['fase'] : null;
    
    // Obtener todas las clasificaciones o por fase
    $clasificacionModel = new Clasificacion();
    
    if ($filtro) {
        $clasificaciones = $clasificacionModel->obtenerPorFase($filtro);
    } else {
        $clasificaciones = $clasificacionModel->obtenerTodas();
    }
    
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'estado' => true,
        'clasificaciones' => $clasificaciones
    ]);
    exit;
}
?> 
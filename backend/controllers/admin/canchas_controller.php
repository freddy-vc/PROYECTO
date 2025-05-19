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
require_once __DIR__ . '/../../models/Cancha.php';

// Verificar que se ha enviado una acción
if (!isset($_REQUEST['accion'])) {
    $_SESSION['error_canchas'] = 'No se especificó ninguna acción';
    header('Location: ../../../frontend/pages/admin/canchas.php');
    exit;
}

// Procesar la acción solicitada
$accion = $_REQUEST['accion'];

switch ($accion) {
    case 'crear':
        crearCancha();
        break;
    
    case 'actualizar':
        actualizarCancha();
        break;
    
    case 'eliminar':
        eliminarCancha();
        break;
    
    case 'listar':
        listarCanchas();
        break;
    
    default:
        $_SESSION['error_canchas'] = 'Acción no reconocida';
        header('Location: ../../../frontend/pages/admin/canchas.php');
        break;
}

/**
 * Función para crear una nueva cancha
 */
function crearCancha() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['nombre']) || trim($_POST['nombre']) === '') {
        $_SESSION['error_canchas'] = 'El nombre de la cancha es obligatorio';
        header('Location: ../../../frontend/pages/admin/canchas_form.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $nombre = trim($_POST['nombre']);
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : null;
    $capacidad = !empty($_POST['capacidad']) ? intval($_POST['capacidad']) : null;
    
    // Crear la cancha en la base de datos
    $canchaModel = new Cancha();
    $resultado = $canchaModel->crear([
        'nombre' => $nombre,
        'direccion' => $direccion,
        'capacidad' => $capacidad
    ]);
    
    if ($resultado['estado']) {
        // Éxito al crear la cancha
        $_SESSION['exito_canchas'] = 'Cancha creada correctamente';
        header('Location: ../../../frontend/pages/admin/canchas.php');
        exit;
    } else {
        // Error al crear la cancha
        $_SESSION['error_canchas'] = 'Error al crear la cancha: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/canchas_form.php');
        exit;
    }
}

/**
 * Función para actualizar una cancha existente
 */
function actualizarCancha() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['id']) || !isset($_POST['nombre']) || trim($_POST['nombre']) === '') {
        $_SESSION['error_canchas'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/canchas.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : null;
    $capacidad = !empty($_POST['capacidad']) ? intval($_POST['capacidad']) : null;
    
    // Actualizar la cancha en la base de datos
    $canchaModel = new Cancha();
    $resultado = $canchaModel->actualizar([
        'cod_cancha' => $id,
        'nombre' => $nombre,
        'direccion' => $direccion,
        'capacidad' => $capacidad
    ]);
    
    if ($resultado['estado']) {
        // Éxito al actualizar la cancha
        $_SESSION['exito_canchas'] = 'Cancha actualizada correctamente';
        header('Location: ../../../frontend/pages/admin/canchas.php');
        exit;
    } else {
        // Error al actualizar la cancha
        $_SESSION['error_canchas'] = 'Error al actualizar la cancha: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/canchas_form.php?id=' . $id);
        exit;
    }
}

/**
 * Función para eliminar una cancha
 */
function eliminarCancha() {
    // Verificar que se ha enviado el ID de la cancha
    if (!isset($_POST['id'])) {
        $_SESSION['error_canchas'] = 'No se especificó la cancha a eliminar';
        header('Location: ../../../frontend/pages/admin/canchas.php');
        exit;
    }
    
    // Obtener el ID de la cancha
    $id = intval($_POST['id']);
    
    // Eliminar la cancha de la base de datos
    $canchaModel = new Cancha();
    $resultado = $canchaModel->eliminar($id);
    
    if ($resultado['estado']) {
        // Éxito al eliminar la cancha
        $_SESSION['exito_canchas'] = 'Cancha eliminada correctamente';
    } else {
        // Error al eliminar la cancha
        $_SESSION['error_canchas'] = 'Error al eliminar la cancha: ' . $resultado['mensaje'];
    }
    
    // Redireccionar a la lista de canchas
    header('Location: ../../../frontend/pages/admin/canchas.php');
    exit;
}

/**
 * Función para listar todas las canchas (respuesta JSON)
 */
function listarCanchas() {
    // Obtener todas las canchas
    $canchaModel = new Cancha();
    $canchas = $canchaModel->obtenerTodas();
    
    // Procesar las imágenes para mostrarlas en frontend
    $canchas = $canchaModel->procesarImagenes($canchas);
    
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'estado' => true,
        'canchas' => $canchas
    ]);
    exit;
} 
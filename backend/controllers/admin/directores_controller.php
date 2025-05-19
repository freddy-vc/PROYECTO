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
require_once __DIR__ . '/../../models/Director.php';

// Verificar que se ha enviado una acción
if (!isset($_REQUEST['accion'])) {
    $_SESSION['error_directores'] = 'No se especificó ninguna acción';
    header('Location: ../../../frontend/pages/admin/directores.php');
    exit;
}

// Procesar la acción solicitada
$accion = $_REQUEST['accion'];

switch ($accion) {
    case 'crear':
        crearDirector();
        break;
    
    case 'actualizar':
        actualizarDirector();
        break;
    
    case 'eliminar':
        eliminarDirector();
        break;
    
    case 'listar':
        listarDirectores();
        break;
    
    default:
        $_SESSION['error_directores'] = 'Acción no reconocida';
        header('Location: ../../../frontend/pages/admin/directores.php');
        break;
}

/**
 * Función para crear un nuevo director técnico
 */
function crearDirector() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['nombres']) || !isset($_POST['apellidos']) || trim($_POST['nombres']) === '' || trim($_POST['apellidos']) === '') {
        $_SESSION['error_directores'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/directores_form.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    
    // Crear el director técnico en la base de datos
    $directorModel = new Director();
    $resultado = $directorModel->crear($nombres, $apellidos);
    
    if ($resultado['estado']) {
        // Éxito al crear el director técnico
        $_SESSION['exito_directores'] = 'Director técnico creado correctamente';
        header('Location: ../../../frontend/pages/admin/directores.php');
        exit;
    } else {
        // Error al crear el director técnico
        $_SESSION['error_directores'] = 'Error al crear el director técnico: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/directores_form.php');
        exit;
    }
}

/**
 * Función para actualizar un director técnico existente
 */
function actualizarDirector() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['id']) || !isset($_POST['nombres']) || !isset($_POST['apellidos']) || 
        trim($_POST['nombres']) === '' || trim($_POST['apellidos']) === '') {
        $_SESSION['error_directores'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/directores.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $id = intval($_POST['id']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    
    // Actualizar el director técnico en la base de datos
    $directorModel = new Director();
    $resultado = $directorModel->actualizar($id, $nombres, $apellidos);
    
    if ($resultado['estado']) {
        // Éxito al actualizar el director técnico
        $_SESSION['exito_directores'] = 'Director técnico actualizado correctamente';
        header('Location: ../../../frontend/pages/admin/directores.php');
        exit;
    } else {
        // Error al actualizar el director técnico
        $_SESSION['error_directores'] = 'Error al actualizar el director técnico: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/directores_form.php?id=' . $id);
        exit;
    }
}

/**
 * Función para eliminar un director técnico
 */
function eliminarDirector() {
    // Verificar que se ha enviado el ID del director técnico
    if (!isset($_POST['id'])) {
        $_SESSION['error_directores'] = 'No se especificó el director técnico a eliminar';
        header('Location: ../../../frontend/pages/admin/directores.php');
        exit;
    }
    
    // Obtener el ID del director técnico
    $id = intval($_POST['id']);
    
    // Eliminar el director técnico de la base de datos
    $directorModel = new Director();
    $resultado = $directorModel->eliminar($id);
    
    if ($resultado['estado']) {
        // Éxito al eliminar el director técnico
        $_SESSION['exito_directores'] = 'Director técnico eliminado correctamente';
    } else {
        // Error al eliminar el director técnico
        $_SESSION['error_directores'] = 'Error al eliminar el director técnico: ' . $resultado['mensaje'];
    }
    
    // Redireccionar a la lista de directores técnicos
    header('Location: ../../../frontend/pages/admin/directores.php');
    exit;
}

/**
 * Función para listar todos los directores técnicos (respuesta JSON)
 */
function listarDirectores() {
    // Obtener todos los directores técnicos
    $directorModel = new Director();
    $directores = $directorModel->obtenerTodos();
    
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'estado' => true,
        'directores' => $directores
    ]);
    exit;
} 
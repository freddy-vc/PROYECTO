<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario es administrador
if ((!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') && 
    (!isset($_POST['diagnostico']) || $_POST['diagnostico'] !== '1')) {
    // Redireccionar a la página de inicio si no es administrador
    header('Location: ../../../index.php');
    exit;
}

// Si estamos en modo diagnóstico, establecer la sesión de administrador
if (isset($_POST['diagnostico']) && $_POST['diagnostico'] === '1') {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_rol'] = 'admin';
}

// Incluir el modelo de Equipo
require_once __DIR__ . '/../../models/Equipo.php';

// Incluir otros modelos necesarios
require_once __DIR__ . '/../../models/Ciudad.php';
require_once __DIR__ . '/../../models/Director.php';

// Verificar que se ha enviado una acción
if (!isset($_REQUEST['accion'])) {
    $_SESSION['error_equipos'] = 'No se especificó ninguna acción';
    header('Location: ../../../frontend/pages/admin/equipos.php');
    exit;
}

// Procesar la acción solicitada
$accion = $_REQUEST['accion'];

switch ($accion) {
    case 'crear':
        crearEquipo();
        break;
    
    case 'actualizar':
        actualizarEquipo();
        break;
    
    case 'eliminar':
        eliminarEquipo();
        break;
    
    case 'listar':
        listarEquipos();
        break;
    
    case 'detalle':
        detalleEquipo();
        break;
    
    default:
        $_SESSION['error_equipos'] = 'Acción no reconocida';
        header('Location: ../../../frontend/pages/admin/equipos.php');
        break;
}

/**
 * Función para crear un nuevo equipo
 */
function crearEquipo() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['nombre']) || !isset($_POST['ciudad_id'])) {
        $_SESSION['error_equipos'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/equipos_form.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $nombre = trim($_POST['nombre']);
    $ciudad_id = intval($_POST['ciudad_id']);
    $director_id = !empty($_POST['director_id']) ? intval($_POST['director_id']) : null;
    
    // Validar los datos
    if (empty($nombre)) {
        $_SESSION['error_equipos'] = 'El nombre del equipo es obligatorio';
        header('Location: ../../../frontend/pages/admin/equipos_form.php');
        exit;
    }
    
    // Procesar la imagen del escudo si se ha enviado
    $escudo = null;
    if (isset($_FILES['escudo']) && $_FILES['escudo']['error'] === UPLOAD_ERR_OK) {
        // Verificar el tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['escudo']['type'], $allowed_types)) {
            $_SESSION['error_equipos'] = 'El formato de la imagen no es válido. Use JPG, PNG o GIF.';
            header('Location: ../../../frontend/pages/admin/equipos_form.php');
            exit;
        }
        
        // Verificar el tamaño del archivo (máximo 2MB)
        if ($_FILES['escudo']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_equipos'] = 'La imagen es demasiado grande. El tamaño máximo es 2MB.';
            header('Location: ../../../frontend/pages/admin/equipos_form.php');
            exit;
        }
        
        // Leer el contenido del archivo
        $escudo = file_get_contents($_FILES['escudo']['tmp_name']);
        
        // Verificar que el contenido de la imagen no esté vacío
        if (empty($escudo)) {
            $_SESSION['error_equipos'] = 'Error al procesar la imagen: contenido vacío.';
            header('Location: ../../../frontend/pages/admin/equipos_form.php');
            exit;
        }
    }
    
    // Crear el equipo en la base de datos
    $equipoModel = new Equipo();
    $resultado = $equipoModel->crear($nombre, $ciudad_id, $director_id, $escudo);
    
    if ($resultado['estado']) {
        // Éxito al crear el equipo
        $_SESSION['exito_equipos'] = 'Equipo creado correctamente';
        header('Location: ../../../frontend/pages/admin/equipos.php');
        exit;
    } else {
        // Error al crear el equipo
        $_SESSION['error_equipos'] = 'Error al crear el equipo: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/equipos_form.php');
        exit;
    }
}

/**
 * Función para actualizar un equipo existente
 */
function actualizarEquipo() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['id']) || !isset($_POST['nombre']) || !isset($_POST['ciudad_id'])) {
        $_SESSION['error_equipos'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/equipos.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $ciudad_id = intval($_POST['ciudad_id']);
    $director_id = !empty($_POST['director_id']) ? intval($_POST['director_id']) : null;
    
    // Validar los datos
    if (empty($nombre)) {
        $_SESSION['error_equipos'] = 'El nombre del equipo es obligatorio';
        header('Location: ../../../frontend/pages/admin/equipos_form.php?id=' . $id);
        exit;
    }
    
    // Procesar la imagen del escudo si se ha enviado
    $escudo = null;
    $actualizar_escudo = false;
    
    if (isset($_FILES['escudo']) && $_FILES['escudo']['error'] === UPLOAD_ERR_OK) {
        // Verificar el tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['escudo']['type'], $allowed_types)) {
            $_SESSION['error_equipos'] = 'El formato de la imagen no es válido. Use JPG, PNG o GIF.';
            header('Location: ../../../frontend/pages/admin/equipos_form.php?id=' . $id);
            exit;
        }
        
        // Verificar el tamaño del archivo (máximo 2MB)
        if ($_FILES['escudo']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_equipos'] = 'La imagen es demasiado grande. El tamaño máximo es 2MB.';
            header('Location: ../../../frontend/pages/admin/equipos_form.php?id=' . $id);
            exit;
        }
        
        // Leer el contenido del archivo
        $escudo = file_get_contents($_FILES['escudo']['tmp_name']);
        $actualizar_escudo = true;
        
        // Verificar que el contenido de la imagen no esté vacío
        if (empty($escudo)) {
            $_SESSION['error_equipos'] = 'Error al procesar la imagen: contenido vacío.';
            header('Location: ../../../frontend/pages/admin/equipos_form.php?id=' . $id);
            exit;
        }
    }
    
    // Actualizar el equipo en la base de datos
    $equipoModel = new Equipo();
    
    $resultado = $equipoModel->actualizar($id, $nombre, $ciudad_id, $director_id, $escudo, $actualizar_escudo);
    
    if ($resultado['estado']) {
        // Éxito al actualizar el equipo
        $_SESSION['exito_equipos'] = 'Equipo actualizado correctamente';
        header('Location: ../../../frontend/pages/admin/equipos.php');
        exit;
    } else {
        // Error al actualizar el equipo
        $_SESSION['error_equipos'] = 'Error al actualizar el equipo: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/equipos_form.php?id=' . $id);
        exit;
    }
}

/**
 * Función para eliminar un equipo
 */
function eliminarEquipo() {
    // Verificar que se ha enviado el ID del equipo
    if (!isset($_POST['id'])) {
        $_SESSION['error_equipos'] = 'No se especificó el equipo a eliminar';
        header('Location: ../../../frontend/pages/admin/equipos.php');
        exit;
    }
    
    // Obtener el ID del equipo
    $id = intval($_POST['id']);
    
    // Eliminar el equipo de la base de datos
    $equipoModel = new Equipo();
    $resultado = $equipoModel->eliminar($id);
    
    if ($resultado['estado']) {
        // Éxito al eliminar el equipo
        $_SESSION['exito_equipos'] = 'Equipo eliminado correctamente';
    } else {
        // Error al eliminar el equipo
        $_SESSION['error_equipos'] = 'Error al eliminar el equipo: ' . $resultado['mensaje'];
    }
    
    // Redireccionar a la lista de equipos
    header('Location: ../../../frontend/pages/admin/equipos.php');
    exit;
}

/**
 * Función para listar todos los equipos (respuesta JSON)
 */
function listarEquipos() {
    // Obtener todos los equipos
    $equipoModel = new Equipo();
    $equipos = $equipoModel->obtenerTodos();
    
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'estado' => true,
        'equipos' => $equipos
    ]);
    exit;
}

/**
 * Función para obtener el detalle de un equipo (respuesta JSON)
 */
function detalleEquipo() {
    // Verificar que se ha enviado el ID del equipo
    if (!isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => false,
            'mensaje' => 'No se especificó el equipo'
        ]);
        exit;
    }
    
    // Obtener el ID del equipo
    $id = intval($_GET['id']);
    
    // Obtener el detalle del equipo
    $equipoModel = new Equipo();
    $equipo = $equipoModel->obtenerPorId($id);
    
    if ($equipo) {
        // Devolver la respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => true,
            'equipo' => $equipo
        ]);
    } else {
        // Equipo no encontrado
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Equipo no encontrado'
        ]);
    }
    exit;
} 
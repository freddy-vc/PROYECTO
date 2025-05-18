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
        $_SESSION['error_equipos'] = 'Faltan datos requeridos';
        header('Location: ../../../frontend/pages/admin/equipos_form.php');
        exit;
    }
    
    // Recoger los datos del formulario
    $nombre = trim($_POST['nombre']);
    $ciudad_id = intval($_POST['ciudad_id']);
    $director_id = !empty($_POST['director_id']) ? intval($_POST['director_id']) : null;
    
    // Validar los datos
    if (empty($nombre)) {
        $_SESSION['error_equipos'] = 'El nombre del equipo es obligatorio';
        header('Location: ../../../frontend/pages/admin/equipos_form.php');
        exit;
    }
    
    // Procesar la imagen del escudo si se ha subido
    $escudo_data = null;
    if (isset($_FILES['escudo']) && $_FILES['escudo']['error'] === UPLOAD_ERR_OK) {
        // Verificar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['escudo']['type'], $allowed_types)) {
            $_SESSION['error_equipos'] = 'El formato de la imagen no es válido. Se permiten: JPG, PNG, GIF';
            header('Location: ../../../frontend/pages/admin/equipos_form.php');
            exit;
        }
        
        // Verificar tamaño del archivo (máximo 2MB)
        if ($_FILES['escudo']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_equipos'] = 'La imagen no debe superar los 2MB';
            header('Location: ../../../frontend/pages/admin/equipos_form.php');
            exit;
        }
        
        // Leer el contenido de la imagen
        $escudo_data = file_get_contents($_FILES['escudo']['tmp_name']);
    }
    
    try {
        // Crear instancia del modelo y guardar los datos
        $equipoModel = new Equipo();
        
        // Aquí iría el código para guardar el equipo en la base de datos
        $resultado = $equipoModel->crear([
            'nombre' => $nombre,
            'ciudad_id' => $ciudad_id,
            'director_id' => $director_id,
            'escudo' => $escudo_data
        ]);
        
        if ($resultado) {
            $_SESSION['exito_equipos'] = 'Equipo creado correctamente';
            header('Location: ../../../frontend/pages/admin/equipos.php');
        } else {
            $_SESSION['error_equipos'] = 'Error al crear el equipo';
            header('Location: ../../../frontend/pages/admin/equipos_form.php');
        }
    } catch (Exception $e) {
        $_SESSION['error_equipos'] = 'Error: ' . $e->getMessage();
        header('Location: ../../../frontend/pages/admin/equipos_form.php');
    }
}

/**
 * Función para actualizar un equipo existente
 */
function actualizarEquipo() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['id']) || !isset($_POST['nombre']) || !isset($_POST['ciudad_id'])) {
        $_SESSION['error_equipos'] = 'Faltan datos requeridos';
        header('Location: ../../../frontend/pages/admin/equipos.php');
        exit;
    }
    
    // Recoger los datos del formulario
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
    
    // Procesar la imagen del escudo si se ha subido
    $escudo_data = null;
    $actualizar_escudo = false;
    
    if (isset($_FILES['escudo']) && $_FILES['escudo']['error'] === UPLOAD_ERR_OK) {
        // Verificar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['escudo']['type'], $allowed_types)) {
            $_SESSION['error_equipos'] = 'El formato de la imagen no es válido. Se permiten: JPG, PNG, GIF';
            header('Location: ../../../frontend/pages/admin/equipos_form.php?id=' . $id);
            exit;
        }
        
        // Verificar tamaño del archivo (máximo 2MB)
        if ($_FILES['escudo']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_equipos'] = 'La imagen no debe superar los 2MB';
            header('Location: ../../../frontend/pages/admin/equipos_form.php?id=' . $id);
            exit;
        }
        
        // Leer el contenido de la imagen
        $escudo_data = file_get_contents($_FILES['escudo']['tmp_name']);
        $actualizar_escudo = true;
    }
    
    try {
        // Crear instancia del modelo y actualizar los datos
        $equipoModel = new Equipo();
        
        // Preparar datos para actualizar
        $datos = [
            'id' => $id,
            'nombre' => $nombre,
            'ciudad_id' => $ciudad_id,
            'director_id' => $director_id
        ];
        
        // Agregar el escudo solo si se ha subido una nueva imagen
        if ($actualizar_escudo) {
            $datos['escudo'] = $escudo_data;
        }
        
        // Aquí iría el código para actualizar el equipo en la base de datos
        $resultado = $equipoModel->actualizar($datos);
        
        if ($resultado) {
            $_SESSION['exito_equipos'] = 'Equipo actualizado correctamente';
            header('Location: ../../../frontend/pages/admin/equipos.php');
        } else {
            $_SESSION['error_equipos'] = 'Error al actualizar el equipo';
            header('Location: ../../../frontend/pages/admin/equipos_form.php?id=' . $id);
        }
    } catch (Exception $e) {
        $_SESSION['error_equipos'] = 'Error: ' . $e->getMessage();
        header('Location: ../../../frontend/pages/admin/equipos_form.php?id=' . $id);
    }
}

/**
 * Función para eliminar un equipo
 */
function eliminarEquipo() {
    // Verificar que se ha enviado el ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['error_equipos'] = 'ID de equipo no válido';
        header('Location: ../../../frontend/pages/admin/equipos.php');
        exit;
    }
    
    $id = intval($_GET['id']);
    
    try {
        // Crear instancia del modelo y eliminar el equipo
        $equipoModel = new Equipo();
        
        // Aquí iría el código para eliminar el equipo de la base de datos
        $resultado = $equipoModel->eliminar($id);
        
        if ($resultado) {
            $_SESSION['exito_equipos'] = 'Equipo eliminado correctamente';
        } else {
            $_SESSION['error_equipos'] = 'Error al eliminar el equipo';
        }
    } catch (Exception $e) {
        $_SESSION['error_equipos'] = 'Error: ' . $e->getMessage();
    }
    
    // Redireccionar a la lista de equipos
    header('Location: ../../../frontend/pages/admin/equipos.php');
}

/**
 * Función para listar equipos (JSON)
 */
function listarEquipos() {
    try {
        // Recoger parámetros de paginación y filtrado
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = isset($_GET['limite']) ? intval($_GET['limite']) : 10;
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
        $ciudad_id = isset($_GET['ciudad_id']) && is_numeric($_GET['ciudad_id']) ? intval($_GET['ciudad_id']) : null;
        
        // Crear instancia del modelo
        $equipoModel = new Equipo();
        
        // Aquí iría el código para obtener los equipos de la base de datos con paginación y filtros
        $equipos = $equipoModel->listar($pagina, $limite, $busqueda, $ciudad_id);
        $total = $equipoModel->contarEquipos($busqueda, $ciudad_id);
        
        // Enviar respuesta JSON
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => true,
            'equipos' => $equipos,
            'total' => $total,
            'pagina_actual' => $pagina,
            'total_paginas' => ceil($total / $limite)
        ]);
    } catch (Exception $e) {
        // Enviar respuesta de error
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => false,
            'mensaje' => $e->getMessage()
        ]);
    }
}

/**
 * Función para obtener los detalles de un equipo (JSON)
 */
function detalleEquipo() {
    // Verificar que se ha enviado el ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => false,
            'mensaje' => 'ID de equipo no válido'
        ]);
        exit;
    }
    
    $id = intval($_GET['id']);
    
    try {
        // Crear instancia del modelo
        $equipoModel = new Equipo();
        
        // Aquí iría el código para obtener los detalles del equipo de la base de datos
        $equipo = $equipoModel->obtenerPorId($id);
        
        if ($equipo) {
            // Enviar respuesta JSON
            header('Content-Type: application/json');
            echo json_encode([
                'estado' => true,
                'equipo' => $equipo
            ]);
        } else {
            // Enviar respuesta de error
            header('Content-Type: application/json');
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Equipo no encontrado'
            ]);
        }
    } catch (Exception $e) {
        // Enviar respuesta de error
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => false,
            'mensaje' => $e->getMessage()
        ]);
    }
} 
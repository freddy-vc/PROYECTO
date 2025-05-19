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
require_once __DIR__ . '/../../models/Jugador.php';
require_once __DIR__ . '/../../models/Equipo.php';

// Verificar que se ha enviado una acción
if (!isset($_REQUEST['accion'])) {
    $_SESSION['error_jugadores'] = 'No se especificó ninguna acción';
    header('Location: ../../../frontend/pages/admin/jugadores.php');
    exit;
}

// Procesar la acción solicitada
$accion = $_REQUEST['accion'];

switch ($accion) {
    case 'crear':
        crearJugador();
        break;
    
    case 'actualizar':
        actualizarJugador();
        break;
    
    case 'eliminar':
        eliminarJugador();
        break;
    
    case 'listar':
        listarJugadores();
        break;
    
    case 'detalle':
        detalleJugador();
        break;
    
    default:
        $_SESSION['error_jugadores'] = 'Acción no reconocida';
        header('Location: ../../../frontend/pages/admin/jugadores.php');
        break;
}

/**
 * Función para crear un nuevo jugador
 */
function crearJugador() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['nombres']) || !isset($_POST['apellidos']) || !isset($_POST['fecha_nacimiento']) || 
        !isset($_POST['documento']) || !isset($_POST['equipo_id']) || !isset($_POST['posicion']) || 
        !isset($_POST['numero_camiseta'])) {
        
        $_SESSION['error_jugadores'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/jugadores_form.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $fechaNacimiento = trim($_POST['fecha_nacimiento']);
    $documento = trim($_POST['documento']);
    $equipoId = intval($_POST['equipo_id']);
    $posicion = trim($_POST['posicion']);
    $numeroCamiseta = intval($_POST['numero_camiseta']);
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'activo';
    $estatura = !empty($_POST['estatura']) ? intval($_POST['estatura']) : null;
    $peso = !empty($_POST['peso']) ? floatval($_POST['peso']) : null;
    
    // Validar los datos
    if (empty($nombres) || empty($apellidos) || empty($fechaNacimiento) || empty($documento) || 
        empty($posicion) || $numeroCamiseta < 1 || $numeroCamiseta > 99) {
        $_SESSION['error_jugadores'] = 'Hay errores en los datos del formulario';
        header('Location: ../../../frontend/pages/admin/jugadores_form.php');
        exit;
    }
    
    // Procesar la foto del jugador si se ha enviado
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Verificar el tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['foto']['type'], $allowed_types)) {
            $_SESSION['error_jugadores'] = 'El formato de la imagen no es válido. Use JPG, PNG o GIF.';
            header('Location: ../../../frontend/pages/admin/jugadores_form.php');
            exit;
        }
        
        // Verificar el tamaño del archivo (máximo 2MB)
        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_jugadores'] = 'La imagen es demasiado grande. El tamaño máximo es 2MB.';
            header('Location: ../../../frontend/pages/admin/jugadores_form.php');
            exit;
        }
        
        // Leer el contenido del archivo
        $foto = file_get_contents($_FILES['foto']['tmp_name']);
    }
    
    // Crear el jugador en la base de datos
    $jugadorModel = new Jugador();
    $resultado = $jugadorModel->crear([
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'fecha_nacimiento' => $fechaNacimiento,
        'documento' => $documento,
        'cod_equ' => $equipoId,
        'posicion' => $posicion,
        'num_camiseta' => $numeroCamiseta,
        'estado' => $estado,
        'estatura' => $estatura,
        'peso' => $peso,
        'foto' => $foto
    ]);
    
    if ($resultado['estado']) {
        // Éxito al crear el jugador
        $_SESSION['exito_jugadores'] = 'Jugador registrado correctamente';
        header('Location: ../../../frontend/pages/admin/jugadores.php');
        exit;
    } else {
        // Error al crear el jugador
        $_SESSION['error_jugadores'] = 'Error al registrar el jugador: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/jugadores_form.php');
        exit;
    }
}

/**
 * Función para actualizar un jugador existente
 */
function actualizarJugador() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['id']) || !isset($_POST['nombres']) || !isset($_POST['apellidos']) || 
        !isset($_POST['fecha_nacimiento']) || !isset($_POST['documento']) || !isset($_POST['equipo_id']) || 
        !isset($_POST['posicion']) || !isset($_POST['numero_camiseta'])) {
        
        $_SESSION['error_jugadores'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/jugadores.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $id = intval($_POST['id']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $fechaNacimiento = trim($_POST['fecha_nacimiento']);
    $documento = trim($_POST['documento']);
    $equipoId = intval($_POST['equipo_id']);
    $posicion = trim($_POST['posicion']);
    $numeroCamiseta = intval($_POST['numero_camiseta']);
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'activo';
    $estatura = !empty($_POST['estatura']) ? intval($_POST['estatura']) : null;
    $peso = !empty($_POST['peso']) ? floatval($_POST['peso']) : null;
    
    // Validar los datos
    if (empty($nombres) || empty($apellidos) || empty($fechaNacimiento) || empty($documento) || 
        empty($posicion) || $numeroCamiseta < 1 || $numeroCamiseta > 99) {
        $_SESSION['error_jugadores'] = 'Hay errores en los datos del formulario';
        header('Location: ../../../frontend/pages/admin/jugadores_form.php?id=' . $id);
        exit;
    }
    
    // Procesar la foto del jugador si se ha enviado
    $foto = null;
    $actualizar_foto = false;
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Verificar el tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['foto']['type'], $allowed_types)) {
            $_SESSION['error_jugadores'] = 'El formato de la imagen no es válido. Use JPG, PNG o GIF.';
            header('Location: ../../../frontend/pages/admin/jugadores_form.php?id=' . $id);
            exit;
        }
        
        // Verificar el tamaño del archivo (máximo 2MB)
        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_jugadores'] = 'La imagen es demasiado grande. El tamaño máximo es 2MB.';
            header('Location: ../../../frontend/pages/admin/jugadores_form.php?id=' . $id);
            exit;
        }
        
        // Leer el contenido del archivo
        $foto = file_get_contents($_FILES['foto']['tmp_name']);
        $actualizar_foto = true;
    }
    
    // Actualizar el jugador en la base de datos
    $jugadorModel = new Jugador();
    $resultado = $jugadorModel->actualizar([
        'cod_jug' => $id,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'fecha_nacimiento' => $fechaNacimiento,
        'documento' => $documento,
        'cod_equ' => $equipoId,
        'posicion' => $posicion,
        'num_camiseta' => $numeroCamiseta,
        'estado' => $estado,
        'estatura' => $estatura,
        'peso' => $peso,
        'foto' => $foto,
        'actualizar_foto' => $actualizar_foto
    ]);
    
    if ($resultado['estado']) {
        // Éxito al actualizar el jugador
        $_SESSION['exito_jugadores'] = 'Jugador actualizado correctamente';
        header('Location: ../../../frontend/pages/admin/jugadores.php');
        exit;
    } else {
        // Error al actualizar el jugador
        $_SESSION['error_jugadores'] = 'Error al actualizar el jugador: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/jugadores_form.php?id=' . $id);
        exit;
    }
}

/**
 * Función para eliminar un jugador
 */
function eliminarJugador() {
    // Verificar que se ha enviado el ID del jugador
    if (!isset($_POST['id'])) {
        $_SESSION['error_jugadores'] = 'No se especificó el jugador a eliminar';
        header('Location: ../../../frontend/pages/admin/jugadores.php');
        exit;
    }
    
    // Obtener el ID del jugador
    $id = intval($_POST['id']);
    
    // Eliminar el jugador de la base de datos
    $jugadorModel = new Jugador();
    $resultado = $jugadorModel->eliminar($id);
    
    if ($resultado['estado']) {
        // Éxito al eliminar el jugador
        $_SESSION['exito_jugadores'] = 'Jugador eliminado correctamente';
    } else {
        // Error al eliminar el jugador
        $_SESSION['error_jugadores'] = 'Error al eliminar el jugador: ' . $resultado['mensaje'];
    }
    
    // Redireccionar a la lista de jugadores
    header('Location: ../../../frontend/pages/admin/jugadores.php');
    exit;
}

/**
 * Función para listar todos los jugadores (respuesta JSON)
 */
function listarJugadores() {
    // Obtener todos los jugadores
    $jugadorModel = new Jugador();
    $jugadores = $jugadorModel->obtenerTodosConEstadisticas();
    
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'estado' => true,
        'jugadores' => $jugadores
    ]);
    exit;
}

/**
 * Función para obtener el detalle de un jugador (respuesta JSON)
 */
function detalleJugador() {
    // Verificar que se ha enviado el ID del jugador
    if (!isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => false,
            'mensaje' => 'No se especificó el jugador'
        ]);
        exit;
    }
    
    // Obtener el ID del jugador
    $id = intval($_GET['id']);
    
    // Obtener el detalle del jugador
    $jugadorModel = new Jugador();
    $jugador = $jugadorModel->obtenerDetalleCompleto($id);
    
    if ($jugador) {
        // Devolver la respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => true,
            'jugador' => $jugador
        ]);
    } else {
        // Jugador no encontrado
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Jugador no encontrado'
        ]);
    }
    exit;
} 
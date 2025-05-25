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
require_once __DIR__ . '/../../models/Partido.php';
require_once __DIR__ . '/../../models/Cancha.php';
require_once __DIR__ . '/../../models/Equipo.php';

// Verificar que se ha enviado una acción
if (!isset($_REQUEST['accion'])) {
    $_SESSION['error_partidos'] = 'No se especificó ninguna acción';
    header('Location: ../../../frontend/pages/admin/partidos.php');
    exit;
}

// Procesar la acción solicitada
$accion = $_REQUEST['accion'];

switch ($accion) {
    case 'crear':
        crearPartido();
        break;
    
    case 'actualizar':
        actualizarPartido();
        break;
    
    case 'eliminar':
        eliminarPartido();
        break;
    
    case 'listar':
        listarPartidos();
        break;
        
    case 'registrar_gol':
        registrarGol();
        break;
        
    case 'registrar_asistencia':
        registrarAsistencia();
        break;
        
    case 'registrar_falta':
        registrarFalta();
        break;
    
    default:
        $_SESSION['error_partidos'] = 'Acción no reconocida';
        header('Location: ../../../frontend/pages/admin/partidos.php');
        break;
}

/**
 * Función para crear un nuevo partido
 */
function crearPartido() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['fecha']) || !isset($_POST['hora']) || !isset($_POST['cancha_id']) || 
        !isset($_POST['equipo_local']) || !isset($_POST['equipo_visitante']) || !isset($_POST['fase'])) {
        $_SESSION['error_partidos'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/partidos_form.php');
        exit;
    }
    
    // Validar que los equipos sean diferentes
    if ($_POST['equipo_local'] == $_POST['equipo_visitante']) {
        $_SESSION['error_partidos'] = 'El equipo local y el visitante no pueden ser el mismo';
        header('Location: ../../../frontend/pages/admin/partidos_form.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $fecha = trim($_POST['fecha']);
    $hora = trim($_POST['hora']);
    $cancha_id = intval($_POST['cancha_id']);
    $equipo_local = intval($_POST['equipo_local']);
    $equipo_visitante = intval($_POST['equipo_visitante']);
    $fase = trim($_POST['fase']);
    
    // Crear el partido en la base de datos
    $partidoModel = new Partido();
    $resultado = $partidoModel->crear($fecha, $hora, $cancha_id, $equipo_local, $equipo_visitante, $fase);
    
    if ($resultado['estado']) {
        // Éxito al crear el partido
        $_SESSION['exito_partidos'] = 'Partido creado correctamente';
        header('Location: ../../../frontend/pages/admin/partidos.php');
        exit;
    } else {
        // Error al crear el partido
        $_SESSION['error_partidos'] = 'Error al crear el partido: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/partidos_form.php');
        exit;
    }
}

/**
 * Función para actualizar un partido existente
 */
function actualizarPartido() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['id']) || !isset($_POST['fecha']) || !isset($_POST['hora']) || 
        !isset($_POST['cancha_id']) || !isset($_POST['estado']) || !isset($_POST['fase'])) {
        $_SESSION['error_partidos'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/partidos.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $id = intval($_POST['id']);
    $fecha = trim($_POST['fecha']);
    $hora = trim($_POST['hora']);
    $cancha_id = intval($_POST['cancha_id']);
    $estado = trim($_POST['estado']);
    $fase = trim($_POST['fase']);
    
    // Actualizar el partido en la base de datos
    $partidoModel = new Partido();
    $resultado = $partidoModel->actualizar($id, $fecha, $hora, $cancha_id, $estado, $fase);
    
    // --- Procesar estadísticas temporales si existen ---
    if (isset($_POST['estadisticas_temporales'])) {
        $stats = json_decode($_POST['estadisticas_temporales'], true);
        if ($stats) {
            // Goles
            foreach ($stats['goles'] as $gol) {
                if (isset($gol['id']) && strpos($gol['id'], 'temp_') === 0) {
                    // Nuevo gol
                    $partidoModel->registrarGol($id, $gol['jugador_id'], $gol['minuto'], $gol['tipo']);
                } elseif (isset($gol['cod_gol'])) {
                    // Actualizar gol existente
                    $partidoModel->actualizarGol($gol['cod_gol'], $id, $gol['jugador_id'], $gol['minuto'], $gol['tipo']);
                }
            }
            // Asistencias
            foreach ($stats['asistencias'] as $asis) {
                if (isset($asis['id']) && strpos($asis['id'], 'temp_') === 0) {
                    $partidoModel->registrarAsistencia($id, $asis['jugador_id'], $asis['minuto']);
                } elseif (isset($asis['cod_asis'])) {
                    $partidoModel->actualizarAsistencia($asis['cod_asis'], $id, $asis['jugador_id'], $asis['minuto']);
                }
            }
            // Faltas
            foreach ($stats['faltas'] as $falta) {
                if (isset($falta['id']) && strpos($falta['id'], 'temp_') === 0) {
                    $partidoModel->registrarFalta($id, $falta['jugador_id'], $falta['minuto'], $falta['tipo_falta']);
                } elseif (isset($falta['cod_falta'])) {
                    $partidoModel->actualizarFalta($falta['cod_falta'], $id, $falta['jugador_id'], $falta['minuto'], $falta['tipo_falta']);
                }
            }
        }
    }
    // --- Procesar eliminados ---
    if (isset($_POST['estadisticas_eliminadas'])) {
        $deleted = json_decode($_POST['estadisticas_eliminadas'], true);
        if ($deleted) {
            foreach ($deleted['goles'] as $cod_gol) {
                $partidoModel->eliminarGol($cod_gol);
            }
            foreach ($deleted['asistencias'] as $cod_asis) {
                $partidoModel->eliminarAsistencia($cod_asis);
            }
            foreach ($deleted['faltas'] as $cod_falta) {
                $partidoModel->eliminarFalta($cod_falta);
            }
        }
    }
    // ---
    if ($resultado['estado']) {
        // Éxito al actualizar el partido
        $_SESSION['exito_partidos'] = 'Partido actualizado correctamente';
        header('Location: ../../../frontend/pages/admin/partidos.php');
        exit;
    } else {
        // Error al actualizar el partido
        $_SESSION['error_partidos'] = 'Error al actualizar el partido: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/partidos_form.php?id=' . $id);
        exit;
    }
}

/**
 * Función para eliminar un partido
 */
function eliminarPartido() {
    // Verificar que se ha enviado el ID del partido
    if (!isset($_POST['id'])) {
        $_SESSION['error_partidos'] = 'No se especificó el partido a eliminar';
        header('Location: ../../../frontend/pages/admin/partidos.php');
        exit;
    }
    
    // Obtener el ID del partido
    $id = intval($_POST['id']);
    
    // Eliminar el partido de la base de datos
    $partidoModel = new Partido();
    $resultado = $partidoModel->eliminar($id);
    
    if ($resultado['estado']) {
        // Éxito al eliminar el partido
        $_SESSION['exito_partidos'] = 'Partido eliminado correctamente';
    } else {
        // Error al eliminar el partido
        $_SESSION['error_partidos'] = 'Error al eliminar el partido: ' . $resultado['mensaje'];
    }
    
    // Redireccionar a la lista de partidos
    header('Location: ../../../frontend/pages/admin/partidos.php');
    exit;
}

/**
 * Función para registrar un gol en un partido
 */
function registrarGol() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['partido_id']) || !isset($_POST['jugador_id']) || 
        !isset($_POST['minuto']) || !isset($_POST['tipo_gol'])) {
        $_SESSION['error_partidos'] = 'Faltan datos obligatorios para registrar el gol';
        header('Location: ../../../frontend/pages/admin/partidos.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $partido_id = intval($_POST['partido_id']);
    $jugador_id = intval($_POST['jugador_id']);
    $minuto = intval($_POST['minuto']);
    $tipo_gol = trim($_POST['tipo_gol']);
    
    // Registrar el gol
    $partidoModel = new Partido();
    $resultado = $partidoModel->registrarGol($partido_id, $jugador_id, $minuto, $tipo_gol);
    
    if ($resultado['estado']) {
        // Éxito al registrar el gol
        $_SESSION['exito_partidos'] = 'Gol registrado correctamente';
    } else {
        // Error al registrar el gol
        $_SESSION['error_partidos'] = 'Error al registrar el gol: ' . $resultado['mensaje'];
    }
    
    // Redireccionar a la página del partido
    header('Location: ../../../frontend/pages/admin/partidos_form.php?id=' . $partido_id);
    exit;
}

/**
 * Función para registrar una asistencia en un partido
 */
function registrarAsistencia() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['partido_id']) || !isset($_POST['jugador_id']) || !isset($_POST['minuto'])) {
        $_SESSION['error_partidos'] = 'Faltan datos obligatorios para registrar la asistencia';
        header('Location: ../../../frontend/pages/admin/partidos.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $partido_id = intval($_POST['partido_id']);
    $jugador_id = intval($_POST['jugador_id']);
    $minuto = intval($_POST['minuto']);
    
    // Registrar la asistencia
    $partidoModel = new Partido();
    $resultado = $partidoModel->registrarAsistencia($partido_id, $jugador_id, $minuto);
    
    if ($resultado['estado']) {
        // Éxito al registrar la asistencia
        $_SESSION['exito_partidos'] = 'Asistencia registrada correctamente';
    } else {
        // Error al registrar la asistencia
        $_SESSION['error_partidos'] = 'Error al registrar la asistencia: ' . $resultado['mensaje'];
    }
    
    // Redireccionar a la página del partido
    header('Location: ../../../frontend/pages/admin/partidos_form.php?id=' . $partido_id);
    exit;
}

/**
 * Función para registrar una falta en un partido
 */
function registrarFalta() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['partido_id']) || !isset($_POST['jugador_id']) || 
        !isset($_POST['minuto']) || !isset($_POST['tipo_falta'])) {
        $_SESSION['error_partidos'] = 'Faltan datos obligatorios para registrar la falta';
        header('Location: ../../../frontend/pages/admin/partidos.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $partido_id = intval($_POST['partido_id']);
    $jugador_id = intval($_POST['jugador_id']);
    $minuto = intval($_POST['minuto']);
    $tipo_falta = trim($_POST['tipo_falta']);
    
    // Registrar la falta
    $partidoModel = new Partido();
    $resultado = $partidoModel->registrarFalta($partido_id, $jugador_id, $minuto, $tipo_falta);
    
    if ($resultado['estado']) {
        // Éxito al registrar la falta
        $_SESSION['exito_partidos'] = 'Falta registrada correctamente';
    } else {
        // Error al registrar la falta
        $_SESSION['error_partidos'] = 'Error al registrar la falta: ' . $resultado['mensaje'];
    }
    
    // Redireccionar a la página del partido
    header('Location: ../../../frontend/pages/admin/partidos_form.php?id=' . $partido_id);
    exit;
}

/**
 * Función para listar todos los partidos (respuesta JSON)
 */
function listarPartidos() {
    // Obtener el filtro si existe
    $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : null;
    
    // Obtener todos los partidos
    $partidoModel = new Partido();
    $partidos = $partidoModel->obtenerTodos($filtro);
    
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'estado' => true,
        'partidos' => $partidos
    ]);
    exit;
} 
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
    
    // Validar límites de partidos por fase
    $partidoModel = new Partido();
    $partidos_fase = $partidoModel->contarPartidosPorFase($fase);
    
    $limite_alcanzado = false;
    $mensaje_error = '';
    
    switch ($fase) {
        case 'cuartos':
            if ($partidos_fase >= 4) {
                $limite_alcanzado = true;
                $mensaje_error = 'Ya se han creado los 4 partidos máximos para cuartos de final';
            }
            break;
        case 'semis':
            if ($partidos_fase >= 2) {
                $limite_alcanzado = true;
                $mensaje_error = 'Ya se han creado los 2 partidos máximos para semifinales';
            }
            break;
        case 'final':
            if ($partidos_fase >= 1) {
                $limite_alcanzado = true;
                $mensaje_error = 'Ya se ha creado el partido de la final';
            }
            break;
    }
    
    if ($limite_alcanzado) {
        $_SESSION['error_partidos'] = $mensaje_error;
        header('Location: ../../../frontend/pages/admin/partidos_form.php');
        exit;
    }
    
    // Crear el partido en la base de datos
    $resultado = $partidoModel->crear($fecha, $hora, $cancha_id, $equipo_local, $equipo_visitante, $fase);
    
    if ($resultado) {
        // Éxito al crear el partido
        $_SESSION['exito_partidos'] = 'Partido creado correctamente';
        header('Location: ../../../frontend/pages/admin/partidos.php');
        exit;
    } else {
        // Error al crear el partido
        $_SESSION['error_partidos'] = 'Error al crear el partido';
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
        !isset($_POST['cancha_id']) || !isset($_POST['estado'])) {
        $isAjax = (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios']);
            exit;
        } else {
            $_SESSION['error_partidos'] = 'Faltan datos obligatorios';
            header('Location: ../../../frontend/pages/admin/partidos.php');
            exit;
        }
    }
    
    // Obtener los datos del formulario
    $id = intval($_POST['id']);
    $fecha = trim($_POST['fecha']);
    $hora = trim($_POST['hora']);
    $cancha_id = intval($_POST['cancha_id']);
    $estado = trim($_POST['estado']);
    
    // Verificar si es una actualización AJAX en dos pasos
    $isAjax = (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    );
    
    // Verificar si es el paso 1 (guardar estadísticas)
    $isFirstStep = isset($_POST['_ajax_save_stats']) && $_POST['_ajax_save_stats'] === '1';
    
    // Verificar si es el paso 2 (finalizar partido)
    $isSecondStep = isset($_POST['_ajax_finalize']) && $_POST['_ajax_finalize'] === '1';
    
    // Actualizar el partido en la base de datos
    $partidoModel = new Partido();
    try {
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
        
        // Si es el primer paso (solo guardando estadísticas), no cambiar el estado a finalizado
        if ($isFirstStep && $estado === 'finalizado') {
            $estado = 'programado'; // Forzar a programado para el primer paso
        }
        
        // Actualizar el partido
        $resultado = $partidoModel->actualizar($id, $fecha, $hora, $cancha_id, $estado);
        
        if ($resultado) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'id' => $id,
                    'message' => $isFirstStep ? 'Estadísticas guardadas correctamente' : 'Partido actualizado correctamente',
                    'step' => $isFirstStep ? 1 : ($isSecondStep ? 2 : 0)
                ]);
                exit;
            } else {
                $_SESSION['exito_partidos'] = 'Partido actualizado correctamente';
                header('Location: ../../../frontend/pages/admin/partidos.php');
                exit;
            }
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al actualizar el partido. La base de datos no reportó detalles adicionales.'
                ]);
                exit;
            } else {
                $_SESSION['error_partidos'] = 'Error al actualizar el partido. La base de datos no reportó detalles adicionales.';
                header('Location: ../../../frontend/pages/admin/partidos_form.php?id=' . $id);
                exit;
            }
        }
    } catch (Exception $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Error al actualizar el partido: ' . $e->getMessage()
            ]);
            exit;
        } else {
            $_SESSION['error_partidos'] = 'Error al actualizar el partido: ' . $e->getMessage();
            header('Location: ../../../frontend/pages/admin/partidos_form.php?id=' . $id);
            exit;
        }
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
    try {
        $partidoModel = new Partido();
        $resultado = $partidoModel->eliminar($id);
        
        if ($resultado['estado']) {
            // Éxito al eliminar el partido
            $_SESSION['exito_partidos'] = 'Partido eliminado correctamente';
        } else {
            // Error al eliminar el partido
            $_SESSION['error_partidos'] = $resultado['mensaje'];
        }
    } catch (Exception $e) {
        // Error al eliminar el partido
        $_SESSION['error_partidos'] = 'Error al eliminar el partido: ' . $e->getMessage();
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
    try {
        $partidoModel = new Partido();
        $resultado = $partidoModel->registrarGol($partido_id, $jugador_id, $minuto, $tipo_gol);
        
        // Éxito al registrar el gol
        $_SESSION['exito_partidos'] = 'Gol registrado correctamente';
    } catch (Exception $e) {
        // Error al registrar el gol
        $_SESSION['error_partidos'] = 'Error al registrar el gol: ' . $e->getMessage();
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
    try {
        $partidoModel = new Partido();
        $resultado = $partidoModel->registrarAsistencia($partido_id, $jugador_id, $minuto);
        
        // Éxito al registrar la asistencia
        $_SESSION['exito_partidos'] = 'Asistencia registrada correctamente';
    } catch (Exception $e) {
        // Error al registrar la asistencia
        $_SESSION['error_partidos'] = 'Error al registrar la asistencia: ' . $e->getMessage();
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
    try {
        $partidoModel = new Partido();
        $resultado = $partidoModel->registrarFalta($partido_id, $jugador_id, $minuto, $tipo_falta);
        
        // Éxito al registrar la falta
        $_SESSION['exito_partidos'] = 'Falta registrada correctamente';
    } catch (Exception $e) {
        // Error al registrar la falta
        $_SESSION['error_partidos'] = 'Error al registrar la falta: ' . $e->getMessage();
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
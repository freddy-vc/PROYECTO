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
require_once __DIR__ . '/../../models/Partido.php';

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
    if (!isset($_POST['nombres']) || !isset($_POST['apellidos']) || !isset($_POST['equipo_id']) || !isset($_POST['posicion']) || (!isset($_POST['numero_camiseta']) && !isset($_POST['dorsal']))) {
        $_SESSION['error_jugadores'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/jugadores_form.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $equipoId = intval($_POST['equipo_id']);
    $posicion = trim($_POST['posicion']);
    // Usar dorsal si está disponible, de lo contrario usar numero_camiseta
    $dorsal = isset($_POST['dorsal']) ? intval($_POST['dorsal']) : intval($_POST['numero_camiseta']);
    
    // Validar los datos
    if (empty($nombres) || empty($apellidos) || empty($posicion) || $dorsal < 1 || $dorsal > 99) {
        $_SESSION['error_jugadores'] = 'Hay errores en los datos del formulario';
        header('Location: ../../../frontend/pages/admin/jugadores_form.php');
        exit;
    }
    
    // Procesar la foto del jugador si se ha enviado
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['foto']['type'], $allowed_types)) {
            $_SESSION['error_jugadores'] = 'El formato de la imagen no es válido. Use JPG, PNG o GIF.';
            header('Location: ../../../frontend/pages/admin/jugadores_form.php');
            exit;
        }
        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_jugadores'] = 'La imagen es demasiado grande. El tamaño máximo es 2MB.';
            header('Location: ../../../frontend/pages/admin/jugadores_form.php');
            exit;
        }
        $foto = file_get_contents($_FILES['foto']['tmp_name']);
        
        // Verificar que el contenido de la imagen no esté vacío
        if (empty($foto)) {
            $_SESSION['error_jugadores'] = 'Error al procesar la imagen: contenido vacío.';
            header('Location: ../../../frontend/pages/admin/jugadores_form.php');
            exit;
        }
    }
    
    // Crear el jugador en la base de datos
    $jugadorModel = new Jugador();
    $resultado = $jugadorModel->crear([
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'posicion' => $posicion,
        'dorsal' => $dorsal,
        'cod_equ' => $equipoId,
        'foto' => $foto
    ]);
    
    if ($resultado['estado']) {
        $_SESSION['exito_jugadores'] = 'Jugador registrado correctamente';
        header('Location: ../../../frontend/pages/admin/jugadores.php');
        exit;
    } else {
        $_SESSION['error_jugadores'] = 'Error al registrar el jugador: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/jugadores_form.php');
        exit;
    }
}

/**
 * Función para actualizar un jugador existente
 */
function actualizarJugador() {
    if (!isset($_POST['id']) || !isset($_POST['nombres']) || !isset($_POST['apellidos']) || !isset($_POST['equipo_id']) || !isset($_POST['posicion']) || (!isset($_POST['numero_camiseta']) && !isset($_POST['dorsal']))) {
        $_SESSION['error_jugadores'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/jugadores.php');
        exit;
    }

    $id = intval($_POST['id']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $equipoId = intval($_POST['equipo_id']);
    $posicion = trim($_POST['posicion']);
    // Usar dorsal si está disponible, de lo contrario usar numero_camiseta
    $dorsal = isset($_POST['dorsal']) ? intval($_POST['dorsal']) : intval($_POST['numero_camiseta']);
    
    if (empty($nombres) || empty($apellidos) || empty($posicion) || $dorsal < 1 || $dorsal > 99) {
        $_SESSION['error_jugadores'] = 'Hay errores en los datos del formulario';
        header('Location: ../../../frontend/pages/admin/jugadores_form.php?id=' . $id);
        exit;
    }

    $foto = null;
    $actualizar_foto = false;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['foto']['type'], $allowed_types)) {
            $_SESSION['error_jugadores'] = 'El formato de la imagen no es válido. Use JPG, PNG o GIF.';
            header('Location: ../../../frontend/pages/admin/jugadores_form.php?id=' . $id);
            exit;
        }
        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_jugadores'] = 'La imagen es demasiado grande. El tamaño máximo es 2MB.';
            header('Location: ../../../frontend/pages/admin/jugadores_form.php?id=' . $id);
            exit;
        }
        $foto = file_get_contents($_FILES['foto']['tmp_name']);
        $actualizar_foto = true;
        
        // Verificar que el contenido de la imagen no esté vacío
        if (empty($foto)) {
            $_SESSION['error_jugadores'] = 'Error al procesar la imagen: contenido vacío.';
            header('Location: ../../../frontend/pages/admin/jugadores_form.php?id=' . $id);
            exit;
        }
    }

    $jugadorModel = new Jugador();
    
    $resultado = $jugadorModel->actualizar([
        'cod_jug' => $id,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'posicion' => $posicion,
        'dorsal' => $dorsal,
        'cod_equ' => $equipoId,
        'foto' => $foto,
        'actualizar_foto' => $actualizar_foto
    ]);

    if ($resultado['estado']) {
        // Procesar estadísticas temporales si existen
        if (isset($_POST['estadisticas_temporales'])) {
            $estadisticas = json_decode($_POST['estadisticas_temporales'], true);
            $partidoModel = new Partido();
            
            // Procesar goles
            if (isset($estadisticas['goles'])) {
                foreach ($estadisticas['goles'] as $gol) {
                    try {
                        // Si tiene cod_gol, es edición, si no, es nuevo
                        if (isset($gol['cod_gol'])) {
                            // Verificar que exista el partido_id, si no usar cod_par
                            $partidoId = isset($gol['partido_id']) ? $gol['partido_id'] : (isset($gol['cod_par']) ? $gol['cod_par'] : null);
                            
                            if ($partidoId === null) {
                                continue; // Saltamos este gol
                            }
                            
                            $partidoModel->actualizarGol(
                                $gol['cod_gol'], 
                                $partidoId, 
                                $id, 
                                $gol['minuto'], 
                                $gol['tipo']
                            );
                        } else {
                            // Para nuevos goles, el partido_id es obligatorio
                            if (!isset($gol['partido_id'])) {
                                continue; // Saltamos este gol
                            }
                            
                            $partidoModel->registrarGol(
                                $gol['partido_id'], 
                                $id, 
                                $gol['minuto'], 
                                $gol['tipo']
                            );
                        }
                    } catch (Exception $e) {
                        // Continuamos con el siguiente gol
                    }
                }
            }
            
            // Procesar asistencias
            if (isset($estadisticas['asistencias'])) {
                foreach ($estadisticas['asistencias'] as $asistencia) {
                    try {
                        if (isset($asistencia['cod_asis'])) {
                            // Verificar que exista el partido_id, si no usar cod_par
                            $partidoId = isset($asistencia['partido_id']) ? $asistencia['partido_id'] : (isset($asistencia['cod_par']) ? $asistencia['cod_par'] : null);
                            
                            if ($partidoId === null) {
                                continue; // Saltamos esta asistencia
                            }
                            
                            $partidoModel->actualizarAsistencia(
                                $asistencia['cod_asis'], 
                                $partidoId, 
                                $id, 
                                $asistencia['minuto']
                            );
                        } else {
                            // Para nuevas asistencias, el partido_id es obligatorio
                            if (!isset($asistencia['partido_id'])) {
                                continue; // Saltamos esta asistencia
                            }
                            
                            $partidoModel->registrarAsistencia(
                                $asistencia['partido_id'], 
                                $id, 
                                $asistencia['minuto']
                            );
                        }
                    } catch (Exception $e) {
                        // Continuamos con la siguiente asistencia
                    }
                }
            }
            
            // Procesar faltas
            if (isset($estadisticas['faltas'])) {
                foreach ($estadisticas['faltas'] as $falta) {
                    try {
                        if (isset($falta['cod_falta'])) {
                            // Verificar que exista el partido_id, si no usar cod_par
                            $partidoId = isset($falta['partido_id']) ? $falta['partido_id'] : (isset($falta['cod_par']) ? $falta['cod_par'] : null);
                            
                            if ($partidoId === null) {
                                continue; // Saltamos esta falta
                            }
                            
                            $partidoModel->actualizarFalta(
                                $falta['cod_falta'], 
                                $partidoId, 
                                $id, 
                                $falta['minuto'], 
                                $falta['tipo_falta']
                            );
                        } else {
                            // Para nuevas faltas, el partido_id es obligatorio
                            if (!isset($falta['partido_id'])) {
                                continue; // Saltamos esta falta
                            }
                            
                            $partidoModel->registrarFalta(
                                $falta['partido_id'], 
                                $id, 
                                $falta['minuto'], 
                                $falta['tipo_falta']
                            );
                        }
                    } catch (Exception $e) {
                        // Continuamos con la siguiente falta
                    }
                }
            }
        }
        // Procesar eliminados
        if (isset($_POST['estadisticas_eliminadas'])) {
            $eliminadas = json_decode($_POST['estadisticas_eliminadas'], true);
            $partidoModel = new Partido();
            if (isset($eliminadas['goles'])) {
                foreach ($eliminadas['goles'] as $cod_gol) {
                    $partidoModel->eliminarGol($cod_gol);
                }
            }
            if (isset($eliminadas['asistencias'])) {
                foreach ($eliminadas['asistencias'] as $cod_asis) {
                    $partidoModel->eliminarAsistencia($cod_asis);
                }
            }
            if (isset($eliminadas['faltas'])) {
                foreach ($eliminadas['faltas'] as $cod_falta) {
                    $partidoModel->eliminarFalta($cod_falta);
                }
            }
        }
        
        $_SESSION['exito_jugadores'] = 'Jugador actualizado correctamente';
        header('Location: ../../../frontend/pages/admin/jugadores.php');
        exit;
    } else {
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
<?php
require_once '../../../models/Jugador.php';
require_once '../../../models/Partido.php';

session_start();

// Función para devolver respuesta JSON
function jsonResponse($estado, $mensaje, $datos = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'estado' => $estado,
        'mensaje' => $mensaje,
        'datos' => $datos
    ]);
    exit;
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? null;
$jugadorModel = new Jugador();
$partidoModel = new Partido();

switch ($accion) {
    case 'listar_goles':
        $goles = $jugadorModel->obtenerDetalleGoles($id);
        jsonResponse(true, 'Goles obtenidos correctamente', $goles);
        break;
        
    case 'listar_asistencias':
        $asistencias = $jugadorModel->obtenerDetalleAsistencias($id);
        jsonResponse(true, 'Asistencias obtenidas correctamente', $asistencias);
        break;
        
    case 'listar_faltas':
        $faltas = $jugadorModel->obtenerDetalleFaltas($id);
        jsonResponse(true, 'Faltas obtenidas correctamente', $faltas);
        break;
        
    case 'listar_partidos':
        $partidos = $partidoModel->obtenerTodos();
        jsonResponse(true, 'Partidos obtenidos correctamente', $partidos);
        break;
        
    case 'agregar_gol':
        $res = $partidoModel->registrarGol($_POST['partido_id'], $id, $_POST['minuto'], $_POST['tipo']);
        jsonResponse($res['estado'], $res['mensaje']);
        break;
        
    case 'editar_gol':
        $res = $partidoModel->actualizarGol($_POST['cod_gol'], $_POST['partido_id'], $id, $_POST['minuto'], $_POST['tipo']);
        jsonResponse($res['estado'], $res['mensaje']);
        break;
        
    case 'eliminar_gol':
        $res = $partidoModel->eliminarGol($_POST['cod_gol']);
        jsonResponse($res['estado'], $res['mensaje']);
        break;
        
    case 'agregar_asistencia':
        $res = $partidoModel->registrarAsistencia($_POST['partido_id'], $id, $_POST['minuto']);
        jsonResponse($res['estado'], $res['mensaje']);
        break;
        
    case 'editar_asistencia':
        $res = $partidoModel->actualizarAsistencia($_POST['cod_asis'], $_POST['partido_id'], $id, $_POST['minuto']);
        jsonResponse($res['estado'], $res['mensaje']);
        break;
        
    case 'eliminar_asistencia':
        $res = $partidoModel->eliminarAsistencia($_POST['cod_asis']);
        jsonResponse($res['estado'], $res['mensaje']);
        break;
        
    case 'agregar_falta':
        $res = $partidoModel->registrarFalta($_POST['partido_id'], $id, $_POST['minuto'], $_POST['tipo_falta']);
        jsonResponse($res['estado'], $res['mensaje']);
        break;
        
    case 'editar_falta':
        $res = $partidoModel->actualizarFalta($_POST['cod_falta'], $_POST['partido_id'], $id, $_POST['minuto'], $_POST['tipo_falta']);
        jsonResponse($res['estado'], $res['mensaje']);
        break;
        
    case 'eliminar_falta':
        $res = $partidoModel->eliminarFalta($_POST['cod_falta']);
        jsonResponse($res['estado'], $res['mensaje']);
        break;
        
    default:
        jsonResponse(false, 'Acción no válida');
        break;
} 
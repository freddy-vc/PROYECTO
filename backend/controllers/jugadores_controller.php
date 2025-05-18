<?php
// Incluir el modelo de jugador
require_once '../models/Jugador.php';

// Verificar que se haya enviado una acción
if (!isset($_GET['accion'])) {
    echo json_encode([
        'estado' => false,
        'mensaje' => 'No se especificó ninguna acción'
    ]);
    exit;
}

// Procesar la acción solicitada
$accion = $_GET['accion'];

switch ($accion) {
    case 'listar':
        listarJugadores();
        break;
    
    case 'detalle':
        if (!isset($_GET['id'])) {
            echo json_encode([
                'estado' => false,
                'mensaje' => 'No se especificó el ID del jugador'
            ]);
            exit;
        }
        
        $idJugador = intval($_GET['id']);
        mostrarDetalleJugador($idJugador);
        break;
    
    case 'por_equipo':
        if (!isset($_GET['equipo_id'])) {
            echo json_encode([
                'estado' => false,
                'mensaje' => 'No se especificó el ID del equipo'
            ]);
            exit;
        }
        
        $idEquipo = intval($_GET['equipo_id']);
        listarJugadoresPorEquipo($idEquipo);
        break;
    
    default:
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Acción no reconocida'
        ]);
        break;
}

/**
 * Función para obtener y devolver todos los jugadores con sus estadísticas
 */
function listarJugadores() {
    $jugadorModel = new Jugador();
    
    try {
        $jugadores = $jugadorModel->obtenerTodosConEstadisticas();
        
        echo json_encode([
            'estado' => true,
            'jugadores' => $jugadores
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Error al obtener los jugadores: ' . $e->getMessage()
        ]);
    }
}

/**
 * Función para obtener y devolver los detalles de un jugador específico
 */
function mostrarDetalleJugador($idJugador) {
    $jugadorModel = new Jugador();
    
    try {
        $jugador = $jugadorModel->obtenerDetalleCompleto($idJugador);
        
        if ($jugador) {
            echo json_encode([
                'estado' => true,
                'jugador' => $jugador
            ]);
        } else {
            echo json_encode([
                'estado' => false,
                'mensaje' => 'No se encontró el jugador especificado'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Error al obtener el detalle del jugador: ' . $e->getMessage()
        ]);
    }
}

/**
 * Función para obtener y devolver los jugadores de un equipo específico
 */
function listarJugadoresPorEquipo($idEquipo) {
    $jugadorModel = new Jugador();
    
    try {
        $jugadores = $jugadorModel->obtenerPorEquipo($idEquipo);
        
        echo json_encode([
            'estado' => true,
            'jugadores' => $jugadores
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Error al obtener los jugadores del equipo: ' . $e->getMessage()
        ]);
    }
} 
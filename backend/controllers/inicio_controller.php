<?php
// Incluir los modelos necesarios
require_once __DIR__ . '/../models/Partido.php';
require_once __DIR__ . '/../models/Jugador.php';

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
    case 'ultimos_partidos':
        obtenerUltimosPartidos();
        break;
    
    case 'jugadores_destacados':
        obtenerJugadoresDestacados();
        break;
    
    case 'verificar_hay_partidos_finalizados':
        verificarHayPartidosFinalizados();
        break;
        
    default:
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Acción no reconocida'
        ]);
        break;
}

/**
 * Función para obtener los últimos partidos finalizados
 */
function obtenerUltimosPartidos() {
    $partidoModel = new Partido();
    
    try {
        // Obtener los últimos partidos finalizados (limitado a 3)
        $partidos = $partidoModel->obtenerUltimosFinalizados(3);
        
        // Preparar los datos para devolverlos en formato JSON
        echo json_encode([
            'estado' => true,
            'partidos' => $partidos
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Error al obtener los últimos partidos: ' . $e->getMessage()
        ]);
    }
}

/**
 * Función para verificar si hay partidos finalizados
 */
function verificarHayPartidosFinalizados() {
    $partidoModel = new Partido();
    
    try {
        // Obtener los últimos partidos finalizados (limitado a 1)
        $partidos = $partidoModel->obtenerUltimosFinalizados(1);
        
        // Verificar si hay al menos un partido finalizado
        $hayPartidosFinalizados = !empty($partidos);
        
        // Preparar los datos para devolverlos en formato JSON
        echo json_encode([
            'estado' => true,
            'hay_partidos_finalizados' => $hayPartidosFinalizados
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Error al verificar partidos finalizados: ' . $e->getMessage()
        ]);
    }
}

/**
 * Función para obtener los jugadores destacados (goleador y máximo asistidor)
 */
function obtenerJugadoresDestacados() {
    $jugadorModel = new Jugador();
    
    try {
        // Obtener el goleador del torneo
        $goleador = $jugadorModel->obtenerGoleador();
        
        // Obtener el máximo asistidor
        $asistidor = $jugadorModel->obtenerMaximoAsistidor();
        
        // Preparar los datos para devolverlos en formato JSON
        echo json_encode([
            'estado' => true,
            'goleador' => $goleador,
            'asistidor' => $asistidor
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Error al obtener jugadores destacados: ' . $e->getMessage()
        ]);
    }
}
?> 
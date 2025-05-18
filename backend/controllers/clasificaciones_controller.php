<?php
// Incluir los modelos necesarios
require_once __DIR__ . '/../models/Equipo.php';
require_once __DIR__ . '/../models/Partido.php';

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
    case 'cuadro_torneo':
        obtenerCuadroTorneo();
        break;
    
    case 'tabla_posiciones':
        obtenerTablaPosiciones();
        break;
    
    default:
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Acción no reconocida'
        ]);
        break;
}

/**
 * Función para obtener los datos del cuadro del torneo (playoffs)
 */
function obtenerCuadroTorneo() {
    $equipoModel = new Equipo();
    $partidoModel = new Partido();
    
    try {
        // Obtener todos los equipos
        $equipos = $equipoModel->obtenerTodos();
        
        // Obtener los partidos de las fases eliminatorias
        $partidos = $partidoModel->obtenerPartidosPorFases(['cuartos', 'semis', 'final']);
        
        // Obtener información de las fases
        $fases = $equipoModel->obtenerFases();
        
        // Enviar los datos
        echo json_encode([
            'estado' => true,
            'equipos' => $equipos,
            'partidos' => $partidos,
            'fases' => $fases
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Error al obtener datos del torneo: ' . $e->getMessage()
        ]);
    }
}

/**
 * Función para obtener los datos de la tabla de posiciones
 */
function obtenerTablaPosiciones() {
    $equipoModel = new Equipo();
    
    try {
        // Obtener la tabla de posiciones
        $tabla = $equipoModel->obtenerTablaPosiciones();
        
        echo json_encode([
            'estado' => true,
            'tabla' => $tabla
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Error al obtener tabla de posiciones: ' . $e->getMessage()
        ]);
    }
} 
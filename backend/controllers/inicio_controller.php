<?php
// Incluir el modelo de Partido
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
    case 'ultimos_partidos':
        obtenerUltimosPartidos();
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
?> 
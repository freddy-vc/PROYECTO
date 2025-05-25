<?php
// Iniciar sesión
session_start();

// Establecer encabezados para JSON y errores
header('Content-Type: application/json');

// Configurar manejo de errores para evitar salida de errores en JSON
ini_set('display_errors', 0);

try {
    // Incluir el modelo de Partido
    require_once '../models/Partido.php';

    // Crear instancia del modelo
    $partido = new Partido();

    // Determinar la acción a realizar
    $accion = isset($_GET['accion']) ? $_GET['accion'] : 'listar';

    // Realizar la acción correspondiente
    switch ($accion) {
        case 'listar':
            // Verificar si hay un filtro específico
            $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : null;
            
            // Obtener los partidos según el filtro
            $partidos = $partido->obtenerTodos($filtro);
            
            if (!$partidos) {
                $partidos = [];
            }
            
            // Formatear la fecha para mostrar
            foreach ($partidos as &$p) {
                // Asegurarse de que los campos sensibles existan
                if (isset($p['fecha'])) {
                    $fecha = new DateTime($p['fecha']);
                    $p['fecha_formateada'] = $fecha->format('d/m/Y');
                } else {
                    $p['fecha_formateada'] = '';
                }
            }
            
            // Preparar los datos para devolverlos en formato JSON
            echo json_encode([
                'estado' => true,
                'partidos' => $partidos
            ]);
            break;
            
        case 'detalle':
            // Obtener el ID del partido
            $cod_par = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($cod_par <= 0) {
                // ID no válido
                echo json_encode([
                    'estado' => false,
                    'mensaje' => 'ID de partido no válido'
                ]);
                break;
            }
            
            // Obtener los datos del partido
            $datos_partido = $partido->obtenerPorId($cod_par);
            
            if (!$datos_partido) {
                // Partido no encontrado
                echo json_encode([
                    'estado' => false,
                    'mensaje' => 'Partido no encontrado'
                ]);
                break;
            }
            
            // Formatear la fecha para mostrar
            if (isset($datos_partido['fecha'])) {
                $fecha = new DateTime($datos_partido['fecha']);
                $datos_partido['fecha_formateada'] = $fecha->format('d/m/Y');
            } else {
                $datos_partido['fecha_formateada'] = '';
            }
            
            // Preparar los datos para devolverlos en formato JSON
            echo json_encode([
                'estado' => true,
                'partido' => $datos_partido
            ]);
            break;
            
        case 'obtener_goles':
            // Obtener el ID del partido
            $partido_id = isset($_GET['partido_id']) ? intval($_GET['partido_id']) : 0;
            
            if ($partido_id <= 0) {
                // ID no válido
                echo json_encode([
                    'estado' => false,
                    'mensaje' => 'ID de partido no válido'
                ]);
                break;
            }
            
            // Obtener datos del partido para saber equipos
            $datos_partido = $partido->obtenerPorId($partido_id);
            
            if (!$datos_partido) {
                // Partido no encontrado
                echo json_encode([
                    'estado' => false,
                    'mensaje' => 'Partido no encontrado'
                ]);
                break;
            }
            
            // Contar goles de cada equipo
            $goles_local = $partido->contarGoles($partido_id, $datos_partido['local_id']);
            $goles_visitante = $partido->contarGoles($partido_id, $datos_partido['visitante_id']);
            
            // Devolver los datos en formato JSON
            echo json_encode([
                'estado' => true,
                'goles_local' => $goles_local,
                'goles_visitante' => $goles_visitante
            ]);
            break;
            
        default:
            // Acción no válida
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Acción no válida'
            ]);
            break;
    }
} catch (Exception $e) {
    // Capturar cualquier error y devolver respuesta JSON
    echo json_encode([
        'estado' => false,
        'mensaje' => 'Error en el servidor: ' . $e->getMessage(),
        'error' => $e->getTraceAsString()
    ]);
}
?> 
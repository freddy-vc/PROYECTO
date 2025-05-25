<?php
// Iniciar sesión
session_start();

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
        
        // Convertir los escudos a base64 para mostrar en la página
        foreach ($partidos as &$p) {
            if ($p['local_escudo']) {
                $p['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($p['local_escudo']);
            } else {
                $p['local_escudo_base64'] = '../assets/images/team.png';
            }
            
            if ($p['visitante_escudo']) {
                $p['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($p['visitante_escudo']);
            } else {
                $p['visitante_escudo_base64'] = '../assets/images/team.png';
            }
            
            // Si el partido está finalizado, agregar resultados
            if ($p['estado'] === 'finalizado') {
                // Verificar si ya tenemos los goles calculados
                if (!isset($p['goles_local'])) {
                    $p['goles_local'] = $partido->contarGoles($p['cod_par'], $p['local_id']);
                }
                
                if (!isset($p['goles_visitante'])) {
                    $p['goles_visitante'] = $partido->contarGoles($p['cod_par'], $p['visitante_id']);
                }
            }
            
            // Formatear la fecha para mostrar
            $fecha = new DateTime($p['fecha']);
            $p['fecha_formateada'] = $fecha->format('d/m/Y');
        }
        
        // Preparar los datos para devolverlos en formato JSON
        header('Content-Type: application/json');
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
            header('Content-Type: application/json');
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
            header('Content-Type: application/json');
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Partido no encontrado'
            ]);
            break;
        }
        
        // Convertir los escudos a base64
        if ($datos_partido['local_escudo']) {
            $datos_partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($datos_partido['local_escudo']);
        } else {
            $datos_partido['local_escudo_base64'] = '../assets/images/team.png';
        }
        
        if ($datos_partido['visitante_escudo']) {
            $datos_partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($datos_partido['visitante_escudo']);
        } else {
            $datos_partido['visitante_escudo_base64'] = '../assets/images/team.png';
        }
        
        // Formatear la fecha para mostrar
        $fecha = new DateTime($datos_partido['fecha']);
        $datos_partido['fecha_formateada'] = $fecha->format('d/m/Y');
        
        // Preparar los datos para devolverlos en formato JSON
        header('Content-Type: application/json');
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
            header('Content-Type: application/json');
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
            header('Content-Type: application/json');
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
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => true,
            'goles_local' => $goles_local,
            'goles_visitante' => $goles_visitante
        ]);
        break;
        
    default:
        // Acción no válida
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Acción no válida'
        ]);
        break;
}
?> 
<?php
// Incluir los modelos necesarios
require_once __DIR__ . '/../models/Equipo.php';
require_once __DIR__ . '/../models/Partido.php';

header('Content-Type: application/json');

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

switch ($accion) {
    case 'cuadro_torneo':
        obtenerCuadroTorneo();
        break;
    
    case 'tabla_posiciones':
        obtenerTablaPosiciones();
        break;
    
    case 'eliminatorias':
        $partidoModel = new Partido();
        $equipoModel = new Equipo();
        function getEquipoData($id, $equipoModel) {
            if (!$id) return null;
            $equipo = $equipoModel->obtenerPorId($id);
            return [
                'id' => $equipo['cod_equ'],
                'nombre' => $equipo['nombre'],
                'escudo' => $equipo['escudo_base64'] ?: '../assets/images/team.png'
            ];
        }
        $clasificaciones = [
            'cuartos' => [],
            'semifinales' => [],
            'final' => null
        ];
        $cuartos = $partidoModel->obtenerPorFase('cuartos');
        for ($i = 0; $i < 4; $i++) {
            if (isset($cuartos[$i])) {
                $p = $cuartos[$i];
                $marcador = $partidoModel->calcularMarcadorPorDetalle($p['cod_par'], $p['local_nombre'], $p['visitante_nombre']);
                $clasificaciones['cuartos'][] = [
                    'id' => $p['cod_par'],
                    'local' => getEquipoData($p['equ_local'], $equipoModel),
                    'visitante' => getEquipoData($p['equ_visitante'], $equipoModel),
                    'goles_local' => $p['estado'] === 'finalizado' ? $marcador['goles_local'] : '-',
                    'goles_visitante' => $p['estado'] === 'finalizado' ? $marcador['goles_visitante'] : '-',
                    'estado' => $p['estado']
                ];
            } else {
                $clasificaciones['cuartos'][] = null;
            }
        }
        $semis = $partidoModel->obtenerPorFase('semis');
        for ($i = 0; $i < 2; $i++) {
            if (isset($semis[$i])) {
                $p = $semis[$i];
                $marcador = $partidoModel->calcularMarcadorPorDetalle($p['cod_par'], $p['local_nombre'], $p['visitante_nombre']);
                $clasificaciones['semifinales'][] = [
                    'id' => $p['cod_par'],
                    'local' => getEquipoData($p['equ_local'], $equipoModel),
                    'visitante' => getEquipoData($p['equ_visitante'], $equipoModel),
                    'goles_local' => $p['estado'] === 'finalizado' ? $marcador['goles_local'] : '-',
                    'goles_visitante' => $p['estado'] === 'finalizado' ? $marcador['goles_visitante'] : '-',
                    'estado' => $p['estado']
                ];
            } else {
                $clasificaciones['semifinales'][] = null;
            }
        }
        $final = $partidoModel->obtenerPorFase('final');
        if (isset($final[0])) {
            $p = $final[0];
            $marcador = $partidoModel->calcularMarcadorPorDetalle($p['cod_par'], $p['local_nombre'], $p['visitante_nombre']);
            $clasificaciones['final'] = [
                'id' => $p['cod_par'],
                'local' => getEquipoData($p['equ_local'], $equipoModel),
                'visitante' => getEquipoData($p['equ_visitante'], $equipoModel),
                'goles_local' => $p['estado'] === 'finalizado' ? $marcador['goles_local'] : '-',
                'goles_visitante' => $p['estado'] === 'finalizado' ? $marcador['goles_visitante'] : '-',
                'estado' => $p['estado']
            ];
        } else {
            $clasificaciones['final'] = null;
        }
        echo json_encode($clasificaciones);
        break;
    
    default:
        echo json_encode([
            'estado' => false,
            'mensaje' => 'No se especificó ninguna acción'
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
        
        // Obtener la estructura de brackets con sus partidos asociados
        $brackets = $partidoModel->obtenerEstructuraBrackets();
        
        // Obtener los partidos de las fases eliminatorias
        $partidos = $partidoModel->obtenerPartidosPorFases(['cuartos', 'semis', 'final']);
        
        // Asignar número de bracket como orden a cada partido
        foreach ($partidos as &$partido) {
            // Buscar el bracket asociado a este partido
            foreach ($brackets as $bracket) {
                if ($bracket['cod_par'] == $partido['cod_par']) {
                    $partido['orden'] = $bracket['bracket_id'];
                    $partido['bracket_id'] = $bracket['bracket_id'];
                    break;
                }
            }
        }
        
        // Enviar los datos
        echo json_encode([
            'estado' => true,
            'equipos' => $equipos,
            'partidos' => $partidos,
            'brackets' => $brackets
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Error al obtener datos del torneo: ' . $e->getMessage(),
            'equipos' => [],
            'partidos' => [],
            'brackets' => []
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
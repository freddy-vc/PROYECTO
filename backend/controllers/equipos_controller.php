<?php
// Iniciar sesión
session_start();

// Establecer encabezados para JSON y errores
header('Content-Type: application/json');

// Configurar manejo de errores para evitar salida de errores en JSON
ini_set('display_errors', 0);

try {
    // Incluir el modelo de Equipo
    require_once '../models/Equipo.php';
    
    // Crear instancia del modelo
    $equipo = new Equipo();
    
    // Determinar la acción a realizar
    $accion = isset($_GET['accion']) ? $_GET['accion'] : 'listar';
    
    // Realizar la acción correspondiente
    switch ($accion) {
        case 'listar':
            // Obtener todos los equipos
            $equipos = $equipo->obtenerTodos();
            
            if (!$equipos) {
                $equipos = [];
            }
            
            // Convertir los escudos a base64 para mostrar en la página
            foreach ($equipos as &$e) {
                if (!empty($e['escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($e['escudo'])) {
                        $content = stream_get_contents($e['escudo']);
                        rewind($e['escudo']);
                        $e['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                    } else {
                        $e['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($e['escudo']);
                    }
                } else {
                    $e['escudo_base64'] = '../assets/images/team.png';
                }
            }
            
            // Preparar los datos para devolverlos en formato JSON
            echo json_encode([
                'estado' => true,
                'equipos' => $equipos
            ]);
            break;
            
        case 'detalle':
            // Obtener el ID del equipo
            $cod_equ = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($cod_equ <= 0) {
                // ID no válido
                echo json_encode([
                    'estado' => false,
                    'mensaje' => 'ID de equipo no válido'
                ]);
                break;
            }
            
            // Obtener los datos del equipo
            $datos_equipo = $equipo->obtenerPorId($cod_equ);
            
            if (!$datos_equipo) {
                // Equipo no encontrado
                echo json_encode([
                    'estado' => false,
                    'mensaje' => 'Equipo no encontrado'
                ]);
                break;
            }
            
            // Convertir el escudo a base64
            if (!empty($datos_equipo['escudo'])) {
                // Verificar si es un recurso o un string
                if (is_resource($datos_equipo['escudo'])) {
                    $content = stream_get_contents($datos_equipo['escudo']);
                    rewind($datos_equipo['escudo']);
                    $datos_equipo['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                } else {
                    $datos_equipo['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($datos_equipo['escudo']);
                }
            } else {
                $datos_equipo['escudo_base64'] = '../assets/images/team.png';
            }
            
            // Obtener los jugadores del equipo
            $jugadores = $equipo->obtenerJugadores($cod_equ);
            
            if (!$jugadores) {
                $jugadores = [];
            }
            
            // Convertir las fotos de los jugadores a base64
            foreach ($jugadores as &$jugador) {
                if (!empty($jugador['foto'])) {
                    if (is_resource($jugador['foto'])) {
                        $content = stream_get_contents($jugador['foto']);
                        rewind($jugador['foto']);
                        $jugador['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                    } else {
                        $jugador['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($jugador['foto']);
                    }
                } else {
                    $jugador['foto_base64'] = '../assets/images/player.png';
                }
            }
            
            // Obtener los partidos del equipo
            $partidos = $equipo->obtenerPartidos($cod_equ);
            
            if (!$partidos) {
                $partidos = [];
            }
            
            // Procesar los escudos de los partidos a base64
            foreach ($partidos as &$partido) {
                if (!empty($partido['local_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['local_escudo'])) {
                        $content = stream_get_contents($partido['local_escudo']);
                        rewind($partido['local_escudo']);
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                    } else {
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']);
                    }
                } else {
                    $partido['local_escudo_base64'] = '../assets/images/team.png';
                }
                
                if (!empty($partido['visitante_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['visitante_escudo'])) {
                        $content = stream_get_contents($partido['visitante_escudo']);
                        rewind($partido['visitante_escudo']);
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                    } else {
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']);
                    }
                } else {
                    $partido['visitante_escudo_base64'] = '../assets/images/team.png';
                }
            }
            
            // Preparar los datos para devolverlos en formato JSON
            echo json_encode([
                'estado' => true,
                'equipo' => $datos_equipo,
                'jugadores' => $jugadores,
                'partidos' => $partidos
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
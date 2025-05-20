<?php
// Iniciar sesión
session_start();

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
        
        // Convertir los escudos a base64 para mostrar en la página
        foreach ($equipos as &$e) {
            if ($e['escudo']) {
                $e['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($e['escudo']);
            } else {
                $e['escudo_base64'] = '../assets/images/team.png';
            }
        }
        
        // Preparar los datos para devolverlos en formato JSON
        header('Content-Type: application/json');
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
            header('Content-Type: application/json');
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
            header('Content-Type: application/json');
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Equipo no encontrado'
            ]);
            break;
        }
        
        // Convertir el escudo a base64
        if ($datos_equipo['escudo']) {
            $datos_equipo['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($datos_equipo['escudo']);
        } else {
            $datos_equipo['escudo_base64'] = '../assets/images/team.png';
        }
        
        // Obtener los jugadores del equipo
        $jugadores = $equipo->obtenerJugadores($cod_equ);
        
        // Convertir las fotos de los jugadores a base64
        foreach ($jugadores as &$jugador) {
            if ($jugador['foto']) {
                $jugador['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($jugador['foto']);
            } else {
                $jugador['foto_base64'] = '../assets/images/player.png';
            }
        }
        
        // Obtener los partidos del equipo
        $partidos = $equipo->obtenerPartidos($cod_equ);
        
        // Procesar los escudos de los partidos a base64
        foreach ($partidos as &$partido) {
            if ($partido['local_escudo']) {
                $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']);
            } else {
                $partido['local_escudo_base64'] = '../assets/images/team.png';
            }
            if ($partido['visitante_escudo']) {
                $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']);
            } else {
                $partido['visitante_escudo_base64'] = '../assets/images/team.png';
            }
        }
        
        // Preparar los datos para devolverlos en formato JSON
        header('Content-Type: application/json');
        echo json_encode([
            'estado' => true,
            'equipo' => $datos_equipo,
            'jugadores' => $jugadores,
            'partidos' => $partidos
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
<?php
// Incluir los archivos necesarios
require_once 'backend/models/Partido.php';

// Crear instancia del modelo
$partido = new Partido();

// Obtener todos los partidos
try {
    $partidos = $partido->obtenerTodos();
    
    echo "Número total de partidos: " . count($partidos) . "<br><br>";
    
    if (count($partidos) > 0) {
        echo "<h3>Primer partido encontrado:</h3>";
        $primerPartido = $partidos[0];
        echo "ID: " . $primerPartido['cod_par'] . "<br>";
        echo "Fecha: " . $primerPartido['fecha'] . "<br>";
        echo "Hora: " . $primerPartido['hora'] . "<br>";
        echo "Equipo Local: " . $primerPartido['local_nombre'] . "<br>";
        echo "Equipo Visitante: " . $primerPartido['visitante_nombre'] . "<br>";
        echo "Estado: " . $primerPartido['estado'] . "<br>";
        
        echo "<h4>Escudo Equipo Local:</h4>";
        if (isset($primerPartido['local_escudo_base64'])) {
            echo "<img src='" . $primerPartido['local_escudo_base64'] . "' width='100'><br>";
        } else {
            echo "No hay escudo disponible<br>";
        }
        
        echo "<h4>Escudo Equipo Visitante:</h4>";
        if (isset($primerPartido['visitante_escudo_base64'])) {
            echo "<img src='" . $primerPartido['visitante_escudo_base64'] . "' width='100'><br>";
        } else {
            echo "No hay escudo disponible<br>";
        }
    }
    
    // Obtener los últimos partidos finalizados
    echo "<h3>Últimos partidos finalizados:</h3>";
    $ultimosFinalizados = $partido->obtenerUltimosFinalizados(2);
    if (count($ultimosFinalizados) > 0) {
        foreach ($ultimosFinalizados as $p) {
            echo "Partido #" . $p['cod_par'] . ": " . $p['local_nombre'] . " vs " . $p['visitante_nombre'] . "<br>";
        }
    } else {
        echo "No hay partidos finalizados<br>";
    }
    
    // Obtener partidos por equipo
    echo "<h3>Partidos del primer equipo:</h3>";
    if (count($partidos) > 0) {
        $equipoId = $primerPartido['local_id'];
        $partidosEquipo = $partido->obtenerPorEquipo($equipoId);
        if (count($partidosEquipo) > 0) {
            foreach ($partidosEquipo as $p) {
                echo "Partido #" . $p['cod_par'] . ": " . $p['local_nombre'] . " vs " . $p['visitante_nombre'] . "<br>";
            }
        } else {
            echo "No hay partidos para este equipo<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 
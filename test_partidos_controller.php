<?php
// Incluir el modelo de Partido
require_once 'backend/models/Partido.php';

// Crear instancia del modelo
$partido = new Partido();

// Obtener todos los partidos
$partidos = $partido->obtenerTodos();

// Verificar si hay partidos
if (count($partidos) > 0) {
    echo "Se encontraron " . count($partidos) . " partidos.<br>";
    
    // Mostrar detalles del primer partido
    $primerPartido = $partidos[0];
    
    echo "<h3>Detalles del primer partido:</h3>";
    echo "ID: " . $primerPartido['cod_par'] . "<br>";
    echo "Fecha: " . $primerPartido['fecha'] . "<br>";
    echo "Hora: " . $primerPartido['hora'] . "<br>";
    echo "Estado: " . $primerPartido['estado'] . "<br>";
    echo "Equipos: " . $primerPartido['local_nombre'] . " vs " . $primerPartido['visitante_nombre'] . "<br>";
    
    // Verificar si se procesaron correctamente los escudos
    echo "<h3>Verificación de escudos:</h3>";
    echo "Escudo local base64: " . (isset($primerPartido['local_escudo_base64']) ? "SÍ" : "NO") . "<br>";
    echo "Escudo visitante base64: " . (isset($primerPartido['visitante_escudo_base64']) ? "SÍ" : "NO") . "<br>";
    
    // Mostrar los escudos
    if (isset($primerPartido['local_escudo_base64'])) {
        echo "<img src='" . $primerPartido['local_escudo_base64'] . "' style='max-width: 100px;'><br>";
    }
    
    if (isset($primerPartido['visitante_escudo_base64'])) {
        echo "<img src='" . $primerPartido['visitante_escudo_base64'] . "' style='max-width: 100px;'><br>";
    }
    
    // Verificar si se calcularon los goles
    echo "<h3>Verificación de goles:</h3>";
    echo "Goles local: " . (isset($primerPartido['goles_local']) ? $primerPartido['goles_local'] : "No disponible") . "<br>";
    echo "Goles visitante: " . (isset($primerPartido['goles_visitante']) ? $primerPartido['goles_visitante'] : "No disponible") . "<br>";
    
} else {
    echo "No se encontraron partidos.";
}
?> 
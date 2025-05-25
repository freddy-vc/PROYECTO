<?php
// Incluir el modelo de Partido
require_once 'backend/models/Partido.php';

// Crear instancia del modelo
$partido = new Partido();

// Obtener todos los partidos
$partidos = $partido->obtenerTodos();

// Mostrar información
echo "Número de partidos encontrados: " . count($partidos) . "<br><br>";

if (count($partidos) > 0) {
    // Mostrar el primer partido
    $primerPartido = $partidos[0];
    
    echo "<h3>Detalles del primer partido:</h3>";
    echo "ID: " . $primerPartido['cod_par'] . "<br>";
    echo "Fecha: " . $primerPartido['fecha'] . "<br>";
    echo "Hora: " . $primerPartido['hora'] . "<br>";
    echo "Estado: " . $primerPartido['estado'] . "<br>";
    echo "Equipos: " . $primerPartido['local_nombre'] . " vs " . $primerPartido['visitante_nombre'] . "<br>";
    
    // Verificar si los escudos se están procesando correctamente
    echo "<h4>Escudo local:</h4>";
    echo "Tipo: " . gettype($primerPartido['local_escudo']) . "<br>";
    if (is_resource($primerPartido['local_escudo'])) {
        echo "Es un recurso<br>";
    } elseif (is_string($primerPartido['local_escudo'])) {
        echo "Es un string de longitud: " . strlen($primerPartido['local_escudo']) . "<br>";
    }
    
    echo "<h4>Escudo visitante:</h4>";
    echo "Tipo: " . gettype($primerPartido['visitante_escudo']) . "<br>";
    if (is_resource($primerPartido['visitante_escudo'])) {
        echo "Es un recurso<br>";
    } elseif (is_string($primerPartido['visitante_escudo'])) {
        echo "Es un string de longitud: " . strlen($primerPartido['visitante_escudo']) . "<br>";
    }
    
    // Verificar si se están generando correctamente las versiones base64
    echo "<h4>Escudos base64:</h4>";
    echo "Local base64 existe: " . (isset($primerPartido['local_escudo_base64']) ? "Sí" : "No") . "<br>";
    echo "Visitante base64 existe: " . (isset($primerPartido['visitante_escudo_base64']) ? "Sí" : "No") . "<br>";
    
    // Mostrar los escudos si existen
    if (isset($primerPartido['local_escudo_base64'])) {
        echo "<img src='" . $primerPartido['local_escudo_base64'] . "' alt='Escudo local' style='max-width: 100px;'><br>";
    }
    
    if (isset($primerPartido['visitante_escudo_base64'])) {
        echo "<img src='" . $primerPartido['visitante_escudo_base64'] . "' alt='Escudo visitante' style='max-width: 100px;'><br>";
    }
} else {
    echo "No se encontraron partidos en la base de datos.";
}
?> 
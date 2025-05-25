<?php
// Incluir el modelo de Partido
require_once 'backend/models/Partido.php';

// Crear instancia del modelo
$partido = new Partido();

// Obtener todos los partidos
try {
    $partidos = $partido->obtenerTodos();
    echo "Número de partidos encontrados: " . count($partidos) . "<br>";
    
    if (count($partidos) > 0) {
        $primerPartido = $partidos[0];
        echo "<h3>Detalles del primer partido:</h3>";
        echo "ID: " . $primerPartido['cod_par'] . "<br>";
        echo "Fecha: " . $primerPartido['fecha'] . "<br>";
        echo "Equipos: " . $primerPartido['local_nombre'] . " vs " . $primerPartido['visitante_nombre'] . "<br>";
        
        // Verificar si los escudos se están procesando correctamente
        echo "<h4>Escudo local:</h4>";
        if (isset($primerPartido['local_escudo_base64'])) {
            echo "<img src='" . $primerPartido['local_escudo_base64'] . "' alt='Escudo local' style='max-width: 100px;'><br>";
        } else {
            echo "No hay escudo local disponible<br>";
        }
        
        echo "<h4>Escudo visitante:</h4>";
        if (isset($primerPartido['visitante_escudo_base64'])) {
            echo "<img src='" . $primerPartido['visitante_escudo_base64'] . "' alt='Escudo visitante' style='max-width: 100px;'><br>";
        } else {
            echo "No hay escudo visitante disponible<br>";
        }
    } else {
        echo "No se encontraron partidos en la base de datos.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 
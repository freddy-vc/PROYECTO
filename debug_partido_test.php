<?php
require_once 'backend/models/Partido.php';

// Crear una instancia del modelo
$partidoModel = new Partido();

// Obtener todos los partidos
$partidos = $partidoModel->obtenerTodos();

// Comprobar si hay partidos
if (!empty($partidos)) {
    echo '<pre>';
    echo "Número de partidos: " . count($partidos) . "\n\n";
    
    // Obtener el primer partido para analizarlo
    $primerPartido = $partidos[0];
    
    // Verificar si existen las propiedades local_escudo_base64 y visitante_escudo_base64
    echo "Verificación de propiedades en el primer partido:\n";
    echo "local_escudo_base64: " . (isset($primerPartido['local_escudo_base64']) ? "Existe" : "No existe") . "\n";
    echo "visitante_escudo_base64: " . (isset($primerPartido['visitante_escudo_base64']) ? "Existe" : "No existe") . "\n";
    
    // Verificar si local_escudo y visitante_escudo siguen existiendo (deberían haber sido eliminados)
    echo "local_escudo: " . (isset($primerPartido['local_escudo']) ? "Existe" : "No existe") . "\n";
    echo "visitante_escudo: " . (isset($primerPartido['visitante_escudo']) ? "Existe" : "No existe") . "\n\n";
    
    // Mostrar todas las propiedades del primer partido
    echo "Propiedades del primer partido:\n";
    foreach ($primerPartido as $key => $value) {
        if ($key === 'local_escudo_base64' || $key === 'visitante_escudo_base64') {
            echo "$key: " . substr($value, 0, 30) . "...\n";
        } else {
            echo "$key: $value\n";
        }
    }
    
    echo '</pre>';
} else {
    echo "No hay partidos disponibles.";
}
?> 
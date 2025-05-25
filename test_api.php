<?php
// Configuración para mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

// Función para llamar a una API y mostrar resultados
function testApi($url, $title) {
    echo "<h2>$title</h2>";
    echo "<p>URL: $url</p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<h3>Código de respuesta HTTP: " . $info['http_code'] . "</h3>";
    
    if ($error) {
        echo "<p style='color: red'>Error cURL: $error</p>";
    }
    
    echo "<h3>Respuesta:</h3>";
    echo "<textarea style='width: 100%; height: 300px;'>" . $response . "</textarea>";
    
    // Verificar si es JSON válido
    $json = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<p style='color: red'>Error al decodificar JSON: " . json_last_error_msg() . "</p>";
    } else {
        echo "<p style='color: green'>JSON válido</p>";
        echo "<p>Estructura:</p><pre>" . print_r($json, true) . "</pre>";
    }
    
    echo "<hr>";
}

echo "<h1>Prueba de APIs</h1>";

// Probar API de partidos
testApi('http://localhost/PROYECTO/backend/controllers/partidos_controller.php?accion=listar', 'API de Partidos - Listar');

// Probar API de equipos
testApi('http://localhost/PROYECTO/backend/controllers/equipos_controller.php?accion=listar', 'API de Equipos - Listar');
?> 
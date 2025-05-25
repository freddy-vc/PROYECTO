<?php
// Configuración para mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir el modelo de Partido
require_once 'backend/models/Partido.php';

// Función para imprimir datos estructurados
function dump($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

// Función para verificar la validez del JSON
function checkJson($data) {
    $json = json_encode($data);
    if ($json === false) {
        echo '<div style="color: red; font-weight: bold;">ERROR JSON: ' . json_last_error_msg() . '</div>';
        
        // Identificar qué parte es problemática
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                echo "<h3>Verificando elemento: $key</h3>";
                if (json_encode($value) === false) {
                    echo '<div style="color: red">Problema encontrado en clave: ' . $key . '</div>';
                    if (is_array($value)) {
                        checkJson($value);
                    } else {
                        echo 'Tipo: ' . gettype($value) . '<br>';
                        if (is_resource($value)) {
                            echo 'Es un recurso de tipo: ' . get_resource_type($value) . '<br>';
                        }
                    }
                }
            }
        }
    } else {
        echo '<div style="color: green">JSON VÁLIDO</div>';
    }
}

echo '<h1>Depuración de Partidos</h1>';

try {
    // Crear instancia del modelo
    $partido = new Partido();
    
    echo '<h2>Obtener todos los partidos</h2>';
    $partidos = $partido->obtenerTodos();
    
    echo '<h3>Número de partidos: ' . count($partidos) . '</h3>';
    
    // Verificar la validez del JSON de los partidos
    echo '<h3>Verificación de JSON para partidos</h3>';
    checkJson($partidos);
    
    // Si hay partidos, mostrar el primero
    if (count($partidos) > 0) {
        echo '<h3>Primer partido</h3>';
        $primerPartido = $partidos[0];
        dump($primerPartido);
        
        // Comprobar tipos de datos
        echo '<h3>Tipos de datos</h3>';
        foreach ($primerPartido as $key => $value) {
            echo "$key: " . gettype($value);
            if (is_resource($value)) {
                echo " (Recurso de tipo: " . get_resource_type($value) . ")";
            }
            echo '<br>';
        }
        
        echo '<h3>Verificación de JSON para el primer partido</h3>';
        checkJson($primerPartido);
        
        // Verificar la validez de los escudos
        echo '<h3>Escudo local</h3>';
        if (isset($primerPartido['local_escudo'])) {
            echo 'Tipo: ' . gettype($primerPartido['local_escudo']) . '<br>';
            if (is_resource($primerPartido['local_escudo'])) {
                echo 'Es un recurso de tipo: ' . get_resource_type($primerPartido['local_escudo']) . '<br>';
                
                // Intentar obtener el contenido
                $content = @stream_get_contents($primerPartido['local_escudo']);
                if ($content === false) {
                    echo '<div style="color: red">Error al leer el recurso</div>';
                } else {
                    echo 'Longitud del contenido: ' . strlen($content) . '<br>';
                    // Intentar reposicionar el puntero
                    if (@rewind($primerPartido['local_escudo'])) {
                        echo 'Rewind exitoso<br>';
                    } else {
                        echo '<div style="color: red">Error al hacer rewind</div>';
                    }
                }
            } elseif (is_string($primerPartido['local_escudo'])) {
                echo 'Longitud: ' . strlen($primerPartido['local_escudo']) . '<br>';
            }
        } else {
            echo 'No hay escudo local<br>';
        }
        
        echo '<h3>Escudo visitante</h3>';
        if (isset($primerPartido['visitante_escudo'])) {
            echo 'Tipo: ' . gettype($primerPartido['visitante_escudo']) . '<br>';
            if (is_resource($primerPartido['visitante_escudo'])) {
                echo 'Es un recurso de tipo: ' . get_resource_type($primerPartido['visitante_escudo']) . '<br>';
                
                // Intentar obtener el contenido
                $content = @stream_get_contents($primerPartido['visitante_escudo']);
                if ($content === false) {
                    echo '<div style="color: red">Error al leer el recurso</div>';
                } else {
                    echo 'Longitud del contenido: ' . strlen($content) . '<br>';
                    // Intentar reposicionar el puntero
                    if (@rewind($primerPartido['visitante_escudo'])) {
                        echo 'Rewind exitoso<br>';
                    } else {
                        echo '<div style="color: red">Error al hacer rewind</div>';
                    }
                }
            } elseif (is_string($primerPartido['visitante_escudo'])) {
                echo 'Longitud: ' . strlen($primerPartido['visitante_escudo']) . '<br>';
            }
        } else {
            echo 'No hay escudo visitante<br>';
        }
        
        // Verificar escudos procesados
        echo '<h3>Escudos procesados</h3>';
        echo 'Escudo local base64 existe: ' . (isset($primerPartido['local_escudo_base64']) ? 'Sí' : 'No') . '<br>';
        echo 'Escudo visitante base64 existe: ' . (isset($primerPartido['visitante_escudo_base64']) ? 'Sí' : 'No') . '<br>';
        
        // Mostrar imágenes si existen
        if (isset($primerPartido['local_escudo_base64'])) {
            echo '<h4>Imagen local:</h4>';
            echo '<img src="' . $primerPartido['local_escudo_base64'] . '" style="max-width: 100px;"><br>';
        }
        
        if (isset($primerPartido['visitante_escudo_base64'])) {
            echo '<h4>Imagen visitante:</h4>';
            echo '<img src="' . $primerPartido['visitante_escudo_base64'] . '" style="max-width: 100px;"><br>';
        }
    }
    
    // Hacer una prueba con el controlador
    echo '<h2>Prueba del controlador (obtenerTodos)</h2>';
    // Preparar un buffer para capturar la salida
    ob_start();
    // Incluir el controlador con los parámetros necesarios
    $_GET['accion'] = 'listar';
    include 'backend/controllers/partidos_controller.php';
    $output = ob_get_clean();
    
    // Mostrar la salida
    echo '<h3>Salida del controlador:</h3>';
    echo '<textarea style="width: 100%; height: 200px;">' . htmlspecialchars($output) . '</textarea>';
    
    // Verificar si es un JSON válido
    $jsonData = json_decode($output, true);
    if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
        echo '<div style="color: red; font-weight: bold;">ERROR JSON: ' . json_last_error_msg() . '</div>';
    } else {
        echo '<div style="color: green">JSON VÁLIDO</div>';
        echo '<h4>Número de partidos en la respuesta: ' . count($jsonData['partidos']) . '</h4>';
    }
    
} catch (Exception $e) {
    echo '<div style="color: red; font-weight: bold;">Error: ' . $e->getMessage() . '</div>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
?> 
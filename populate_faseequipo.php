<?php
/**
 * Script para poblar la tabla FaseEquipo basado en los resultados de los partidos
 */

// Incluir la conexión a la base de datos
require_once 'backend/database/connection.php';

// Función para ejecutar el script SQL
function poblarFaseEquipo() {
    // Obtener la conexión
    $conn = Conexion::getConexion();
    
    try {
        // Comenzar una transacción
        $conn->beginTransaction();
        
        // Limpiar la tabla FaseEquipo primero
        $conn->exec("DELETE FROM FaseEquipo");
        
        // Insertar equipos en fase de cuartos
        $conn->exec("
            INSERT INTO FaseEquipo (cod_equ, fase, clasificado) VALUES 
            (1, 'cuartos', TRUE),   -- Leones FC (ganó 2-0)
            (2, 'cuartos', FALSE),  -- Tigres FC (perdió 0-2)
            (3, 'cuartos', TRUE),   -- Águilas Doradas (empató 1-1, consideramos que clasifica)
            (4, 'cuartos', FALSE),  -- Dragones FC (empató 1-1, no clasifica)
            (5, 'cuartos', FALSE),  -- Halcones FC (partido pendiente)
            (6, 'cuartos', FALSE),  -- Pumas FC (partido pendiente)
            (7, 'cuartos', FALSE),  -- Toros FC (partido pendiente)
            (8, 'cuartos', FALSE)   -- Panteras FC (partido pendiente)
        ");
        
        // Insertar equipos clasificados a semifinales
        $conn->exec("
            INSERT INTO FaseEquipo (cod_equ, fase, clasificado) VALUES 
            (1, 'semis', FALSE),   -- Leones FC (clasificado a semis, pero aún no se juega)
            (3, 'semis', FALSE)    -- Águilas Doradas (clasificado a semis, pero aún no se juega)
        ");
        
        // Confirmar la transacción
        $conn->commit();
        
        return [
            "success" => true,
            "message" => "Tabla FaseEquipo poblada exitosamente"
        ];
    } catch (PDOException $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        
        return [
            "success" => false,
            "message" => "Error al poblar la tabla FaseEquipo: " . $e->getMessage()
        ];
    }
}

// Determinar si se ejecuta desde navegador o línea de comandos
$is_browser = isset($_SERVER['HTTP_USER_AGENT']);

// Ejecutar la función
$result = poblarFaseEquipo();

// Mostrar el resultado según el contexto
if ($is_browser) {
    // Formato para navegador
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Poblar FaseEquipo</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 40px;
                line-height: 1.6;
            }
            .success {
                background-color: #dff0d8;
                color: #3c763d;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            .error {
                background-color: #f2dede;
                color: #a94442;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            .back-link {
                margin-top: 20px;
            }
        </style>
    </head>
    <body>';
    
    if ($result["success"]) {
        echo '<div class="success">' . $result["message"] . '</div>';
    } else {
        echo '<div class="error">' . $result["message"] . '</div>';
    }
    
    echo '<div class="back-link"><a href="index.php">Volver al inicio</a></div>
    </body>
    </html>';
} else {
    // Formato para línea de comandos
    if ($result["success"]) {
        echo "ÉXITO: " . $result["message"] . "\n";
    } else {
        echo "ERROR: " . $result["message"] . "\n";
    }
}
?> 
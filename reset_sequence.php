<?php
// Incluir el archivo de conexión
require_once 'backend/database/connection.php';

try {
    // Obtener la conexión
    $conexion = Conexion::getConexion();
    
    // Obtener el valor máximo actual de cod_user
    $stmt = $conexion->query("SELECT MAX(cod_user) FROM Usuarios");
    $maxId = $stmt->fetchColumn();
    
    if ($maxId) {
        // Resetear la secuencia al valor máximo actual + 1
        $resetQuery = "SELECT setval('usuarios_cod_user_seq', $maxId, true)";
        $conexion->query($resetQuery);
        echo "¡Secuencia resetada correctamente! El próximo ID será " . ($maxId + 1) . ".";
    } else {
        echo "No se encontraron usuarios en la base de datos.";
    }
    
} catch (PDOException $e) {
    echo "Error al resetear la secuencia: " . $e->getMessage();
}
?> 
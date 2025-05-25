<?php
// Incluir el archivo de conexión
require_once 'backend/database/connection.php';

// Obtener conexión
$conexion = Conexion::getConexion();

try {
    // Consulta para verificar si hay partidos
    $sql = "SELECT COUNT(*) as total FROM Partidos";
    $stmt = $conexion->query($sql);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Total de partidos en la base de datos: " . $resultado['total'] . "<br>";
    
    if ($resultado['total'] > 0) {
        // Si hay partidos, obtener algunos detalles
        $sql = "SELECT p.cod_par, p.fecha, p.hora, p.estado, 
                e1.nombre as local_nombre, e2.nombre as visitante_nombre
                FROM Partidos p
                JOIN Equipos e1 ON p.equ_local = e1.cod_equ
                JOIN Equipos e2 ON p.equ_visitante = e2.cod_equ
                LIMIT 5";
        
        $stmt = $conexion->query($sql);
        $partidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Últimos 5 partidos:</h3>";
        echo "<ul>";
        foreach ($partidos as $partido) {
            echo "<li>ID: " . $partido['cod_par'] . " - ";
            echo $partido['local_nombre'] . " vs " . $partido['visitante_nombre'] . " - ";
            echo "Fecha: " . $partido['fecha'] . " " . $partido['hora'] . " - ";
            echo "Estado: " . $partido['estado'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "No hay partidos registrados en la base de datos.";
    }
    
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
}
?> 
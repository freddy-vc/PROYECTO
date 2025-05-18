<?php
// Iniciar la sesión
session_start();

// Calcular la ruta base relativa
$current_dir = dirname(__FILE__);
$project_root = dirname(dirname(dirname($current_dir)));
$relative_path = '';

// Si estamos en un subdirectorio del servidor (localhost/proyecto)
if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
    $project_folder = basename(dirname(dirname(dirname(__FILE__))));
    $relative_path = '';
    
    // Determinar si estamos en un subdirectorio o en la raíz
    if ($project_folder != 'www' && $project_folder != 'htdocs') {
        $relative_path = '/' . $project_folder;
    }
}

// Incluir el archivo de conexión para cerrar la conexión a la base de datos
require_once '../database/connection.php';

// Cerrar la conexión a la base de datos
Conexion::cerrarConexion();

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redireccionar al inicio
header('Location: ' . $relative_path . '/index.php');
exit;
?> 
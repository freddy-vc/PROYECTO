<?php
// Iniciar la sesión
session_start();

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
header('Location: ../../index.php');
exit;
?> 
<?php
/**
 * Archivo de configuración global
 * 
 * Este archivo contiene configuraciones globales como rutas,
 * conexiones de base de datos y otras constantes del sistema.
 */

// Obtener la ruta base del proyecto
function getBasePath() {
    // Detectar si estamos en un subdirectorio
    $project_folder = basename(dirname(__FILE__));
    $is_root_folder = ($project_folder == 'www' || $project_folder == 'htdocs');
    
    // Si no estamos en la raíz, usar el nombre de la carpeta
    if (!$is_root_folder) {
        return '/' . $project_folder;
    }
    
    // Si estamos en la raíz, devolver solo /
    return '';
}

// Definir la ruta base del proyecto
$base_path = getBasePath();

// Definir variables globales para recursos
define('BASE_PATH', $base_path);
define('ASSETS_PATH', $base_path . '/frontend/assets');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');
define('IMAGES_PATH', ASSETS_PATH . '/images');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'futsala');
define('DB_USER', 'postgres');
define('DB_PASS', 'postgres');

// Otras configuraciones globales
define('SITE_NAME', 'Campeonato Futsala Villavicencio');
define('DEFAULT_ADMIN_EMAIL', 'admin@futsala.com');

// Configuraciones de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_set_cookie_params([
    'lifetime' => 3600, // 1 hora
    'path' => '/',
    'secure' => false, // Cambia a true si usas HTTPS
    'httponly' => true
]);
?> 
<?php
// Inicia la sesión en cada página
session_start();

// Definir parámetros de conexión a la base de datos directamente
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'futsala');
define('DB_USER', 'postgres');
define('DB_PASS', 'postgres');

// Definir el nombre del sitio
define('SITE_NAME', 'Campeonato Futsala Villavicencio');

// Configuraciones de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_set_cookie_params([
    'lifetime' => 3600, // 1 hora
    'path' => '/',
    'secure' => false, // Cambia a true si usas HTTPS
    'httponly' => true
]);

// Si no está definida la ruta raíz, determinarla según la ubicación del archivo
if (!isset($ruta_raiz)) {
    if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
        $ruta_raiz = "../../../";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
        $ruta_raiz = "../../";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
        $ruta_raiz = "../";
    } else {
        $ruta_raiz = "./";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <!-- CSS Común -->
    <link rel="stylesheet" href="<?php echo $ruta_raiz; ?>frontend/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS específico de la página actual -->
    <?php if(isset($pagina_actual)): ?>
        <?php
        // Comprobar primero si existe un archivo CSS específico para esta página
        $css_file_path = dirname(__FILE__) . '/../../frontend/assets/css/' . $pagina_actual . '.css';
        $css_page_file = $ruta_raiz . 'frontend/assets/css/' . $pagina_actual . '.css';
        
        // Si estamos en una página de detalle, cargar también ese CSS
        if (strpos($_SERVER['PHP_SELF'], 'detalle-') !== false) {
            $detalle_css = $ruta_raiz . 'frontend/assets/css/detalle-' . substr($pagina_actual, 0, -1) . '.css';
            echo '<link rel="stylesheet" href="' . $detalle_css . '">';
        }
        
        // Cargar el CSS de la página actual si existe
        if (file_exists($css_file_path)): ?>
            <link rel="stylesheet" href="<?php echo $css_page_file; ?>">
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Cache Buster -->
    <script src="<?php echo $ruta_raiz; ?>frontend/assets/js/cache-buster.js"></script>
    
    <!-- JavaScript común -->
    <script src="<?php echo $ruta_raiz; ?>frontend/assets/js/main.js" defer></script>
    
    <!-- JavaScript específico de la página actual -->
    <?php if(isset($pagina_actual) && file_exists(dirname(__FILE__) . '/../../frontend/assets/js/' . $pagina_actual . '.js')): ?>
    <script src="<?php echo $ruta_raiz; ?>frontend/assets/js/<?php echo $pagina_actual; ?>.js" defer></script>
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo $ruta_raiz; ?>index.php">
                        <img src="<?php echo $ruta_raiz; ?>frontend/assets/images/logo.png" alt="Logo VILLAVOCUP">
                        <span>VILLAVOCUP</span>
                    </a>
                </div>
                
                <?php include_once dirname(__FILE__) . '/nav.php'; ?>
                
                <div class="user-section">
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <div class="user-profile">
                            <img src="<?php 
                            if (isset($_SESSION['usuario_foto']) && $_SESSION['usuario_foto'] && 
                                (strpos($_SESSION['usuario_foto'], 'data:') === 0)) {
                                // Si es una imagen en base64, usarla directamente
                                echo $_SESSION['usuario_foto'];
                            } else {
                                // De lo contrario, determinar la ruta correcta según la ubicación actual
                                echo $ruta_raiz . 'frontend/assets/images/user.png';
                            }
                            ?>" alt="Foto de perfil">
                            <span><?php echo $_SESSION['usuario_nombre']; ?></span>
                            <div class="user-dropdown">
                                <ul>
                                    <li><a href="<?php echo $ruta_raiz; ?>frontend/pages/perfil.php">Mi Perfil</a></li>
                                    <?php if(isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                                    <li><a href="<?php echo $ruta_raiz; ?>frontend/pages/admin/index.php" class="admin-link">Panel de Administración</a></li>
                                    <?php endif; ?>
                                    <li><a href="<?php echo $ruta_raiz; ?>backend/controllers/logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="<?php echo $ruta_raiz; ?>frontend/pages/login.php" class="btn btn-login">Iniciar Sesión</a>
                            <a href="<?php echo $ruta_raiz; ?>frontend/pages/registro.php" class="btn btn-register">Registrarse</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container"> 
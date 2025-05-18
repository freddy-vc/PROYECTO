<?php
// Inicia la sesión en cada página
session_start();

// Incluir el archivo de configuración global
$config_path = dirname(dirname(dirname(__FILE__))) . '/config.php';
if (file_exists($config_path)) {
    include_once $config_path;
}

// Usar la configuración global si está disponible, o calcular las rutas si no
if (!isset($base_path)) {
    $current_path = $_SERVER['PHP_SELF'];
    $project_folder = basename(dirname(dirname(dirname($current_path))));
    
    // Si el proyecto está en la raíz de localhost
    if (strpos($current_path, '/frontend/pages/') !== false) {
        $base_path = '../..';
    } else if (strpos($current_path, '/frontend/') !== false) {
        $base_path = '..';
    } else {
        $base_path = '';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' - ' : ''; ?><?php echo defined('SITE_NAME') ? SITE_NAME : 'Campeonato Futsala Villavicencio'; ?></title>
    
    <!-- CSS Común -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>/frontend/assets/css/styles.css">
    
    <!-- CSS específico de la página actual -->
    <?php if(isset($pagina_actual) && file_exists(dirname(__FILE__) . '/../../frontend/assets/css/' . $pagina_actual . '.css')): ?>
    <link rel="stylesheet" href="<?php echo $base_path; ?>/frontend/assets/css/<?php echo $pagina_actual; ?>.css">
    <?php endif; ?>
    
    <!-- JavaScript común -->
    <script src="<?php echo $base_path; ?>/frontend/assets/js/main.js" defer></script>
    
    <!-- JavaScript específico de la página actual -->
    <?php if(isset($pagina_actual) && file_exists(dirname(__FILE__) . '/../../frontend/assets/js/' . $pagina_actual . '.js')): ?>
    <script src="<?php echo $base_path; ?>/frontend/assets/js/<?php echo $pagina_actual; ?>.js" defer></script>
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo $base_path; ?>/index.php">
                        <img src="<?php echo $base_path; ?>/frontend/assets/images/logo.png" alt="Logo Futsala Villavicencio">
                        <span>Futsala Villavicencio</span>
                    </a>
                </div>
                
                <?php include_once dirname(__FILE__) . '/nav.php'; ?>
                
                <div class="user-section">
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <div class="user-profile">
                            <img src="<?php echo $_SESSION['usuario_foto'] ? $_SESSION['usuario_foto'] : $base_path . '/frontend/assets/images/default-profile.png'; ?>" alt="Foto de perfil">
                            <span><?php echo $_SESSION['usuario_nombre']; ?></span>
                            <div class="user-dropdown">
                                <ul>
                                    <li><a href="<?php echo $base_path; ?>/frontend/pages/perfil.php">Mi Perfil</a></li>
                                    <li><a href="<?php echo $base_path; ?>/backend/controllers/logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="<?php echo $base_path; ?>/frontend/pages/login.php" class="btn btn-login">Iniciar Sesión</a>
                            <a href="<?php echo $base_path; ?>/frontend/pages/registro.php" class="btn btn-register">Registrarse</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container"> 
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <!-- CSS Común -->
    <link rel="stylesheet" href="<?php 
    // Determinar la ruta relativa según la ubicación del archivo
    if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
        echo "../../assets/css/styles.css";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
        echo "../assets/css/styles.css";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
        echo "./assets/css/styles.css";
    } else {
        echo "./frontend/assets/css/styles.css";
    }
    ?>">
    
    <!-- CSS específico de la página actual -->
    <?php if(isset($pagina_actual) && file_exists(dirname(__FILE__) . '/../../frontend/assets/css/' . $pagina_actual . '.css')): ?>
    <link rel="stylesheet" href="<?php 
    if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
        echo "../../assets/css/" . $pagina_actual . ".css";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
        echo "../assets/css/" . $pagina_actual . ".css";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
        echo "./assets/css/" . $pagina_actual . ".css";
    } else {
        echo "./frontend/assets/css/" . $pagina_actual . ".css";
    }
    ?>">
    <?php endif; ?>
    
    <!-- Cache Buster -->
    <script src="<?php 
    if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
        echo "../../assets/js/cache-buster.js";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
        echo "../assets/js/cache-buster.js";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
        echo "./assets/js/cache-buster.js";
    } else {
        echo "./frontend/assets/js/cache-buster.js";
    }
    ?>"></script>
    
    <!-- JavaScript común -->
    <script src="<?php 
    if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
        echo "../../assets/js/main.js";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
        echo "../assets/js/main.js";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
        echo "./assets/js/main.js";
    } else {
        echo "./frontend/assets/js/main.js";
    }
    ?>" defer></script>
    
    <!-- JavaScript específico de la página actual -->
    <?php if(isset($pagina_actual) && file_exists(dirname(__FILE__) . '/../../frontend/assets/js/' . $pagina_actual . '.js')): ?>
    <script src="<?php 
    if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
        echo "../../assets/js/" . $pagina_actual . ".js";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
        echo "../assets/js/" . $pagina_actual . ".js";
    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
        echo "./assets/js/" . $pagina_actual . ".js";
    } else {
        echo "./frontend/assets/js/" . $pagina_actual . ".js";
    }
    ?>" defer></script>
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php 
                    if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                        echo "../../index.php";
                    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                        echo "../index.php";
                    } else {
                        echo "./index.php";
                    }
                    ?>">
                        <img src="<?php 
                        if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
                            echo "../../assets/images/logo.png";
                        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                            echo "../assets/images/logo.png";
                        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                            echo "./assets/images/logo.png";
                        } else {
                            echo "./frontend/assets/images/logo.png";
                        }
                        ?>" alt="Logo VILLAVOCUP">
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
                                if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
                                    echo "../../assets/images/user.png";
                                } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                                    echo "../assets/images/user.png";
                                } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                                    echo "./assets/images/user.png";
                                } else {
                                    echo "./frontend/assets/images/user.png";
                                }
                            }
                            ?>" alt="Foto de perfil">
                            <span><?php echo $_SESSION['usuario_nombre']; ?></span>
                            <div class="user-dropdown">
                                <ul>
                                    <li><a href="<?php 
                                    if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
                                        echo "../perfil.php";
                                    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                                        echo "./perfil.php";
                                    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                                        echo "./pages/perfil.php";
                                    } else {
                                        echo "./frontend/pages/perfil.php";
                                    }
                                    ?>">Mi Perfil</a></li>
                                    <?php if(isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                                    <li><a href="<?php 
                                    if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
                                        echo "./index.php";
                                    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                                        echo "./admin/index.php";
                                    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                                        echo "./pages/admin/index.php";
                                    } else {
                                        echo "./frontend/pages/admin/index.php";
                                    }
                                    ?>" class="admin-link">Panel de Administración</a></li>
                                    <?php endif; ?>
                                    <li><a href="<?php 
                                    if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
                                        echo "../../../backend/controllers/logout.php";
                                    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                                        echo "../../backend/controllers/logout.php";
                                    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                                        echo "../backend/controllers/logout.php";
                                    } else {
                                        echo "./backend/controllers/logout.php";
                                    }
                                    ?>">Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="<?php 
                            if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                                echo "./login.php";
                            } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                                echo "./pages/login.php";
                            } else {
                                echo "./frontend/pages/login.php";
                            }
                            ?>" class="btn btn-login">Iniciar Sesión</a>
                            <a href="<?php 
                            if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                                echo "./registro.php";
                            } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                                echo "./pages/registro.php";
                            } else {
                                echo "./frontend/pages/registro.php";
                            }
                            ?>" class="btn btn-register">Registrarse</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container"> 
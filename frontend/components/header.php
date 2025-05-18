<?php
// Inicia la sesión en cada página
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' - ' : ''; ?>Campeonato Futsala Villavicencio</title>
    
    <!-- CSS Común -->
    <link rel="stylesheet" href="/frontend/assets/css/styles.css">
    
    <!-- CSS específico de la página actual -->
    <?php if(isset($pagina_actual) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/frontend/assets/css/' . $pagina_actual . '.css')): ?>
    <link rel="stylesheet" href="/frontend/assets/css/<?php echo $pagina_actual; ?>.css">
    <?php endif; ?>
    
    <!-- JavaScript común -->
    <script src="/frontend/assets/js/main.js" defer></script>
    
    <!-- JavaScript específico de la página actual -->
    <?php if(isset($pagina_actual) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/frontend/assets/js/' . $pagina_actual . '.js')): ?>
    <script src="/frontend/assets/js/<?php echo $pagina_actual; ?>.js" defer></script>
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="/index.php">
                        <img src="/frontend/assets/images/logo.png" alt="Logo Futsala Villavicencio">
                        <span>Futsala Villavicencio</span>
                    </a>
                </div>
                
                <?php include_once 'nav.php'; ?>
                
                <div class="user-section">
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <div class="user-profile">
                            <img src="<?php echo $_SESSION['usuario_foto'] ? $_SESSION['usuario_foto'] : '/frontend/assets/images/default-profile.png'; ?>" alt="Foto de perfil">
                            <span><?php echo $_SESSION['usuario_nombre']; ?></span>
                            <div class="user-dropdown">
                                <ul>
                                    <li><a href="/frontend/pages/perfil.php">Mi Perfil</a></li>
                                    <li><a href="/backend/controllers/logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="/frontend/pages/login.php" class="btn btn-login">Iniciar Sesión</a>
                            <a href="/frontend/pages/registro.php" class="btn btn-register">Registrarse</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container"> 
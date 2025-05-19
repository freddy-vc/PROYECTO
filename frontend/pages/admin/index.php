<?php
// Definir variables para la página
$titulo_pagina = 'Panel de Administración';
$pagina_actual = 'admin';

// Incluir el header
include_once '../../components/header.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Redireccionar a la página de inicio si no es administrador
    header('Location: ../../index.php');
    exit;
}

// Incluir el componente de notificaciones
include_once '../../components/notificaciones.php';
?>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="container">
    <h1 class="page-title">Panel de Administración</h1>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_admin', 'exito_admin']);
    ?>

    <!-- Información de bienvenida -->
    <div class="section-intro">
        <p>Bienvenido al panel de administración del Campeonato de Futsala Villavicencio</p>
    </div>

    <div class="admin-container">
        <div class="admin-modules">
            <div class="admin-card">
                <div class="admin-card-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h2>Equipos</h2>
                <p>Gestionar los equipos del campeonato</p>
                <a href="./equipos.php" class="btn">Administrar</a>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-icon">
                    <i class="fas fa-running"></i>
                </div>
                <h2>Jugadores</h2>
                <p>Gestionar los jugadores del campeonato</p>
                <a href="./jugadores.php" class="btn">Administrar</a>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-icon">
                    <i class="fas fa-city"></i>
                </div>
                <h2>Ciudades</h2>
                <p>Gestionar las ciudades participantes</p>
                <a href="./ciudades.php" class="btn">Administrar</a>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-icon">
                    <i class="fas fa-futbol"></i>
                </div>
                <h2>Canchas</h2>
                <p>Gestionar las canchas de juego</p>
                <a href="./canchas.php" class="btn">Administrar</a>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h2>Directores Técnicos</h2>
                <p>Gestionar los directores técnicos</p>
                <a href="./directores.php" class="btn">Administrar</a>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h2>Partidos</h2>
                <p>Gestionar los partidos y resultados</p>
                <a href="./partidos.php" class="btn">Administrar</a>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-icon">
                    <i class="fas fa-table"></i>
                </div>
                <h2>Tabla de Puntuación</h2>
                <p>Gestionar la tabla de posiciones</p>
                <a href="./clasificaciones.php" class="btn">Administrar</a>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h2>Usuarios</h2>
                <p>Gestionar los usuarios del sistema</p>
                <a href="./usuarios.php" class="btn">Administrar</a>
            </div>
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
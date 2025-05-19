<?php
// Definir variables para la página
$titulo_pagina = 'Inicio';
$pagina_actual = 'inicio';

// Incluir el header
include_once 'frontend/components/header.php';

// Incluir el componente de notificaciones
include_once 'frontend/components/notificaciones.php';
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="./frontend/assets/css/inicio.css">

<div class="container">
    <!-- Título de la página -->
    <h1 class="page-title">Campeonato de Futsala Villavicencio</h1>
    
    <!-- Mostrar notificaciones de éxito -->
    <?php mostrarNotificaciones(['exito_login']); ?>
</div>

<!-- Contenido principal de la página de inicio -->
<section class="hero">
    <div class="hero-content">
        <h1>Campeonato de Futsala Villavicencio</h1>
        <p>Toda la información del campeonato en un solo lugar</p>
        <div class="hero-buttons">
            <a href="./frontend/pages/partidos.php" class="btn btn-primary">Ver Próximos Partidos</a>
            <a href="./frontend/pages/clasificaciones.php" class="btn btn-secondary">Ver Clasificaciones</a>
        </div>
    </div>
</section>

<section class="home-features">
    <div class="feature">
        <img src="./frontend/assets/images/equipos-icon.png" alt="Equipos">
        <h2>Equipos</h2>
        <p>Conoce los equipos participantes en el campeonato</p>
        <a href="./frontend/pages/equipos.php" class="btn">Ver Equipos</a>
    </div>
    
    <div class="feature">
        <img src="./frontend/assets/images/jugadores-icon.png" alt="Jugadores">
        <h2>Jugadores</h2>
        <p>Información detallada de todos los jugadores del torneo</p>
        <a href="./frontend/pages/jugadores.php" class="btn">Ver Jugadores</a>
    </div>
    
    <div class="feature">
        <img src="./frontend/assets/images/partidos-icon.png" alt="Partidos">
        <h2>Partidos</h2>
        <p>Calendario y resultados de todos los encuentros</p>
        <a href="./frontend/pages/partidos.php" class="btn">Ver Partidos</a>
    </div>
</section>

<!-- Esta sección se mostrará solo si hay partidos finalizados -->
<section class="recent-matches" style="display: none;">
    <h2>Últimos Resultados</h2>
    <div class="matches-container" id="ultimos-partidos">
        <div class="loading-matches">Cargando últimos resultados...</div>
    </div>
</section>

<!-- Esta sección se mostrará solo si hay jugadores con goles o asistencias -->
<section class="featured-players" style="display: none;">
    <h2>Jugadores Destacados</h2>
    <div class="players-container" id="jugadores-destacados">
        <div class="loading-players">Cargando jugadores destacados...</div>
    </div>
</section>

<!-- Incluir el script específico para esta página -->
<script src="./frontend/assets/js/inicio.js"></script>

<?php
// Incluir el footer
include_once 'frontend/components/footer.php';
?> 
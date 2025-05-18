<?php
// Definir variables para la página
$titulo_pagina = 'Inicio';
$pagina_actual = 'inicio';

// Incluir el header
include_once 'frontend/components/header.php';
?>

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

<section class="recent-matches">
    <h2>Últimos Resultados</h2>
    <div class="matches-container">
        <!-- Aquí se cargarán los últimos partidos desde la base de datos -->
        <?php
        // Ejemplo de cómo se mostrará (esto se reemplazará con datos reales)
        ?>
        <div class="match-card">
            <div class="match-date">15 Mayo, 2023 - 18:00</div>
            <div class="match-teams">
                <div class="team">
                    <img src="./frontend/assets/images/team-placeholder.png" alt="Equipo Local">
                    <span>Equipo Local</span>
                </div>
                <div class="match-score">
                    <span>3 - 2</span>
                </div>
                <div class="team">
                    <img src="./frontend/assets/images/team-placeholder.png" alt="Equipo Visitante">
                    <span>Equipo Visitante</span>
                </div>
            </div>
            <a href="./frontend/pages/detalle-partido.php?id=1" class="btn btn-small">Ver Detalles</a>
        </div>
        <!-- Fin del ejemplo -->
    </div>
</section>

<?php
// Incluir el footer
include_once 'frontend/components/footer.php';
?> 
<?php
// Definir variables para la página
$titulo_pagina = 'Jugadores';
$pagina_actual = 'jugadores';

// Incluir el header
include_once '../components/header.php';

// Incluir el componente de notificaciones
include_once '../components/notificaciones.php';
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../assets/css/jugadores.css">

<div class="container">
    <h1 class="page-title">Jugadores del Campeonato</h1>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_jugadores', 'exito_jugadores']);
    ?>
    
    <div class="filter-bar">
        <div class="search-box">
            <input type="text" id="buscar-jugador" placeholder="Buscar jugador...">
            <i class="fa fa-search"></i>
        </div>
        
        <div class="filter-options">
            <select id="filtro-equipo" class="filter-select">
                <option value="">Todos los equipos</option>
            </select>
            
            <select id="filtro-posicion" class="filter-select">
                <option value="">Todas las posiciones</option>
                <option value="delantero">Delanteros</option>
                <option value="mediocampista">Mediocampistas</option>
                <option value="defensa">Defensas</option>
                <option value="arquero">Arqueros</option>
            </select>
        </div>
    </div>
    
    <div class="jugadores-container" id="jugadores-container">
        <!-- Aquí se cargarán dinámicamente los jugadores -->
        <div class="loading">Cargando jugadores...</div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../assets/js/jugadores.js?v=<?php echo time(); ?>"></script>

<?php
// Incluir el footer
include_once '../components/footer.php';
?> 
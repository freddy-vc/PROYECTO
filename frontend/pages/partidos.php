<?php
// Definir variables para la página
$titulo_pagina = 'Partidos';
$pagina_actual = 'partidos';

// Incluir el header
include_once '../components/header.php';

// Incluir el componente de notificaciones
include_once '../components/notificaciones.php';
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../assets/css/partidos.css">

<div class="container">
    <h1 class="page-title">Calendario de Partidos</h1>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_partidos', 'exito_partidos']);
    ?>
    
    <div class="filter-bar">
        <div class="filter-options">
            <button class="filter-btn active" data-filtro="todos">Todos</button>
            <button class="filter-btn" data-filtro="programados">Programados</button>
            <button class="filter-btn" data-filtro="finalizados">Finalizados</button>
        </div>
    </div>
    
    <div class="partidos-container" id="partidos-container">
        <!-- Aquí se cargarán dinámicamente los partidos -->
        <div class="loading">Cargando partidos...</div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../assets/js/partidos.js"></script>

<?php
// Incluir el footer
include_once '../components/footer.php';
?> 
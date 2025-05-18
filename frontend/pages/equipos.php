<?php
// Definir variables para la página
$titulo_pagina = 'Equipos';
$pagina_actual = 'equipos';

// Incluir el header
include_once '../components/header.php';

// Incluir el componente de notificaciones
include_once '../components/notificaciones.php';
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../assets/css/equipos.css">

<div class="container">
    <h1 class="page-title">Equipos</h1>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_equipos', 'exito_equipos']);
    ?>
    
    <div class="filter-bar">
        <div class="search-box">
            <input type="text" id="buscar-equipo" placeholder="Buscar equipo...">
            <i class="fa fa-search"></i>
        </div>
    </div>
    
    <div class="equipos-container" id="equipos-container">
        <!-- Aquí se cargarán dinámicamente los equipos -->
        <div class="loading">Cargando equipos...</div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../assets/js/equipos.js?v=<?php echo time(); ?>"></script>

<?php
// Incluir el footer
include_once '../components/footer.php';
?> 
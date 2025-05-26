<?php
// Definir variables para la página
$titulo_pagina = 'Clasificaciones - Eliminatorias';
$pagina_actual = 'clasificaciones';

// Incluir el header
include_once '../components/header.php';

// Incluir el componente de notificaciones
include_once '../components/notificaciones.php';
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../assets/css/clasificaciones.css">

<div class="container">
    <h1 class="page-title">Clasificaciones - Eliminatorias</h1>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_clasificaciones', 'exito_clasificaciones']);
    ?>
    
    <!-- Contenedor principal para el cuadro de eliminatorias -->
    <div id="clasificaciones-container">
        <!-- El contenido será cargado mediante JavaScript -->
        <div class="loading-message">
            <i class="fas fa-spinner fa-spin"></i> Cargando cuadro de eliminatorias...
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../assets/js/clasificaciones.js"></script>

<?php
// Incluir el footer
include_once '../components/footer.php';
?> 
<?php
// Definir variables para la página
$titulo_pagina = 'Detalle del Partido';
$pagina_actual = 'partidos';

// Incluir el header
include_once '../components/header.php';

// Incluir el componente de notificaciones
include_once '../components/notificaciones.php';

// Verificar que se ha enviado un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirigir a la página de partidos si no hay ID válido
    echo '<script>window.location.href = "partidos.php";</script>';
    exit;
}

$partido_id = intval($_GET['id']);
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../assets/css/detalle-partido.css">

<div class="container">
    <div class="back-link">
        <a href="partidos.php"><i class="fas fa-arrow-left"></i> Volver a Partidos</a>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_partido', 'exito_partido']);
    ?>
    
    <div class="partido-detalle" id="partido-detalle">
        <div class="loading">Cargando información del partido...</div>
    </div>
</div>

<!-- Incluir el script específico para esta página -->
<script src="../assets/js/detalle-partido.js"></script>

<?php
// Incluir el footer
include_once '../components/footer.php';
?> 
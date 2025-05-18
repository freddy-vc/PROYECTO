<?php
// Definir variables para la página
$titulo_pagina = 'Clasificaciones';
$pagina_actual = 'clasificaciones';

// Incluir el header
include_once '../components/header.php';

// Incluir el componente de notificaciones
include_once '../components/notificaciones.php';
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../assets/css/clasificaciones.css">

<div class="container">
    <h1 class="page-title">Clasificaciones</h1>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_clasificaciones', 'exito_clasificaciones']);
    ?>
    
    <div class="bracket-container">
        <div class="loading">Cargando clasificaciones...</div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../assets/js/clasificaciones.js?v=<?php echo time(); ?>"></script>

<?php
// Incluir el footer
include_once '../components/footer.php';
?> 
<?php
// Definir variables para la página
$titulo_pagina = 'Detalle de Equipo';
$pagina_actual = 'equipos';

// Obtener el ID del equipo
$id_equipo = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validar que el ID sea válido
if ($id_equipo <= 0) {
    // Redireccionar a la página de equipos
    header('Location: equipos.php');
    exit;
}

// Ruta relativa para acceder a los componentes desde la raíz
$ruta_raiz = '../../';

// Incluir el header principal
include_once $ruta_raiz . 'frontend/components/header.php';

// Incluir el componente de notificaciones
include_once $ruta_raiz . 'frontend/components/notificaciones.php';
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../assets/css/detalle-equipo.css">

<div class="container">
    <div class="back-link">
        <a href="equipos.php"><i class="fa fa-arrow-left"></i> Volver a Equipos</a>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_equipo', 'exito_equipo']);
    ?>
    
    <div id="detalle-equipo-container" class="loading-container">
        <div class="loading">Cargando información del equipo...</div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../assets/js/detalle-equipo.js"></script>

<?php
// Incluir el footer
include_once $ruta_raiz . 'frontend/components/footer.php';
?> 
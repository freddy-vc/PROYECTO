<?php
// Definir variables para la página
$titulo_pagina = 'Detalle del Partido';
$pagina_actual = 'partidos';

// Ruta relativa para acceder a los componentes desde la raíz
$ruta_raiz = '../../';

// Incluir el header principal
include_once $ruta_raiz . 'frontend/components/header.php';

// Incluir el componente de notificaciones
include_once $ruta_raiz . 'frontend/components/notificaciones.php';

// Verificar que se ha enviado un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirigir a la página de partidos si no hay ID válido
    echo '<script>window.location.href = "partidos.php";</script>';
    exit;
}

$partido_id = intval($_GET['id']);
?>

<!-- Font Awesome ya se carga en el header -->
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

<!-- Incluir el script específico para esta página -->
<script src="<?php echo $ruta_raiz; ?>frontend/assets/js/detalle-partido.js"></script>

<?php
// Incluir el footer
include_once $ruta_raiz . 'frontend/components/footer.php';
?> 
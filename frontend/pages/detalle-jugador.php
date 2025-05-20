<?php
// Definir variables para la página
$titulo_pagina = 'Detalle del Jugador';
$pagina_actual = 'detalle_jugador';

// Incluir el header
include_once '../components/header.php';

// Incluir el componente de notificaciones
include_once '../components/notificaciones.php';

// Verificar que se ha recibido un ID de jugador
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Si no se ha recibido un ID, redirigir a la página de jugadores
    header('Location: jugadores.php');
    exit;
}

// Obtener el ID del jugador
$jugadorId = intval($_GET['id']);
?>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../assets/css/detalle-jugador.css">

<div class="container player-profile-container">
    <div class="loading-container">
        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i> Cargando información del jugador...
        </div>
    </div>
    
    <div id="player-profile" style="display: none;">
        <!-- El contenido del perfil del jugador se cargará aquí mediante JavaScript -->
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../assets/js/detalle-jugador.js"></script>

<?php
// Incluir el footer
include_once '../components/footer.php';
?> 
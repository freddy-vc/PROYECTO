<?php
// Definir variables para la página
$titulo_pagina = 'Administrar Cancha';
$pagina_actual = 'admin_canchas';

// Incluir el header
include_once '../../components/header.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Redireccionar a la página de inicio si no es administrador
    header('Location: ../../index.php');
    exit;
}

// Incluir el componente de notificaciones
include_once '../../components/notificaciones.php';

// Incluir los modelos necesarios
require_once '../../../backend/models/Cancha.php';

// Verificar si se está editando una cancha existente
$esEdicion = isset($_GET['id']) && !empty($_GET['id']);
$cancha = null;
$titulo_accion = 'Agregar Cancha';
$accion = 'crear';

if ($esEdicion) {
    $canchaId = intval($_GET['id']);
    $canchaModel = new Cancha();
    $cancha = $canchaModel->obtenerPorId($canchaId);
    
    if ($cancha) {
        $titulo_accion = 'Editar Cancha';
        $accion = 'actualizar';
    } else {
        // Si no se encuentra la cancha, redireccionar a la lista
        $_SESSION['error_canchas'] = 'La cancha solicitada no existe';
        header('Location: ./canchas.php');
        exit;
    }
}
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">
<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="container">
    <div class="breadcrumb">
        <a href="./canchas.php">
            <i class="fas fa-arrow-left"></i> Volver a Canchas
        </a>
    </div>
    
    <h1 class="page-title"><?php echo $titulo_accion; ?></h1>
    
    <div class="section-intro">
        <p>Completa el formulario para <?php echo $esEdicion ? 'actualizar' : 'agregar'; ?> la cancha</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_canchas', 'exito_canchas']);
    ?>
    
    <div class="admin-form-container">
        <form action="../../../backend/controllers/admin/canchas_controller.php" method="POST" class="admin-form" id="cancha-form">
            <input type="hidden" name="accion" value="<?php echo $accion; ?>">
            
            <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?php echo $cancha['cod_cancha']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nombre">Nombre de la Cancha <span class="required">*</span></label>
                <input type="text" id="nombre" name="nombre" required maxlength="100" 
                    value="<?php echo $esEdicion ? htmlspecialchars($cancha['nombre']) : ''; ?>">
                <div class="form-error" id="error-nombre"></div>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" maxlength="255" 
                    value="<?php echo $esEdicion && isset($cancha['direccion']) ? htmlspecialchars($cancha['direccion']) : ''; ?>">
                <div class="form-error" id="error-direccion"></div>
            </div>
            
            <div class="form-group">
                <label for="capacidad">Capacidad (personas)</label>
                <input type="number" id="capacidad" name="capacidad" min="0" max="100000" 
                    value="<?php echo $esEdicion && isset($cancha['capacidad']) ? htmlspecialchars($cancha['capacidad']) : ''; ?>">
                <div class="form-error" id="error-capacidad"></div>
            </div>
            
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='./canchas.php'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cancha</button>
            </div>
        </form>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_canchas.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
<?php
// Definir variables para la página
$titulo_pagina = 'Administrar Ciudad';
$pagina_actual = 'admin_ciudades';

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
require_once '../../../backend/models/Ciudad.php';

// Verificar si se está editando una ciudad existente
$esEdicion = isset($_GET['id']) && !empty($_GET['id']);
$ciudad = null;
$titulo_accion = 'Agregar Ciudad';
$accion = 'crear';

if ($esEdicion) {
    $ciudadId = intval($_GET['id']);
    $ciudadModel = new Ciudad();
    $ciudad = $ciudadModel->obtenerPorId($ciudadId);
    
    if ($ciudad) {
        $titulo_accion = 'Editar Ciudad';
        $accion = 'actualizar';
    } else {
        // Si no se encuentra la ciudad, redireccionar a la lista
        $_SESSION['error_ciudades'] = 'La ciudad solicitada no existe';
        header('Location: ./ciudades.php');
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
        <a href="./ciudades.php">
            <i class="fas fa-arrow-left"></i> Volver a Ciudades
        </a>
    </div>
    
    <h1 class="page-title"><?php echo $titulo_accion; ?></h1>
    
    <div class="section-intro">
        <p>Completa el formulario para <?php echo $esEdicion ? 'actualizar' : 'agregar'; ?> la ciudad</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_ciudades', 'exito_ciudades']);
    ?>
    
    <div class="admin-form-container">
        <form action="../../../backend/controllers/admin/ciudades_controller.php" method="POST" class="admin-form" id="ciudad-form">
            <input type="hidden" name="accion" value="<?php echo $accion; ?>">
            
            <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?php echo $ciudad['cod_ciu']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nombre">Nombre de la Ciudad <span class="required">*</span></label>
                <input type="text" id="nombre" name="nombre" required maxlength="50" 
                    value="<?php echo $esEdicion ? htmlspecialchars($ciudad['nombre']) : ''; ?>">
                <div class="form-error" id="error-nombre"></div>
            </div>
            
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='./ciudades.php'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Ciudad</button>
            </div>
        </form>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_ciudades.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
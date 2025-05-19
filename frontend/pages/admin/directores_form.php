<?php
// Definir variables para la página
$titulo_pagina = 'Administrar Director Técnico';
$pagina_actual = 'admin_directores';

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
require_once '../../../backend/models/Director.php';

// Verificar si se está editando un director técnico existente
$esEdicion = isset($_GET['id']) && !empty($_GET['id']);
$director = null;
$titulo_accion = 'Agregar Director Técnico';
$accion = 'crear';

if ($esEdicion) {
    $directorId = intval($_GET['id']);
    $directorModel = new Director();
    $director = $directorModel->obtenerPorId($directorId);
    
    if ($director) {
        $titulo_accion = 'Editar Director Técnico';
        $accion = 'actualizar';
        
        // Obtener equipos dirigidos por este director
        $equiposDirigidos = $directorModel->obtenerEquipos($directorId);
    } else {
        // Si no se encuentra el director técnico, redireccionar a la lista
        $_SESSION['error_directores'] = 'El director técnico solicitado no existe';
        header('Location: ./directores.php');
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
        <a href="./directores.php">
            <i class="fas fa-arrow-left"></i> Volver a Directores Técnicos
        </a>
    </div>
    
    <h1 class="page-title"><?php echo $titulo_accion; ?></h1>
    
    <div class="section-intro">
        <p>Completa el formulario para <?php echo $esEdicion ? 'actualizar' : 'agregar'; ?> el director técnico</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_directores', 'exito_directores']);
    ?>
    
    <div class="admin-form-container">
        <form action="../../../backend/controllers/admin/directores_controller.php" method="POST" class="admin-form" id="director-form">
            <input type="hidden" name="accion" value="<?php echo $accion; ?>">
            
            <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?php echo $director['cod_dt']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nombres">Nombres <span class="required">*</span></label>
                <input type="text" id="nombres" name="nombres" required maxlength="50" 
                    value="<?php echo $esEdicion ? htmlspecialchars($director['nombres']) : ''; ?>">
                <div class="form-error" id="error-nombres"></div>
            </div>
            
            <div class="form-group">
                <label for="apellidos">Apellidos <span class="required">*</span></label>
                <input type="text" id="apellidos" name="apellidos" required maxlength="50" 
                    value="<?php echo $esEdicion ? htmlspecialchars($director['apellidos']) : ''; ?>">
                <div class="form-error" id="error-apellidos"></div>
            </div>
            
            <?php if ($esEdicion && !empty($equiposDirigidos)): ?>
            <div class="form-group">
                <label>Equipos Dirigidos</label>
                <div class="equipos-list">
                    <?php foreach ($equiposDirigidos as $equipo): ?>
                    <div class="equipo-item">
                        <img src="<?php echo $equipo['escudo_base64']; ?>" alt="<?php echo $equipo['nombre']; ?>" class="equipo-icon">
                        <span><?php echo $equipo['nombre']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-help">Para cambiar los equipos asignados, ve a la sección de equipos.</div>
            </div>
            <?php endif; ?>
            
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='./directores.php'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Director Técnico</button>
            </div>
        </form>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_directores.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
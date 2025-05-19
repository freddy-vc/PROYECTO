<?php
// Definir variables para la página
$titulo_pagina = 'Administrar Clasificación';
$pagina_actual = 'admin_clasificaciones';

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
require_once '../../../backend/models/Clasificacion.php';
require_once '../../../backend/models/Equipo.php';

// Verificar si se está editando una clasificación existente
$esEdicion = isset($_GET['id']) && !empty($_GET['id']);
$clasificacion = null;
$titulo_accion = 'Agregar Clasificación';
$accion = 'crear';

if ($esEdicion) {
    $clasificacionId = intval($_GET['id']);
    $clasificacionModel = new Clasificacion();
    $clasificacion = $clasificacionModel->obtenerPorId($clasificacionId);
    
    if ($clasificacion) {
        $titulo_accion = 'Editar Clasificación';
        $accion = 'actualizar';
    } else {
        // Si no se encuentra la clasificación, redireccionar a la lista
        $_SESSION['error_clasificaciones'] = 'La clasificación solicitada no existe';
        header('Location: ./clasificaciones.php');
        exit;
    }
}

// Obtener todos los equipos para el selector
$equipoModel = new Equipo();
$equipos = $equipoModel->obtenerTodos();

// Obtener las fases disponibles
$clasificacionModel = new Clasificacion();
$fasesDisponibles = $clasificacionModel->obtenerFasesDisponibles();
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">
<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="container">
    <div class="breadcrumb">
        <a href="./clasificaciones.php">
            <i class="fas fa-arrow-left"></i> Volver a Clasificaciones
        </a>
    </div>
    
    <h1 class="page-title"><?php echo $titulo_accion; ?></h1>
    
    <div class="section-intro">
        <p>Completa el formulario para <?php echo $esEdicion ? 'actualizar' : 'agregar'; ?> la clasificación de un equipo</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_clasificaciones', 'exito_clasificaciones']);
    ?>
    
    <div class="admin-form-container">
        <form action="../../../backend/controllers/admin/clasificaciones_controller.php" method="POST" class="admin-form" id="clasificacion-form">
            <input type="hidden" name="accion" value="<?php echo $accion; ?>">
            
            <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?php echo $clasificacion['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="equipo_id">Equipo <span class="required">*</span></label>
                <select id="equipo_id" name="equipo_id" required>
                    <option value="">Selecciona un equipo</option>
                    <?php foreach ($equipos as $equipo): ?>
                    <option value="<?php echo $equipo['cod_equ']; ?>" 
                        <?php echo ($esEdicion && $clasificacion['cod_equ'] == $equipo['cod_equ']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($equipo['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-error" id="error-equipo"></div>
            </div>
            
            <div class="form-group">
                <label for="fase">Fase <span class="required">*</span></label>
                <select id="fase" name="fase" required>
                    <option value="">Selecciona una fase</option>
                    <?php foreach ($fasesDisponibles as $codigo => $nombre): ?>
                    <option value="<?php echo $codigo; ?>" 
                        <?php echo ($esEdicion && $clasificacion['fase'] == $codigo) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($nombre); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-error" id="error-fase"></div>
            </div>
            
            <div class="form-group">
                <label for="posicion">Posición <span class="required">*</span></label>
                <input type="number" id="posicion" name="posicion" required min="1" max="100" 
                    value="<?php echo $esEdicion ? htmlspecialchars($clasificacion['posicion']) : '1'; ?>">
                <div class="form-error" id="error-posicion"></div>
            </div>
            
            <div class="form-group">
                <label for="fecha_clasificacion">Fecha de Clasificación</label>
                <input type="date" id="fecha_clasificacion" name="fecha_clasificacion" 
                    value="<?php echo $esEdicion ? htmlspecialchars($clasificacion['fecha_clasificacion']) : date('Y-m-d'); ?>">
                <div class="form-error" id="error-fecha-clasificacion"></div>
            </div>
            
            <div class="form-group">
                <label for="comentario">Comentario</label>
                <textarea id="comentario" name="comentario" rows="3" maxlength="255"><?php echo $esEdicion ? htmlspecialchars($clasificacion['comentario']) : ''; ?></textarea>
                <div class="form-error" id="error-comentario"></div>
            </div>
            
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='./clasificaciones.php'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Clasificación</button>
            </div>
        </form>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_clasificaciones.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
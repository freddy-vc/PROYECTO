<?php
// Definir variables para la página
$titulo_pagina = 'Administración de Ciudades';
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

// Incluir el modelo de Ciudad
require_once '../../../backend/models/Ciudad.php';

// Obtener todas las ciudades
$ciudadModel = new Ciudad();
$ciudades = $ciudadModel->obtenerTodas();
?>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">

<div class="container">
    <h1 class="page-title">Administración de Ciudades</h1>
    
    <div class="section-intro">
        <p>Gestiona las ciudades del campeonato de Futsala Villavicencio</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_ciudades', 'exito_ciudades']);
    ?>

    <div class="admin-container">
        <!-- Navegación dentro del panel de administración -->
        <div class="admin-nav">
            <ul>
                <li><a href="./index.php">Inicio</a></li>
                <li><a href="./equipos.php">Equipos</a></li>
                <li><a href="./jugadores.php">Jugadores</a></li>
                <li><a href="./ciudades.php" class="active">Ciudades</a></li>
                <li><a href="./canchas.php">Canchas</a></li>
                <li><a href="./directores.php">Directores Técnicos</a></li>
                <li><a href="./partidos.php">Partidos</a></li>
                <li><a href="./usuarios.php">Usuarios</a></li>
            </ul>
        </div>
        
        <!-- Sección de filtros y búsqueda -->
        <div class="admin-filters">
            <div class="admin-search">
                <input type="text" placeholder="Buscar ciudad..." data-table="ciudades-table">
                <i class="fas fa-search"></i>
            </div>
            
            <a href="./ciudades_form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Ciudad
            </a>
        </div>
        
        <!-- Tabla de ciudades -->
        <div class="admin-table-container">
            <table class="admin-table" id="ciudades-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ciudades as $ciudad): ?>
                    <tr>
                        <td><?php echo $ciudad['cod_ciu']; ?></td>
                        <td><?php echo $ciudad['nombre']; ?></td>
                        <td class="admin-actions">
                            <a href="./ciudades_form.php?id=<?php echo $ciudad['cod_ciu']; ?>" class="action-btn edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="../../../backend/controllers/admin/ciudades_controller.php" method="POST" style="display: inline;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $ciudad['cod_ciu']; ?>">
                                <button type="button" class="action-btn delete delete-btn" title="Eliminar" data-name="<?php echo $ciudad['nombre']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($ciudades)): ?>
                    <tr>
                        <td colspan="3" class="no-results">No hay ciudades registradas</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_ciudades.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
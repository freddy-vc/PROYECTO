<?php
// Definir variables para la página
$titulo_pagina = 'Administración de Equipos';
$pagina_actual = 'admin_equipos';

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

// Incluir el modelo de Equipo
require_once '../../../backend/models/Equipo.php';

// Obtener los equipos
$equipoModel = new Equipo();
$equipos = $equipoModel->obtenerTodos();
?>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">

<div class="container">
    <h1 class="page-title">Administración de Equipos</h1>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_equipos', 'exito_equipos']);
    ?>

    <div class="admin-container">
        <!-- Navegación dentro del panel de administración -->
        <div class="admin-nav">
            <ul>
                <li><a href="./index.php">Inicio</a></li>
                <li><a href="./equipos.php" class="active">Equipos</a></li>
                <li><a href="./jugadores.php">Jugadores</a></li>
                <li><a href="./ciudades.php">Ciudades</a></li>
                <li><a href="./canchas.php">Canchas</a></li>
                <li><a href="./directores.php">Directores Técnicos</a></li>
                <li><a href="./partidos.php">Partidos</a></li>
                <li><a href="./usuarios.php">Usuarios</a></li>
            </ul>
        </div>
        
        <div class="admin-header">
            <p>Gestiona los equipos del campeonato de Futsala Villavicencio</p>
        </div>
        
        <!-- Sección de filtros y búsqueda -->
        <div class="admin-filters">
            <div class="admin-search">
                <input type="text" placeholder="Buscar equipo..." data-table="equipos-table">
                <i class="fas fa-search"></i>
            </div>
            
            <select class="admin-filter-select" data-table="equipos-table" data-column="ciudad">
                <option value="">Todas las ciudades</option>
                <?php
                // Obtener ciudades únicas de los equipos
                $ciudades = [];
                foreach ($equipos as $equipo) {
                    if (!in_array($equipo['ciudad_nombre'], $ciudades)) {
                        $ciudades[] = $equipo['ciudad_nombre'];
                        echo '<option value="' . $equipo['ciudad_nombre'] . '">' . $equipo['ciudad_nombre'] . '</option>';
                    }
                }
                ?>
            </select>
            
            <a href="./equipos_form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Equipo
            </a>
        </div>
        
        <!-- Tabla de equipos -->
        <div class="admin-table-container">
            <table class="admin-table" id="equipos-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Escudo</th>
                        <th>Nombre</th>
                        <th data-column="ciudad">Ciudad</th>
                        <th>Director Técnico</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipos as $equipo): ?>
                    <tr>
                        <td><?php echo $equipo['cod_equ']; ?></td>
                        <td>
                            <img src="<?php echo $equipo['escudo_base64'] ?? '../../assets/images/team.png'; ?>" 
                                alt="<?php echo $equipo['nombre']; ?>" 
                                class="admin-table-img">
                        </td>
                        <td><?php echo $equipo['nombre']; ?></td>
                        <td data-column="ciudad"><?php echo $equipo['ciudad_nombre']; ?></td>
                        <td><?php echo $equipo['dt_nombres'] . ' ' . $equipo['dt_apellidos']; ?></td>
                        <td class="admin-actions">
                            <a href="../../pages/detalle-equipo.php?id=<?php echo $equipo['cod_equ']; ?>" class="action-btn view" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="./equipos_form.php?id=<?php echo $equipo['cod_equ']; ?>" class="action-btn edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="../../../backend/controllers/admin/equipos_controller.php" method="POST" style="display: inline;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $equipo['cod_equ']; ?>">
                                <button type="button" class="action-btn delete delete-btn" title="Eliminar" data-name="<?php echo $equipo['nombre']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($equipos)): ?>
                    <tr>
                        <td colspan="6" class="no-results">No hay equipos registrados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_equipos.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
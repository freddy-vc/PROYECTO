<?php
// Definir variables para la página
$titulo_pagina = 'Administración de Directores Técnicos';
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

// Incluir el modelo de Director
require_once '../../../backend/models/Director.php';

// Obtener todos los directores técnicos
$directorModel = new Director();
$directores = $directorModel->obtenerTodos();
?>

<!-- Incluir los estilos comunes para el panel de administración -->
<?php include_once '../../components/admin_styles.php'; ?>

<div class="container">
    <h1 class="page-title">Administración de Directores Técnicos</h1>
    
    <div class="section-intro">
        <p>Gestiona los directores técnicos de VILLAVOCUP</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_directores', 'exito_directores']);
    ?>

    <div class="admin-container">
        <!-- Navegación dentro del panel de administración -->
        <div class="admin-nav">
            <ul>
                <li><a href="./index.php">Inicio</a></li>
                <li><a href="./equipos.php">Equipos</a></li>
                <li><a href="./jugadores.php">Jugadores</a></li>
                <li><a href="./ciudades.php">Ciudades</a></li>
                <li><a href="./canchas.php">Canchas</a></li>
                <li><a href="./directores.php" class="active">Directores Técnicos</a></li>
                <li><a href="./partidos.php">Partidos</a></li>
                <li><a href="./usuarios.php">Usuarios</a></li>
            </ul>
        </div>
        
        <!-- Sección de filtros y búsqueda -->
        <div class="admin-filters">
            <div class="admin-search">
                <input type="text" placeholder="Buscar director técnico..." data-table="directores-table">
                <i class="fas fa-search"></i>
            </div>
            
            <a href="./directores_form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Director Técnico
            </a>
        </div>
        
        <!-- Tabla de directores técnicos -->
        <div class="admin-table-container">
            <table class="admin-table" id="directores-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Equipos Dirigidos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($directores as $director): 
                        // Obtener los equipos dirigidos por este director
                        $equiposDirigidos = $directorModel->obtenerEquipos($director['cod_dt']);
                    ?>
                    <tr>
                        <td><?php echo $director['cod_dt']; ?></td>
                        <td><?php echo $director['nombres']; ?></td>
                        <td><?php echo $director['apellidos']; ?></td>
                        <td>
                            <?php if (!empty($equiposDirigidos)): ?>
                                <div class="equipos-dirigidos">
                                <?php foreach ($equiposDirigidos as $equipo): ?>
                                    <span class="equipo-badge" title="<?php echo $equipo['nombre']; ?>">
                                        <img src="<?php echo $equipo['escudo_base64']; ?>" alt="<?php echo $equipo['nombre']; ?>" class="equipo-icon">
                                        <?php echo $equipo['nombre']; ?>
                                    </span>
                                <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="no-equipos">Sin equipos asignados</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-actions">
                            <a href="./directores_form.php?id=<?php echo $director['cod_dt']; ?>" class="action-btn edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="../../../backend/controllers/admin/directores_controller.php" method="POST" style="display: inline;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $director['cod_dt']; ?>">
                                <button type="button" class="action-btn delete delete-btn" title="Eliminar" data-name="<?php echo $director['nombres'] . ' ' . $director['apellidos']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($directores)): ?>
                    <tr>
                        <td colspan="5" class="no-results">No hay directores técnicos registrados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_directores.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
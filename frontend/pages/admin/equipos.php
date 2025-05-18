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
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">

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
            <li><a href="./tabla.php">Tabla de Puntuación</a></li>
            <li><a href="./usuarios.php">Usuarios</a></li>
        </ul>
    </div>
    
    <div class="admin-header">
        <h1>Administración de Equipos</h1>
        <p>Gestiona los equipos del campeonato de Futsala Villavicencio</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_equipos', 'exito_equipos']);
    ?>
    
    <!-- Sección de filtros y búsqueda -->
    <div class="admin-filters">
        <form action="" method="GET" class="admin-search" onsubmit="return buscar(this)">
            <input type="text" id="busqueda" name="busqueda" placeholder="Buscar equipo..." value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
            <i class="fas fa-search"></i>
        </form>
        
        <select name="filtro_ciudad" id="filtro_ciudad" class="admin-filter-select" onchange="this.form.submit()">
            <option value="">Todas las ciudades</option>
            <?php
            // Aquí iría el código para cargar las ciudades desde la base de datos
            ?>
        </select>
    </div>
    
    <!-- Tabla de equipos -->
    <div class="admin-table-container">
        <div class="admin-table-header">
            <h2>Listado de Equipos</h2>
            <a href="./equipos_form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Equipo
            </a>
        </div>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Escudo</th>
                    <th>Nombre</th>
                    <th>Ciudad</th>
                    <th>Director Técnico</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Aquí iría el código para cargar los equipos desde la base de datos
                // Por ahora, mostraremos datos de ejemplo
                $equipos_ejemplo = [
                    [
                        'id' => 1,
                        'escudo' => '../../assets/images/default-team.png',
                        'nombre' => 'Los Halcones',
                        'ciudad' => 'Villavicencio',
                        'director' => 'Juan Pérez'
                    ],
                    [
                        'id' => 2,
                        'escudo' => '../../assets/images/default-team.png',
                        'nombre' => 'Águilas FC',
                        'ciudad' => 'Acacías',
                        'director' => 'Ana Gómez'
                    ],
                    [
                        'id' => 3,
                        'escudo' => '../../assets/images/default-team.png',
                        'nombre' => 'Deportivo Meta',
                        'ciudad' => 'Villavicencio',
                        'director' => 'Carlos Rodríguez'
                    ]
                ];
                
                foreach ($equipos_ejemplo as $equipo) {
                    echo '<tr>';
                    echo '<td>' . $equipo['id'] . '</td>';
                    echo '<td><img src="' . $equipo['escudo'] . '" alt="Escudo" width="40" height="40"></td>';
                    echo '<td>' . $equipo['nombre'] . '</td>';
                    echo '<td>' . $equipo['ciudad'] . '</td>';
                    echo '<td>' . $equipo['director'] . '</td>';
                    echo '<td class="admin-actions">';
                    echo '<a href="./equipos_ver.php?id=' . $equipo['id'] . '" class="action-btn view"><i class="fas fa-eye"></i> Ver</a>';
                    echo '<a href="./equipos_form.php?id=' . $equipo['id'] . '" class="action-btn edit"><i class="fas fa-edit"></i> Editar</a>';
                    echo '<a href="../../backend/controllers/admin/equipos_controller.php?accion=eliminar&id=' . $equipo['id'] . '" class="action-btn delete" data-confirm="¿Estás seguro de que deseas eliminar este equipo?"><i class="fas fa-trash"></i> Eliminar</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <div class="admin-pagination">
        <a href="javascript:cambiarPagina(1)" class="pagination-btn <?php echo isset($_GET['pagina']) && $_GET['pagina'] == 1 ? 'active' : ''; ?>">1</a>
        <a href="javascript:cambiarPagina(2)" class="pagination-btn <?php echo isset($_GET['pagina']) && $_GET['pagina'] == 2 ? 'active' : ''; ?>">2</a>
        <a href="javascript:cambiarPagina(3)" class="pagination-btn <?php echo isset($_GET['pagina']) && $_GET['pagina'] == 3 ? 'active' : ''; ?>">3</a>
        <span class="pagination-btn disabled">...</span>
        <a href="javascript:cambiarPagina(10)" class="pagination-btn">10</a>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
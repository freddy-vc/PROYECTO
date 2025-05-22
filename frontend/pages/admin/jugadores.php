<?php
// Definir variables para la página
$titulo_pagina = 'Administración de Jugadores';
$pagina_actual = 'admin_jugadores';

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

// Incluir el modelo de Jugador y Equipo
require_once '../../../backend/models/Jugador.php';
require_once '../../../backend/models/Equipo.php';

// Obtener todos los jugadores
$jugadorModel = new Jugador();
$jugadores = $jugadorModel->obtenerTodosConEstadisticas();

// Obtener todos los equipos para el filtro
$equipoModel = new Equipo();
$equipos = $equipoModel->obtenerTodos();

// Obtener valores únicos para filtros
$equiposUnicos = [];
$posicionesUnicas = [];

foreach ($jugadores as $jugador) {
    if (!empty($jugador['nombre_equipo']) && !isset($equiposUnicos[$jugador['cod_equ']])) {
        $equiposUnicos[$jugador['cod_equ']] = $jugador['nombre_equipo'];
    }
    
    if (!empty($jugador['posicion']) && !in_array($jugador['posicion'], $posicionesUnicas)) {
        $posicionesUnicas[] = $jugador['posicion'];
    }
}
?>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">

<div class="container">
    <h1 class="page-title">Administración de Jugadores</h1>
    
    <div class="section-intro">
        <p>Gestiona los jugadores del campeonato de Futsala Villavicencio</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_jugadores', 'exito_jugadores']);
    ?>

    <div class="admin-container">
        <!-- Navegación dentro del panel de administración -->
        <div class="admin-nav">
            <ul>
                <li><a href="./index.php">Inicio</a></li>
                <li><a href="./equipos.php">Equipos</a></li>
                <li><a href="./jugadores.php" class="active">Jugadores</a></li>
                <li><a href="./ciudades.php">Ciudades</a></li>
                <li><a href="./canchas.php">Canchas</a></li>
                <li><a href="./directores.php">Directores Técnicos</a></li>
                <li><a href="./partidos.php">Partidos</a></li>
                <li><a href="./usuarios.php">Usuarios</a></li>
            </ul>
        </div>
        
        <!-- Sección de filtros y búsqueda -->
        <div class="admin-filters">
            <div class="admin-search">
                <input type="text" placeholder="Buscar jugador..." data-table="jugadores-table">
                <i class="fas fa-search"></i>
            </div>
            
            <select class="admin-filter-select" id="filtro-equipo" data-table="jugadores-table" data-column="equipo">
                <option value="">Todos los equipos</option>
                <?php foreach ($equiposUnicos as $id => $nombre): ?>
                    <option value="<?php echo $nombre; ?>"><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
            
            <select class="admin-filter-select" id="filtro-posicion" data-table="jugadores-table" data-column="posicion">
                <option value="">Todas las posiciones</option>
                <?php foreach ($posicionesUnicas as $posicion): ?>
                    <option value="<?php echo $posicion; ?>"><?php echo $posicion; ?></option>
                <?php endforeach; ?>
            </select>
            
            <a href="./jugadores_form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Jugador
            </a>
        </div>
        
        <!-- Tabla de jugadores -->
        <div class="admin-table-container">
            <table class="admin-table" id="jugadores-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th data-column="equipo">Equipo</th>
                        <th data-column="posicion">Posición</th>
                        <th>Dorsal</th>
                        <th>Estadísticas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jugadores as $jugador): ?>
                    <tr>
                        <td><?php echo $jugador['cod_jug']; ?></td>
                        <td>
                            <img src="<?php echo $jugador['foto_base64']; ?>" 
                                alt="<?php echo $jugador['nombres'] . ' ' . $jugador['apellidos']; ?>" 
                                class="admin-table-img">
                        </td>
                        <td><?php echo $jugador['nombres'] . ' ' . $jugador['apellidos']; ?></td>
                        <td data-column="equipo"><?php echo $jugador['nombre_equipo'] ?? 'Sin equipo'; ?></td>
                        <td data-column="posicion"><?php echo $jugador['posicion']; ?></td>
                        <td><?php echo $jugador['num_camiseta']; ?></td>
                        <td>
                            <div class="player-stats">
                                <span class="stat-item" title="Goles">
                                    <i class="fas fa-futbol"></i> <?php echo $jugador['goles']; ?>
                                </span>
                                <span class="stat-item" title="Asistencias">
                                    <i class="fas fa-hands-helping"></i> <?php echo $jugador['asistencias']; ?>
                                </span>
                                <span class="stat-item" title="Tarjetas Amarillas">
                                    <i class="fas fa-square" style="color: #ffc107;"></i> <?php echo $jugador['tarjetas_amarillas']; ?>
                                </span>
                                <span class="stat-item" title="Tarjetas Rojas">
                                    <i class="fas fa-square" style="color: #dc3545;"></i> <?php echo $jugador['tarjetas_rojas']; ?>
                                </span>
                            </div>
                        </td>
                        <td class="admin-actions">
                            <a href="../../pages/detalle-jugador.php?id=<?php echo $jugador['cod_jug']; ?>" class="action-btn view" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="./jugadores_form.php?id=<?php echo $jugador['cod_jug']; ?>" class="action-btn edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="../../../backend/controllers/admin/jugadores_controller.php" method="POST" style="display: inline;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $jugador['cod_jug']; ?>">
                                <button type="button" class="action-btn delete delete-btn" title="Eliminar" data-name="<?php echo $jugador['nombres'] . ' ' . $jugador['apellidos']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($jugadores)): ?>
                    <tr>
                        <td colspan="8" class="no-results">No hay jugadores registrados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_jugadores.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
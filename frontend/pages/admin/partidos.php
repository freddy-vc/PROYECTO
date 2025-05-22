<?php
// Definir variables para la página
$titulo_pagina = 'Administración de Partidos';
$pagina_actual = 'admin_partidos';

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
require_once '../../../backend/models/Partido.php';

// Obtener todos los partidos
$partidoModel = new Partido();
$partidos = $partidoModel->obtenerTodos();
?>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">
<link rel="stylesheet" href="../../assets/css/admin_partidos.css">

<div class="container">
    <h1 class="page-title">Administración de Partidos</h1>
    
    <div class="section-intro">
        <p>Gestiona los partidos del campeonato de Futsala Villavicencio</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_partidos', 'exito_partidos']);
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
                <li><a href="./directores.php">Directores Técnicos</a></li>
                <li><a href="./partidos.php" class="active">Partidos</a></li>
                <li><a href="./usuarios.php">Usuarios</a></li>
            </ul>
        </div>
        
        <!-- Sección de filtros y búsqueda -->
        <div class="admin-filters">
            <div class="admin-search">
                <input type="text" placeholder="Buscar partido..." data-table="partidos-table">
                <i class="fas fa-search"></i>
            </div>
            
            <select class="admin-filter-select" id="filtro-estado" data-table="partidos-table" data-column="estado">
                <option value="">Todos los estados</option>
                <option value="programado">Programados</option>
                <option value="finalizado">Finalizados</option>
            </select>
            
            <a href="./partidos_form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Partido
            </a>
        </div>
        
        <!-- Tabla de partidos -->
        <div class="admin-table-container">
            <table class="admin-table" id="partidos-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Local</th>
                        <th>Visitante</th>
                        <th>Resultado</th>
                        <th>Cancha</th>
                        <th>Fase</th>
                        <th data-column="estado">Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partidos as $partido): ?>
                    <tr>
                        <td><?php echo $partido['cod_par']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($partido['fecha'])); ?></td>
                        <td><?php echo date('H:i', strtotime($partido['hora'])); ?></td>
                        <td>
                            <div class="equipo-info">
                                <img src="<?php echo (!empty($partido['local_escudo'])) ? 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']) : '../../assets/images/team.png'; ?>" 
                                    alt="<?php echo $partido['local_nombre']; ?>" class="equipo-icon">
                                <?php echo $partido['local_nombre']; ?>
                            </div>
                        </td>
                        <td>
                            <div class="equipo-info">
                                <img src="<?php echo (!empty($partido['visitante_escudo'])) ? 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']) : '../../assets/images/team.png'; ?>" 
                                    alt="<?php echo $partido['visitante_nombre']; ?>" class="equipo-icon">
                                <?php echo $partido['visitante_nombre']; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($partido['estado'] === 'finalizado'): ?>
                                <div class="resultado">
                                    <span class="goles"><?php echo $partido['goles_local']; ?></span>
                                    <span class="vs">-</span>
                                    <span class="goles"><?php echo $partido['goles_visitante']; ?></span>
                                </div>
                            <?php else: ?>
                                <span class="vs">vs</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $partido['cancha']; ?></td>
                        <td><?php echo ucfirst($partido['fase']); ?></td>
                        <td data-column="estado">
                            <?php if ($partido['estado'] === 'programado'): ?>
                                <span class="badge badge-programado">Programado</span>
                            <?php else: ?>
                                <span class="badge badge-finalizado">Finalizado</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-actions">
                            <a href="./partidos_form.php?id=<?php echo $partido['cod_par']; ?>" class="action-btn edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="../../../backend/controllers/admin/partidos_controller.php" method="POST" style="display: inline;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $partido['cod_par']; ?>">
                                <button type="button" class="action-btn delete delete-btn" title="Eliminar" data-name="<?php echo $partido['local_nombre'] . ' vs ' . $partido['visitante_nombre']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($partidos)): ?>
                    <tr>
                        <td colspan="9" class="no-results">No hay partidos registrados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_partidos.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
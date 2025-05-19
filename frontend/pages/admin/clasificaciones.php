<?php
// Definir variables para la página
$titulo_pagina = 'Administración de Clasificaciones del Torneo';
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
require_once '../../../backend/models/Partido.php';

// Obtener datos necesarios
$clasificacionModel = new Clasificacion();
$equipoModel = new Equipo();
$partidoModel = new Partido();

// Obtener todas las clasificaciones agrupadas por fase
$cuartos = $clasificacionModel->obtenerPorFase('cuartos');
$semifinales = $clasificacionModel->obtenerPorFase('semis');
$final = $clasificacionModel->obtenerPorFase('final');

// Obtener partidos por fase
$partidosCuartos = $clasificacionModel->obtenerPartidosPorFase('cuartos');
$partidosSemis = $clasificacionModel->obtenerPartidosPorFase('semis');
$partidosFinal = $clasificacionModel->obtenerPartidosPorFase('final');

// Obtener las fases disponibles para el filtro
$fasesDisponibles = $clasificacionModel->obtenerFasesDisponibles();

// Obtener todos los equipos para el selector
$equipos = $equipoModel->obtenerTodos();

// Fase actual de visualización (por defecto, todas)
$faseSeleccionada = isset($_GET['fase']) ? $_GET['fase'] : '';
?>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">
<style>
    .bracket-container {
        margin: 20px 0;
        overflow-x: auto;
    }
    
    .tournament-bracket {
        display: flex;
        padding: 20px;
    }
    
    .bracket-round {
        display: flex;
        flex-direction: column;
        margin-right: 40px;
        min-width: 200px;
    }
    
    .bracket-round-header {
        text-align: center;
        margin-bottom: 15px;
        font-weight: bold;
        padding: 8px;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
    
    .bracket-match {
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        background-color: #fff;
        min-height: 100px;
        position: relative;
    }
    
    .bracket-match.connected::after {
        content: '';
        position: absolute;
        right: -40px;
        top: 50%;
        width: 40px;
        height: 2px;
        background-color: #ddd;
    }
    
    .bracket-team {
        display: flex;
        align-items: center;
        padding: 8px 0;
    }
    
    .bracket-team:first-child {
        border-bottom: 1px solid #eee;
    }
    
    .bracket-score {
        margin-left: auto;
        font-weight: bold;
    }
    
    .bracket-date {
        font-size: 12px;
        color: #666;
        margin-top: 8px;
        text-align: center;
    }
    
    .empty-bracket {
        background-color: #f8f9fa;
        border: 1px dashed #ddd;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100px;
        margin: 10px 0;
    }
    
    .team-classification {
        margin: 10px 0;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
    }
    
    .team-classification-info {
        flex: 1;
    }
    
    .team-classification-actions {
        display: flex;
        gap: 8px;
    }
    
    .phase-tab {
        padding: 10px 15px;
        margin-right: 5px;
        cursor: pointer;
        border: 1px solid #ddd;
        border-bottom: none;
        border-radius: 4px 4px 0 0;
        background-color: #f8f9fa;
    }
    
    .phase-tab.active {
        background-color: #fff;
        border-bottom: 2px solid #fff;
        margin-bottom: -1px;
    }
    
    .phase-content {
        border: 1px solid #ddd;
        padding: 20px;
        background-color: #fff;
    }
    
    .phase-content:not(.active) {
        display: none;
    }
</style>

<div class="container">
    <h1 class="page-title">Administración de Clasificaciones del Torneo</h1>
    
    <div class="section-intro">
        <p>Gestiona las clasificaciones de equipos para las diferentes fases del campeonato de Futsala Villavicencio</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_clasificaciones', 'exito_clasificaciones']);
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
                <li><a href="./partidos.php">Partidos</a></li>
                <li><a href="./clasificaciones.php" class="active">Clasificaciones</a></li>
                <li><a href="./usuarios.php">Usuarios</a></li>
            </ul>
        </div>
        
        <!-- Tabs para cambiar entre vista de bracket y gestión de clasificaciones -->
        <div class="admin-tabs">
            <div class="phase-tab active" data-target="bracket-view">Visualizar Torneo</div>
            <div class="phase-tab" data-target="manage-classifications">Gestionar Clasificaciones</div>
        </div>
        
        <!-- Vista de bracket del torneo -->
        <div class="phase-content active" id="bracket-view">
            <h2>Estructura del Torneo</h2>
            
            <div class="bracket-container">
                <div class="tournament-bracket">
                    <!-- Cuartos de final -->
                    <div class="bracket-round">
                        <div class="bracket-round-header">Cuartos de Final</div>
                        
                        <?php if (count($partidosCuartos) > 0): ?>
                            <?php foreach ($partidosCuartos as $partido): ?>
                            <div class="bracket-match connected">
                                <div class="bracket-team">
                                    <img src="<?php echo $partido['local_escudo_base64']; ?>" alt="<?php echo $partido['local_nombre']; ?>" class="equipo-icon">
                                    <span><?php echo $partido['local_nombre']; ?></span>
                                    <?php if ($partido['estado'] === 'finalizado'): ?>
                                        <span class="bracket-score"><?php echo $partido['goles_local']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bracket-team">
                                    <img src="<?php echo $partido['visitante_escudo_base64']; ?>" alt="<?php echo $partido['visitante_nombre']; ?>" class="equipo-icon">
                                    <span><?php echo $partido['visitante_nombre']; ?></span>
                                    <?php if ($partido['estado'] === 'finalizado'): ?>
                                        <span class="bracket-score"><?php echo $partido['goles_visitante']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bracket-date">
                                    <?php echo date('d/m/Y H:i', strtotime($partido['fecha'] . ' ' . $partido['hora'])); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-bracket">No hay partidos de cuartos de final programados</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Semifinales -->
                    <div class="bracket-round">
                        <div class="bracket-round-header">Semifinales</div>
                        
                        <?php if (count($partidosSemis) > 0): ?>
                            <?php foreach ($partidosSemis as $partido): ?>
                            <div class="bracket-match connected">
                                <div class="bracket-team">
                                    <img src="<?php echo $partido['local_escudo_base64']; ?>" alt="<?php echo $partido['local_nombre']; ?>" class="equipo-icon">
                                    <span><?php echo $partido['local_nombre']; ?></span>
                                    <?php if ($partido['estado'] === 'finalizado'): ?>
                                        <span class="bracket-score"><?php echo $partido['goles_local']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bracket-team">
                                    <img src="<?php echo $partido['visitante_escudo_base64']; ?>" alt="<?php echo $partido['visitante_nombre']; ?>" class="equipo-icon">
                                    <span><?php echo $partido['visitante_nombre']; ?></span>
                                    <?php if ($partido['estado'] === 'finalizado'): ?>
                                        <span class="bracket-score"><?php echo $partido['goles_visitante']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bracket-date">
                                    <?php echo date('d/m/Y H:i', strtotime($partido['fecha'] . ' ' . $partido['hora'])); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-bracket">No hay partidos de semifinal programados</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Final -->
                    <div class="bracket-round">
                        <div class="bracket-round-header">Final</div>
                        
                        <?php if (count($partidosFinal) > 0): ?>
                            <?php foreach ($partidosFinal as $partido): ?>
                            <div class="bracket-match">
                                <div class="bracket-team">
                                    <img src="<?php echo $partido['local_escudo_base64']; ?>" alt="<?php echo $partido['local_nombre']; ?>" class="equipo-icon">
                                    <span><?php echo $partido['local_nombre']; ?></span>
                                    <?php if ($partido['estado'] === 'finalizado'): ?>
                                        <span class="bracket-score"><?php echo $partido['goles_local']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bracket-team">
                                    <img src="<?php echo $partido['visitante_escudo_base64']; ?>" alt="<?php echo $partido['visitante_nombre']; ?>" class="equipo-icon">
                                    <span><?php echo $partido['visitante_nombre']; ?></span>
                                    <?php if ($partido['estado'] === 'finalizado'): ?>
                                        <span class="bracket-score"><?php echo $partido['goles_visitante']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bracket-date">
                                    <?php echo date('d/m/Y H:i', strtotime($partido['fecha'] . ' ' . $partido['hora'])); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-bracket">No hay partido de final programado</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gestión de clasificaciones -->
        <div class="phase-content" id="manage-classifications">
            <!-- Sección de filtros y búsqueda -->
            <div class="admin-filters">
                <div class="admin-search">
                    <input type="text" placeholder="Buscar clasificación..." data-table="clasificaciones-table">
                    <i class="fas fa-search"></i>
                </div>
                
                <select class="admin-filter-select" id="filtro-fase" data-table="clasificaciones-table" data-column="fase">
                    <option value="">Todas las fases</option>
                    <?php foreach ($fasesDisponibles as $codigo => $nombre): ?>
                        <option value="<?php echo $codigo; ?>"><?php echo $nombre; ?></option>
                    <?php endforeach; ?>
                </select>
                
                <a href="./clasificaciones_form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Clasificación
                </a>
            </div>
            
            <!-- Tabla de clasificaciones -->
            <div class="admin-table-container">
                <table class="admin-table" id="clasificaciones-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Equipo</th>
                            <th data-column="fase">Fase</th>
                            <th>Posición</th>
                            <th>Fecha Clasificación</th>
                            <th>Comentario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Combinar todas las clasificaciones
                        $clasificaciones = array_merge($cuartos, $semifinales, $final);
                        
                        foreach ($clasificaciones as $clasificacion): 
                        ?>
                        <tr>
                            <td><?php echo $clasificacion['id']; ?></td>
                            <td>
                                <div class="equipo-info">
                                    <img src="<?php echo $clasificacion['escudo_base64']; ?>" 
                                         alt="<?php echo $clasificacion['nombre_equipo']; ?>" 
                                         class="equipo-icon">
                                    <?php echo $clasificacion['nombre_equipo']; ?>
                                </div>
                            </td>
                            <td data-column="fase">
                                <?php echo $fasesDisponibles[$clasificacion['fase']] ?? $clasificacion['fase']; ?>
                            </td>
                            <td><?php echo $clasificacion['posicion']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($clasificacion['fecha_clasificacion'])); ?></td>
                            <td>
                                <span class="comentario-texto">
                                    <?php echo !empty($clasificacion['comentario']) ? $clasificacion['comentario'] : 'Sin comentarios'; ?>
                                </span>
                            </td>
                            <td class="admin-actions">
                                <a href="./clasificaciones_form.php?id=<?php echo $clasificacion['id']; ?>" class="action-btn edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="../../../backend/controllers/admin/clasificaciones_controller.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?php echo $clasificacion['id']; ?>">
                                    <button type="button" class="action-btn delete delete-btn" title="Eliminar" data-name="<?php echo $clasificacion['nombre_equipo']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($clasificaciones)): ?>
                        <tr>
                            <td colspan="7" class="no-results">No hay clasificaciones registradas</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_clasificaciones.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejo de tabs
        const tabs = document.querySelectorAll('.phase-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                
                // Desactivar todas las tabs y contenidos
                tabs.forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.phase-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Activar la tab seleccionada y su contenido
                this.classList.add('active');
                document.getElementById(targetId).classList.add('active');
            });
        });
    });
</script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
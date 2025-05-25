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

// Depuración: ver la estructura del primer partido
if (!empty($partidos)) {
    $partidoPrueba = $partidos[0];
    // Comentar o eliminar estas líneas después de depurar
    echo '<pre style="background:#f5f5f5; padding:10px; margin:10px; border:1px solid #ddd; font-size:12px;">';
    echo "Debug - Variables del primer partido (ID: {$partidoPrueba['cod_par']}):\n";
    echo "local_escudo_base64: " . (isset($partidoPrueba['local_escudo_base64']) ? "Existe" : "No existe") . "\n";
    echo "visitante_escudo_base64: " . (isset($partidoPrueba['visitante_escudo_base64']) ? "Existe" : "No existe") . "\n";
    echo "local_escudo: " . (isset($partidoPrueba['local_escudo']) ? "Existe" : "No existe") . "\n";
    echo "visitante_escudo: " . (isset($partidoPrueba['visitante_escudo']) ? "Existe" : "No existe") . "\n";
    
    // Mostrar las primeras 20 claves del array
    echo "\nClaves disponibles en el partido:\n";
    $i = 0;
    foreach (array_keys($partidoPrueba) as $key) {
        echo "$key, ";
        if (++$i >= 20) {
            echo "...";
            break;
        }
    }
    echo '</pre>';
}
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
        <p>Gestiona los partidos de VILLAVOCUP</p>
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
                <option value="programado">Programado</option>
                <option value="finalizado">Finalizado</option>
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
                                <img src="<?php echo isset($partido['local_escudo_base64']) ? $partido['local_escudo_base64'] : '/PROYECTO/frontend/assets/images/team.png'; ?>" 
                                    alt="<?php echo $partido['local_nombre']; ?>" class="equipo-icon">
                                <?php echo $partido['local_nombre']; ?>
                            </div>
                        </td>
                        <td>
                            <div class="equipo-info">
                                <img src="<?php echo isset($partido['visitante_escudo_base64']) ? $partido['visitante_escudo_base64'] : '/PROYECTO/frontend/assets/images/team.png'; ?>" 
                                    alt="<?php echo $partido['visitante_nombre']; ?>" class="equipo-icon">
                                <?php echo $partido['visitante_nombre']; ?>
                            </div>
                        </td>
                        <td>
                            <div class="resultado">
                                <span class="goles"><?php echo (isset($partido['goles_local']) ? $partido['goles_local'] : 0); ?></span>
                                <span class="vs">-</span>
                                <span class="goles"><?php echo (isset($partido['goles_visitante']) ? $partido['goles_visitante'] : 0); ?></span>
                            </div>
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
                            <a href="../../pages/detalle-partido.php?id=<?php echo $partido['cod_par']; ?>" class="action-btn view" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
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
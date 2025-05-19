<?php
// Definir variables para la página
$titulo_pagina = 'Administración de Canchas';
$pagina_actual = 'admin_canchas';

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
require_once '../../../backend/models/Cancha.php';

// Obtener todas las canchas
$canchaModel = new Cancha();
$canchas = $canchaModel->obtenerTodas();
$canchas = $canchaModel->procesarImagenes($canchas);
?>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">

<div class="container">
    <h1 class="page-title">Administración de Canchas</h1>
    
    <div class="section-intro">
        <p>Gestiona las canchas del campeonato de Futsala Villavicencio</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_canchas', 'exito_canchas']);
    ?>

    <div class="admin-container">
        <!-- Navegación dentro del panel de administración -->
        <div class="admin-nav">
            <ul>
                <li><a href="./index.php">Inicio</a></li>
                <li><a href="./equipos.php">Equipos</a></li>
                <li><a href="./jugadores.php">Jugadores</a></li>
                <li><a href="./ciudades.php">Ciudades</a></li>
                <li><a href="./canchas.php" class="active">Canchas</a></li>
                <li><a href="./directores.php">Directores Técnicos</a></li>
                <li><a href="./partidos.php">Partidos</a></li>
                <li><a href="./clasificaciones.php">Clasificaciones</a></li>
                <li><a href="./usuarios.php">Usuarios</a></li>
            </ul>
        </div>
        
        <!-- Sección de filtros y búsqueda -->
        <div class="admin-filters">
            <div class="admin-search">
                <input type="text" placeholder="Buscar cancha..." data-table="canchas-table">
                <i class="fas fa-search"></i>
            </div>
            
            <a href="./canchas_form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Cancha
            </a>
        </div>
        
        <!-- Tabla de canchas -->
        <div class="admin-table-container">
            <table class="admin-table" id="canchas-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Capacidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($canchas as $cancha): ?>
                    <tr>
                        <td><?php echo $cancha['cod_cancha']; ?></td>
                        <td>
                            <img src="<?php echo $cancha['foto_base64']; ?>" 
                                alt="<?php echo $cancha['nombre']; ?>" 
                                class="admin-table-img">
                        </td>
                        <td><?php echo $cancha['nombre']; ?></td>
                        <td><?php echo $cancha['direccion'] ?? 'No definida'; ?></td>
                        <td><?php echo $cancha['capacidad'] ? $cancha['capacidad'] . ' personas' : 'No definida'; ?></td>
                        <td class="admin-actions">
                            <a href="./canchas_form.php?id=<?php echo $cancha['cod_cancha']; ?>" class="action-btn edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="../../../backend/controllers/admin/canchas_controller.php" method="POST" style="display: inline;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $cancha['cod_cancha']; ?>">
                                <button type="button" class="action-btn delete delete-btn" title="Eliminar" data-name="<?php echo $cancha['nombre']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($canchas)): ?>
                    <tr>
                        <td colspan="6" class="no-results">No hay canchas registradas</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_canchas.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
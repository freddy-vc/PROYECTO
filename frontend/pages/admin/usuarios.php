<?php
// Definir variables para la página
$titulo_pagina = 'Administración de Usuarios';
$pagina_actual = 'admin_usuarios';

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

// Incluir el modelo de Usuario
require_once '../../../backend/models/Usuario.php';

// Obtener todos los usuarios
$usuarioModel = new Usuario();
$usuarios = $usuarioModel->obtenerTodos();
?>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">

<div class="container">
    <h1 class="page-title">Administración de Usuarios</h1>
    
    <div class="section-intro">
        <p>Gestiona los usuarios del sistema de administración del campeonato de Futsala Villavicencio</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_usuarios', 'exito_usuarios']);
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
                <li><a href="./usuarios.php" class="active">Usuarios</a></li>
            </ul>
        </div>
        
        <!-- Sección de filtros y búsqueda -->
        <div class="admin-filters">
            <div class="admin-search">
                <input type="text" placeholder="Buscar usuario..." data-table="usuarios-table">
                <i class="fas fa-search"></i>
            </div>
            
            <select class="admin-filter-select" id="filtro-rol" data-table="usuarios-table" data-column="rol">
                <option value="">Todos los roles</option>
                <option value="admin">Administrador</option>
                <option value="usuario">Usuario normal</option>
            </select>
            
            <a href="./usuarios_form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Usuario
            </a>
        </div>
        
        <!-- Tabla de usuarios -->
        <div class="admin-table-container">
            <table class="admin-table" id="usuarios-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th data-column="rol">Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['cod_user']; ?></td>
                        <td>
                            <div class="usuario-info">
                                <?php if (!empty($usuario['foto_perfil_base64'])): ?>
                                <img src="<?php echo $usuario['foto_perfil_base64']; ?>" 
                                    alt="<?php echo $usuario['username']; ?>" 
                                    class="admin-table-img">
                                <?php else: ?>
                                <img src="../../assets/images/user.png" 
                                    alt="<?php echo $usuario['username']; ?>" 
                                    class="admin-table-img">
                                <?php endif; ?>
                                <?php echo $usuario['username']; ?>
                            </div>
                        </td>
                        <td><?php echo $usuario['email']; ?></td>
                        <td data-column="rol">
                            <?php if ($usuario['rol'] === 'admin'): ?>
                                <span class="role-badge admin">Administrador</span>
                            <?php else: ?>
                                <span class="role-badge user">Usuario</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-actions">
                            <a href="./usuarios_form.php?id=<?php echo $usuario['cod_user']; ?>" class="action-btn edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <?php if ($usuario['cod_user'] != $_SESSION['usuario_id']): // No permitir eliminar el usuario actual ?>
                            <form action="../../../backend/controllers/admin/usuarios_controller.php" method="POST" style="display: inline;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $usuario['cod_user']; ?>">
                                <button type="button" class="action-btn delete delete-btn" title="Eliminar" data-name="<?php echo $usuario['username']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="5" class="no-results">No hay usuarios registrados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_usuarios.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
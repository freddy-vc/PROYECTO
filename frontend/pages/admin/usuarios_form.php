<?php
// Definir variables para la página
$titulo_pagina = 'Administrar Usuario';
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

// Verificar si se está editando un usuario existente
$esEdicion = isset($_GET['id']) && !empty($_GET['id']);
$usuario = null;
$titulo_accion = 'Agregar Usuario';
$accion = 'crear';

if ($esEdicion) {
    $usuarioId = intval($_GET['id']);
    $usuarioModel = new Usuario();
    $usuario = $usuarioModel->obtenerPorId($usuarioId);
    
    if ($usuario) {
        $titulo_accion = 'Editar Usuario';
        $accion = 'actualizar';
    } else {
        // Si no se encuentra el usuario, redireccionar a la lista
        $_SESSION['error_usuarios'] = 'El usuario solicitado no existe';
        header('Location: ./usuarios.php');
        exit;
    }
}
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">
<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="container">
    <div class="breadcrumb">
        <a href="./usuarios.php">
            <i class="fas fa-arrow-left"></i> Volver a Usuarios
        </a>
    </div>
    
    <h1 class="page-title"><?php echo $titulo_accion; ?></h1>
    
    <div class="section-intro">
        <p>Completa el formulario para <?php echo $esEdicion ? 'actualizar' : 'agregar'; ?> el usuario</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_usuarios', 'exito_usuarios']);
    ?>
    
    <div class="admin-form-container">
        <form action="../../../backend/controllers/admin/usuarios_controller.php" method="POST" class="admin-form" enctype="multipart/form-data" id="usuario-form">
            <input type="hidden" name="accion" value="<?php echo $accion; ?>">
            
            <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?php echo $usuario['cod_user']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Nombre de Usuario <span class="required">*</span></label>
                <input type="text" id="username" name="username" required maxlength="50" 
                    value="<?php echo $esEdicion ? htmlspecialchars($usuario['username']) : ''; ?>">
                <div class="form-error" id="error-username"></div>
            </div>
            
            <div class="form-group">
                <label for="email">Correo Electrónico <span class="required">*</span></label>
                <input type="email" id="email" name="email" required maxlength="100" 
                    value="<?php echo $esEdicion ? htmlspecialchars($usuario['email']) : ''; ?>">
                <div class="form-error" id="error-email"></div>
            </div>
            
            <div class="form-group">
                <label for="password"><?php echo $esEdicion ? 'Nueva Contraseña (dejar en blanco para no cambiar)' : 'Contraseña *'; ?></label>
                <input type="password" id="password" name="password" <?php echo !$esEdicion ? 'required' : ''; ?> maxlength="50">
                <div class="form-error" id="error-password"></div>
            </div>
            
            <?php if ($esEdicion): ?>
            <div class="form-group">
                <label for="confirm_password">Confirmar Nueva Contraseña</label>
                <input type="password" id="confirm_password" name="confirm_password" maxlength="50">
                <div class="form-error" id="error-confirm-password"></div>
            </div>
            <?php else: ?>
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña <span class="required">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" required maxlength="50">
                <div class="form-error" id="error-confirm-password"></div>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="rol">Rol <span class="required">*</span></label>
                <select id="rol" name="rol" required>
                    <option value="usuario" <?php echo ($esEdicion && $usuario['rol'] === 'usuario') ? 'selected' : ''; ?>>Usuario normal</option>
                    <option value="admin" <?php echo ($esEdicion && $usuario['rol'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                </select>
                <div class="form-error" id="error-rol"></div>
            </div>
            
            <div class="form-group">
                <label for="foto_perfil">Foto de Perfil</label>
                <div class="file-input-container">
                    <input type="file" id="foto_perfil" name="foto_perfil" accept=".jpg, .jpeg, .png, .gif" class="file-input">
                    <label for="foto_perfil" class="file-input-label">
                        <span class="file-input-button">Seleccionar archivo</span>
                        <span class="file-input-text" id="file-name">Ningún archivo seleccionado</span>
                    </label>
                </div>
                <div class="form-help">Formato: JPG, PNG o GIF. Tamaño máximo: 2MB</div>
                <div class="form-error" id="error-foto-perfil"></div>
                
                <?php if ($esEdicion && !empty($usuario['foto_perfil_base64'])): ?>
                <div class="current-image">
                    <p>Imagen actual:</p>
                    <img src="<?php echo $usuario['foto_perfil_base64']; ?>" alt="<?php echo htmlspecialchars($usuario['username']); ?>" class="thumbnail">
                    <form action="../../../backend/controllers/admin/eliminar_foto_usuario.php" method="POST" style="display: inline-block; margin-top: 10px;">
                        <input type="hidden" name="usuario_id" value="<?php echo $usuario['cod_user']; ?>">
                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('¿Está seguro de eliminar la foto de perfil?')">
                            <i class="fas fa-trash"></i> Eliminar foto
                        </button>
                    </form>
                </div>
                <?php endif; ?>
                
                <div id="image-preview" class="image-preview" style="display: none;">
                    <p>Vista previa:</p>
                    <img id="preview-img" src="" alt="Vista previa">
                </div>
            </div>
            
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='./usuarios.php'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_usuarios.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
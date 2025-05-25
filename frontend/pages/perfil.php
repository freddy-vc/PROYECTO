<?php
// Definir variables para la página
$titulo_pagina = 'Mi Perfil';
$pagina_actual = 'perfil';

// Incluir el header
include_once '../components/header.php';

// Incluir el componente de notificaciones
include_once '../components/notificaciones.php';

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    // Redireccionar a login si no hay sesión
    header('Location: login.php');
    exit;
}

// Obtener los datos del usuario desde la sesión
$cod_user = $_SESSION['usuario_id'];
$username = $_SESSION['usuario_nombre'];
$email = $_SESSION['usuario_email'];
$rol = $_SESSION['usuario_rol'];
$foto = $_SESSION['usuario_foto'] ? $_SESSION['usuario_foto'] : '../assets/images/user.png';
?>

<div class="profile-container">
    <div class="profile-card">
        <h1>Mi Perfil</h1>
        
        <?php 
        // Mostrar notificaciones de error y éxito
        mostrarNotificaciones(['error_perfil', 'exito_perfil', 'exito_login']);
        ?>
        
        <div class="profile-content">
            <div class="profile-image">
                <img src="<?php echo $foto; ?>" alt="Foto de perfil">
                <h2><?php echo $username; ?></h2>
                <p class="profile-role"><?php echo $rol === 'admin' ? 'Administrador' : 'Usuario'; ?></p>
                
                <!-- Formulario para cambiar foto de perfil -->
                <form action="../../backend/controllers/actualizar_foto.php" method="POST" enctype="multipart/form-data" class="foto-form">
                    <div class="form-group">
                        <label for="foto_perfil">Cambiar foto de perfil</label>
                        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png,image/gif" required>
                        <small>Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.</small>
                    </div>
                    <button type="submit" class="btn btn-small">Subir Foto</button>
                </form>
                
                <!-- Formulario para eliminar foto de perfil -->
                <form action="../../backend/controllers/eliminar_foto.php" method="POST" class="foto-form eliminar-foto-form">
                    <button type="submit" class="btn btn-small btn-danger">Eliminar Foto</button>
                </form>
            </div>
            
            <div class="profile-details">
                <div class="detail-group">
                    <label>Nombre de Usuario:</label>
                    <p><?php echo $username; ?></p>
                </div>
                
                <div class="detail-group">
                    <label>Correo Electrónico:</label>
                    <p><?php echo $email; ?></p>
                </div>
                
                <div class="detail-group">
                    <label>Rol:</label>
                    <p><?php echo $rol === 'admin' ? 'Administrador' : 'Usuario'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="profile-actions">
            <a href="../../backend/controllers/logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
</div>

<style>
    /* Estilos específicos para la página de perfil */
    .profile-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .profile-card {
        background: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        padding: 30px;
    }
    
    .profile-card h1 {
        text-align: center;
        margin-bottom: 30px;
        color: var(--primary-color);
    }
    
    .profile-content {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .profile-image {
        flex: 1;
        min-width: 200px;
        text-align: center;
    }
    
    .profile-image img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 15px;
        border: 5px solid var(--light-color);
    }
    
    .profile-role {
        color: #777;
        margin-top: 5px;
        margin-bottom: 20px;
    }
    
    .foto-form {
        margin-top: 15px;
        padding: 15px;
        background: var(--light-color);
        border-radius: 8px;
    }
    
    .foto-form .form-group {
        margin-bottom: 10px;
    }
    
    .foto-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: var(--primary-color);
    }
    
    .foto-form small {
        display: block;
        margin-top: 5px;
        color: #777;
    }
    
    .profile-details {
        flex: 2;
        min-width: 300px;
    }
    
    .detail-group {
        margin-bottom: 15px;
    }
    
    .detail-group label {
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
        color: var(--primary-color);
    }
    
    .detail-group p {
        padding: 10px;
        background: var(--light-color);
        border-radius: 5px;
    }
    
    .profile-actions {
        display: flex;
        justify-content: center;
        gap: 20px;
    }
    
    .btn-danger {
        background-color: var(--danger-color);
    }
    
    .eliminar-foto-form {
        margin-top: 10px;
    }
    
    @media (max-width: 768px) {
        .profile-content {
            flex-direction: column;
        }
    }
</style>

<?php
// Incluir el footer
include_once '../components/footer.php';
?> 
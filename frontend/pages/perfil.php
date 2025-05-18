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
$id_usuario = $_SESSION['usuario_id'];
$nombre = $_SESSION['usuario_nombre'];
$email = $_SESSION['usuario_email'];
$rol = $_SESSION['usuario_rol'];
$foto = $_SESSION['usuario_foto'] ? $_SESSION['usuario_foto'] : $base_path . '/frontend/assets/images/default-profile.png';
?>

<div class="profile-container">
    <div class="profile-card">
        <h1>Mi Perfil</h1>
        
        <?php 
        // Mostrar notificaciones de error y éxito
        mostrarNotificaciones(['error_perfil', 'exito_perfil']);
        ?>
        
        <div class="profile-content">
            <div class="profile-image">
                <img src="<?php echo $foto; ?>" alt="Foto de perfil">
                <h2><?php echo $nombre; ?></h2>
                <p class="profile-role"><?php echo $rol === 'admin' ? 'Administrador' : 'Usuario'; ?></p>
            </div>
            
            <div class="profile-details">
                <div class="detail-group">
                    <label>Correo Electrónico:</label>
                    <p><?php echo $email; ?></p>
                </div>
                
                <div class="detail-group">
                    <label>Nombre:</label>
                    <p><?php echo $nombre; ?></p>
                </div>
                
                <div class="detail-group">
                    <label>Rol:</label>
                    <p><?php echo $rol === 'admin' ? 'Administrador' : 'Usuario'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="profile-actions">
            <a href="<?php echo $base_path; ?>/backend/controllers/logout.php" class="btn btn-danger">Cerrar Sesión</a>
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
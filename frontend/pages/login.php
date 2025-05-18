<?php
// Definir variables para la página
$titulo_pagina = 'Iniciar Sesión';
$pagina_actual = 'login';

// Incluir el header
include_once '../components/header.php';

// Incluir el componente de notificaciones
include_once '../components/notificaciones.php';

// Verificar si ya hay una sesión activa
if (isset($_SESSION['usuario_id'])) {
    // Redireccionar a la página de inicio
    header('Location: ' . $base_path . '/index.php');
    exit;
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Iniciar Sesión</h1>
        
        <?php 
        // Mostrar notificaciones de error y éxito
        mostrarNotificaciones(['error_login', 'exito_login']);
        ?>
        
        <form action="<?php echo $base_path; ?>/backend/controllers/login.php" method="POST" onsubmit="return validarFormulario(this)">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </div>
        </form>
        
        <div class="auth-links">
            <p>¿No tienes una cuenta? <a href="registro.php">Regístrate</a></p>
        </div>
    </div>
</div>

<?php
// Incluir el footer
include_once '../components/footer.php';
?> 
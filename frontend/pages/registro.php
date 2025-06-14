<?php
// Definir variables para la página
$titulo_pagina = 'Registro';
$pagina_actual = 'registro';

// Incluir el header
include_once '../components/header.php';

// Incluir el componente de notificaciones
include_once '../components/notificaciones.php';

// Verificar si ya hay una sesión activa
if (isset($_SESSION['usuario_id'])) {
    // Redireccionar a la página de inicio
    header('Location: ../../index.php');
    exit;
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Crear Cuenta</h1>
        
        <?php 
        // Mostrar notificaciones de error y éxito
        mostrarNotificaciones(['error_registro', 'exito_registro']);
        ?>
        
        <form action="../../backend/controllers/register.php" method="POST" onsubmit="return validarFormulario(this)">
            <div class="form-group">
                <label for="username">Nombre de Usuario</label>
                <input type="text" id="username" name="username" required>
                <small>Este será tu identificador para iniciar sesión</small>
            </div>
            
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required minlength="6">
                <small>La contraseña debe tener al menos 6 caracteres</small>
            </div>
            
            <div class="form-group">
                <label for="confirmar_password">Confirmar Contraseña</label>
                <input type="password" id="confirmar_password" name="confirmar_password" required minlength="6">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Registrarse</button>
            </div>
        </form>
        
        <div class="auth-links">
            <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar Sesión</a></p>
        </div>
    </div>
</div>

<?php
// Incluir el footer
include_once '../components/footer.php';
?> 
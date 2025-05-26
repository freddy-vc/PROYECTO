/**
 * JavaScript específico para la página de login
 */
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    
    // Enfocar el campo de usuario al cargar la página
    if (usernameInput) {
        usernameInput.focus();
    }
    
    // Validación específica para el formulario de login
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            let formValid = true;
            
            // Validar que los campos no estén vacíos
            if (!usernameInput || !usernameInput.value.trim()) {
                mostrarError(usernameInput, 'El nombre de usuario es obligatorio');
                formValid = false;
            } else {
                limpiarError(usernameInput);
            }
            
            if (!passwordInput || !passwordInput.value) {
                mostrarError(passwordInput, 'La contraseña es obligatoria');
                formValid = false;
            } else {
                limpiarError(passwordInput);
            }
            
            if (!formValid) {
                event.preventDefault();
                return false;
            }
            
            return true;
        });
    }
    
    // Limpiar mensajes de error al escribir
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            limpiarError(this);
        });
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            limpiarError(this);
        });
    }
    
    // Funciones para mostrar y limpiar errores
    function mostrarError(campo, mensaje) {
        if (!campo) return;
        
        limpiarError(campo); // Limpiamos primero para evitar duplicados
        
        const contenedorCampo = campo.parentElement;
        const mensajeError = document.createElement('div');
        
        mensajeError.textContent = mensaje;
        mensajeError.className = 'mensaje-error';
        mensajeError.style.color = 'red';
        mensajeError.style.fontSize = '0.8rem';
        mensajeError.style.marginTop = '5px';
        
        contenedorCampo.appendChild(mensajeError);
        campo.classList.add('campo-error');
        campo.style.borderColor = 'red';
    }
    
    function limpiarError(campo) {
        if (!campo) return;
        
        const contenedorCampo = campo.parentElement;
        const mensajeError = contenedorCampo.querySelector('.mensaje-error');
        
        if (mensajeError) {
            contenedorCampo.removeChild(mensajeError);
        }
        
        campo.classList.remove('campo-error');
        campo.style.borderColor = '';
    }
}); 
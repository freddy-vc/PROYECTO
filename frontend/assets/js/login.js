/**
 * JavaScript específico para la página de login
 */
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    // Enfocar el campo de email al cargar la página
    emailInput.focus();
    
    // Validación específica para el formulario de login
    loginForm.addEventListener('submit', function(event) {
        // Validar email
        if (!validarEmail(emailInput.value)) {
            mostrarError(emailInput, 'Por favor, introduce un email válido');
            event.preventDefault();
            return false;
        } else {
            limpiarError(emailInput);
        }
        
        // Validar contraseña
        if (passwordInput.value.length < 1) {
            mostrarError(passwordInput, 'Por favor, introduce tu contraseña');
            event.preventDefault();
            return false;
        } else {
            limpiarError(passwordInput);
        }
        
        return true;
    });
    
    // Limpiar mensajes de error al escribir
    emailInput.addEventListener('input', function() {
        limpiarError(this);
    });
    
    passwordInput.addEventListener('input', function() {
        limpiarError(this);
    });
    
    // Función para validar email
    function validarEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    // Funciones para mostrar y limpiar errores
    function mostrarError(campo, mensaje) {
        limpiarError(campo); // Limpiamos primero para evitar duplicados
        
        const contenedorCampo = campo.parentElement;
        const mensajeError = document.createElement('div');
        
        mensajeError.textContent = mensaje;
        mensajeError.className = 'mensaje-error';
        mensajeError.style.fontSize = '0.8rem';
        mensajeError.style.marginTop = '5px';
        
        contenedorCampo.appendChild(mensajeError);
        campo.classList.add('campo-error');
        campo.style.borderColor = 'red';
    }
    
    function limpiarError(campo) {
        const contenedorCampo = campo.parentElement;
        const mensajeError = contenedorCampo.querySelector('.mensaje-error');
        
        if (mensajeError) {
            contenedorCampo.removeChild(mensajeError);
        }
        
        campo.classList.remove('campo-error');
        campo.style.borderColor = '';
    }
}); 
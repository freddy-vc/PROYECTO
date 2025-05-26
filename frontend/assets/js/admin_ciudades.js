/**
 * JavaScript específico para la gestión de ciudades en el panel de administración
 */
document.addEventListener('DOMContentLoaded', function() {
    // Configuración del formulario de ciudad
    setupCiudadForm();
});

/**
 * Configura el formulario de ciudad con validaciones específicas
 */
function setupCiudadForm() {
    const ciudadForm = document.getElementById('ciudad-form');
    if (!ciudadForm) return;
    
    // Obtener referencia al campo de nombre
    const nombreInput = document.getElementById('nombre');
    
    // Validación en tiempo real para el campo de nombre
    if (nombreInput) {
        nombreInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                showError(this, 'El nombre de la ciudad es obligatorio');
            } else if (this.value.trim().length < 3) {
                showError(this, 'El nombre debe tener al menos 3 caracteres');
            } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/.test(this.value.trim())) {
                showError(this, 'El nombre solo debe contener letras, espacios y guiones');
            } else {
                clearError(this);
            }
        });
    }
    
    // Validar el formulario completo antes de enviar
    ciudadForm.addEventListener('submit', function(e) {
        if (!validarFormulario()) {
            e.preventDefault();
        }
});

/**
     * Valida todos los campos del formulario
 */
    function validarFormulario() {
        let isValid = true;
        
        // Validar nombre
        if (!nombreInput.value.trim()) {
            showError(nombreInput, 'El nombre de la ciudad es obligatorio');
            isValid = false;
        } else if (nombreInput.value.trim().length < 3) {
            showError(nombreInput, 'El nombre debe tener al menos 3 caracteres');
            isValid = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/.test(nombreInput.value.trim())) {
            showError(nombreInput, 'El nombre solo debe contener letras, espacios y guiones');
            isValid = false;
        } else {
            clearError(nombreInput);
        }
        
        return isValid;
        }
}

/**
 * Muestra un mensaje de error para un campo
 */
function showError(input, message) {
    const errorElement = document.getElementById('error-' + input.id);
    if (errorElement) {
        errorElement.textContent = message;
        input.classList.add('input-error');
    }
}

/**
 * Limpia un mensaje de error para un campo
 */
function clearError(input) {
    const errorElement = document.getElementById('error-' + input.id);
    if (errorElement) {
        errorElement.textContent = '';
        input.classList.remove('input-error');
    }
} 
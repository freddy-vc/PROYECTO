/**
 * JavaScript para la administración de directores técnicos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el elemento del formulario si existe
    const form = document.getElementById('director-form');
    if (form) {
        setupDirectorForm();
    }
    
    // No inicializar los botones de eliminación aquí, se hace en admin.js
});

/**
 * Configura el formulario de director técnico con validaciones específicas
 */
function setupDirectorForm() {
    const directorForm = document.getElementById('director-form');
    if (!directorForm) return;
    
    // Obtener referencias a los campos
    const nombresInput = document.getElementById('nombres');
    const apellidosInput = document.getElementById('apellidos');
    
    // Validación en tiempo real para el campo de nombres
    if (nombresInput) {
        nombresInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                showError(this, 'El nombre del director técnico es obligatorio');
            } else if (this.value.trim().length < 2) {
                showError(this, 'El nombre debe tener al menos 2 caracteres');
            } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(this.value.trim())) {
                showError(this, 'El nombre solo debe contener letras y espacios');
            } else {
                clearError(this);
            }
        });
    }
    
    // Validación en tiempo real para el campo de apellidos
    if (apellidosInput) {
        apellidosInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                showError(this, 'Los apellidos del director técnico son obligatorios');
            } else if (this.value.trim().length < 2) {
                showError(this, 'Los apellidos deben tener al menos 2 caracteres');
            } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(this.value.trim())) {
                showError(this, 'Los apellidos solo deben contener letras y espacios');
            } else {
                clearError(this);
            }
        });
    }
    
    // Validar el formulario completo antes de enviar
    directorForm.addEventListener('submit', function(e) {
        if (!validarFormulario()) {
            e.preventDefault();
        }
    });
    
    /**
     * Valida todos los campos del formulario
     */
    function validarFormulario() {
        let isValid = true;
        
        // Validar nombres
        if (!nombresInput.value.trim()) {
            showError(nombresInput, 'El nombre del director técnico es obligatorio');
            isValid = false;
        } else if (nombresInput.value.trim().length < 2) {
            showError(nombresInput, 'El nombre debe tener al menos 2 caracteres');
            isValid = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(nombresInput.value.trim())) {
            showError(nombresInput, 'El nombre solo debe contener letras y espacios');
            isValid = false;
        } else {
            clearError(nombresInput);
        }
        
        // Validar apellidos
        if (!apellidosInput.value.trim()) {
            showError(apellidosInput, 'Los apellidos del director técnico son obligatorios');
            isValid = false;
        } else if (apellidosInput.value.trim().length < 2) {
            showError(apellidosInput, 'Los apellidos deben tener al menos 2 caracteres');
            isValid = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(apellidosInput.value.trim())) {
            showError(apellidosInput, 'Los apellidos solo deben contener letras y espacios');
            isValid = false;
        } else {
            clearError(apellidosInput);
        }
        
        return isValid;
        }
}

/**
 * Mostrar un mensaje de error para un campo
 */
function showError(input, message) {
    const errorElement = document.getElementById('error-' + input.id);
    if (errorElement) {
        errorElement.textContent = message;
        input.classList.add('input-error');
    }
}

/**
 * Limpiar un mensaje de error para un campo
 */
function clearError(input) {
    const errorElement = document.getElementById('error-' + input.id);
    if (errorElement) {
        errorElement.textContent = '';
        input.classList.remove('input-error');
    }
} 
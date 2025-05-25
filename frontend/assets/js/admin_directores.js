/**
 * JavaScript para la administración de directores técnicos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el elemento del formulario si existe
    const form = document.getElementById('director-form');
    if (form) {
        initializeFormValidation();
    }
    
    // No inicializar los botones de eliminación aquí, se hace en admin.js
});

/**
 * Inicializar la validación del formulario
 */
function initializeFormValidation() {
    const form = document.getElementById('director-form');
    
    // Validar el formulario antes de enviarlo
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar nombres
        const nombres = document.getElementById('nombres');
        if (!nombres.value.trim()) {
            showError(nombres, 'Los nombres son obligatorios');
            isValid = false;
        } else {
            clearError(nombres);
        }
        
        // Validar apellidos
        const apellidos = document.getElementById('apellidos');
        if (!apellidos.value.trim()) {
            showError(apellidos, 'Los apellidos son obligatorios');
            isValid = false;
        } else {
            clearError(apellidos);
        }
        
        // Prevenir el envío del formulario si hay errores
        if (!isValid) {
            e.preventDefault();
        }
    });
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
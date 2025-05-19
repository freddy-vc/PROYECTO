/**
 * JavaScript para la administración de ciudades
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el elemento del formulario si existe
    const form = document.getElementById('ciudad-form');
    if (form) {
        initializeFormValidation();
    }
    
    // Inicializar los botones de eliminación si existen
    initializeDeleteButtons();
});

/**
 * Inicializar la validación del formulario
 */
function initializeFormValidation() {
    const form = document.getElementById('ciudad-form');
    
    // Validar el formulario antes de enviarlo
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar nombre
        const nombre = document.getElementById('nombre');
        if (!nombre.value.trim()) {
            showError(nombre, 'El nombre de la ciudad es obligatorio');
            isValid = false;
        } else {
            clearError(nombre);
        }
        
        // Prevenir el envío del formulario si hay errores
        if (!isValid) {
            e.preventDefault();
        }
    });
}

/**
 * Inicializar los botones de eliminación
 */
function initializeDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cityName = this.getAttribute('data-name');
            
            if (confirm(`¿Estás seguro de que deseas eliminar la ciudad "${cityName}"? Esta acción no se puede deshacer y podría afectar a las canchas asociadas.`)) {
                this.closest('form').submit();
            }
        });
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
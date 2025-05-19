/**
 * JavaScript para la administración de clasificaciones
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el elemento del formulario si existe
    const form = document.getElementById('clasificacion-form');
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
    const form = document.getElementById('clasificacion-form');
    
    // Validar el formulario antes de enviarlo
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar equipo
        const equipoId = document.getElementById('equipo_id');
        if (!equipoId.value) {
            showError(equipoId, 'Selecciona un equipo');
            isValid = false;
        } else {
            clearError(equipoId);
        }
        
        // Validar fase
        const fase = document.getElementById('fase');
        if (!fase.value) {
            showError(fase, 'Selecciona una fase');
            isValid = false;
        } else {
            clearError(fase);
        }
        
        // Validar posición
        const posicion = document.getElementById('posicion');
        if (!posicion.value || posicion.value < 1) {
            showError(posicion, 'Ingresa una posición válida');
            isValid = false;
        } else {
            clearError(posicion);
        }
        
        // Validar fecha de clasificación (opcional pero debe ser válida si se proporciona)
        const fechaClasificacion = document.getElementById('fecha_clasificacion');
        if (fechaClasificacion.value && !isValidDate(fechaClasificacion.value)) {
            showError(fechaClasificacion, 'La fecha no es válida');
            isValid = false;
        } else {
            clearError(fechaClasificacion);
        }
        
        // Prevenir el envío del formulario si hay errores
        if (!isValid) {
            e.preventDefault();
        }
    });
}

/**
 * Validar si una fecha es válida
 */
function isValidDate(dateString) {
    const date = new Date(dateString);
    return !isNaN(date.getTime());
}

/**
 * Inicializar los botones de eliminación
 */
function initializeDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const nombreEquipo = this.getAttribute('data-name');
            
            if (confirm(`¿Estás seguro de que deseas eliminar la clasificación del equipo "${nombreEquipo}"? Esta acción no se puede deshacer.`)) {
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
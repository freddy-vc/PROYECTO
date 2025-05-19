/**
 * JavaScript para la administración de partidos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el elemento del formulario si existe
    const form = document.getElementById('partido-form');
    if (form) {
        initializeFormValidation();
    }
    
    // Inicializar los botones de eliminación si existen
    initializeDeleteButtons();
    
    // Inicializar las pestañas en la sección de estadísticas si existen
    initializeTabs();
});

/**
 * Inicializar la validación del formulario
 */
function initializeFormValidation() {
    const form = document.getElementById('partido-form');
    
    // Validar el formulario antes de enviarlo
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar fecha
        const fecha = document.getElementById('fecha');
        if (!fecha.value) {
            showError(fecha, 'La fecha es obligatoria');
            isValid = false;
        } else {
            clearError(fecha);
        }
        
        // Validar hora
        const hora = document.getElementById('hora');
        if (!hora.value) {
            showError(hora, 'La hora es obligatoria');
            isValid = false;
        } else {
            clearError(hora);
        }
        
        // Validar cancha
        const cancha = document.getElementById('cancha_id');
        if (!cancha.value) {
            showError(cancha, 'Selecciona una cancha');
            isValid = false;
        } else {
            clearError(cancha);
        }
        
        // Validar equipos si están presentes en el formulario
        const equipoLocal = document.getElementById('equipo_local');
        const equipoVisitante = document.getElementById('equipo_visitante');
        
        if (equipoLocal && equipoVisitante) {
            // Validar equipo local
            if (!equipoLocal.value) {
                showError(equipoLocal, 'Selecciona un equipo local');
                isValid = false;
            } else {
                clearError(equipoLocal);
            }
            
            // Validar equipo visitante
            if (!equipoVisitante.value) {
                showError(equipoVisitante, 'Selecciona un equipo visitante');
                isValid = false;
            } else {
                clearError(equipoVisitante);
            }
            
            // Validar que los equipos sean diferentes
            if (equipoLocal.value && equipoVisitante.value && equipoLocal.value === equipoVisitante.value) {
                showError(equipoVisitante, 'El equipo local y visitante no pueden ser el mismo');
                isValid = false;
            }
        }
        
        // Validar estado si está presente
        const estado = document.getElementById('estado');
        if (estado && !estado.value) {
            showError(estado, 'Selecciona un estado para el partido');
            isValid = false;
        } else if (estado) {
            clearError(estado);
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
            const partidoName = this.getAttribute('data-name');
            
            if (confirm(`¿Estás seguro de que deseas eliminar el partido "${partidoName}"? Esta acción no se puede deshacer.`)) {
                this.closest('form').submit();
            }
        });
    });
}

/**
 * Inicializar las pestañas en la sección de estadísticas
 */
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    if (tabButtons.length > 0) {
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remover la clase active de todos los botones y contenido
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Agregar la clase active al botón actual
                this.classList.add('active');
                
                // Mostrar el contenido correspondiente
                const tabId = this.getAttribute('data-tab');
                document.getElementById('tab-' + tabId).classList.add('active');
            });
        });
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
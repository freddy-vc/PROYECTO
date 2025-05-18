/**
 * JavaScript para el panel de administración
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicialización para los íconos de Font Awesome
    if (typeof FontAwesome !== 'undefined') {
        FontAwesome.dom.i2svg();
    }
    
    // Agregar listeners para los botones de eliminación si existen
    setupDeleteButtons();
    
    // Configurar validación de formularios si existen
    setupFormValidation();
});

/**
 * Configurar los botones de eliminación para mostrar confirmación
 */
function setupDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.action-btn.delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const confirmMessage = this.getAttribute('data-confirm') || '¿Estás seguro de que deseas eliminar este elemento?';
            
            if (confirm(confirmMessage)) {
                // Si se confirma, seguir con la acción de eliminación
                window.location.href = this.getAttribute('href');
            }
        });
    });
}

/**
 * Configurar validación básica de formularios
 */
function setupFormValidation() {
    const adminForms = document.querySelectorAll('.admin-form');
    
    adminForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validar campos requeridos
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    // Crear mensaje de error si no existe
                    let errorMessage = field.nextElementSibling;
                    if (!errorMessage || !errorMessage.classList.contains('error-message')) {
                        errorMessage = document.createElement('div');
                        errorMessage.className = 'error-message';
                        errorMessage.style.fontSize = '0.8rem';
                        errorMessage.style.color = 'var(--danger-color)';
                        errorMessage.style.marginTop = '5px';
                        field.parentNode.insertBefore(errorMessage, field.nextSibling);
                    }
                    
                    errorMessage.textContent = 'Este campo es requerido';
                } else {
                    field.classList.remove('is-invalid');
                    
                    // Eliminar mensaje de error si existe
                    const errorMessage = field.nextElementSibling;
                    if (errorMessage && errorMessage.classList.contains('error-message')) {
                        errorMessage.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Función para manejar la paginación
 */
function cambiarPagina(pagina) {
    // Obtener la URL actual
    const url = new URL(window.location.href);
    
    // Establecer el parámetro de página
    url.searchParams.set('pagina', pagina);
    
    // Redirigir a la nueva URL
    window.location.href = url.toString();
}

/**
 * Función para manejar la búsqueda
 */
function buscar(formElement) {
    const busqueda = document.getElementById('busqueda').value.trim();
    
    if (busqueda !== '') {
        formElement.submit();
    }
    
    return false;
}

/**
 * Función para previsualización de imágenes antes de subirlas
 */
function previsualizarImagen(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
} 
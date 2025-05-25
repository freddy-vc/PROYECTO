/**
 * Función para ocultar notificaciones después de 5 segundos
 */
function ocultarNotificaciones() {
    document.querySelectorAll('.mensaje-error, .mensaje-exito').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(function() { el.style.display = 'none'; }, 500);
        }, 5000);
    });
}

/**
 * JavaScript para el panel de administración
 */
document.addEventListener('DOMContentLoaded', function() {
    // Ocultar notificaciones existentes
    ocultarNotificaciones();
    
    // Configurar los botones de eliminación
    setupDeleteButtons();
    
    // Configurar validación de formularios
    setupFormValidation();
    
    // Configurar filtros y búsqueda
    setupFilters();
});

/**
 * Configura los botones de eliminación para mostrar un diálogo de confirmación
 */
function setupDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const itemName = this.getAttribute('data-name');
            const confirmMessage = `¿Estás seguro de que deseas eliminar "${itemName}"? Esta acción no se puede deshacer.`;
            
            if (confirm(confirmMessage)) {
                // Si el usuario confirma, enviar el formulario o realizar la acción
                const form = this.closest('form');
                if (form) {
                    form.submit();
                } else {
                    window.location.href = this.getAttribute('href');
                }
            }
        });
    });
}

/**
 * Configura la validación básica de formularios
 */
function setupFormValidation() {
    const forms = document.querySelectorAll('.admin-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Crear mensaje de error si no existe
                    let errorMessage = field.nextElementSibling;
                    if (!errorMessage || !errorMessage.classList.contains('error-message')) {
                        errorMessage = document.createElement('div');
                        errorMessage.classList.add('error-message');
                        errorMessage.textContent = 'Este campo es obligatorio';
                        field.parentNode.insertBefore(errorMessage, field.nextSibling);
                    }
                } else {
                    field.classList.remove('error');
                    
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
 * Configura los filtros y la búsqueda en las tablas
 */
function setupFilters() {
    const searchInputs = document.querySelectorAll('.admin-search input');
    const filterSelects = document.querySelectorAll('.admin-filter-select');
    
    // Configurar búsqueda
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableId = this.getAttribute('data-table');
            const table = document.getElementById(tableId);
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    let found = false;
                    const cells = row.querySelectorAll('td');
                    
                    cells.forEach(cell => {
                        if (cell.textContent.toLowerCase().includes(searchTerm)) {
                            found = true;
                        }
                    });
                    
                    row.style.display = found ? '' : 'none';
                });
            }
        });
    });
    
    // Configurar filtros
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            
            const filterColumn = this.getAttribute('data-column');
            const tableId = this.getAttribute('data-table');
            
            const table = document.getElementById(tableId);
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    // Si no hay valor de filtro, mostrar todas las filas
                    if (filterValue === '') {
                        row.style.display = '';
                        return;
                    }
                    
                    const cell = row.querySelector(`td[data-column="${filterColumn}"]`);
                    if (!cell) {
                        row.style.display = 'none';
                        return;
                    }
                    
                    // Contenido general de la celda
                    const cellContent = cell.textContent.toLowerCase().trim();
                    
                    // Si el contenido general incluye el valor de filtro, mostrar la fila
                    if (cellContent.includes(filterValue)) {
                        row.style.display = '';
                        return;
                    }
                    
                    // Ocultar la fila si no hay coincidencia
                    row.style.display = 'none';
                });
            }
        });
    });
} 
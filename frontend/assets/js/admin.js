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
    
    // Inicializar el modal de confirmación
    initDeleteModal();
    
    // Garantizar que el modal esté disponible
    setTimeout(function() {
        if (!document.getElementById('delete-modal-container')) {
            forceInitComponents();
        }
    }, 300);
});

/**
 * Inicializa forzadamente los componentes si no se cargaron correctamente
 */
function forceInitComponents() {
    // Volver a ocultar notificaciones existentes
    ocultarNotificaciones();
    
    // Volver a configurar los botones de eliminación
    setupDeleteButtons();
    
    // Volver a inicializar el modal de confirmación
    initDeleteModal();
}

/**
 * Inicializa el modal de confirmación para eliminar registros
 */
function initDeleteModal() {
    // Verificar si ya existe un modal en la página
    if (document.getElementById('delete-modal-container')) {
        return;
    }

    // Crear el contenedor del modal
    const modalContainer = document.createElement('div');
    modalContainer.id = 'delete-modal-container';
    modalContainer.className = 'delete-modal-container';

    // Crear el contenido del modal
    modalContainer.innerHTML = `
        <div id="delete-modal" class="delete-modal">
            <div class="delete-modal-header">
                <i id="delete-modal-icon" class="fas fa-exclamation-triangle"></i>
                <h3>Confirmar eliminación</h3>
            </div>
            <div class="delete-modal-body">
                <p id="delete-modal-message">¿Estás seguro de que deseas eliminar este elemento?</p>
            </div>
            <div class="delete-modal-footer">
                <button id="delete-modal-cancel" class="btn btn-secondary">Cancelar</button>
                <button id="delete-modal-confirm" class="btn btn-danger">Eliminar</button>
            </div>
        </div>
    `;

    // Agregar el modal al final del body
    document.body.appendChild(modalContainer);

    // Configurar eventos para cerrar el modal
    const cancelButton = document.getElementById('delete-modal-cancel');
    cancelButton.addEventListener('click', closeDeleteModal);
    
    // Configurar cierre al hacer clic fuera del modal
    modalContainer.addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
}

/**
 * Abre el modal de confirmación para eliminar un registro
 * @param {string} itemName - Nombre del elemento a eliminar
 * @param {HTMLElement} form - Formulario que contiene el botón de eliminación
 * @param {string} crudType - Tipo de CRUD (equipos, jugadores, etc.)
 */
function openDeleteModal(itemName, form, crudType) {
    const modalContainer = document.getElementById('delete-modal-container');
    if (!modalContainer) {
        initDeleteModal();
        return openDeleteModal(itemName, form, crudType); // Intentamos de nuevo después de inicializar
    }
    
    const deleteModal = document.getElementById('delete-modal');
    const modalMessage = document.getElementById('delete-modal-message');
    const modalIcon = document.getElementById('delete-modal-icon');
    const confirmButton = document.getElementById('delete-modal-confirm');
    
    // Establecer el mensaje según el tipo de elemento
    modalMessage.textContent = `¿Estás seguro de que deseas eliminar ${crudType === 'usuarios' ? 'al usuario' : 'el ' + getSingular(crudType)}: "${itemName}"?`;
    modalMessage.innerHTML += `<p class="delete-modal-submessage">Esta acción no se puede deshacer.</p>`;
    
    // Ajustar ícono según el tipo de CRUD
    modalIcon.className = 'fas fa-trash';
    
    // Ajustar el estilo según el tipo de CRUD (siempre azul principal)
    deleteModal.className = 'delete-modal';
    
    // Establecer el ícono correspondiente
    modalIcon.className = 'fas fa-trash';
    
    // Configurar el botón de confirmación
    confirmButton.onclick = function() {
        // Mostrar indicador de carga
        confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
        confirmButton.disabled = true;
        document.getElementById('delete-modal-cancel').disabled = true;
        
        // Agregar un campo oculto para registrar que la eliminación fue confirmada explícitamente
        const confirmInput = document.createElement('input');
        confirmInput.type = 'hidden';
        confirmInput.name = 'confirmacion_explicita';
        confirmInput.value = 'true';
        form.appendChild(confirmInput);
        
        // Enviar el formulario
        form.submit();
    };
    
    // Mostrar el modal
    modalContainer.style.display = 'flex';
    
    // Agregar animación
    setTimeout(() => {
        deleteModal.style.transform = 'translateY(0)';
        deleteModal.style.opacity = '1';
    }, 10);
}

/**
 * Cierra el modal de confirmación para eliminar registros
 */
function closeDeleteModal() {
    const modalContainer = document.getElementById('delete-modal-container');
    const deleteModal = document.getElementById('delete-modal');
    
    // Agregar animación de salida
    deleteModal.style.transform = 'translateY(-20px)';
    deleteModal.style.opacity = '0';
    
    setTimeout(() => {
        modalContainer.style.display = 'none';
    }, 300);
}

/**
 * Obtiene el singular de un tipo de CRUD
 * @param {string} type - Tipo de CRUD en plural
 * @returns {string} - Tipo de CRUD en singular
 */
function getSingular(type) {
    const singulars = {
        'equipos': 'equipo',
        'jugadores': 'jugador',
        'partidos': 'partido',
        'canchas': 'cancha',
        'ciudades': 'ciudad',
        'directores': 'director técnico',
        'usuarios': 'usuario'
    };
    
    return singulars[type] || type;
}

/**
 * Configura los botones de eliminación para mostrar un modal de confirmación personalizado
 */
function setupDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach((button) => {
        // Asegurar que el botón nunca envíe el formulario directamente
        button.type = "button";
        
        // Evento de clic en el botón
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const itemName = this.getAttribute('data-name');
            const form = this.closest('form');
            
            // Determinar el tipo de CRUD basado en la URL actual
            let crudType = getCrudTypeFromUrl();
            
            // Mostrar el modal de confirmación
            openDeleteModal(itemName, form, crudType);
        });
    });
}

/**
 * Determina el tipo de CRUD basado en la URL actual
 * @returns {string} - Tipo de CRUD (equipos, jugadores, etc.)
 */
function getCrudTypeFromUrl() {
    const url = window.location.pathname;
    let crudType = 'elementos';
    
    // Mapeo de tipos de CRUD a sus páginas correspondientes
    const crudTypes = {
        'equipos': /equipos\.php/,
        'jugadores': /jugadores\.php/,
        'partidos': /partidos\.php/,
        'canchas': /canchas\.php/,
        'ciudades': /ciudades\.php/,
        'directores': /directores\.php/,
        'usuarios': /usuarios\.php/
    };
    
    // Buscar coincidencias en la URL
    for (const type in crudTypes) {
        if (crudTypes[type].test(url)) {
            crudType = type;
            break;
        }
    }
    
    return crudType;
}

/**
 * Configura la validación de formularios
 */
function setupFormValidation() {
    // Validación de formularios (implementar según necesidades)
}

/**
 * Configura los filtros y búsqueda en tablas
 */
function setupFilters() {
    // Configurar la búsqueda en tablas
    const searchInputs = document.querySelectorAll('.admin-search input');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const tableId = this.getAttribute('data-table');
            const table = document.getElementById(tableId);
            
            if (table) {
                filterTable(table, this.value);
            }
        });
    });
    
    // Configurar los filtros de selección
    const filterSelects = document.querySelectorAll('.admin-filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            const tableId = this.getAttribute('data-table');
            const table = document.getElementById(tableId);
            const column = this.getAttribute('data-column');
            
            if (table && column) {
                filterTableByColumn(table, column, this.value);
            }
        });
    });
}

/**
 * Filtra una tabla basado en un término de búsqueda
 * @param {HTMLElement} table - Tabla a filtrar
 * @param {string} term - Término de búsqueda
 */
function filterTable(table, term) {
    const rows = table.querySelectorAll('tbody tr');
    const lowerTerm = term.toLowerCase();
    
    rows.forEach(row => {
        let found = false;
        const cells = row.querySelectorAll('td');
        
        cells.forEach(cell => {
            if (cell.textContent.toLowerCase().includes(lowerTerm)) {
                found = true;
            }
        });
        
        row.style.display = found ? '' : 'none';
    });
}

/**
 * Filtra una tabla basado en una columna específica
 * @param {HTMLElement} table - Tabla a filtrar
 * @param {string} column - Nombre de la columna para filtrar
 * @param {string} value - Valor para filtrar
 */
function filterTableByColumn(table, column, value) {
    const rows = table.querySelectorAll('tbody tr');
    const headerCells = table.querySelectorAll('thead th');
    let columnIndex = -1;
    
    // Encontrar el índice de la columna
    headerCells.forEach((cell, index) => {
        if (cell.getAttribute('data-column') === column) {
            columnIndex = index;
        }
    });
    
    // Si no se encontró la columna, no hacer nada
    if (columnIndex === -1) return;
    
    // Filtrar las filas
    rows.forEach(row => {
        const cell = row.querySelectorAll('td')[columnIndex];
        
        if (!value || (cell && cell.textContent.includes(value))) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

/**
 * Oculta las notificaciones después de un tiempo
 * Esta función ahora usa el método centralizado en notifications.js
 */
function ocultarNotificaciones() {
    // Llamar a la función centralizada
    if (typeof hideAllNotifications === 'function') {
        hideAllNotifications();
    } else {
        // Fallback por si el script centralizado no está cargado
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 500);
            }, 3000);
        });
    }
}
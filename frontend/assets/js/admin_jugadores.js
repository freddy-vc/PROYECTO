/**
 * JavaScript específico para la gestión de jugadores en el panel de administración
 */
document.addEventListener('DOMContentLoaded', function() {
    // Configuración del formulario de jugador
    setupJugadorForm();
    
    // Configurar filtros específicos para jugadores
    setupJugadoresFilters();
});

/**
 * Configura el formulario de jugador con validaciones específicas
 */
function setupJugadorForm() {
    const jugadorForm = document.querySelector('form[action*="jugadores_controller.php"]');
    
    if (!jugadorForm) return;
    
    jugadorForm.addEventListener('submit', function(e) {
        // Validación de los campos del formulario
        const nombres = document.getElementById('nombres');
        const apellidos = document.getElementById('apellidos');
        const equipo = document.getElementById('equipo');
        const posicion = document.getElementById('posicion');
        const numeroCamiseta = document.getElementById('numero_camiseta');
        let isValid = true;
        
        // Validar nombre del jugador
        if (!nombres.value.trim()) {
            showError(nombres, 'El nombre del jugador es obligatorio');
            isValid = false;
        } else {
            clearError(nombres);
        }
        
        // Validar apellidos del jugador
        if (!apellidos.value.trim()) {
            showError(apellidos, 'Los apellidos del jugador son obligatorios');
            isValid = false;
        } else {
            clearError(apellidos);
        }
        
        // Validar selección de equipo
        if (!equipo.value) {
            showError(equipo, 'Debe seleccionar un equipo para el jugador');
            isValid = false;
        } else {
            clearError(equipo);
        }
        
        // Validar posición
        if (!posicion.value) {
            showError(posicion, 'Debe seleccionar una posición para el jugador');
            isValid = false;
        } else {
            clearError(posicion);
        }
        
        // Validar número de camiseta
        if (!numeroCamiseta.value) {
            showError(numeroCamiseta, 'El número de camiseta es obligatorio');
            isValid = false;
        } else if (isNaN(numeroCamiseta.value) || numeroCamiseta.value < 1 || numeroCamiseta.value > 99) {
            showError(numeroCamiseta, 'El número de camiseta debe estar entre 1 y 99');
            isValid = false;
        } else {
            clearError(numeroCamiseta);
        }
        
        // Validar imagen si se ha seleccionado
        const foto = document.getElementById('foto');
        if (foto && foto.files.length > 0) {
            const file = foto.files[0];
            const fileSize = file.size / 1024 / 1024; // tamaño en MB
            const validExtensions = ['image/jpeg', 'image/png', 'image/gif'];
            
            // Verificar tipo de archivo
            if (!validExtensions.includes(file.type)) {
                showError(foto, 'El archivo debe ser una imagen (JPG, PNG o GIF)');
                isValid = false;
            } 
            // Verificar tamaño de archivo
            else if (fileSize > 2) {
                showError(foto, 'La imagen no debe superar los 2MB');
                isValid = false;
            } else {
                clearError(foto);
            }
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Mostrar vista previa de la imagen
    const fotoInput = document.getElementById('foto');
    if (fotoInput) {
        fotoInput.addEventListener('change', function() {
            const previewContainer = document.getElementById('foto-preview');
            
            // Crear el contenedor de vista previa si no existe
            if (!previewContainer) {
                const container = document.createElement('div');
                container.id = 'foto-preview';
                container.className = 'image-preview';
                fotoInput.parentNode.insertBefore(container, fotoInput.nextSibling);
            }
            
            const preview = document.getElementById('foto-preview');
            
            // Limpiar vista previa anterior
            preview.innerHTML = '';
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Vista previa';
                    preview.appendChild(img);
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
}

/**
 * Configura los filtros específicos para la tabla de jugadores
 */
function setupJugadoresFilters() {
    // Filtro por equipo
    const equipoFilter = document.getElementById('filtro-equipo');
    if (equipoFilter) {
        equipoFilter.addEventListener('change', function() {
            const equipoValue = this.value;
            const table = document.getElementById('jugadores-table');
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const equipoCell = row.querySelector('td[data-column="equipo"]');
                    
                    if (equipoValue === '' || (equipoCell && equipoCell.textContent === equipoValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    }
    
    // Filtro por posición
    const posicionFilter = document.getElementById('filtro-posicion');
    if (posicionFilter) {
        posicionFilter.addEventListener('change', function() {
            const posicionValue = this.value;
            const table = document.getElementById('jugadores-table');
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const posicionCell = row.querySelector('td[data-column="posicion"]');
                    
                    if (posicionValue === '' || (posicionCell && posicionCell.textContent === posicionValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    }
}

/**
 * Muestra un mensaje de error para un campo de formulario
 */
function showError(field, message) {
    field.classList.add('error');
    
    // Eliminar mensaje de error previo si existe
    clearError(field);
    
    // Crear nuevo mensaje de error
    const errorMessage = document.createElement('div');
    errorMessage.className = 'error-message';
    errorMessage.textContent = message;
    field.parentNode.insertBefore(errorMessage, field.nextSibling);
}

/**
 * Elimina el mensaje de error de un campo
 */
function clearError(field) {
    field.classList.remove('error');
    
    // Buscar y eliminar mensaje de error si existe
    const errorMessage = field.nextElementSibling;
    if (errorMessage && errorMessage.className === 'error-message') {
        errorMessage.remove();
    }
} 
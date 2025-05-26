/**
 * JavaScript específico para la gestión de equipos en el panel de administración
 */
document.addEventListener('DOMContentLoaded', function() {
    // Configuración del formulario de equipo
    setupEquipoForm();
});

/**
 * Configura el formulario de equipo con validaciones específicas
 */
function setupEquipoForm() {
    const equipoForm = document.getElementById('equipo-form');
    if (!equipoForm) return;
    
    // Obtener referencias a los campos
    const nombreInput = document.getElementById('nombre');
    const ciudadInput = document.getElementById('ciudad_id');
    const directorInput = document.getElementById('director_id');
    const escudoInput = document.getElementById('escudo');
    
    // Validación en tiempo real para el campo de nombre
    if (nombreInput) {
        nombreInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                showError(this, 'El nombre del equipo es obligatorio');
            } else if (this.value.trim().length < 3) {
                showError(this, 'El nombre debe tener al menos 3 caracteres');
            } else {
                clearError(this);
            }
        });
    }
    
    // Validación en tiempo real para el campo de ciudad
    if (ciudadInput) {
        ciudadInput.addEventListener('change', function() {
            if (!this.value) {
                showError(this, 'Debe seleccionar una ciudad');
            } else {
                clearError(this);
            }
        });
    }
    
    // Validación en tiempo real para el campo de director técnico
    if (directorInput) {
        directorInput.addEventListener('change', function() {
            if (!this.value) {
                showError(this, 'Debe seleccionar un director técnico');
            } else {
                clearError(this);
            }
        });
    }
    
    // Validación en tiempo real para la imagen del escudo
    if (escudoInput) {
        escudoInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                const fileSize = file.size / 1024 / 1024; // tamaño en MB
                const validExtensions = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (!validExtensions.includes(file.type)) {
                    showError(this, 'El formato de imagen no es válido. Sólo se permiten JPG, PNG o GIF');
                } else if (fileSize > 2) {
                    showError(this, 'La imagen es demasiado grande. El tamaño máximo permitido es 2MB');
                } else {
                    clearError(this);
                    
                    // Mostrar vista previa de la imagen
                    const previewContainer = document.getElementById('escudo-preview');
                    if (!previewContainer) {
                        const container = document.createElement('div');
                        container.id = 'escudo-preview';
                        container.className = 'image-preview';
                        this.parentNode.insertBefore(container, this.nextSibling);
                    }
                    
                    const preview = document.getElementById('escudo-preview');
                    preview.innerHTML = '';
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Vista previa del escudo';
                        preview.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                }
            }
        });
    }
    
    // Validar el formulario completo antes de enviar
    equipoForm.addEventListener('submit', function(e) {
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
            showError(nombreInput, 'El nombre del equipo es obligatorio');
            isValid = false;
        } else if (nombreInput.value.trim().length < 3) {
            showError(nombreInput, 'El nombre debe tener al menos 3 caracteres');
            isValid = false;
        } else {
            clearError(nombreInput);
        }
        
        // Validar ciudad
        if (!ciudadInput.value) {
            showError(ciudadInput, 'Debe seleccionar una ciudad');
            isValid = false;
        } else {
            clearError(ciudadInput);
        }
        
        // Validar director técnico
        if (!directorInput.value) {
            showError(directorInput, 'Debe seleccionar un director técnico');
            isValid = false;
        } else {
            clearError(directorInput);
        }
        
        // Validar escudo si se ha seleccionado
        if (escudoInput && escudoInput.files.length > 0) {
            const file = escudoInput.files[0];
            const fileSize = file.size / 1024 / 1024; // tamaño en MB
            const validExtensions = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!validExtensions.includes(file.type)) {
                showError(escudoInput, 'El formato de imagen no es válido. Sólo se permiten JPG, PNG o GIF');
                isValid = false;
            } else if (fileSize > 2) {
                showError(escudoInput, 'La imagen es demasiado grande. El tamaño máximo permitido es 2MB');
                isValid = false;
            } else {
                clearError(escudoInput);
            }
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
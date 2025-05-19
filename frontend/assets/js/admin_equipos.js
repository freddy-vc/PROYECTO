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
    const equipoForm = document.querySelector('form[action*="equipos_controller.php"]');
    
    if (!equipoForm) return;
    
    equipoForm.addEventListener('submit', function(e) {
        // Validación de los campos del formulario
        const nombre = document.getElementById('nombre');
        const ciudad = document.getElementById('ciudad');
        let isValid = true;
        
        // Validar nombre del equipo
        if (!nombre.value.trim()) {
            showError(nombre, 'El nombre del equipo es obligatorio');
            isValid = false;
        } else {
            clearError(nombre);
        }
        
        // Validar selección de ciudad
        if (!ciudad.value) {
            showError(ciudad, 'Debe seleccionar una ciudad para el equipo');
            isValid = false;
        } else {
            clearError(ciudad);
        }
        
        // Validar imagen si se ha seleccionado
        const escudo = document.getElementById('escudo');
        if (escudo && escudo.files.length > 0) {
            const file = escudo.files[0];
            const fileSize = file.size / 1024 / 1024; // tamaño en MB
            const validExtensions = ['image/jpeg', 'image/png', 'image/gif'];
            
            // Verificar tipo de archivo
            if (!validExtensions.includes(file.type)) {
                showError(escudo, 'El archivo debe ser una imagen (JPG, PNG o GIF)');
                isValid = false;
            } 
            // Verificar tamaño de archivo
            else if (fileSize > 2) {
                showError(escudo, 'La imagen no debe superar los 2MB');
                isValid = false;
            } else {
                clearError(escudo);
            }
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Mostrar vista previa de la imagen
    const escudoInput = document.getElementById('escudo');
    if (escudoInput) {
        escudoInput.addEventListener('change', function() {
            const previewContainer = document.getElementById('escudo-preview');
            
            // Crear el contenedor de vista previa si no existe
            if (!previewContainer) {
                const container = document.createElement('div');
                container.id = 'escudo-preview';
                container.className = 'image-preview';
                escudoInput.parentNode.insertBefore(container, escudoInput.nextSibling);
            }
            
            const preview = document.getElementById('escudo-preview');
            
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
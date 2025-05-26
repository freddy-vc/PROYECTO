/**
 * JavaScript para la administración de canchas
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el elemento del formulario si existe
    const form = document.getElementById('cancha-form');
    if (form) {
        initializeFormValidation();
        initializeImagePreview();
    }
    
    // No inicializar los botones de eliminación aquí, se hace en admin.js
});

/**
 * Inicializar la validación del formulario
 */
function initializeFormValidation() {
    const form = document.getElementById('cancha-form');
    
    // Mostrar el nombre del archivo seleccionado
    const fileInput = document.getElementById('foto');
    const fileNameDisplay = document.getElementById('file-name');
    
    if (fileInput && fileNameDisplay) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = 'Ningún archivo seleccionado';
            }
        });
    }
    
    // Validar el formulario antes de enviarlo
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar nombre
        const nombre = document.getElementById('nombre');
        if (!nombre.value.trim()) {
            showError(nombre, 'El nombre de la cancha es obligatorio');
            isValid = false;
        } else {
            clearError(nombre);
        }
        
        // Validar dirección
        const direccion = document.getElementById('direccion');
        if (!direccion.value.trim()) {
            showError(direccion, 'La dirección es obligatoria');
            isValid = false;
        } else {
            clearError(direccion);
        }
        
        // Validar ciudad
        const ciudad = document.getElementById('ciudad_id');
        if (!ciudad.value) {
            showError(ciudad, 'Selecciona una ciudad');
            isValid = false;
        } else {
            clearError(ciudad);
        }
        
        // Validar capacidad (opcional pero debe ser un número positivo si se proporciona)
        const capacidad = document.getElementById('capacidad');
        if (capacidad.value && (isNaN(capacidad.value) || parseInt(capacidad.value) < 0)) {
            showError(capacidad, 'La capacidad debe ser un número positivo');
            isValid = false;
        } else {
            clearError(capacidad);
        }
        
        // Validar tamaño y tipo de la imagen si se ha seleccionado una
        const foto = document.getElementById('foto');
        if (foto.files && foto.files.length > 0) {
            const file = foto.files[0];
            
            // Validar tipo de archivo
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showError(foto, 'El formato de la imagen no es válido. Use JPG, PNG o GIF.');
                isValid = false;
            }
            
            // Validar tamaño de archivo (máximo 2MB)
            else if (file.size > 2 * 1024 * 1024) {
                showError(foto, 'La imagen es demasiado grande. El tamaño máximo es 2MB.');
                isValid = false;
            } else {
                clearError(foto);
            }
        } else {
            clearError(foto);
        }
        
        // Prevenir el envío del formulario si hay errores
        if (!isValid) {
            e.preventDefault();
        }
    });
}

/**
 * Inicializar la vista previa de la imagen
 */
function initializeImagePreview() {
    const fileInput = document.getElementById('foto');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    if (fileInput && imagePreview && previewImg) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                
                // Verificar si es una imagen
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.style.display = 'block';
                    };
                    
                    reader.readAsDataURL(file);
                }
            } else {
                imagePreview.style.display = 'none';
            }
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

/**
 * JavaScript específico para la gestión de canchas en el panel de administración
 */
document.addEventListener('DOMContentLoaded', function() {
    // Configuración del formulario de cancha
    setupCanchaForm();
});

/**
 * Configura el formulario de cancha con validaciones específicas
 */
function setupCanchaForm() {
    const canchaForm = document.getElementById('cancha-form');
    if (!canchaForm) return;
    
    // Obtener referencias a los campos
    const nombreInput = document.getElementById('nombre');
    const direccionInput = document.getElementById('direccion');
    const capacidadInput = document.getElementById('capacidad');
    
    // Validación en tiempo real para el campo de capacidad
    if (capacidadInput) {
        capacidadInput.addEventListener('input', function() {
            if (this.value === '') {
                showError(this, 'La capacidad es obligatoria');
            } else if (isNaN(this.value)) {
                showError(this, 'La capacidad debe ser un valor numérico');
            } else if (parseInt(this.value) < 0) {
                showError(this, 'La capacidad no puede ser negativa');
                this.value = 0; // Corregir automáticamente a valor mínimo permitido
            } else if (parseInt(this.value) > 100000) {
                showError(this, 'La capacidad parece demasiado alta');
            } else {
                clearError(this);
            }
        });
    }
    
    // Validación en tiempo real para el campo de nombre
    if (nombreInput) {
        nombreInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                showError(this, 'El nombre de la cancha es obligatorio');
            } else if (this.value.trim().length < 3) {
                showError(this, 'El nombre debe tener al menos 3 caracteres');
            } else {
                clearError(this);
            }
        });
    }
    
    // Validación en tiempo real para el campo de dirección
    if (direccionInput) {
        direccionInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                showError(this, 'La dirección es obligatoria');
            } else if (this.value.trim().length < 5) {
                showError(this, 'La dirección debe ser más específica');
            } else {
                clearError(this);
            }
        });
    }
    
    // Validar el formulario completo antes de enviar
    canchaForm.addEventListener('submit', function(e) {
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
            showError(nombreInput, 'El nombre de la cancha es obligatorio');
            isValid = false;
        } else if (nombreInput.value.trim().length < 3) {
            showError(nombreInput, 'El nombre debe tener al menos 3 caracteres');
            isValid = false;
        } else {
            clearError(nombreInput);
        }
        
        // Validar dirección
        if (!direccionInput.value.trim()) {
            showError(direccionInput, 'La dirección es obligatoria');
            isValid = false;
        } else if (direccionInput.value.trim().length < 5) {
            showError(direccionInput, 'La dirección debe ser más específica');
            isValid = false;
        } else {
            clearError(direccionInput);
        }
        
        // Validar capacidad
        if (!capacidadInput.value) {
            showError(capacidadInput, 'La capacidad es obligatoria');
            isValid = false;
        } else if (isNaN(capacidadInput.value)) {
            showError(capacidadInput, 'La capacidad debe ser un valor numérico');
            isValid = false;
        } else if (parseInt(capacidadInput.value) < 0) {
            showError(capacidadInput, 'La capacidad no puede ser negativa');
            isValid = false;
        } else if (parseInt(capacidadInput.value) > 100000) {
            showError(capacidadInput, 'La capacidad parece demasiado alta, por favor verifica');
            isValid = false;
        } else {
            clearError(capacidadInput);
        }
        
        return isValid;
    }
} 
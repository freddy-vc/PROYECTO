/**
 * JavaScript para la administración de usuarios
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el elemento del formulario si existe
    const form = document.getElementById('usuario-form');
    if (form) {
        initializeFormValidation();
        initializeFileInput();
    }
    
    // No inicializar los botones de eliminación aquí, se hace en admin.js
});

/**
 * Inicializar la validación del formulario
 */
function initializeFormValidation() {
    const form = document.getElementById('usuario-form');
    
    // Validar el formulario antes de enviarlo
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar username
        const username = document.getElementById('username');
        if (!username.value.trim()) {
            showError(username, 'El nombre de usuario es obligatorio');
            isValid = false;
        } else if (username.value.trim().length < 3) {
            showError(username, 'El nombre de usuario debe tener al menos 3 caracteres');
            isValid = false;
        } else {
            clearError(username);
        }
        
        // Validar email
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim()) {
            showError(email, 'El correo electrónico es obligatorio');
            isValid = false;
        } else if (!emailRegex.test(email.value.trim())) {
            showError(email, 'Ingresa un correo electrónico válido');
            isValid = false;
        } else {
            clearError(email);
        }
        
        // Validar contraseña y confirmación
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        // Verificar si el campo password es obligatorio (en caso de crear un nuevo usuario)
        if (password.required && !password.value) {
            showError(password, 'La contraseña es obligatoria');
            isValid = false;
        } else if (password.value && password.value.length < 6) {
            showError(password, 'La contraseña debe tener al menos 6 caracteres');
            isValid = false;
        } else {
            clearError(password);
        }
        
        // Verificar que las contraseñas coinciden si se ha proporcionado una contraseña
        if (password.value && password.value !== confirmPassword.value) {
            showError(confirmPassword, 'Las contraseñas no coinciden');
            isValid = false;
        } else if (password.required && !confirmPassword.value) {
            showError(confirmPassword, 'Debes confirmar la contraseña');
            isValid = false;
        } else {
            clearError(confirmPassword);
        }
        
        // Validar rol
        const rol = document.getElementById('rol');
        if (!rol.value) {
            showError(rol, 'Selecciona un rol');
            isValid = false;
        } else {
            clearError(rol);
        }
        
        // Validar foto de perfil si se ha seleccionado
        const fotoPerfil = document.getElementById('foto_perfil');
        if (fotoPerfil.files.length > 0) {
            const file = fotoPerfil.files[0];
            
            // Verificar el tipo de archivo
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showError(fotoPerfil, 'Formato de imagen no válido. Use JPG, PNG o GIF');
                isValid = false;
            } else if (file.size > 2 * 1024 * 1024) { // 2MB
                showError(fotoPerfil, 'La imagen es demasiado grande. Máximo 2MB');
                isValid = false;
            } else {
                clearError(fotoPerfil);
            }
        } else {
            clearError(fotoPerfil);
        }
        
        // Prevenir el envío del formulario si hay errores
        if (!isValid) {
            e.preventDefault();
        }
    });
}

/**
 * Inicializar la previsualización de imágenes
 */
function initializeFileInput() {
    const fileInput = document.getElementById('foto_perfil');
    const fileText = document.getElementById('file-name');
    const preview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            fileText.textContent = file.name;
            
            // Mostrar la vista previa de la imagen
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            fileText.textContent = 'Ningún archivo seleccionado';
            preview.style.display = 'none';
        }
    });
}

/**
 * Mostrar un mensaje de error para un campo
 */
function showError(input, message) {
    const errorId = 'error-' + input.id.replace('_', '-');
    const errorElement = document.getElementById(errorId);
    if (errorElement) {
        errorElement.textContent = message;
        input.classList.add('input-error');
    }
}

/**
 * Limpiar un mensaje de error para un campo
 */
function clearError(input) {
    const errorId = 'error-' + input.id.replace('_', '-');
    const errorElement = document.getElementById(errorId);
    if (errorElement) {
        errorElement.textContent = '';
        input.classList.remove('input-error');
    }
} 
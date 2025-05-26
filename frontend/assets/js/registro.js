/**
 * JavaScript específico para la página de registro
 */
document.addEventListener('DOMContentLoaded', function() {
    const registroForm = document.querySelector('form');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmarPasswordInput = document.getElementById('confirmar_password');
    const fotoPerfil = document.getElementById('foto_perfil');
    
    // Enfocar el campo de usuario al cargar la página
    if (usernameInput) {
        usernameInput.focus();
    }
    
    // Validación específica para el formulario de registro
    if (registroForm) {
        registroForm.addEventListener('submit', function(event) {
            let esValido = true;
            
            // Validar nombre de usuario
            if (!usernameInput || usernameInput.value.trim().length < 3) {
                mostrarError(usernameInput, 'El nombre de usuario debe tener al menos 3 caracteres');
                esValido = false;
            } else {
                limpiarError(usernameInput);
            }
            
            // Validar email
            if (!emailInput || !validarEmail(emailInput.value)) {
                mostrarError(emailInput, 'Por favor, introduce un email válido con formato usuario@dominio.com');
                esValido = false;
            } else {
                limpiarError(emailInput);
            }
            
            // Validar contraseña
            if (!passwordInput || passwordInput.value.length < 6) {
                mostrarError(passwordInput, 'La contraseña debe tener al menos 6 caracteres');
                esValido = false;
            } else {
                limpiarError(passwordInput);
            }
            
            // Validar confirmación de contraseña
            if (!confirmarPasswordInput || confirmarPasswordInput.value.length < 6) {
                mostrarError(confirmarPasswordInput, 'La confirmación de contraseña debe tener al menos 6 caracteres');
                esValido = false;
            } else if (passwordInput.value !== confirmarPasswordInput.value) {
                mostrarError(confirmarPasswordInput, 'Las contraseñas no coinciden');
                esValido = false;
            } else {
                limpiarError(confirmarPasswordInput);
            }
            
            // Validar foto de perfil si se ha seleccionado
            if (fotoPerfil.files.length > 0) {
                const file = fotoPerfil.files[0];
                const fileSize = file.size / 1024 / 1024; // tamaño en MB
                const fileType = file.type;
                
                const tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (!tiposPermitidos.includes(fileType)) {
                    mostrarError(fotoPerfil, 'El formato de la imagen no es válido. Sólo se permiten JPG, PNG y GIF');
                    esValido = false;
                } else if (fileSize > 2) {
                    mostrarError(fotoPerfil, 'La imagen es demasiado grande. El tamaño máximo es 2MB');
                    esValido = false;
                } else {
                    limpiarError(fotoPerfil);
                }
            }
            
            if (!esValido) {
                event.preventDefault();
                return false;
            }
            
            return true;
        });
    }
    
    // Eventos para limpiar errores mientras se escribe
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            limpiarError(this);
        });
    }
    
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            limpiarError(this);
            // Validación en tiempo real del email
            if (this.value && !validarEmail(this.value)) {
                mostrarError(this, 'Por favor, introduce un email válido con formato usuario@dominio.com');
            }
        });
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            limpiarError(this);
            
            // Validación en tiempo real de la contraseña
            if (this.value.length > 0 && this.value.length < 6) {
                mostrarError(this, 'La contraseña debe tener al menos 6 caracteres');
            }
            
            // Si hay una confirmación de contraseña, validar si coinciden
            if (confirmarPasswordInput && confirmarPasswordInput.value.length > 0) {
                if (passwordInput.value !== confirmarPasswordInput.value) {
                    mostrarError(confirmarPasswordInput, 'Las contraseñas no coinciden');
                } else {
                    limpiarError(confirmarPasswordInput);
                }
            }
        });
    }
    
    if (confirmarPasswordInput) {
        confirmarPasswordInput.addEventListener('input', function() {
            limpiarError(this);
            
            // Validación en tiempo real de la confirmación
            if (this.value.length > 0 && this.value.length < 6) {
                mostrarError(this, 'La confirmación de contraseña debe tener al menos 6 caracteres');
            } else if (passwordInput && passwordInput.value !== this.value) {
                mostrarError(this, 'Las contraseñas no coinciden');
            }
        });
    }
    
    fotoPerfil.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            const fileSize = file.size / 1024 / 1024; // tamaño en MB
            const fileType = file.type;
            
            const tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!tiposPermitidos.includes(fileType)) {
                mostrarError(this, 'El formato de la imagen no es válido. Sólo se permiten JPG, PNG y GIF');
            } else if (fileSize > 2) {
                mostrarError(this, 'La imagen es demasiado grande. El tamaño máximo es 2MB');
            } else {
                limpiarError(this);
                
                // Mostrar vista previa de la imagen
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewContainer = document.createElement('div');
                    previewContainer.className = 'image-preview';
                    previewContainer.style.marginTop = '10px';
                    
                    const previewImage = document.createElement('img');
                    previewImage.src = e.target.result;
                    previewImage.style.maxWidth = '100%';
                    previewImage.style.maxHeight = '150px';
                    previewImage.style.borderRadius = '5px';
                    
                    // Eliminar vista previa anterior si existe
                    const existingPreview = fotoPerfil.parentElement.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    previewContainer.appendChild(previewImage);
                    fotoPerfil.parentElement.appendChild(previewContainer);
                };
                reader.readAsDataURL(file);
            }
        }
    });
    
    // Función para validar email
    function validarEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    // Funciones para mostrar y limpiar errores
    function mostrarError(campo, mensaje) {
        if (!campo) return;
        
        limpiarError(campo); // Limpiamos primero para evitar duplicados
        
        const contenedorCampo = campo.parentElement;
        const mensajeError = document.createElement('div');
        
        mensajeError.textContent = mensaje;
        mensajeError.className = 'mensaje-error';
        mensajeError.style.color = 'red';
        mensajeError.style.fontSize = '0.8rem';
        mensajeError.style.marginTop = '5px';
        
        contenedorCampo.appendChild(mensajeError);
        campo.classList.add('campo-error');
        campo.style.borderColor = 'red';
    }
    
    function limpiarError(campo) {
        if (!campo) return;
        
        const contenedorCampo = campo.parentElement;
        const mensajeError = contenedorCampo.querySelector('.mensaje-error');
        
        if (mensajeError) {
            contenedorCampo.removeChild(mensajeError);
        }
        
        campo.classList.remove('campo-error');
        campo.style.borderColor = '';
    }
}); 
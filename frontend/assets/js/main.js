/**
 * Script principal para la aplicación de Futsala Villavicencio
 */

document.addEventListener('DOMContentLoaded', function() {
    // Manejar el perfil de usuario en dispositivos móviles
    const userProfile = document.querySelector('.user-profile');
    
    if (userProfile) {
        userProfile.addEventListener('click', function() {
            const dropdown = this.querySelector('.user-dropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
    }
    
    // Cerrar el dropdown cuando se hace clic en otra parte
    document.addEventListener('click', function(event) {
        if (userProfile && !userProfile.contains(event.target)) {
            const dropdown = userProfile.querySelector('.user-dropdown');
            if (dropdown) {
                dropdown.style.display = 'none';
            }
        }
    });
    
    // Animación para los cards de partidos
    const matchCards = document.querySelectorAll('.match-card');
    
    if (matchCards.length > 0) {
        matchCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.2)';
                this.style.transition = 'all 0.3s ease';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            });
        });
    }
    
    // Placeholder para cargar datos vía AJAX (para implementar después)
    function cargarDatosRecientes() {
        // Aquí se implementará la carga de datos recientes mediante AJAX
        console.log('Función para cargar datos recientes');
    }
    
    // Función para validar formularios
    window.validarFormulario = function(formulario) {
        let esValido = true;
        const campos = formulario.querySelectorAll('[required]');
        
        campos.forEach(campo => {
            if (!campo.value.trim()) {
                mostrarError(campo, 'Este campo es obligatorio');
                esValido = false;
            } else {
                limpiarError(campo);
                
                // Validaciones específicas según el tipo de campo
                if (campo.type === 'email' && !validarEmail(campo.value)) {
                    mostrarError(campo, 'Ingrese un email válido');
                    esValido = false;
                }
            }
        });
        
        return esValido;
    };
    
    // Función para validar email
    function validarEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    // Funciones para mostrar y limpiar errores
    function mostrarError(campo, mensaje) {
        const contenedorCampo = campo.parentElement;
        const mensajeError = contenedorCampo.querySelector('.mensaje-error') || document.createElement('div');
        
        mensajeError.textContent = mensaje;
        mensajeError.className = 'mensaje-error';
        mensajeError.style.color = 'red';
        mensajeError.style.fontSize = '0.8rem';
        mensajeError.style.marginTop = '5px';
        
        if (!contenedorCampo.querySelector('.mensaje-error')) {
            contenedorCampo.appendChild(mensajeError);
        }
        
        campo.classList.add('campo-error');
        campo.style.borderColor = 'red';
    }
    
    function limpiarError(campo) {
        const contenedorCampo = campo.parentElement;
        const mensajeError = contenedorCampo.querySelector('.mensaje-error');
        
        if (mensajeError) {
            contenedorCampo.removeChild(mensajeError);
        }
        
        campo.classList.remove('campo-error');
        campo.style.borderColor = '';
    }
}); 
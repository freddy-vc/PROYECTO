/**
 * JavaScript específico para la página de perfil
 */
document.addEventListener('DOMContentLoaded', function() {
    const profileImage = document.querySelector('.profile-image img');
    const profileDetails = document.querySelectorAll('.detail-group p');
    
    // Añadir efectos a la imagen de perfil
    if (profileImage) {
        // Añadir efecto de zoom al pasar el mouse
        profileImage.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.2)';
            this.style.transition = 'all 0.3s ease';
        });
        
        profileImage.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = 'none';
        });
    }
    
    // Añadir efectos a los detalles del perfil
    if (profileDetails.length > 0) {
        profileDetails.forEach(detail => {
            detail.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#e8eaf6';
                this.style.transition = 'background-color 0.3s ease';
            });
            
            detail.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'var(--light-color)';
            });
        });
    }
    
    // Manejar cierre de sesión con confirmación
    const logoutButton = document.querySelector('.profile-actions .btn-danger');
    
    if (logoutButton) {
        logoutButton.addEventListener('click', function(event) {
            if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                event.preventDefault();
            }
        });
    }
    
    // Animación inicial
    animateProfileDetails();
    
    function animateProfileDetails() {
        const details = document.querySelectorAll('.detail-group');
        
        details.forEach((detail, index) => {
            detail.style.opacity = '0';
            detail.style.transform = 'translateY(20px)';
            detail.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            // Animación con delay escalonado
            setTimeout(() => {
                detail.style.opacity = '1';
                detail.style.transform = 'translateY(0)';
            }, 100 * (index + 1));
        });
    }
}); 
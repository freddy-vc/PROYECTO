/**
 * Archivo centralizado para manejar las notificaciones en toda la aplicación
 * Establece un tiempo de 3 segundos para todas las notificaciones
 */

// Al cargar el DOM, ocultar las notificaciones existentes
document.addEventListener('DOMContentLoaded', function() {
    // Manejar notificaciones existentes
    hideAllNotifications();
});

/**
 * Oculta todas las notificaciones en la página después de 3 segundos
 * Esta función maneja tanto las notificaciones de clase "notification" como "mensaje-error" y "mensaje-exito"
 */
function hideAllNotifications() {
    // Ocultar notificaciones del panel de administración (clase "notification")
    const adminNotifications = document.querySelectorAll('.notification');
    adminNotifications.forEach(notification => {
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 500);
        }, 3000);
    });

    // Ocultar mensajes de error/éxito del frontend público (clases "mensaje-error" y "mensaje-exito")
    document.querySelectorAll('.mensaje-error, .mensaje-exito').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(function() { el.style.display = 'none'; }, 500);
        }, 3000);
    });
}

/**
 * Muestra una notificación en el panel de administración
 * @param {string} type - El tipo de notificación ("success" o "error")
 * @param {string} message - El mensaje a mostrar
 */
function showNotification(type, message) {
    // Verificar si el mensaje está relacionado con goles, asistencias o faltas
    // Si es así, no mostrar la notificación
    if (message.includes('Gol') || 
        message.includes('gol') || 
        message.includes('Asistencia') || 
        message.includes('asistencia') || 
        message.includes('Falta') || 
        message.includes('falta') ||
        message.includes('Tarjeta') ||
        message.includes('tarjeta')) {
        return; // No mostrar notificación para estas operaciones
    }
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 3000);
} 
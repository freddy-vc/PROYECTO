/**
 * Script para forzar la recarga de recursos y evitar problemas de caché
 */
(function() {
    // Genera un timestamp único para añadir a las URLs y forzar la recarga
    const cacheBuster = new Date().getTime();
    
    // Función para añadir parámetro de versión a todos los scripts y CSS
    function addVersionToResources() {
        // Obtener todos los scripts
        const scripts = document.querySelectorAll('script[src]');
        scripts.forEach(script => {
            const currentSrc = script.getAttribute('src');
            if (!currentSrc.includes('?v=')) {
                script.setAttribute('src', `${currentSrc}?v=${cacheBuster}`);
            }
        });
        
        // Obtener todas las hojas de estilo
        const stylesheets = document.querySelectorAll('link[rel="stylesheet"]');
        stylesheets.forEach(stylesheet => {
            const currentHref = stylesheet.getAttribute('href');
            if (!currentHref.includes('?v=')) {
                stylesheet.setAttribute('href', `${currentHref}?v=${cacheBuster}`);
            }
        });
    }
    
    // Ejecutar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', addVersionToResources);
})(); 
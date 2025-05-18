<?php
/**
 * Componente para mostrar notificaciones de error y éxito
 * 
 * Uso: include_once 'frontend/components/notificaciones.php';
 *      mostrarNotificaciones(['error_key', 'exito_key']);
 * 
 * Donde 'error_key' y 'exito_key' son claves en $_SESSION que contienen mensajes
 */

/**
 * Muestra notificaciones de error y éxito
 * 
 * @param array $keys Un array con las claves de $_SESSION a mostrar
 */
function mostrarNotificaciones($keys = []) {
    foreach ($keys as $key) {
        // Obtener el mensaje de la sesión
        $mensaje = isset($_SESSION[$key]) ? $_SESSION[$key] : '';
        
        // Si hay mensaje, mostrarlo
        if (!empty($mensaje)) {
            // Determinar si es error o éxito basado en el nombre de la clave
            $tipo = (strpos($key, 'error') !== false) ? 'error' : 'exito';
            
            // Mostrar la notificación
            echo '<div class="mensaje-' . $tipo . '">' . $mensaje . '</div>';
            
            // Limpiar el mensaje de la sesión
            unset($_SESSION[$key]);
        }
    }
}
?> 
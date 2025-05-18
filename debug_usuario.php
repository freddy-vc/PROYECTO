<?php
// Iniciar la sesión
session_start();

// Mostrar información sobre la sesión y el usuario
echo "<h2>Depuración de Usuario</h2>";

// Verificar si hay sesión iniciada
if(!isset($_SESSION['usuario_id'])) {
    echo "<p>No hay sesión de usuario activa</p>";
    echo "<a href='./frontend/pages/login.php'>Iniciar sesión</a>";
    exit;
}

// Mostrar ruta actual
echo "<p>Archivo actual: " . $_SERVER['PHP_SELF'] . "</p>";

// Mostrar información del usuario
echo "<h3>Datos del usuario:</h3>";
echo "<ul>";
echo "<li>ID: " . $_SESSION['usuario_id'] . "</li>";
echo "<li>Nombre: " . $_SESSION['usuario_nombre'] . "</li>";
echo "<li>Email: " . $_SESSION['usuario_email'] . "</li>";
echo "<li>Rol: " . $_SESSION['usuario_rol'] . "</li>";
echo "</ul>";

// Mostrar información de la imagen
echo "<h3>Información de imagen:</h3>";
if(isset($_SESSION['usuario_foto'])) {
    echo "<p>La sesión tiene una imagen: ";
    if(strlen($_SESSION['usuario_foto']) > 100) {
        echo "[Imagen en formato base64]";
    } else {
        echo $_SESSION['usuario_foto'];
    }
    echo "</p>";
    
    // Mostrar la imagen actual
    echo "<img src='" . $_SESSION['usuario_foto'] . "' style='max-width: 100px;' />";
} else {
    echo "<p>No hay imagen en la sesión</p>";
}

// Mostrar las rutas de imágenes que se usarían según el contexto
echo "<h3>Rutas de imagen por defecto según contexto:</h3>";
echo "<ul>";
echo "<li>En /frontend/pages/: ../assets/images/user.png</li>";
echo "<li>En /frontend/: ./assets/images/user.png</li>";
echo "<li>En la raíz: ./frontend/assets/images/user.png</li>";
echo "</ul>";

// Enlaces para verificar la existencia de las imágenes
echo "<h3>Verificar existencia de imágenes:</h3>";
echo "<ul>";
echo "<li><a href='./frontend/assets/images/user.png' target='_blank'>./frontend/assets/images/user.png</a></li>";
echo "</ul>";
?> 
<?php
// Iniciar la sesión
session_start();

// Mostrar información sobre la ubicación actual y rutas
echo "<h1>Información de rutas para la imagen de usuario</h1>";
echo "<p>PHP_SELF: " . $_SERVER['PHP_SELF'] . "</p>";
echo "<p>SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Verificar si estamos en una página de administración
$es_admin = (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false);
echo "<p>¿Es página de administración? " . ($es_admin ? 'Sí' : 'No') . "</p>";

// Mostrar la ruta que se usaría para la imagen de perfil
echo "<h2>Ruta que se usaría para la imagen de usuario</h2>";
if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
    echo "<p>Ruta: <code>../../assets/images/user.png</code></p>";
} else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
    echo "<p>Ruta: <code>../assets/images/user.png</code></p>";
} else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
    echo "<p>Ruta: <code>./assets/images/user.png</code></p>";
} else {
    echo "<p>Ruta: <code>./frontend/assets/images/user.png</code></p>";
}

// Mostrar la ruta completa del archivo
echo "<h2>Verificación de existencia de los archivos</h2>";
$rutas = [
    "../../assets/images/user.png",
    "../assets/images/user.png",
    "./assets/images/user.png",
    "./frontend/assets/images/user.png"
];

foreach ($rutas as $ruta) {
    $ruta_real = realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $ruta);
    echo "<p>Ruta <code>$ruta</code>: " . ($ruta_real && file_exists($ruta_real) ? "EXISTE ($ruta_real)" : "NO EXISTE") . "</p>";
}

// Comprobar rutas alternativas con rutas absolutas
echo "<h2>Verificación con rutas absolutas</h2>";
$bases = [
    "/var/www/html/PROYECTO/",
    "/var/www/html/PROYECTO/frontend/",
    "/var/www/html/PROYECTO/frontend/pages/",
    "/var/www/html/PROYECTO/frontend/pages/admin/"
];

foreach ($bases as $base) {
    $ruta = $base . "assets/images/user.png";
    echo "<p>Ruta <code>$ruta</code>: " . (file_exists($ruta) ? "EXISTE" : "NO EXISTE") . "</p>";
}

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
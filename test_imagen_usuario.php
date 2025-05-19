<?php
// Iniciar la sesión
session_start();

// Asegurarnos de que la sesión tiene los datos básicos para simular un usuario
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Usuario de Prueba';
$_SESSION['usuario_rol'] = 'admin';
$_SESSION['usuario_foto'] = ''; // Sin foto para probar la imagen por defecto

// Función para mostrar la ruta de la imagen según la ubicación
function obtenerRutaImagen($ruta) {
    if (strpos($ruta, '/frontend/pages/admin/') !== false) {
        return "../../assets/images/user.png";
    } else if (strpos($ruta, '/frontend/pages/') !== false) {
        return "../assets/images/user.png";
    } else if (strpos($ruta, '/frontend/') !== false) {
        return "./assets/images/user.png";
    } else {
        return "./frontend/assets/images/user.png";
    }
}

// Rutas de prueba
$rutas = [
    '/frontend/pages/admin/index.php',
    '/frontend/pages/admin/equipos.php',
    '/frontend/pages/jugadores.php',
    '/frontend/index.php',
    '/index.php'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de imagen de usuario</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #1e88e5; }
        .test-case { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
        .image-test { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
        img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .code { background: #f5f5f5; padding: 5px 10px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>Prueba de visualización de imagen de usuario</h1>
    <p>Esta página muestra cómo se vería la imagen de usuario en diferentes rutas.</p>
    
    <h2>Información de la sesión actual</h2>
    <p>PHP_SELF: <span class="code"><?= $_SERVER['PHP_SELF'] ?></span></p>
    <p>Usuario ID: <span class="code"><?= $_SESSION['usuario_id'] ?></span></p>
    <p>Usuario Nombre: <span class="code"><?= $_SESSION['usuario_nombre'] ?></span></p>
    <p>Usuario Rol: <span class="code"><?= $_SESSION['usuario_rol'] ?></span></p>
    <p>Usuario Foto: <span class="code"><?= empty($_SESSION['usuario_foto']) ? 'Sin foto (usará imagen por defecto)' : $_SESSION['usuario_foto'] ?></span></p>
    
    <h2>Pruebas de visualización</h2>
    
    <?php foreach ($rutas as $ruta): ?>
    <div class="test-case">
        <h3>Ruta: <span class="code"><?= $ruta ?></span></h3>
        <p>Imagen a mostrar: <span class="code"><?= obtenerRutaImagen($ruta) ?></span></p>
        
        <div class="image-test">
            <img src="<?= obtenerRutaImagen($ruta) ?>" alt="Foto de perfil">
            <span><?= $_SESSION['usuario_nombre'] ?></span>
        </div>
        
        <p>La imagen <?= file_exists(obtenerRutaImagen($ruta)) ? 'EXISTE' : 'NO EXISTE' ?> en la ruta actual.</p>
        <p>Ruta absoluta: <?= realpath(obtenerRutaImagen($ruta)) ?: 'No se pudo encontrar' ?></p>
    </div>
    <?php endforeach; ?>
    
    <h2>Verificación de archivos</h2>
    <p>Ruta real a user.png: <?= realpath('/var/www/html/PROYECTO/frontend/assets/images/user.png') ?></p>
    <p>¿El archivo existe? <?= file_exists('/var/www/html/PROYECTO/frontend/assets/images/user.png') ? 'SÍ' : 'NO' ?></p>
</body>
</html> 
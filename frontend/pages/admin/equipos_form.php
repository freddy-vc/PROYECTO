<?php
// Definir variables para la página
$titulo_pagina = isset($_GET['id']) ? 'Editar Equipo' : 'Nuevo Equipo';
$pagina_actual = 'admin_equipos';

// Incluir el header
include_once '../../components/header.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Redireccionar a la página de inicio si no es administrador
    header('Location: ../../index.php');
    exit;
}

// Incluir el componente de notificaciones
include_once '../../components/notificaciones.php';

// Variables para los datos del formulario
$equipo = null;
$ciudades = [];
$directores = [];

// Si es una edición, cargar los datos del equipo
if (isset($_GET['id'])) {
    $equipo_id = intval($_GET['id']);
    
    // Aquí iría el código para cargar los datos del equipo desde la base de datos
    // Por ahora, usamos datos de ejemplo
    $equipo = [
        'id' => $equipo_id,
        'nombre' => 'Los Halcones',
        'ciudad_id' => 1,
        'director_id' => 2,
        'escudo' => '../../assets/images/default-team.png'
    ];
}

// Cargar ciudades y directores para los selects
// Aquí iría el código para cargar estos datos desde la base de datos
// Por ahora, usamos datos de ejemplo
$ciudades = [
    ['id' => 1, 'nombre' => 'Villavicencio'],
    ['id' => 2, 'nombre' => 'Acacías'],
    ['id' => 3, 'nombre' => 'Granada']
];

$directores = [
    ['id' => 1, 'nombre' => 'Juan Pérez'],
    ['id' => 2, 'nombre' => 'Ana Gómez'],
    ['id' => 3, 'nombre' => 'Carlos Rodríguez']
];
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">

<div class="admin-container">
    <!-- Navegación dentro del panel de administración -->
    <div class="admin-nav">
        <ul>
            <li><a href="./index.php">Inicio</a></li>
            <li><a href="./equipos.php" class="active">Equipos</a></li>
            <li><a href="./jugadores.php">Jugadores</a></li>
            <li><a href="./ciudades.php">Ciudades</a></li>
            <li><a href="./canchas.php">Canchas</a></li>
            <li><a href="./directores.php">Directores Técnicos</a></li>
            <li><a href="./partidos.php">Partidos</a></li>
            <li><a href="./tabla.php">Tabla de Puntuación</a></li>
            <li><a href="./usuarios.php">Usuarios</a></li>
        </ul>
    </div>
    
    <div class="admin-header">
        <h1><?php echo $titulo_pagina; ?></h1>
        <p><?php echo isset($_GET['id']) ? 'Modifica los datos del equipo existente' : 'Crea un nuevo equipo para el campeonato'; ?></p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_equipos', 'exito_equipos']);
    ?>
    
    <!-- Formulario de equipo -->
    <form class="admin-form" action="../../backend/controllers/admin/equipos_controller.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="<?php echo isset($_GET['id']) ? 'actualizar' : 'crear'; ?>">
        <?php if (isset($_GET['id'])): ?>
        <input type="hidden" name="id" value="<?php echo $equipo['id']; ?>">
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-col">
                <div class="admin-form-group">
                    <label for="nombre">Nombre del Equipo</label>
                    <input type="text" id="nombre" name="nombre" required value="<?php echo isset($equipo) ? htmlspecialchars($equipo['nombre']) : ''; ?>">
                </div>
                
                <div class="admin-form-group">
                    <label for="ciudad">Ciudad</label>
                    <select id="ciudad" name="ciudad_id" required>
                        <option value="">Seleccione una ciudad</option>
                        <?php foreach ($ciudades as $ciudad): ?>
                        <option value="<?php echo $ciudad['id']; ?>" <?php echo isset($equipo) && $equipo['ciudad_id'] == $ciudad['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ciudad['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="admin-form-group">
                    <label for="director">Director Técnico</label>
                    <select id="director" name="director_id">
                        <option value="">Sin director técnico</option>
                        <?php foreach ($directores as $director): ?>
                        <option value="<?php echo $director['id']; ?>" <?php echo isset($equipo) && $equipo['director_id'] == $director['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($director['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-col">
                <div class="admin-form-group">
                    <label for="escudo">Escudo del Equipo</label>
                    <input type="file" id="escudo" name="escudo" accept="image/*" onchange="previsualizarImagen(this, 'preview-escudo')">
                    
                    <div style="margin-top: 15px; text-align: center;">
                        <img id="preview-escudo" src="<?php echo isset($equipo) ? $equipo['escudo'] : '../../assets/images/default-team.png'; ?>" alt="Vista previa del escudo" style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; padding: 5px;">
                    </div>
                    <p style="font-size: 0.8rem; color: #777; margin-top: 5px; text-align: center;">
                        Imagen recomendada: formato cuadrado, PNG o JPG
                    </p>
                </div>
            </div>
        </div>
        
        <div class="admin-form-actions">
            <a href="./equipos.php" class="btn cancel-btn">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo isset($_GET['id']) ? 'Actualizar Equipo' : 'Crear Equipo'; ?>
            </button>
        </div>
    </form>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
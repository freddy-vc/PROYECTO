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

// Incluir los modelos necesarios
require_once '../../../backend/models/Equipo.php';
require_once '../../../backend/models/Ciudad.php';
require_once '../../../backend/models/Director.php';

// Instanciar los modelos
$equipoModel = new Equipo();
$ciudadModel = new Ciudad();
$directorModel = new Director();

// Variables para los datos del formulario
$equipo = null;
$ciudades = $ciudadModel->obtenerTodas();
$directores = $directorModel->obtenerTodos();

// Si es una edición, cargar los datos del equipo
if (isset($_GET['id'])) {
    $equipo_id = intval($_GET['id']);
    $equipo = $equipoModel->obtenerPorId($equipo_id);
    
    // Si no se encuentra el equipo, redirigir
    if (!$equipo) {
        $_SESSION['error_equipos'] = 'No se encontró el equipo solicitado';
        header('Location: ./equipos.php');
        exit;
    }
}
?>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">

<div class="container">
    <h1 class="page-title"><?php echo $titulo_pagina; ?></h1>
    
    <div class="section-intro">
        <p><?php echo isset($_GET['id']) ? 'Modifica los datos del equipo existente' : 'Crea un nuevo equipo para el campeonato'; ?></p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_equipos', 'exito_equipos']);
    ?>

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
                <li><a href="./clasificaciones.php">Clasificaciones</a></li>
                <li><a href="./usuarios.php">Usuarios</a></li>
            </ul>
        </div>
        
        <!-- Formulario de equipo -->
        <div class="admin-form">
            <form action="../../../backend/controllers/admin/equipos_controller.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="<?php echo isset($_GET['id']) ? 'actualizar' : 'crear'; ?>">
                <?php if (isset($_GET['id'])): ?>
                    <input type="hidden" name="id" value="<?php echo $equipo['cod_equ']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="admin-form-group">
                            <label for="nombre">Nombre del Equipo</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo isset($equipo) ? htmlspecialchars($equipo['nombre']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="admin-form-group">
                            <label for="ciudad_id">Ciudad</label>
                            <select id="ciudad_id" name="ciudad_id" required>
                                <option value="">Seleccione una ciudad</option>
                                <?php foreach ($ciudades as $ciudad): ?>
                                    <option value="<?php echo $ciudad['cod_ciu']; ?>" <?php echo (isset($equipo) && $equipo['cod_ciu'] == $ciudad['cod_ciu']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ciudad['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="admin-form-group">
                            <label for="director_id">Director Técnico</label>
                            <select id="director_id" name="director_id">
                                <option value="">Sin director asignado</option>
                                <?php foreach ($directores as $director): ?>
                                    <option value="<?php echo $director['cod_dt']; ?>" <?php echo (isset($equipo) && $equipo['cod_dt'] == $director['cod_dt']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($director['nombres'] . ' ' . $director['apellidos']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="admin-form-group">
                            <label for="escudo">Escudo del Equipo</label>
                            <?php if (isset($equipo) && $equipo['escudo_base64']): ?>
                                <div class="current-image">
                                    <img src="<?php echo $equipo['escudo_base64']; ?>" alt="Escudo actual" style="max-width: 100px; margin-bottom: 10px;">
                                    <p>Escudo actual</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="escudo" name="escudo" accept="image/*">
                            <small>Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.</small>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-actions">
                    <a href="./equipos.php" class="btn cancel-btn">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo isset($_GET['id']) ? 'Actualizar Equipo' : 'Crear Equipo'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_equipos.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
<?php
// Definir variables para la página
$titulo_pagina = isset($_GET['id']) ? 'Editar Jugador' : 'Nuevo Jugador';
$pagina_actual = 'admin_jugadores';

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
require_once '../../../backend/models/Jugador.php';
require_once '../../../backend/models/Equipo.php';

// Instanciar los modelos
$jugadorModel = new Jugador();
$equipoModel = new Equipo();

// Variables para los datos del formulario
$jugador = null;
$equipos = $equipoModel->obtenerTodos();

// Array de posiciones de jugadores
$posiciones = [
    'Portero' => 'Portero',
    'Cierre' => 'Cierre',
    'Ala' => 'Ala',
    'Pívot' => 'Pívot',
    'Ala-Pívot' => 'Ala-Pívot',
    'Universal' => 'Universal'
];

// Si es una edición, cargar los datos del jugador
if (isset($_GET['id'])) {
    $jugador_id = intval($_GET['id']);
    $jugador = $jugadorModel->obtenerDetalleCompleto($jugador_id);
    
    // Si no se encuentra el jugador, redirigir
    if (!$jugador) {
        $_SESSION['error_jugadores'] = 'No se encontró el jugador solicitado';
        header('Location: ./jugadores.php');
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
        <p><?php echo isset($_GET['id']) ? 'Modifica los datos del jugador existente' : 'Registra un nuevo jugador para el campeonato'; ?></p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_jugadores', 'exito_jugadores']);
    ?>

    <div class="admin-container">
        <!-- Navegación dentro del panel de administración -->
        <div class="admin-nav">
            <ul>
                <li><a href="./index.php">Inicio</a></li>
                <li><a href="./equipos.php">Equipos</a></li>
                <li><a href="./jugadores.php" class="active">Jugadores</a></li>
                <li><a href="./ciudades.php">Ciudades</a></li>
                <li><a href="./canchas.php">Canchas</a></li>
                <li><a href="./directores.php">Directores Técnicos</a></li>
                <li><a href="./partidos.php">Partidos</a></li>
                <li><a href="./usuarios.php">Usuarios</a></li>
            </ul>
        </div>
        
        <!-- Formulario de jugador -->
        <div class="admin-form">
            <form action="../../../backend/controllers/admin/jugadores_controller.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="<?php echo isset($_GET['id']) ? 'actualizar' : 'crear'; ?>">
                <?php if (isset($_GET['id'])): ?>
                    <input type="hidden" name="id" value="<?php echo $jugador['cod_jug']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="admin-form-group">
                            <label for="nombres">Nombres</label>
                            <input type="text" id="nombres" name="nombres" value="<?php echo isset($jugador) ? $jugador['nombres'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="admin-form-group">
                            <label for="apellidos">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" value="<?php echo isset($jugador) ? $jugador['apellidos'] : ''; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="admin-form-group">
                            <label for="equipo">Equipo</label>
                            <select id="equipo" name="equipo_id" required>
                                <option value="">Seleccione un equipo</option>
                                <?php foreach ($equipos as $equipo): ?>
                                    <option value="<?php echo $equipo['cod_equ']; ?>" <?php echo (isset($jugador) && $jugador['cod_equ'] == $equipo['cod_equ']) ? 'selected' : ''; ?>>
                                        <?php echo $equipo['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="admin-form-group">
                            <label for="posicion">Posición</label>
                            <select id="posicion" name="posicion" required>
                                <option value="">Seleccione una posición</option>
                                <option value="delantero" <?php echo (isset($jugador) && $jugador['posicion'] == 'delantero') ? 'selected' : ''; ?>>Delantero</option>
                                <option value="defensa" <?php echo (isset($jugador) && $jugador['posicion'] == 'defensa') ? 'selected' : ''; ?>>Defensa</option>
                                <option value="mediocampista" <?php echo (isset($jugador) && $jugador['posicion'] == 'mediocampista') ? 'selected' : ''; ?>>Mediocampista</option>
                                <option value="arquero" <?php echo (isset($jugador) && $jugador['posicion'] == 'arquero') ? 'selected' : ''; ?>>Arquero</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="admin-form-group">
                            <label for="dorsal">Número de Camiseta</label>
                            <input type="number" id="dorsal" name="dorsal" min="1" max="99" value="<?php echo isset($jugador) ? $jugador['dorsal'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="admin-form-group">
                            <label for="foto">Foto del Jugador</label>
                            <?php if (isset($jugador) && $jugador['foto_base64']): ?>
                                <div class="current-image">
                                    <img src="<?php echo $jugador['foto_base64']; ?>" alt="Foto actual" style="max-width: 100px; margin-bottom: 10px;">
                                    <p>Foto actual</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="foto" name="foto" accept="image/*">
                            <small>Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.</small>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-actions">
                    <a href="./jugadores.php" class="btn cancel-btn">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo isset($_GET['id']) ? 'Actualizar Jugador' : 'Registrar Jugador'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_jugadores.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
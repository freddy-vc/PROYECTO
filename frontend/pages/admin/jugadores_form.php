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
require_once '../../../backend/models/Partido.php';

// Instanciar los modelos
$jugadorModel = new Jugador();
$equipoModel = new Equipo();
$partidoModel = new Partido();

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
            <form id="form-jugador" action="../../../backend/controllers/admin/jugadores_controller.php" method="POST" enctype="multipart/form-data">
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
                            <input type="number" id="dorsal" name="numero_camiseta" min="1" max="99" value="<?php echo isset($jugador) ? $jugador['dorsal'] : ''; ?>" required>
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
                            <?php else: ?>
                                <div class="current-image">
                                    <img src="../../assets/images/player.png" alt="Foto por defecto" style="max-width: 100px; margin-bottom: 10px;">
                                    <p>Foto por defecto</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="foto" name="foto" accept="image/*">
                            <small>Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.</small>
                        </div>
                    </div>
                </div>
                <!-- Sección de estadísticas del jugador (Goles, Asistencias, Faltas) -->
                <?php if (isset($jugador)): ?>
                <div class="admin-stats-section" style="margin-top:30px; margin-bottom:30px;">
                    <h2 style="font-size:1.3em; color:#1976d2; margin-bottom:18px;">Estadísticas del Jugador</h2>
                    <!-- Goles -->
                    <div class="admin-table-container" style="margin-bottom: 30px;">
                        <h3 style="font-size:1.1em; color:#333;">Goles <button type="button" class="btn btn-sm btn-add" id="btn-add-gol">Agregar</button></h3>
                        <table class="admin-table" id="tabla-goles">
                            <thead>
                                <tr><th>Partido</th><th>Minuto</th><th>Tipo</th><th>Acciones</th></tr>
                            </thead>
                            <tbody>
                                <!-- Las filas serán renderizadas por JS -->
                            </tbody>
                        </table>
                    </div>
                    <!-- Asistencias -->
                    <div class="admin-table-container" style="margin-bottom: 30px;">
                        <h3 style="font-size:1.1em; color:#333;">Asistencias <button type="button" class="btn btn-sm btn-add" id="btn-add-asistencia">Agregar</button></h3>
                        <table class="admin-table" id="tabla-asistencias">
                            <thead>
                                <tr><th>Partido</th><th>Minuto</th><th>Acciones</th></tr>
                            </thead>
                            <tbody>
                                <!-- Las filas serán renderizadas por JS -->
                            </tbody>
                        </table>
                    </div>
                    <!-- Faltas -->
                    <div class="admin-table-container">
                        <h3 style="font-size:1.1em; color:#333;">Tarjetas <button type="button" class="btn btn-sm btn-add" id="btn-add-falta">Agregar</button></h3>
                        <table class="admin-table" id="tabla-faltas">
                            <thead>
                                <tr><th>Partido</th><th>Minuto</th><th>Tipo</th><th>Acciones</th></tr>
                            </thead>
                            <tbody>
                                <!-- Las filas serán renderizadas por JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
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

<?php if (isset($jugador)): ?>
<script>var jugadorId = <?php echo (int)$jugador['cod_jug']; ?></script>
<?php endif; ?>

<!-- Modales para agregar, editar y eliminar goles, asistencias y faltas (fuera del form principal) -->
<?php if (isset($jugador)): ?>
<div id="modal-gol" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('modal-gol')">&times;</span>
        <form id="form-add-gol" onsubmit="return false;">
            <input type="hidden" name="jugador_id" value="<?= htmlspecialchars($jugador['cod_jug']) ?>">
            <label>Partido</label>
            <select name="partido_id" required>
                <?php foreach ($partidoModel->obtenerPorEquipo($jugador['cod_equ']) as $p): ?>
                    <option value="<?= $p['cod_par'] ?>"><?= htmlspecialchars($p['local_nombre'] . ' vs ' . $p['visitante_nombre'] . ' (' . $p['fecha'] . ' ' . $p['hora'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
            <label>Minuto</label>
            <input type="number" name="minuto" min="0" max="50" required>
            <label>Tipo</label>
            <select name="tipo" required>
                <option value="normal">Normal</option>
                <option value="penal">Penal</option>
                <option value="autogol">Autogol</option>
            </select>
            <button type="submit">Guardar</button>
        </form>
    </div>
</div>

<div id="modal-asistencia" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('modal-asistencia')">&times;</span>
        <form id="form-add-asistencia" onsubmit="return false;">
            <input type="hidden" name="jugador_id" value="<?= $jugador['cod_jug'] ?>">
            <label>Partido</label>
            <select name="partido_id" required>
                <?php foreach ($partidoModel->obtenerPorEquipo($jugador['cod_equ']) as $p): ?>
                    <option value="<?= $p['cod_par'] ?>"><?= htmlspecialchars($p['local_nombre'] . ' vs ' . $p['visitante_nombre'] . ' (' . $p['fecha'] . ' ' . $p['hora'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
            <label>Minuto</label>
            <input type="number" name="minuto" min="0" max="50" required>
            <button type="submit">Guardar</button>
        </form>
    </div>
</div>

<div id="modal-falta" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('modal-falta')">&times;</span>
        <form id="form-add-falta" onsubmit="return false;">
            <input type="hidden" name="jugador_id" value="<?= $jugador['cod_jug'] ?>">
            <label>Partido</label>
            <select name="partido_id" required>
                <?php foreach ($partidoModel->obtenerPorEquipo($jugador['cod_equ']) as $p): ?>
                    <option value="<?= $p['cod_par'] ?>"><?= htmlspecialchars($p['local_nombre'] . ' vs ' . $p['visitante_nombre'] . ' (' . $p['fecha'] . ' ' . $p['hora'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
            <label>Minuto</label>
            <input type="number" name="minuto" min="0" max="50" required>
            <label>Tipo</label>
            <select name="tipo_falta" required>
                <option value="amarilla">Amarilla</option>
                <option value="roja">Roja</option>
            </select>
            <button type="submit">Guardar</button>
        </form>
    </div>
</div>

<div id="modal-edit-gol" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-edit-gol')">&times;</span><form id="form-edit-gol" action="../../../backend/controllers/admin/jugadores_stats_controller.php" method="POST"></form></div></div>
<div id="modal-delete-gol" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-delete-gol')">&times;</span><form id="form-delete-gol" action="../../../backend/controllers/admin/jugadores_stats_controller.php" method="POST"></form></div></div>
<div id="modal-edit-asistencia" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-edit-asistencia')">&times;</span><form id="form-edit-asistencia" action="../../../backend/controllers/admin/jugadores_stats_controller.php" method="POST"></form></div></div>
<div id="modal-delete-asistencia" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-delete-asistencia')">&times;</span><form id="form-delete-asistencia" action="../../../backend/controllers/admin/jugadores_stats_controller.php" method="POST"></form></div></div>
<div id="modal-edit-falta" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-edit-falta')">&times;</span><form id="form-edit-falta" action="../../../backend/controllers/admin/jugadores_stats_controller.php" method="POST"></form></div></div>
<div id="modal-delete-falta" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-delete-falta')">&times;</span><form id="form-delete-falta" action="../../../backend/controllers/admin/jugadores_stats_controller.php" method="POST"></form></div></div>
<script>
function openModal(id) {
    document.getElementById(id).style.display = 'block';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
document.getElementById('btn-add-gol').onclick = function() { openModal('modal-gol'); };
document.getElementById('btn-add-asistencia').onclick = function() { openModal('modal-asistencia'); };
document.getElementById('btn-add-falta').onclick = function() { openModal('modal-falta'); };
function openEditGolModal(gol) {
    var partidos = <?php echo json_encode($partidoModel->obtenerTodos()); ?>;
    var html = `<input type='hidden' name='accion' value='editar_gol'>
        <input type='hidden' name='cod_gol' value='${gol.cod_gol}'>
        <input type='hidden' name='jugador_id' value='${gol.cod_jug}'>
        <label>Partido</label><select name='partido_id' required>`;
    partidos.forEach(function(p) {
        var selected = (p.cod_par == gol.cod_par) ? 'selected' : '';
        html += `<option value='${p.cod_par}' ${selected}>${p.local_nombre} vs ${p.visitante_nombre} (${p.fecha} ${p.hora})</option>`;
    });
    html += `</select>
        <label>Minuto</label><input type='number' name='minuto' min='0' max='50' value='${gol.minuto}' required>
        <label>Tipo</label><select name='tipo' required>
            <option value='normal' ${gol.tipo=='normal'?'selected':''}>Normal</option>
            <option value='penal' ${gol.tipo=='penal'?'selected':''}>Penal</option>
            <option value='autogol' ${gol.tipo=='autogol'?'selected':''}>Autogol</option>
        </select>
        <button type='submit'>Actualizar</button>`;
    document.getElementById('form-edit-gol').innerHTML = html;
    openModal('modal-edit-gol');
}
function openDeleteGolModal(cod_gol) {
    var html = `<input type='hidden' name='accion' value='eliminar_gol'>
        <input type='hidden' name='cod_gol' value='${cod_gol}'>
        <p>¿Estás seguro de que deseas eliminar este gol?</p>
        <button type='submit'>Eliminar</button>`;
    document.getElementById('form-delete-gol').innerHTML = html;
    openModal('modal-delete-gol');
}
function openEditAsistenciaModal(asis) {
    var partidos = <?php echo json_encode($partidoModel->obtenerTodos()); ?>;
    var html = `<input type='hidden' name='accion' value='editar_asistencia'>
        <input type='hidden' name='cod_asis' value='${asis.cod_asis}'>
        <input type='hidden' name='jugador_id' value='${asis.cod_jug}'>
        <label>Partido</label><select name='partido_id' required>`;
    partidos.forEach(function(p) {
        var selected = (p.cod_par == asis.cod_par) ? 'selected' : '';
        html += `<option value='${p.cod_par}' ${selected}>${p.local_nombre} vs ${p.visitante_nombre} (${p.fecha} ${p.hora})</option>`;
    });
    html += `</select>
        <label>Minuto</label><input type='number' name='minuto' min='0' max='50' value='${asis.minuto}' required>
        <button type='submit'>Actualizar</button>`;
    document.getElementById('form-edit-asistencia').innerHTML = html;
    openModal('modal-edit-asistencia');
}
function openDeleteAsistenciaModal(cod_asis) {
    var html = `<input type='hidden' name='accion' value='eliminar_asistencia'>
        <input type='hidden' name='cod_asis' value='${cod_asis}'>
        <p>¿Estás seguro de que deseas eliminar esta asistencia?</p>
        <button type='submit'>Eliminar</button>`;
    document.getElementById('form-delete-asistencia').innerHTML = html;
    openModal('modal-delete-asistencia');
}
function openEditFaltaModal(falta) {
    var partidos = <?php echo json_encode($partidoModel->obtenerTodos()); ?>;
    var html = `<input type='hidden' name='accion' value='editar_falta'>
        <input type='hidden' name='cod_falta' value='${falta.cod_falta}'>
        <input type='hidden' name='jugador_id' value='${falta.cod_jug}'>
        <label>Partido</label><select name='partido_id' required>`;
    partidos.forEach(function(p) {
        var selected = (p.cod_par == falta.cod_par) ? 'selected' : '';
        html += `<option value='${p.cod_par}' ${selected}>${p.local_nombre} vs ${p.visitante_nombre} (${p.fecha} ${p.hora})</option>`;
    });
    html += `</select>
        <label>Minuto</label><input type='number' name='minuto' min='0' max='50' value='${falta.minuto}' required>
        <label>Tipo</label><select name='tipo_falta' required>
            <option value='amarilla' ${falta.tipo_falta=='amarilla'?'selected':''}>Amarilla</option>
            <option value='roja' ${falta.tipo_falta=='roja'?'selected':''}>Roja</option>
        </select>
        <button type='submit'>Actualizar</button>`;
    document.getElementById('form-edit-falta').innerHTML = html;
    openModal('modal-edit-falta');
}
function openDeleteFaltaModal(cod_falta) {
    var html = `<input type='hidden' name='accion' value='eliminar_falta'>
        <input type='hidden' name='cod_falta' value='${cod_falta}'>
        <p>¿Estás seguro de que deseas eliminar esta falta?</p>
        <button type='submit'>Eliminar</button>`;
    document.getElementById('form-delete-falta').innerHTML = html;
    openModal('modal-delete-falta');
}
</script>
<?php endif; ?>

<script>
<?php if (isset($jugador)): ?>
window.initialStats = {
    goles: <?php echo json_encode($jugador['detalle_goles'] ?? []); ?>,
    asistencias: <?php echo json_encode($jugador['detalle_asistencias'] ?? []); ?>,
    faltas: <?php echo json_encode($jugador['detalle_faltas'] ?? []); ?>
};
<?php endif; ?>
</script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
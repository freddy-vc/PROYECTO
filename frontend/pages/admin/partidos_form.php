<?php
// Definir variables para la página
$titulo_pagina = 'Administrar Partido';
$pagina_actual = 'admin_partidos';

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
require_once '../../../backend/models/Partido.php';
require_once '../../../backend/models/Equipo.php';
require_once '../../../backend/models/Cancha.php';
require_once '../../../backend/models/Jugador.php';

// Obtener todos los equipos
$equipoModel = new Equipo();
$equipos = $equipoModel->obtenerTodos();

// Obtener todas las canchas
$canchaModel = new Cancha();
$canchas = $canchaModel->obtenerTodas();

// Verificar si se está editando un partido existente
$esEdicion = isset($_GET['id']) && !empty($_GET['id']);
$partido = null;
$titulo_accion = 'Programar Nuevo Partido';
$accion = 'crear';
$jugadoresLocal = [];
$jugadoresVisitante = [];
$detalleGoles = [];
$detalleAsistencias = [];
$detalleFaltas = [];

if ($esEdicion) {
    $partidoId = intval($_GET['id']);
    $partidoModel = new Partido();
    $partido = $partidoModel->obtenerPorId($partidoId);
    
    if ($partido) {
        $titulo_accion = 'Editar Partido';
        $accion = 'actualizar';
        // Obtener detalles de goles, asistencias y faltas SIEMPRE
        $detalleGoles = $partidoModel->obtenerDetalleGoles($partido['cod_par']);
        $detalleAsistencias = $partidoModel->obtenerDetalleAsistencias($partido['cod_par']);
        $detalleFaltas = $partidoModel->obtenerDetalleFaltas($partido['cod_par']);
        // Obtener jugadores de ambos equipos
        $jugadorModel = new Jugador();
        $jugadoresLocal = $jugadorModel->obtenerPorEquipo($partido['local_id']);
        $jugadoresVisitante = $jugadorModel->obtenerPorEquipo($partido['visitante_id']);
    } else {
        // Si no se encuentra el partido, redireccionar a la lista
        $_SESSION['error_partidos'] = 'El partido solicitado no existe';
        header('Location: ./partidos.php');
        exit;
    }
}
?>

<!-- Incluir los estilos específicos para esta página -->
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="../../assets/css/admin_crud.css">
<link rel="stylesheet" href="../../assets/css/admin_partidos.css">
<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="container">
    <div class="breadcrumb">
        <a href="./partidos.php">
            <i class="fas fa-arrow-left"></i> Volver a Partidos
        </a>
    </div>
    
    <h1 class="page-title"><?php echo $titulo_accion; ?></h1>
    
    <div class="section-intro">
        <p>Completa el formulario para <?php echo $esEdicion ? 'actualizar' : 'agregar'; ?> el partido</p>
    </div>
    
    <?php 
    // Mostrar notificaciones si las hay
    mostrarNotificaciones(['error_partidos', 'exito_partidos']);
    ?>
    
    <div class="admin-form-container">
        <form action="../../../backend/controllers/admin/partidos_controller.php" method="POST" class="admin-form" id="partido-form">
            <input type="hidden" name="accion" value="<?php echo $accion; ?>">
            
            <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?php echo $partido['cod_par']; ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group form-col">
                    <label for="fecha">Fecha <span class="required">*</span></label>
                    <input type="date" id="fecha" name="fecha" required
                        value="<?php echo $esEdicion ? htmlspecialchars($partido['fecha']) : date('Y-m-d'); ?>">
                    <div class="form-error" id="error-fecha"></div>
                </div>
                
                <div class="form-group form-col">
                    <label for="hora">Hora <span class="required">*</span></label>
                    <input type="time" id="hora" name="hora" required
                        value="<?php echo $esEdicion ? htmlspecialchars($partido['hora']) : date('H:i'); ?>">
                    <div class="form-error" id="error-hora"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="cancha_id">Cancha <span class="required">*</span></label>
                <select id="cancha_id" name="cancha_id" required>
                    <?php if (!$esEdicion || empty($partido['cod_cancha'])): ?>
                        <option value="">Selecciona una cancha</option>
                    <?php endif; ?>
                    <?php foreach ($canchas as $cancha): ?>
                    <?php
                        $canchaVal = trim((string)$cancha['cod_cancha']);
                        $partidoCancha = $esEdicion ? trim((string)$partido['cod_cancha']) : '';
                        $selected = ($esEdicion && $partidoCancha === $canchaVal) ? 'selected' : '';
                    ?>
                    <option value="<?php echo htmlspecialchars($canchaVal); ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($cancha['nombre']) . ' (' . htmlspecialchars($cancha['ciudad_nombre']) . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-error" id="error-cancha"></div>
            </div>
            
            <?php if (!$esEdicion): ?>
            <!-- Selección de equipos (solo para nuevos partidos) -->
            <div class="form-group">
                <label for="equipo_local">Equipo Local <span class="required">*</span></label>
                <select id="equipo_local" name="equipo_local" required>
                    <option value="">Selecciona un equipo</option>
                    <?php foreach ($equipos as $equipo): ?>
                    <option value="<?php echo $equipo['cod_equ']; ?>">
                        <?php echo htmlspecialchars($equipo['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-error" id="error-equipo-local"></div>
            </div>
            
            <div class="form-group">
                <label for="equipo_visitante">Equipo Visitante <span class="required">*</span></label>
                <select id="equipo_visitante" name="equipo_visitante" required>
                    <option value="">Selecciona un equipo</option>
                    <?php foreach ($equipos as $equipo): ?>
                    <option value="<?php echo $equipo['cod_equ']; ?>">
                        <?php echo htmlspecialchars($equipo['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-error" id="error-equipo-visitante"></div>
            </div>
            <?php else: ?>
            <!-- Mostrar equipos (para partidos en edición) -->
            <div class="partido-equipos">
                <div class="equipo local">
                    <h3>Local</h3>
                    <div class="equipo-info">
                        <img src="<?php echo (!empty($partido['local_escudo'])) ? 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']) : '../../assets/images/team.png'; ?>" 
                            alt="<?php echo $partido['local_nombre']; ?>" class="equipo-logo">
                        <div class="equipo-nombre">
                            <?php echo $partido['local_nombre']; ?>
                        </div>
                        <?php if ($partido['estado'] === 'finalizado'): ?>
                        <div class="equipo-goles">
                            <input type="number" name="goles_local" min="0" value="<?php echo $partido['goles_local']; ?>" class="goles-input">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="versus" style="display: flex; flex-direction: column; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 40px; margin-bottom: 2px;">
                        <span id="marcador-local" class="marcador" style="font-size:2.5em; color:#1976d2; font-weight:bold;">0</span>
                        <span style="font-size: 2em; font-weight: bold; color: #666;">vs</span>
                        <span id="marcador-visitante" class="marcador" style="font-size:2.5em; color:#1976d2; font-weight:bold;">0</span>
                    </div>
                </div>
                
                <div class="equipo visitante">
                    <h3>Visitante</h3>
                    <div class="equipo-info">
                        <img src="<?php echo (!empty($partido['visitante_escudo'])) ? 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']) : '../../assets/images/team.png'; ?>" 
                            alt="<?php echo $partido['visitante_nombre']; ?>" class="equipo-logo">
                        <div class="equipo-nombre">
                            <?php echo $partido['visitante_nombre']; ?>
                        </div>
                        <?php if ($partido['estado'] === 'finalizado'): ?>
                        <div class="equipo-goles">
                            <input type="number" name="goles_visitante" min="0" value="<?php echo $partido['goles_visitante']; ?>" class="goles-input">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Estado del partido -->
            <div class="form-group">
                <label for="estado">Estado del Partido</label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="programado" <?php echo ($partido && $partido['estado'] == 'programado') ? 'selected' : ''; ?>>Programado</option>
                    <option value="finalizado" <?php echo ($partido && $partido['estado'] == 'finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                </select>
            </div>

            <!-- Fase del partido -->
            <div class="form-group">
                <label for="fase">Fase del Torneo</label>
                <select name="fase" id="fase" class="form-control" required>
                    <option value="cuartos" <?php echo ($partido && $partido['fase'] == 'cuartos') ? 'selected' : ''; ?>>Cuartos de Final</option>
                    <option value="semis" <?php echo ($partido && $partido['fase'] == 'semis') ? 'selected' : ''; ?>>Semifinales</option>
                    <option value="final" <?php echo ($partido && $partido['fase'] == 'final') ? 'selected' : ''; ?>>Final</option>
                </select>
            </div>
            <?php endif; ?>
            
            <?php if ($esEdicion): ?>
            <div class="admin-stats-section" style="margin-top:30px; margin-bottom:30px;">
                <h2 style="font-size:1.3em; color:#1976d2; margin-bottom:18px;">Estadísticas del Partido</h2>
                <!-- Goles -->
                <div class="admin-table-container">
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 8px;">
                        <button type="button" class="btn btn-primary btn-sm" id="btn-add-gol">Agregar</button>
                    </div>
                    <h3 style="font-size:1.1em; color:#333;">Goles</h3>
                    <table class="admin-table" id="tabla-goles">
                        <thead>
                            <tr><th>Minuto</th><th>Jugador</th><th>Equipo</th><th>Tipo</th><th>Acciones</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <!-- Asistencias -->
                <div class="admin-table-container">
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 8px;">
                        <button type="button" class="btn btn-primary btn-sm" id="btn-add-asistencia">Agregar</button>
                    </div>
                    <h3 style="font-size:1.1em; color:#333;">Asistencias</h3>
                    <table class="admin-table" id="tabla-asistencias">
                        <thead>
                            <tr><th>Minuto</th><th>Jugador</th><th>Equipo</th><th>Acciones</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <!-- Faltas -->
                <div class="admin-table-container">
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 8px;">
                        <button type="button" class="btn btn-primary btn-sm" id="btn-add-falta">Agregar</button>
                    </div>
                    <h3 style="font-size:1.1em; color:#333;">Tarjetas</h3>
                    <table class="admin-table" id="tabla-faltas">
                        <thead>
                            <tr><th>Minuto</th><th>Jugador</th><th>Equipo</th><th>Tipo</th><th>Acciones</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='./partidos.php'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Partido</button>
            </div>
        </form>
        
        <!-- Modales para agregar/editar/eliminar goles, asistencias y faltas -->
        <?php if ($esEdicion): ?>
        <div id="modal-gol" class="modal">
            <div class="modal-content">
                <span class="modal-close" onclick="closeModal('modal-gol')">&times;</span>
                <form id="form-add-gol" onsubmit="return false;">
                    <input type="hidden" name="partido_id" value="<?= htmlspecialchars($partido['cod_par']) ?>">
                    <label>Jugador</label>
                    <select name="jugador_id" required>
                        <optgroup label="<?php echo $partido['local_nombre']; ?>">
                            <?php foreach ($jugadoresLocal as $jugador): ?>
                                <option value="<?= $jugador['cod_jug'] ?>"><?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos'] . ' (#' . $jugador['dorsal'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="<?php echo $partido['visitante_nombre']; ?>">
                            <?php foreach ($jugadoresVisitante as $jugador): ?>
                                <option value="<?= $jugador['cod_jug'] ?>"><?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos'] . ' (#' . $jugador['dorsal'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
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
                    <input type="hidden" name="partido_id" value="<?= htmlspecialchars($partido['cod_par']) ?>">
                    <label>Jugador</label>
                    <select name="jugador_id" required>
                        <optgroup label="<?php echo $partido['local_nombre']; ?>">
                            <?php foreach ($jugadoresLocal as $jugador): ?>
                                <option value="<?= $jugador['cod_jug'] ?>"><?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos'] . ' (#' . $jugador['dorsal'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="<?php echo $partido['visitante_nombre']; ?>">
                            <?php foreach ($jugadoresVisitante as $jugador): ?>
                                <option value="<?= $jugador['cod_jug'] ?>"><?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos'] . ' (#' . $jugador['dorsal'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
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
                    <input type="hidden" name="partido_id" value="<?= htmlspecialchars($partido['cod_par']) ?>">
                    <label>Jugador</label>
                    <select name="jugador_id" required>
                        <optgroup label="<?php echo $partido['local_nombre']; ?>">
                            <?php foreach ($jugadoresLocal as $jugador): ?>
                                <option value="<?= $jugador['cod_jug'] ?>"><?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos'] . ' (#' . $jugador['dorsal'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="<?php echo $partido['visitante_nombre']; ?>">
                            <?php foreach ($jugadoresVisitante as $jugador): ?>
                                <option value="<?= $jugador['cod_jug'] ?>"><?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos'] . ' (#' . $jugador['dorsal'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
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
        <!-- Modales de edición y eliminación (rellenados por JS) -->
        <div id="modal-edit-gol" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-edit-gol')">&times;</span><form id="form-edit-gol"></form></div></div>
        <div id="modal-delete-gol" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-delete-gol')">&times;</span><form id="form-delete-gol"></form></div></div>
        <div id="modal-edit-asistencia" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-edit-asistencia')">&times;</span><form id="form-edit-asistencia"></form></div></div>
        <div id="modal-delete-asistencia" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-delete-asistencia')">&times;</span><form id="form-delete-asistencia"></form></div></div>
        <div id="modal-edit-falta" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-edit-falta')">&times;</span><form id="form-edit-falta"></form></div></div>
        <div id="modal-delete-falta" class="modal"><div class="modal-content"><span class="modal-close" onclick="closeModal('modal-delete-falta')">&times;</span><form id="form-delete-falta"></form></div></div>
        <?php endif; ?>
    </div>
</div>

<style>
.partido-equipos {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.equipo {
    flex: 1;
    text-align: center;
}

.equipo-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.equipo-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    margin-bottom: 10px;
}

.equipo-nombre {
    font-weight: bold;
    font-size: 1.1em;
}

.versus {
    font-size: 1.5em;
    font-weight: bold;
    margin: 0 20px;
    color: #666;
}

.goles-input {
    width: 60px;
    text-align: center;
    font-size: 1.2em;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.partido-detalles {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.detalle-seccion {
    margin-bottom: 30px;
}

.detalle-seccion h3 {
    margin-bottom: 15px;
    color: #333;
    border-bottom: 2px solid #ddd;
    padding-bottom: 5px;
}

.gol-item, .asistencia-item, .falta-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: white;
    margin-bottom: 10px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.minuto {
    font-weight: bold;
    color: #666;
    margin-right: 10px;
}

.jugador {
    font-weight: 500;
}

.equipo {
    color: #666;
    font-size: 0.9em;
}

.tipo {
    margin-left: 10px;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8em;
}

.btn-add {
    width: 100%;
    padding: 10px;
    background: #e9ecef;
    border: 1px dashed #ced4da;
    border-radius: 4px;
    color: #495057;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-add:hover {
    background: #dee2e6;
    border-color: #adb5bd;
}

.btn-edit, .btn-delete {
    padding: 5px 10px;
    border: none;
    background: none;
    cursor: pointer;
    color: #666;
    transition: color 0.3s ease;
}

.btn-edit:hover {
    color: #007bff;
}

.btn-delete:hover {
    color: #dc3545;
}

.stats-actions {
    display: flex;
    gap: 5px;
    justify-content: center;
}

.btn-edit, .btn-delete {
    padding: 5px;
    border: none;
    background: none;
    cursor: pointer;
    color: #666;
    transition: color 0.3s ease;
}

.btn-edit:hover {
    color: #007bff;
}

.btn-delete:hover {
    color: #dc3545;
}

.tarjeta-amarilla {
    background-color: #ffc107;
    color: #000;
    padding: 2px 8px;
    border-radius: 4px;
}

.tarjeta-roja {
    background-color: #dc3545;
    color: #fff;
    padding: 2px 8px;
    border-radius: 4px;
}

.tarjeta-normal {
    background-color: #6c757d;
    color: #fff;
    padding: 2px 8px;
    border-radius: 4px;
}

.modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100vw; height: 100vh; overflow: auto; background: rgba(0,0,0,0.4); }
.modal .modal-content { background: #fff; margin: 10% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 400px; position: relative; }
.modal .modal-close { position: absolute; top: 10px; right: 15px; font-size: 1.5em; cursor: pointer; }
.modal[style*='display: block'] { display: block !important; }
</style>

<?php if ($esEdicion): ?>
<script>
window.initialStats = {
    goles: <?php echo json_encode($detalleGoles ?? []); ?>,
    asistencias: <?php echo json_encode($detalleAsistencias ?? []); ?>,
    faltas: <?php echo json_encode($detalleFaltas ?? []); ?>
};

// Preparo un diccionario de jugadores con sus dorsales para el partido
window.jugadoresDorsales = {};
<?php foreach ($jugadoresLocal as $jugador): ?>
window.jugadoresDorsales["<?php echo $jugador['cod_jug']; ?>"] = { nombre: "<?php echo addslashes($jugador['nombres'] . ' ' . $jugador['apellidos']); ?>", dorsal: "<?php echo $jugador['dorsal']; ?>", equipo: "<?php echo addslashes($partido['local_nombre']); ?>" };
<?php endforeach; ?>
<?php foreach ($jugadoresVisitante as $jugador): ?>
window.jugadoresDorsales["<?php echo $jugador['cod_jug']; ?>"] = { nombre: "<?php echo addslashes($jugador['nombres'] . ' ' . $jugador['apellidos']); ?>", dorsal: "<?php echo $jugador['dorsal']; ?>", equipo: "<?php echo addslashes($partido['visitante_nombre']); ?>" };
<?php endforeach; ?>
</script>
<?php endif; ?>
<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_partidos.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
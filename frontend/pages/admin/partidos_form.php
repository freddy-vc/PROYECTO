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
        
        // Si el partido está finalizado, obtener todos los detalles
        if ($partido['estado'] === 'finalizado') {
            $detalleGoles = $partido['detalle_goles'] ?? [];
            $detalleAsistencias = $partido['detalle_asistencias'] ?? [];
            $detalleFaltas = $partido['detalle_faltas'] ?? [];
        }
        
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
                    <option value="">Selecciona una cancha</option>
                    <?php foreach ($canchas as $cancha): ?>
                    <option value="<?php echo $cancha['cod_cancha']; ?>" 
                        <?php echo ($esEdicion && $partido['cod_cancha'] == $cancha['cod_cancha']) ? 'selected' : ''; ?>>
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
                        <div class="equipo-nombre"><?php echo $partido['local_nombre']; ?></div>
                        <?php if ($partido['estado'] === 'finalizado'): ?>
                        <div class="equipo-goles"><?php echo $partido['goles_local']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="versus">vs</div>
                
                <div class="equipo visitante">
                    <h3>Visitante</h3>
                    <div class="equipo-info">
                        <img src="<?php echo (!empty($partido['visitante_escudo'])) ? 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']) : '../../assets/images/team.png'; ?>" 
                            alt="<?php echo $partido['visitante_nombre']; ?>" class="equipo-logo">
                        <div class="equipo-nombre"><?php echo $partido['visitante_nombre']; ?></div>
                        <?php if ($partido['estado'] === 'finalizado'): ?>
                        <div class="equipo-goles"><?php echo $partido['goles_visitante']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Estado del partido (solo para partidos en edición) -->
            <div class="form-group">
                <label for="estado">Estado del Partido <span class="required">*</span></label>
                <select id="estado" name="estado" required>
                    <option value="programado" <?php echo ($esEdicion && $partido['estado'] === 'programado') ? 'selected' : ''; ?>>Programado</option>
                    <option value="finalizado" <?php echo ($esEdicion && $partido['estado'] === 'finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                </select>
                <div class="form-error" id="error-estado"></div>
            </div>
            <?php endif; ?>
            
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='./partidos.php'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Partido</button>
            </div>
        </form>
        
        <?php if ($esEdicion && $partido['estado'] === 'finalizado'): ?>
        <!-- Sección para registrar estadísticas del partido -->
        <div class="partido-stats">
            <h3>Estadísticas del Partido</h3>
            
            <div class="tabs">
                <button class="tab-btn active" data-tab="goles">Goles</button>
                <button class="tab-btn" data-tab="asistencias">Asistencias</button>
                <button class="tab-btn" data-tab="faltas">Faltas</button>
            </div>
            
            <div class="tab-content active" id="tab-goles">
                <h4>Goles</h4>
                
                <!-- Formulario para registrar gol -->
                <form action="../../../backend/controllers/admin/partidos_controller.php" method="POST" class="stats-form" id="gol-form">
                    <input type="hidden" name="accion" value="registrar_gol">
                    <input type="hidden" name="partido_id" value="<?php echo $partido['cod_par']; ?>">
                    
                    <div class="form-row">
                        <div class="form-group form-col">
                            <label for="jugador_gol">Jugador</label>
                            <select id="jugador_gol" name="jugador_id" required>
                                <option value="">Selecciona un jugador</option>
                                <optgroup label="<?php echo $partido['local_nombre']; ?>">
                                    <?php foreach ($jugadoresLocal as $jugador): ?>
                                    <option value="<?php echo $jugador['cod_jug']; ?>">
                                        <?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos']); ?> (#<?php echo $jugador['num_camiseta']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="<?php echo $partido['visitante_nombre']; ?>">
                                    <?php foreach ($jugadoresVisitante as $jugador): ?>
                                    <option value="<?php echo $jugador['cod_jug']; ?>">
                                        <?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos']); ?> (#<?php echo $jugador['num_camiseta']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        
                        <div class="form-group form-col">
                            <label for="minuto_gol">Minuto</label>
                            <input type="number" id="minuto_gol" name="minuto" min="1" max="90" required>
                        </div>
                        
                        <div class="form-group form-col">
                            <label for="tipo_gol">Tipo</label>
                            <select id="tipo_gol" name="tipo_gol" required>
                                <option value="normal">Normal</option>
                                <option value="penal">Penal</option>
                                <option value="autogol">Autogol</option>
                            </select>
                        </div>
                        
                        <div class="form-group form-col">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-sm">Registrar Gol</button>
                        </div>
                    </div>
                </form>
                
                <!-- Lista de goles -->
                <div class="stats-list">
                    <?php if (!empty($detalleGoles)): ?>
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Minuto</th>
                                <th>Jugador</th>
                                <th>Equipo</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalleGoles as $gol): ?>
                            <tr>
                                <td><?php echo $gol['minuto']; ?>'</td>
                                <td><?php echo $gol['nombres'] . ' ' . $gol['apellidos']; ?> (#<?php echo $gol['dorsal']; ?>)</td>
                                <td><?php echo $gol['equipo']; ?></td>
                                <td>
                                    <?php 
                                    switch($gol['tipo']) {
                                        case 'normal':
                                            echo 'Gol';
                                            break;
                                        case 'penal':
                                            echo 'Penal';
                                            break;
                                        case 'autogol':
                                            echo 'Autogol';
                                            break;
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="no-stats">No hay goles registrados</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="tab-content" id="tab-asistencias">
                <h4>Asistencias</h4>
                
                <!-- Formulario para registrar asistencia -->
                <form action="../../../backend/controllers/admin/partidos_controller.php" method="POST" class="stats-form" id="asistencia-form">
                    <input type="hidden" name="accion" value="registrar_asistencia">
                    <input type="hidden" name="partido_id" value="<?php echo $partido['cod_par']; ?>">
                    
                    <div class="form-row">
                        <div class="form-group form-col">
                            <label for="jugador_asistencia">Jugador</label>
                            <select id="jugador_asistencia" name="jugador_id" required>
                                <option value="">Selecciona un jugador</option>
                                <optgroup label="<?php echo $partido['local_nombre']; ?>">
                                    <?php foreach ($jugadoresLocal as $jugador): ?>
                                    <option value="<?php echo $jugador['cod_jug']; ?>">
                                        <?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos']); ?> (#<?php echo $jugador['num_camiseta']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="<?php echo $partido['visitante_nombre']; ?>">
                                    <?php foreach ($jugadoresVisitante as $jugador): ?>
                                    <option value="<?php echo $jugador['cod_jug']; ?>">
                                        <?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos']); ?> (#<?php echo $jugador['num_camiseta']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        
                        <div class="form-group form-col">
                            <label for="minuto_asistencia">Minuto</label>
                            <input type="number" id="minuto_asistencia" name="minuto" min="1" max="90" required>
                        </div>
                        
                        <div class="form-group form-col">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-sm">Registrar Asistencia</button>
                        </div>
                    </div>
                </form>
                
                <!-- Lista de asistencias -->
                <div class="stats-list">
                    <?php if (!empty($detalleAsistencias)): ?>
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Minuto</th>
                                <th>Jugador</th>
                                <th>Equipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalleAsistencias as $asistencia): ?>
                            <tr>
                                <td><?php echo $asistencia['minuto']; ?>'</td>
                                <td><?php echo $asistencia['nombres'] . ' ' . $asistencia['apellidos']; ?> (#<?php echo $asistencia['dorsal']; ?>)</td>
                                <td><?php echo $asistencia['equipo']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="no-stats">No hay asistencias registradas</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="tab-content" id="tab-faltas">
                <h4>Tarjetas</h4>
                
                <!-- Formulario para registrar falta -->
                <form action="../../../backend/controllers/admin/partidos_controller.php" method="POST" class="stats-form" id="falta-form">
                    <input type="hidden" name="accion" value="registrar_falta">
                    <input type="hidden" name="partido_id" value="<?php echo $partido['cod_par']; ?>">
                    
                    <div class="form-row">
                        <div class="form-group form-col">
                            <label for="jugador_falta">Jugador</label>
                            <select id="jugador_falta" name="jugador_id" required>
                                <option value="">Selecciona un jugador</option>
                                <optgroup label="<?php echo $partido['local_nombre']; ?>">
                                    <?php foreach ($jugadoresLocal as $jugador): ?>
                                    <option value="<?php echo $jugador['cod_jug']; ?>">
                                        <?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos']); ?> (#<?php echo $jugador['num_camiseta']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="<?php echo $partido['visitante_nombre']; ?>">
                                    <?php foreach ($jugadoresVisitante as $jugador): ?>
                                    <option value="<?php echo $jugador['cod_jug']; ?>">
                                        <?php echo htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos']); ?> (#<?php echo $jugador['num_camiseta']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        
                        <div class="form-group form-col">
                            <label for="minuto_falta">Minuto</label>
                            <input type="number" id="minuto_falta" name="minuto" min="1" max="90" required>
                        </div>
                        
                        <div class="form-group form-col">
                            <label for="tipo_falta">Tarjeta</label>
                            <select id="tipo_falta" name="tipo_falta" required>
                                <option value="amarilla">Amarilla</option>
                                <option value="roja">Roja</option>
                            </select>
                        </div>
                        
                        <div class="form-group form-col">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-sm">Registrar Tarjeta</button>
                        </div>
                    </div>
                </form>
                
                <!-- Lista de faltas -->
                <div class="stats-list">
                    <?php if (!empty($detalleFaltas)): ?>
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Minuto</th>
                                <th>Jugador</th>
                                <th>Equipo</th>
                                <th>Tarjeta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalleFaltas as $falta): ?>
                            <tr>
                                <td><?php echo $falta['minuto']; ?>'</td>
                                <td><?php echo $falta['nombres'] . ' ' . $falta['apellidos']; ?> (#<?php echo $falta['dorsal']; ?>)</td>
                                <td><?php echo $falta['equipo']; ?></td>
                                <td>
                                    <?php if ($falta['tipo_falta'] === 'amarilla'): ?>
                                    <span class="tarjeta-amarilla">Amarilla</span>
                                    <?php else: ?>
                                    <span class="tarjeta-roja">Roja</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="no-stats">No hay tarjetas registradas</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_partidos.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
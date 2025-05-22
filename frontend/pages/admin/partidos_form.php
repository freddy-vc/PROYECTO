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
                        <div class="equipo-goles">
                            <input type="number" name="goles_local" min="0" value="<?php echo $partido['goles_local']; ?>" class="goles-input">
                        </div>
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
                <button class="tab-btn" data-tab="faltas">Tarjetas</button>
            </div>
            
            <div class="tab-content active" id="tab-goles">
                <h4>Goles</h4>
                
                <!-- Formulario para registrar/editar gol -->
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
                            <input type="number" id="minuto_gol" name="minuto" min="0" max="50" required>
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
                                <th>Acciones</th>
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
                                <td class="stats-actions">
                                    <button type="button" class="btn-edit" onclick="editarGol(<?php echo htmlspecialchars(json_encode($gol)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="../../../backend/controllers/admin/partidos_controller.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="accion" value="eliminar_gol">
                                        <input type="hidden" name="partido_id" value="<?php echo $partido['cod_par']; ?>">
                                        <input type="hidden" name="gol_id" value="<?php echo $gol['cod_gol']; ?>">
                                        <button type="submit" class="btn-delete" onclick="return confirm('¿Estás seguro de eliminar este gol?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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
                
                <!-- Formulario para registrar/editar asistencia -->
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
                            <input type="number" id="minuto_asistencia" name="minuto" min="0" max="50" required>
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
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalleAsistencias as $asistencia): ?>
                            <tr>
                                <td><?php echo $asistencia['minuto']; ?>'</td>
                                <td><?php echo $asistencia['nombres'] . ' ' . $asistencia['apellidos']; ?> (#<?php echo $asistencia['dorsal']; ?>)</td>
                                <td><?php echo $asistencia['equipo']; ?></td>
                                <td class="stats-actions">
                                    <button type="button" class="btn-edit" onclick="editarAsistencia(<?php echo htmlspecialchars(json_encode($asistencia)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="../../../backend/controllers/admin/partidos_controller.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="accion" value="eliminar_asistencia">
                                        <input type="hidden" name="partido_id" value="<?php echo $partido['cod_par']; ?>">
                                        <input type="hidden" name="asistencia_id" value="<?php echo $asistencia['cod_asis']; ?>">
                                        <button type="submit" class="btn-delete" onclick="return confirm('¿Estás seguro de eliminar esta asistencia?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
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
                
                <!-- Formulario para registrar/editar falta -->
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
                            <input type="number" id="minuto_falta" name="minuto" min="0" max="50" required>
                        </div>
                        
                        <div class="form-group form-col">
                            <label for="tipo_falta">Tarjeta</label>
                            <select id="tipo_falta" name="tipo_falta" required>
                                <option value="amarilla">Amarilla</option>
                                <option value="roja">Roja</option>
                                <option value="normal">Normal</option>
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
                                <th>Acciones</th>
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
                                    <?php elseif ($falta['tipo_falta'] === 'roja'): ?>
                                    <span class="tarjeta-roja">Roja</span>
                                    <?php else: ?>
                                    <span class="tarjeta-normal">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="stats-actions">
                                    <button type="button" class="btn-edit" onclick="editarFalta(<?php echo htmlspecialchars(json_encode($falta)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="../../../backend/controllers/admin/partidos_controller.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="accion" value="eliminar_falta">
                                        <input type="hidden" name="partido_id" value="<?php echo $partido['cod_par']; ?>">
                                        <input type="hidden" name="falta_id" value="<?php echo $falta['cod_falta']; ?>">
                                        <button type="submit" class="btn-delete" onclick="return confirm('¿Estás seguro de eliminar esta tarjeta?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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
</style>

<script>
function editarGol(gol) {
    // Llenar el formulario de gol con los datos existentes
    document.getElementById('jugador_gol').value = gol.cod_jug;
    document.getElementById('minuto_gol').value = gol.minuto;
    document.getElementById('tipo_gol').value = gol.tipo;
    
    // Cambiar el formulario a modo edición
    const form = document.getElementById('gol-form');
    form.querySelector('input[name="accion"]').value = 'actualizar_gol';
    form.innerHTML += `<input type="hidden" name="gol_id" value="${gol.cod_gol}">`;
    
    // Cambiar el texto del botón
    form.querySelector('button[type="submit"]').textContent = 'Actualizar Gol';
}

function editarAsistencia(asistencia) {
    // Llenar el formulario de asistencia con los datos existentes
    document.getElementById('jugador_asistencia').value = asistencia.cod_jug;
    document.getElementById('minuto_asistencia').value = asistencia.minuto;
    
    // Cambiar el formulario a modo edición
    const form = document.getElementById('asistencia-form');
    form.querySelector('input[name="accion"]').value = 'actualizar_asistencia';
    form.innerHTML += `<input type="hidden" name="asistencia_id" value="${asistencia.cod_asis}">`;
    
    // Cambiar el texto del botón
    form.querySelector('button[type="submit"]').textContent = 'Actualizar Asistencia';
}

function editarFalta(falta) {
    // Llenar el formulario de falta con los datos existentes
    document.getElementById('jugador_falta').value = falta.cod_jug;
    document.getElementById('minuto_falta').value = falta.minuto;
    document.getElementById('tipo_falta').value = falta.tipo_falta;
    
    // Cambiar el formulario a modo edición
    const form = document.getElementById('falta-form');
    form.querySelector('input[name="accion"]').value = 'actualizar_falta';
    form.innerHTML += `<input type="hidden" name="falta_id" value="${falta.cod_falta}">`;
    
    // Cambiar el texto del botón
    form.querySelector('button[type="submit"]').textContent = 'Actualizar Tarjeta';
}

// Función para resetear los formularios después de enviar
document.querySelectorAll('.stats-form').forEach(form => {
    form.addEventListener('submit', function() {
        setTimeout(() => {
            this.reset();
            this.querySelector('input[name="accion"]').value = this.querySelector('input[name="accion"]').value.replace('actualizar_', 'registrar_');
            this.querySelector('button[type="submit"]').textContent = this.querySelector('button[type="submit"]').textContent.replace('Actualizar', 'Registrar');
            const idInput = this.querySelector('input[name$="_id"]');
            if (idInput) idInput.remove();
        }, 100);
    });
});
</script>

<!-- Incluir los scripts específicos para esta página -->
<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin_partidos.js"></script>

<?php
// Incluir el footer
include_once '../../components/footer.php';
?> 
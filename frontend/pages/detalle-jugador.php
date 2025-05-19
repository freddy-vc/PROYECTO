<?php
// Definir variables para la página
$titulo_pagina = 'Detalle del Jugador';
$pagina_actual = 'detalle_jugador';

// Incluir el header
include_once '../components/header.php';

// Incluir el componente de notificaciones
include_once '../components/notificaciones.php';

// Verificar que se ha recibido un ID de jugador
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Si no se ha recibido un ID, redirigir a la página de jugadores
    header('Location: jugadores.php');
    exit;
}

// Obtener el ID del jugador
$jugadorId = intval($_GET['id']);
?>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Estilos específicos para esta página -->
<style>
    .player-profile-container {
        margin: 20px 0;
    }
    
    .player-header {
        display: flex;
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .player-photo {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 30px;
        border: 4px solid #fff;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
    
    .player-info {
        flex: 1;
    }
    
    .player-name {
        font-size: 2em;
        margin: 0 0 5px;
        color: #333;
    }
    
    .player-number {
        font-size: 3em;
        font-weight: bold;
        margin: 0;
        color: #007bff;
    }
    
    .player-position {
        font-size: 1.2em;
        margin: 5px 0 15px;
        color: #666;
        text-transform: uppercase;
    }
    
    .player-team {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .team-logo {
        width: 40px;
        height: 40px;
        margin-right: 10px;
    }
    
    .section-title {
        font-size: 1.5em;
        margin: 30px 0 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #007bff;
        color: #333;
    }
    
    .stats-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        flex: 1;
        min-width: 150px;
        background-color: #fff;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-value {
        font-size: 2.5em;
        font-weight: bold;
        color: #007bff;
        margin: 5px 0;
    }
    
    .stat-label {
        color: #666;
        font-size: 1em;
    }
    
    .personal-info {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .info-item {
        flex: 1;
        min-width: 200px;
    }
    
    .info-title {
        font-weight: bold;
        margin-bottom: 5px;
        color: #666;
    }
    
    .info-value {
        font-size: 1.1em;
    }
    
    .match-list {
        list-style-type: none;
        padding: 0;
    }
    
    .match-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        margin-bottom: 8px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .match-date {
        width: 100px;
        color: #666;
    }
    
    .match-teams {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .match-team {
        display: flex;
        align-items: center;
    }
    
    .match-vs {
        margin: 0 15px;
        font-weight: bold;
        color: #666;
    }
    
    .match-score {
        padding: 5px 10px;
        background-color: #f3f3f3;
        border-radius: 5px;
        font-weight: bold;
    }
    
    .match-stats {
        margin-left: 20px;
        display: flex;
        gap: 10px;
    }
    
    .match-stat {
        display: flex;
        align-items: center;
        color: #666;
    }
    
    .match-stat i {
        margin-right: 5px;
    }
    
    .yellow-card {
        color: #FFC107;
    }
    
    .red-card {
        color: #DC3545;
    }
    
    .loading-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200px;
        width: 100%;
    }
    
    .loading {
        font-size: 1.2em;
        color: #666;
    }
</style>

<div class="container player-profile-container">
    <div class="loading-container">
        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i> Cargando información del jugador...
        </div>
    </div>
    
    <div id="player-profile" style="display: none;">
        <!-- El contenido del perfil del jugador se cargará aquí mediante JavaScript -->
    </div>
</div>

<!-- Script para cargar la información del jugador -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener el ID del jugador de la URL
        const urlParams = new URLSearchParams(window.location.search);
        const jugadorId = urlParams.get('id');
        
        if (!jugadorId) {
            window.location.href = 'jugadores.php';
            return;
        }
        
        // Cargar la información del jugador
        cargarDetalleJugador(jugadorId);
    });
    
    /**
     * Función para cargar el detalle de un jugador desde el backend
     */
    function cargarDetalleJugador(jugadorId) {
        fetch(`../../backend/controllers/jugadores_controller.php?accion=detalle&id=${jugadorId}`)
            .then(response => response.json())
            .then(data => {
                if (data.estado && data.jugador) {
                    mostrarPerfilJugador(data.jugador);
                } else {
                    // Mostrar mensaje de error
                    document.querySelector('.loading').innerHTML = 
                        `<div class="alert alert-danger">No se pudo cargar la información del jugador.</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.querySelector('.loading').innerHTML = 
                    `<div class="alert alert-danger">Error al cargar la información del jugador.</div>`;
            });
    }
    
    /**
     * Función para mostrar el perfil del jugador con todos sus detalles
     */
    function mostrarPerfilJugador(jugador) {
        // Ocultar el indicador de carga
        document.querySelector('.loading-container').style.display = 'none';
        
        // Mostrar el contenedor del perfil
        const profileContainer = document.getElementById('player-profile');
        profileContainer.style.display = 'block';
        
        // Construir el HTML del perfil del jugador
        let html = `
            <!-- Cabecera con foto y datos básicos -->
            <div class="player-header">
                <img src="${jugador.foto_base64}" alt="${jugador.nombres} ${jugador.apellidos}" class="player-photo">
                
                <div class="player-info">
                    <div class="player-number">#${jugador.num_camiseta || '?'}</div>
                    <h1 class="player-name">${jugador.nombres} ${jugador.apellidos}</h1>
                    <div class="player-position">${jugador.posicion || 'Posición no especificada'}</div>
                    
                    <div class="player-team">
                        <img src="${jugador.escudo_equipo}" alt="${jugador.nombre_equipo}" class="team-logo">
                        <span>${jugador.nombre_equipo || 'Sin equipo'}</span>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas principales -->
            <h2 class="section-title">Estadísticas</h2>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value">${jugador.goles || 0}</div>
                    <div class="stat-label">Goles</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value">${jugador.asistencias || 0}</div>
                    <div class="stat-label">Asistencias</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value">${jugador.tarjetas_amarillas || 0}</div>
                    <div class="stat-label">Tarjetas Amarillas</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value">${jugador.tarjetas_rojas || 0}</div>
                    <div class="stat-label">Tarjetas Rojas</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value">${jugador.partidos ? jugador.partidos.length : 0}</div>
                    <div class="stat-label">Partidos Jugados</div>
                </div>
            </div>
            
            <!-- Información personal -->
            <h2 class="section-title">Información Personal</h2>
            <div class="personal-info">                
                <div class="info-item">
                    <div class="info-title">Equipo</div>
                    <div class="info-value">${jugador.nombre_equipo || 'Sin equipo'}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-title">Ciudad</div>
                    <div class="info-value">${jugador.ciudad_equipo || 'No especificada'}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-title">Director Técnico</div>
                    <div class="info-value">${jugador.dt_nombres && jugador.dt_apellidos ? jugador.dt_nombres + ' ' + jugador.dt_apellidos : 'No especificado'}</div>
                </div>
            </div>
        `;
        
        // Agregar sección de partidos jugados si hay datos
        if (jugador.partidos && jugador.partidos.length > 0) {
            html += `
                <h2 class="section-title">Partidos Jugados</h2>
                <ul class="match-list">
            `;
            
            jugador.partidos.forEach(partido => {
                // Encontrar los goles, asistencias y tarjetas del jugador en este partido
                const golesEnPartido = jugador.detalle_goles ? jugador.detalle_goles.filter(g => g.cod_partido == partido.cod_partido).length : 0;
                const asistenciasEnPartido = jugador.detalle_asistencias ? jugador.detalle_asistencias.filter(a => a.cod_partido == partido.cod_partido).length : 0;
                const faltasEnPartido = jugador.detalle_faltas ? jugador.detalle_faltas.filter(f => f.cod_partido == partido.cod_partido) : [];
                const tarjetasAmarillasEnPartido = faltasEnPartido.filter(f => f.tipo_tarjeta === 'amarilla').length;
                const tarjetasRojasEnPartido = faltasEnPartido.filter(f => f.tipo_tarjeta === 'roja').length;
                
                html += `
                    <li class="match-item">
                        <div class="match-date">${formatDate(partido.fecha)}</div>
                        
                        <div class="match-teams">
                            <div class="match-team">
                                <img src="${partido.local_escudo_base64}" alt="${partido.local_nombre}" class="team-logo">
                                <span>${partido.local_nombre}</span>
                            </div>
                            
                            <div class="match-vs">
                                <div class="match-score">${partido.goles_local} - ${partido.goles_visitante}</div>
                            </div>
                            
                            <div class="match-team">
                                <img src="${partido.visitante_escudo_base64}" alt="${partido.visitante_nombre}" class="team-logo">
                                <span>${partido.visitante_nombre}</span>
                            </div>
                        </div>
                        
                        <div class="match-stats">
                            ${golesEnPartido > 0 ? `<div class="match-stat"><i class="fas fa-futbol"></i> ${golesEnPartido}</div>` : ''}
                            ${asistenciasEnPartido > 0 ? `<div class="match-stat"><i class="fas fa-hands-helping"></i> ${asistenciasEnPartido}</div>` : ''}
                            ${tarjetasAmarillasEnPartido > 0 ? `<div class="match-stat"><i class="fas fa-square yellow-card"></i> ${tarjetasAmarillasEnPartido}</div>` : ''}
                            ${tarjetasRojasEnPartido > 0 ? `<div class="match-stat"><i class="fas fa-square red-card"></i> ${tarjetasRojasEnPartido}</div>` : ''}
                        </div>
                    </li>
                `;
            });
            
            html += `</ul>`;
        }
        
        // Agregar el HTML al contenedor
        profileContainer.innerHTML = html;
    }
    
    /**
     * Formatea una fecha en formato DD/MM/YYYY
     */
    function formatDate(dateStr) {
        if (!dateStr) return '';
        
        const date = new Date(dateStr);
        return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
    

</script>

<?php
// Incluir el footer
include_once '../components/footer.php';
?> 
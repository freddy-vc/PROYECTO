document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const jugadorId = urlParams.get('id');
    if (!jugadorId) {
        window.location.href = 'jugadores.php';
        return;
    }
    cargarDetalleJugador(jugadorId);
});

function cargarDetalleJugador(jugadorId) {
    fetch(`../../backend/controllers/jugadores_controller.php?accion=detalle&id=${jugadorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.estado && data.jugador) {
                mostrarPerfilJugador(data.jugador);
            } else {
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

function mostrarPerfilJugador(jugador) {
    document.querySelector('.loading-container').style.display = 'none';
    const profileContainer = document.getElementById('player-profile');
    profileContainer.style.display = 'block';
    let html = `
        <div class="player-header">
            <img src="${jugador.foto_base64 || '/PROYECTO/frontend/assets/images/player.png'}" alt="${jugador.nombres} ${jugador.apellidos}" class="player-photo">
            <div class="player-info">
                <div class="player-number">${jugador.num_camiseta || ''}</div>
                <h1 class="player-name">${jugador.nombres} ${jugador.apellidos}</h1>
                <div class="player-position">${jugador.posicion || 'Posición no especificada'}</div>
                <div class="player-team">
                    <img src="${jugador.escudo_equipo || '/PROYECTO/frontend/assets/images/team.png'}" alt="${jugador.nombre_equipo}" class="team-logo">
                    <span>${jugador.nombre_equipo || 'Sin equipo'}</span>
                </div>
            </div>
        </div>
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
        <h2 class="section-title">Equipo</h2>
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
    if (jugador.partidos && jugador.partidos.length > 0) {
        html += `
            <h2 class="section-title">Partidos Jugados</h2>
            <ul class="match-list">
        `;
        jugador.partidos.forEach(partido => {
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
    profileContainer.innerHTML = html;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
} 
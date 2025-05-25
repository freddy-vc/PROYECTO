/**
 * Función para ocultar notificaciones después de 5 segundos
 */
function ocultarNotificaciones() {
    document.querySelectorAll('.mensaje-error, .mensaje-exito').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(function() { el.style.display = 'none'; }, 500);
        }, 5000);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Ocultar notificaciones existentes
    ocultarNotificaciones();
    
    // Verificar si hay partidos finalizados
    verificarPartidosFinalizados();
    
    // Verificar si hay jugadores destacados
    verificarJugadoresDestacados();
});

/**
 * Función para verificar si hay partidos finalizados y cargarlos si existen
 */
function verificarPartidosFinalizados() {
    fetch('backend/controllers/inicio_controller.php?accion=verificar_hay_partidos_finalizados')
        .then(response => response.json())
        .then(data => {
            if (data.estado && data.hay_partidos_finalizados) {
                // Hay partidos finalizados, mostrar la sección y cargar los datos
                document.querySelector('.recent-matches').style.display = 'block';
                cargarUltimosPartidos();
            } else {
                // No hay partidos finalizados, ocultar la sección
                document.querySelector('.recent-matches').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.querySelector('.recent-matches').style.display = 'none';
        });
}

/**
 * Función para verificar si hay jugadores destacados y cargarlos si existen
 */
function verificarJugadoresDestacados() {
    fetch('backend/controllers/inicio_controller.php?accion=jugadores_destacados')
        .then(response => response.json())
        .then(data => {
            const seccionDestacados = document.getElementById('jugadores-destacados');
            
            // Verificar si hay al menos un jugador destacado (goleador o asistidor)
            if (data.estado && (data.goleador || data.asistidor)) {
                // Hay jugadores destacados, mostrar la sección
                document.querySelector('.featured-players').style.display = 'block';
                
                let html = '';
                
                // Si hay goleador, mostrar su información
                if (data.goleador) {
                    html += `
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <h3>Goleador</h3>
                            </div>
                            <div class="stat-card-body">
                                <div class="player-avatar">
                                    <img src="${data.goleador.foto_base64 || '/PROYECTO/frontend/assets/images/player.png'}" alt="${data.goleador.nombres} ${data.goleador.apellidos}">
                                </div>
                                <div class="player-info">
                                    <div class="player-name">${data.goleador.nombres} ${data.goleador.apellidos}</div>
                                    <div class="player-team">
                                        <img src="${data.goleador.escudo_equipo_base64 || '/PROYECTO/frontend/assets/images/team.png'}" alt="${data.goleador.nombre_equipo}">
                                        ${data.goleador.nombre_equipo}
                                    </div>
                                    <div class="player-stat">${data.goleador.goles} goles</div>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                // Si hay máximo asistidor, mostrar su información
                if (data.asistidor) {
                    html += `
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <h3>Asistidor</h3>
                            </div>
                            <div class="stat-card-body">
                                <div class="player-avatar">
                                    <img src="${data.asistidor.foto_base64 || '/PROYECTO/frontend/assets/images/player.png'}" alt="${data.asistidor.nombres} ${data.asistidor.apellidos}">
                                </div>
                                <div class="player-info">
                                    <div class="player-name">${data.asistidor.nombres} ${data.asistidor.apellidos}</div>
                                    <div class="player-team">
                                        <img src="${data.asistidor.escudo_equipo_base64 || '/PROYECTO/frontend/assets/images/team.png'}" alt="${data.asistidor.nombre_equipo}">
                                        ${data.asistidor.nombre_equipo}
                                    </div>
                                    <div class="player-stat">${data.asistidor.asistencias} asistencias</div>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                seccionDestacados.innerHTML = html;
            } else {
                // No hay jugadores destacados, ocultar la sección
                document.querySelector('.featured-players').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.querySelector('.featured-players').style.display = 'none';
        });
}

/**
 * Función para cargar los últimos partidos desde el servidor
 */
function cargarUltimosPartidos() {
    fetch('backend/controllers/inicio_controller.php?accion=ultimos_partidos')
        .then(response => response.json())
        .then(data => {
            const contenedor = document.getElementById('ultimos-partidos');
            
            if (data.estado && data.partidos && data.partidos.length > 0) {
                // Hay partidos para mostrar
                let html = '';
                
                data.partidos.forEach(partido => {
                    html += `
                        <div class="match-card">
                            <div class="match-date">${partido.fecha_formateada}</div>
                            <div class="match-teams">
                                <div class="team">
                                    <img src="${partido.local_escudo_base64 || './frontend/assets/images/team.png'}" alt="${partido.local_nombre}">
                                    <span>${partido.local_nombre}</span>
                                </div>
                                <div class="match-score">
                                    <span>${partido.goles_local} - ${partido.goles_visitante}</span>
                                </div>
                                <div class="team">
                                    <img src="${partido.visitante_escudo_base64 || './frontend/assets/images/team.png'}" alt="${partido.visitante_nombre}">
                                    <span>${partido.visitante_nombre}</span>
                                </div>
                            </div>
                            <a href="frontend/pages/detalle-partido.php?id=${partido.cod_par}" class="btn btn-small">Ver Detalles</a>
                        </div>
                    `;
                });
                
                contenedor.innerHTML = html;
            } else {
                // No hay partidos finalizados para mostrar
                contenedor.innerHTML = '<div class="no-matches">Aún no hay partidos finalizados para mostrar.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('ultimos-partidos').innerHTML = 
                '<div class="error-message">Error al cargar los últimos partidos.</div>';
        });
} 
document.addEventListener('DOMContentLoaded', function() {
    // Obtener el ID del equipo de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const idEquipo = urlParams.get('id');
    
    if (!idEquipo) {
        window.location.href = 'equipos.php';
        return;
    }
    
    // Cargar los datos del equipo
    cargarDetalleEquipo(idEquipo);
});

/**
 * Función para cargar los datos del equipo
 */
function cargarDetalleEquipo(idEquipo) {
    fetch(`../../backend/controllers/equipos_controller.php?accion=detalle&id=${idEquipo}`)
        .then(response => response.json())
        .then(data => {
            if (data.estado) {
                mostrarDetalleEquipo(data.equipo, data.jugadores, data.partidos);
            } else {
                document.getElementById('detalle-equipo-container').innerHTML = 
                    `<div class="no-data">${data.mensaje || 'No se pudo cargar la información del equipo.'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detalle-equipo-container').innerHTML = 
                '<div class="no-data">Error al cargar la información del equipo. Intente nuevamente.</div>';
        });
}

/**
 * Función para mostrar los detalles del equipo
 */
function mostrarDetalleEquipo(equipo, jugadores, partidos) {
    const container = document.getElementById('detalle-equipo-container');
    
    // Crear el HTML para el detalle del equipo
    let html = `
        <div class="equipo-detalle">
            <div class="equipo-header">
                <div class="equipo-logo">
                    <img src="${equipo.escudo_base64}" alt="${equipo.nombre}">
                </div>
                <h1 class="equipo-nombre">${equipo.nombre}</h1>
                <div class="equipo-ciudad">${equipo.ciudad_nombre || 'Ciudad no especificada'}</div>
            </div>
            
            <div class="equipo-content">
                <div class="equipo-info-section">
                    <h2>Información del Equipo</h2>
                    <div class="info-dt">
                        <h3>Director Técnico</h3>
                        <p>${equipo.dt_nombres && equipo.dt_apellidos ? equipo.dt_nombres + ' ' + equipo.dt_apellidos : 'No especificado'}</p>
                    </div>
                </div>
                
                <div class="tab-container">
                    <div class="tab-menu">
                        <div class="tab-item active" data-tab="jugadores">Jugadores</div>
                        <div class="tab-item" data-tab="partidos">Partidos</div>
                    </div>
                    
                    <div class="tab-content active" id="tab-jugadores">
                        ${mostrarJugadores(jugadores)}
                    </div>
                    
                    <div class="tab-content" id="tab-partidos">
                        ${mostrarPartidos(partidos, equipo.cod_equ)}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    container.classList.remove('loading-container');
    
    // Configurar los tabs
    configurarTabs();
}

/**
 * Función para configurar el comportamiento de las pestañas
 */
function configurarTabs() {
    const tabItems = document.querySelectorAll('.tab-item');
    tabItems.forEach(tab => {
        tab.addEventListener('click', function() {
            // Desactivar todos los tabs
            tabItems.forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Activar el tab seleccionado
            this.classList.add('active');
            document.getElementById(`tab-${this.dataset.tab}`).classList.add('active');
        });
    });
}

/**
 * Función para mostrar la lista de jugadores
 */
function mostrarJugadores(jugadores) {
    if (jugadores.length === 0) {
        return '<div class="no-data">No hay jugadores registrados para este equipo.</div>';
    }
    
    let html = `
        <table class="jugadores-tabla">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Dorsal</th>
                    <th>Nombre</th>
                    <th>Posición</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    jugadores.forEach(jugador => {
        html += `
            <tr>
                <td><img src="${jugador.foto_base64}" alt="${jugador.nombres}" class="jugador-foto"></td>
                <td>${jugador.dorsal || '-'}</td>
                <td>${jugador.nombres} ${jugador.apellidos}</td>
                <td>${formatearPosicion(jugador.posicion)}</td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
    `;
    
    return html;
}

/**
 * Función para formatear la posición del jugador
 */
function formatearPosicion(posicion) {
    if (!posicion) return 'No especificada';
    
    const posiciones = {
        'delantero': 'Delantero',
        'defensa': 'Defensa',
        'mediocampista': 'Mediocampista',
        'arquero': 'Arquero'
    };
    
    return posiciones[posicion] || posicion;
}

/**
 * Función para mostrar los partidos
 */
function mostrarPartidos(partidos, idEquipo) {
    if (partidos.length === 0) {
        return '<div class="no-data">No hay partidos registrados para este equipo.</div>';
    }
    
    let html = '<div class="partidos-lista">';
    
    partidos.forEach(partido => {
        const esFinalizado = partido.estado === 'finalizado';
        const esLocal = parseInt(partido.local_id) === parseInt(idEquipo);
        
        // Para cada partido finalizado, consultar los goles si no vienen ya incluidos
        if (esFinalizado && (!partido.hasOwnProperty('goles_local') || !partido.hasOwnProperty('goles_visitante'))) {
            fetch(`../../backend/controllers/partidos_controller.php?accion=obtener_goles&partido_id=${partido.cod_par}`)
                .then(response => response.json())
                .then(data => {
                    if (data.estado) {
                        partido.goles_local = data.goles_local;
                        partido.goles_visitante = data.goles_visitante;
                        
                        // Actualizar el marcador en el DOM
                        const marcadorElement = document.querySelector(`#partido-${partido.cod_par} .partido-resultado`);
                        if (marcadorElement) {
                            marcadorElement.textContent = `${data.goles_local} - ${data.goles_visitante}`;
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        html += `
            <div class="partido-card" id="partido-${partido.cod_par}">
                <div class="partido-header">
                    <span class="partido-fecha">${partido.fecha_formateada || partido.fecha}</span>
                    <span class="partido-estado ${partido.estado}">${partido.estado === 'programado' ? 'Programado' : 'Finalizado'}</span>
                </div>
                
                <div class="partido-content">
                    <div class="partido-equipos">
                        <div class="partido-equipo">
                            <img src="${partido.local_escudo_base64 || '../../frontend/assets/images/team.png'}" alt="${partido.local_nombre}">
                            <h4>${partido.local_nombre}</h4>
                        </div>
                        
                        <div class="partido-vs">
                            ${esFinalizado ? 
                                `<div class="partido-resultado">${partido.goles_local !== undefined ? partido.goles_local : '?'} - ${partido.goles_visitante !== undefined ? partido.goles_visitante : '?'}</div>` : 
                                `<div class="partido-hora">${partido.hora.substring(0, 5)}</div>`
                            }
                        </div>
                        
                        <div class="partido-equipo">
                            <img src="${partido.visitante_escudo_base64 || '../../frontend/assets/images/team.png'}" alt="${partido.visitante_nombre}">
                            <h4>${partido.visitante_nombre}</h4>
                        </div>
                    </div>
                    
                    <div class="partido-footer">
                        <a href="detalle-partido.php?id=${partido.cod_par}">Ver Detalles</a>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    return html;
} 
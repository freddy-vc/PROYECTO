document.addEventListener('DOMContentLoaded', function() {
    // Cargar todos los partidos al iniciar la página
    cargarPartidos();
    
    // Configurar filtros
    const filtros = document.querySelectorAll('.filter-btn');
    filtros.forEach(filtro => {
        filtro.addEventListener('click', function() {
            // Desactivar todos los filtros
            filtros.forEach(f => f.classList.remove('active'));
            
            // Activar el filtro seleccionado
            this.classList.add('active');
            
            // Cargar los partidos según el filtro
            cargarPartidos(this.dataset.filtro !== 'todos' ? this.dataset.filtro : null);
        });
    });
});

/**
 * Función para cargar los partidos desde el servidor
 */
function cargarPartidos(filtro = null) {
    const url = filtro ? 
        `../../backend/controllers/partidos_controller.php?accion=listar&filtro=${filtro}` : 
        '../../backend/controllers/partidos_controller.php?accion=listar';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.estado) {
                mostrarPartidos(data.partidos);
            } else {
                document.getElementById('partidos-container').innerHTML = 
                    '<div class="no-results">No se pudieron cargar los partidos.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('partidos-container').innerHTML = 
                '<div class="no-results">Error al cargar los partidos. Intente nuevamente.</div>';
        });
}

/**
 * Función para mostrar los partidos en la página
 */
function mostrarPartidos(partidos) {
    const container = document.getElementById('partidos-container');
    
    if (partidos.length === 0) {
        container.innerHTML = '<div class="no-results">No hay partidos para mostrar.</div>';
        return;
    }
    
    // Agrupar partidos por fecha
    const partidosPorFecha = agruparPorFecha(partidos);
    
    let html = '';
    
    // Recorrer las fechas ordenadas
    Object.keys(partidosPorFecha).sort().forEach(fecha => {
        const partidosFecha = partidosPorFecha[fecha];
        
        // Crear una sección para cada fecha
        html += `<div class="fecha-separador">${formatearFecha(fecha)}</div>`;
        
        // Mostrar los partidos de esa fecha
        partidosFecha.forEach(partido => {
            const esFinalizado = partido.estado === 'finalizado';
            
            html += `
                <div class="partido-card">
                    <div class="partido-header">
                        <span class="partido-fecha">${partido.fecha_formateada}</span>
                        <span class="partido-estado ${partido.estado}">${partido.estado === 'programado' ? 'Programado' : 'Finalizado'}</span>
                    </div>
                    
                    <div class="partido-content">
                        <div class="partido-equipos">
                            <div class="partido-equipo">
                                <img src="${partido.local_escudo_base64}" alt="${partido.local_nombre}">
                                <h4>${partido.local_nombre}</h4>
                            </div>
                            
                            <div class="partido-vs">
                                ${esFinalizado ? 
                                    `<div class="partido-resultado">${partido.goles_local} - ${partido.goles_visitante}</div>` : 
                                    `<div class="partido-hora">${partido.hora.substring(0, 5)}</div>`
                                }
                            </div>
                            
                            <div class="partido-equipo">
                                <img src="${partido.visitante_escudo_base64}" alt="${partido.visitante_nombre}">
                                <h4>${partido.visitante_nombre}</h4>
                            </div>
                        </div>
                        
                        <div class="partido-info">
                            <div class="partido-cancha">Cancha: ${partido.cancha}</div>
                        </div>
                        
                        <div class="partido-footer">
                            <a href="detalle-partido.php?id=${partido.cod_par}">Ver Detalles</a>
                        </div>
                    </div>
                </div>
            `;
        });
    });
    
    container.innerHTML = html;
}

/**
 * Función para agrupar partidos por fecha
 */
function agruparPorFecha(partidos) {
    const agrupados = {};
    
    partidos.forEach(partido => {
        const fecha = partido.fecha;
        
        if (!agrupados[fecha]) {
            agrupados[fecha] = [];
        }
        
        agrupados[fecha].push(partido);
    });
    
    return agrupados;
}

/**
 * Función para formatear la fecha
 */
function formatearFecha(fechaStr) {
    const fecha = new Date(fechaStr);
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return fecha.toLocaleDateString('es-ES', opciones);
} 
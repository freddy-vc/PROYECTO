document.addEventListener('DOMContentLoaded', function() {
    // Obtener ID del partido de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const partidoId = urlParams.get('id');
    
    if (!partidoId) {
        window.location.href = 'partidos.php';
        return;
    }
    
    // Cargar datos del partido
    cargarDetallePartido(partidoId);
});

/**
 * Función para cargar el detalle del partido desde el servidor
 */
function cargarDetallePartido(partidoId) {
    fetch('../../backend/controllers/partidos_controller.php?accion=detalle&id=' + partidoId)
        .then(response => response.json())
        .then(data => {
            if (data.estado && data.partido) {
                mostrarDetallePartido(data.partido);
            } else {
                document.getElementById('partido-detalle').innerHTML = 
                    '<div class="error-message">No hay detalles para mostrar.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('partido-detalle').innerHTML = 
                '<div class="error-message">No hay detalles para mostrar.</div>';
        });
}

/**
 * Función para mostrar el detalle del partido
 */
function mostrarDetallePartido(partido) {
    const container = document.getElementById('partido-detalle');
    
    // Crear elementos para mostrar la información
    let html = `
        <div class="partido-header">
            <div class="partido-fecha">${partido.fecha_formateada} - ${partido.hora}</div>
            <div class="partido-cancha">Cancha: ${partido.cancha}</div>
            <div class="partido-estado ${partido.estado === 'finalizado' ? 'finalizado' : 'programado'}">
                ${partido.estado === 'finalizado' ? 'Finalizado' : 'Programado'}
            </div>
        </div>
        
        <div class="partido-marcador">
            <div class="equipo local">
                <img src="${partido.local_escudo_base64}" alt="${partido.local_nombre}" class="equipo-logo">
                <div class="equipo-nombre">${partido.local_nombre}</div>
                ${partido.estado === 'finalizado' ? 
                    `<div class="equipo-goles">${partido.goles_local || 0}</div>` : ''}
            </div>
            
            <div class="vs">VS</div>
            
            <div class="equipo visitante">
                <img src="${partido.visitante_escudo_base64}" alt="${partido.visitante_nombre}" class="equipo-logo">
                <div class="equipo-nombre">${partido.visitante_nombre}</div>
                ${partido.estado === 'finalizado' ? 
                    `<div class="equipo-goles">${partido.goles_visitante || 0}</div>` : ''}
            </div>
        </div>
    `;
    
    // Si el partido está finalizado, mostrar detalles adicionales
    if (partido.estado === 'finalizado') {
        html += '<div class="partido-detalles">';
        
        // Sección de goles
        if (partido.detalle_goles && partido.detalle_goles.length > 0) {
            html += `
                <div class="detalle-seccion">
                    <h3>Goles</h3>
                    <ul class="detalle-lista">
            `;
            
            partido.detalle_goles.forEach(gol => {
                let tipoGol = '';
                switch(gol.tipo) {
                    case 'normal': tipoGol = ''; break;
                    case 'penal': tipoGol = ' (Penal)'; break;
                    case 'autogol': tipoGol = ' (Autogol)'; break;
                }
                
                html += `
                    <li>
                        <span class="minuto">${gol.minuto}'</span>
                        <span class="jugador">${gol.nombres} ${gol.apellidos}</span>
                        <span class="equipo">${gol.equipo}${tipoGol}</span>
                    </li>
                `;
            });
            
            html += '</ul></div>';
        }
        
        // Sección de asistencias
        if (partido.detalle_asistencias && partido.detalle_asistencias.length > 0) {
            html += `
                <div class="detalle-seccion">
                    <h3>Asistencias</h3>
                    <ul class="detalle-lista">
            `;
            
            partido.detalle_asistencias.forEach(asistencia => {
                html += `
                    <li>
                        <span class="minuto">${asistencia.minuto}'</span>
                        <span class="jugador">${asistencia.nombres} ${asistencia.apellidos}</span>
                        <span class="equipo">${asistencia.equipo}</span>
                    </li>
                `;
            });
            
            html += '</ul></div>';
        }
        
        // Sección de faltas/tarjetas
        if (partido.detalle_faltas && partido.detalle_faltas.length > 0) {
            html += `
                <div class="detalle-seccion">
                    <h3>Tarjetas</h3>
                    <ul class="detalle-lista">
            `;
            
            partido.detalle_faltas.forEach(falta => {
                let tipoTarjeta = '';
                let claseColor = '';
                
                switch(falta.tipo_falta) {
                    case 'amarilla': 
                        tipoTarjeta = 'Tarjeta Amarilla'; 
                        claseColor = 'amarilla';
                        break;
                    case 'roja': 
                        tipoTarjeta = 'Tarjeta Roja'; 
                        claseColor = 'roja';
                        break;
                    default: 
                        tipoTarjeta = 'Falta'; 
                        break;
                }
                
                html += `
                    <li>
                        <span class="minuto">${falta.minuto}'</span>
                        <span class="jugador">${falta.nombres} ${falta.apellidos}</span>
                        <span class="equipo">${falta.equipo}</span>
                        <span class="tipo-falta ${claseColor}">${tipoTarjeta}</span>
                    </li>
                `;
            });
            
            html += '</ul></div>';
        }
        
        html += '</div>';
    } else {
        // Partido aún no jugado
        html += `
            <div class="partido-programado">
                <p>Este partido aún no se ha jugado. Vuelve más tarde para ver los resultados.</p>
            </div>
        `;
    }
    
    container.innerHTML = html;
} 
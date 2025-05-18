document.addEventListener('DOMContentLoaded', function() {
    // Cargar los datos del torneo
    cargarClasificaciones();
});

/**
 * Función para cargar las clasificaciones y datos del torneo
 */
function cargarClasificaciones() {
    fetch('../../backend/controllers/clasificaciones_controller.php?accion=cuadro_torneo')
        .then(response => response.json())
        .then(data => {
            if (data.estado) {
                mostrarCuadroTorneo(data.fases, data.partidos, data.equipos);
            } else {
                document.querySelector('.bracket-container').innerHTML = 
                    `<div class="error-message">No hay clasificaciones para mostrar.</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.querySelector('.bracket-container').innerHTML = 
                '<div class="error-message">No hay clasificaciones para mostrar.</div>';
        });
}

/**
 * Función para mostrar el cuadro del torneo
 */
function mostrarCuadroTorneo(fases, partidos, equipos) {
    const container = document.querySelector('.bracket-container');
    
    // Crear estructura del cuadro
    let html = '<div class="tournament-bracket">';
    
    // Columna de cuartos de final (lado izquierdo)
    html += `
        <div class="round">
            <div class="round-title">Cuartos de final</div>
            
            ${generarPartidosFase('cuartos', partidos, equipos, 'izquierda')}
        </div>
    `;
    
    // Columna de semifinales (lado izquierdo)
    html += `
        <div class="round">
            <div class="round-title">Semifinal</div>
            
            ${generarPartidosFase('semis', partidos, equipos, 'izquierda')}
        </div>
    `;
    
    // Columna de la final
    html += `
        <div class="round">
            <div class="round-title">Final</div>
            
            <div class="match-container">
                <div class="match final-match">
                    <div class="match-title">Final</div>
                    ${generarPartidoFinal(partidos, equipos)}
                </div>
                
                ${generarCampeon(partidos, equipos)}
            </div>
        </div>
    `;
    
    // Columna de semifinales (lado derecho)
    html += `
        <div class="round">
            <div class="round-title">Semifinal</div>
            
            ${generarPartidosFase('semis', partidos, equipos, 'derecha')}
        </div>
    `;
    
    // Columna de cuartos de final (lado derecho)
    html += `
        <div class="round">
            <div class="round-title">Cuartos de final</div>
            
            ${generarPartidosFase('cuartos', partidos, equipos, 'derecha')}
        </div>
    `;
    
    html += '</div>';
    
    container.innerHTML = html;
}

/**
 * Función para generar los partidos de una fase específica
 */
function generarPartidosFase(fase, partidos, equipos, lado) {
    // Filtrar partidos de la fase
    const partidosFase = partidos.filter(partido => {
        // Verificar la fase del partido
        if (!partido.fase || partido.fase !== fase) {
            return false;
        }
        
        // Para semifinales, separar por lados
        if (fase === 'semis') {
            if (lado === 'izquierda' && partido.orden <= 1) {
                return true;
            } else if (lado === 'derecha' && partido.orden > 1) {
                return true;
            } else {
                return false;
            }
        } else if (fase === 'cuartos') {
            if (lado === 'izquierda' && partido.orden <= 2) {
                return true;
            } else if (lado === 'derecha' && partido.orden > 2) {
                return true;
            } else {
                return false;
            }
        }
        
        return true;
    });
    
    // Ordenar por orden
    partidosFase.sort((a, b) => a.orden - b.orden);
    
    let html = '';
    
    // Si no hay partidos, mostrar espacios vacíos
    if (partidosFase.length === 0) {
        if (fase === 'cuartos') {
            const numEspacios = lado === 'izquierda' ? 2 : 2;
            for (let i = 0; i < numEspacios; i++) {
                html += `
                    <div class="match-container">
                        <div class="match empty-match">
                            <div class="match-teams">
                                <div class="team">
                                    <div class="team-name">Por definir</div>
                                </div>
                                <div class="team">
                                    <div class="team-name">Por definir</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
        } else if (fase === 'semis') {
            html += `
                <div class="match-container">
                    <div class="match empty-match">
                        <div class="match-teams">
                            <div class="team">
                                <div class="team-name">Por definir</div>
                            </div>
                            <div class="team">
                                <div class="team-name">Por definir</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        return html;
    }
    
    // Generar HTML para cada partido
    partidosFase.forEach(partido => {
        const equipoLocal = equipos.find(e => e.cod_equ == partido.equ_local);
        const equipoVisitante = equipos.find(e => e.cod_equ == partido.equ_visitante);
        
        // Determinar ganador si el partido está finalizado
        let equipoGanador = null;
        if (partido.estado === 'finalizado') {
            const golesLocal = parseInt(partido.goles_local || 0);
            const golesVisitante = parseInt(partido.goles_visitante || 0);
            
            if (golesLocal > golesVisitante) {
                equipoGanador = equipoLocal;
            } else if (golesVisitante > golesLocal) {
                equipoGanador = equipoVisitante;
            }
        }
        
        html += `
            <div class="match-container">
                <div class="match">
                    <div class="match-teams">
                        <div class="team ${equipoGanador === equipoLocal ? 'winner' : ''}">
                            <img src="${equipoLocal ? equipoLocal.escudo_base64 : '../assets/images/default-team.png'}" alt="${equipoLocal ? equipoLocal.nombre : 'Por definir'}" class="team-logo">
                            <div class="team-name">${equipoLocal ? equipoLocal.nombre : 'Por definir'}</div>
                            <div class="team-score">${partido.estado === 'finalizado' ? (partido.goles_local || 0) : '-'}</div>
                        </div>
                        <div class="team ${equipoGanador === equipoVisitante ? 'winner' : ''}">
                            <img src="${equipoVisitante ? equipoVisitante.escudo_base64 : '../assets/images/default-team.png'}" alt="${equipoVisitante ? equipoVisitante.nombre : 'Por definir'}" class="team-logo">
                            <div class="team-name">${equipoVisitante ? equipoVisitante.nombre : 'Por definir'}</div>
                            <div class="team-score">${partido.estado === 'finalizado' ? (partido.goles_visitante || 0) : '-'}</div>
                        </div>
                    </div>
                    ${partido.fecha ? `<div class="match-info">${formatDate(partido.fecha)}</div>` : ''}
                </div>
            </div>
        `;
    });
    
    return html;
}

/**
 * Función para generar el partido de la final
 */
function generarPartidoFinal(partidos, equipos) {
    // Buscar el partido de la final
    const partidoFinal = partidos.find(p => p.fase === 'final');
    
    if (!partidoFinal) {
        return `
            <div class="match-teams">
                <div class="team">
                    <div class="team-name">Por definir</div>
                </div>
                <div class="team">
                    <div class="team-name">Por definir</div>
                </div>
            </div>
        `;
    }
    
    const equipoLocal = equipos.find(e => e.cod_equ == partidoFinal.equ_local);
    const equipoVisitante = equipos.find(e => e.cod_equ == partidoFinal.equ_visitante);
    
    // Determinar ganador si el partido está finalizado
    let equipoGanador = null;
    if (partidoFinal.estado === 'finalizado') {
        const golesLocal = parseInt(partidoFinal.goles_local || 0);
        const golesVisitante = parseInt(partidoFinal.goles_visitante || 0);
        
        if (golesLocal > golesVisitante) {
            equipoGanador = equipoLocal;
        } else if (golesVisitante > golesLocal) {
            equipoGanador = equipoVisitante;
        }
    }
    
    return `
        <div class="match-teams">
            <div class="team ${equipoGanador === equipoLocal ? 'winner' : ''}">
                <img src="${equipoLocal ? equipoLocal.escudo_base64 : '../assets/images/default-team.png'}" alt="${equipoLocal ? equipoLocal.nombre : 'Por definir'}" class="team-logo">
                <div class="team-name">${equipoLocal ? equipoLocal.nombre : 'Por definir'}</div>
                <div class="team-score">${partidoFinal.estado === 'finalizado' ? (partidoFinal.goles_local || 0) : '-'}</div>
            </div>
            <div class="team ${equipoGanador === equipoVisitante ? 'winner' : ''}">
                <img src="${equipoVisitante ? equipoVisitante.escudo_base64 : '../assets/images/default-team.png'}" alt="${equipoVisitante ? equipoVisitante.nombre : 'Por definir'}" class="team-logo">
                <div class="team-name">${equipoVisitante ? equipoVisitante.nombre : 'Por definir'}</div>
                <div class="team-score">${partidoFinal.estado === 'finalizado' ? (partidoFinal.goles_visitante || 0) : '-'}</div>
            </div>
        </div>
        ${partidoFinal.fecha ? `<div class="match-info">${formatDate(partidoFinal.fecha)}</div>` : ''}
    `;
}

/**
 * Función para generar el campeón del torneo
 */
function generarCampeon(partidos, equipos) {
    // Buscar el partido de la final
    const partidoFinal = partidos.find(p => p.fase === 'final');
    
    if (!partidoFinal || partidoFinal.estado !== 'finalizado') {
        return '';
    }
    
    const equipoLocal = equipos.find(e => e.cod_equ == partidoFinal.equ_local);
    const equipoVisitante = equipos.find(e => e.cod_equ == partidoFinal.equ_visitante);
    
    // Determinar ganador
    let equipoCampeon = null;
    const golesLocal = parseInt(partidoFinal.goles_local || 0);
    const golesVisitante = parseInt(partidoFinal.goles_visitante || 0);
    
    if (golesLocal > golesVisitante) {
        equipoCampeon = equipoLocal;
    } else if (golesVisitante > golesLocal) {
        equipoCampeon = equipoVisitante;
    } else {
        // En caso de empate (no debería ocurrir en una final)
        return '';
    }
    
    if (!equipoCampeon) {
        return '';
    }
    
    return `
        <div class="champion">
            <div class="champion-label">¡Campeón!</div>
            <div class="champion-team">
                <img src="${equipoCampeon.escudo_base64}" alt="${equipoCampeon.nombre}" class="champion-logo">
                <div class="champion-name">${equipoCampeon.nombre}</div>
            </div>
        </div>
    `;
}

/**
 * Función para formatear fechas
 */
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
} 
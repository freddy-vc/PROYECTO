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
                mostrarCuadroTorneo(data.fases, data.partidos, data.equipos, data.brackets);
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
function mostrarCuadroTorneo(fases, partidos, equipos, brackets) {
    const container = document.getElementById('clasificaciones-container');
    if (!container) return;
    
    // Estructura HTML inicial
    let html = '<div class="bracket-container">';
    html += '<div class="tournament-bracket">';
    
    // Crear columnas para cada fase
    html += '<div class="round"><div class="round-title">Cuartos de final</div>';
    // Generar partidos de cuartos
    html += generarPartidosFase('cuartos', partidos, equipos, brackets);
    html += '</div>';
    
    html += '<div class="round"><div class="round-title">Semifinal</div>';
    // Generar partidos de semifinal
    html += generarPartidosFase('semis', partidos, equipos, brackets);
    html += '</div>';
    
    html += '<div class="round"><div class="round-title">Final</div>';
    // Generar partido de la final
    html += generarPartidosFase('final', partidos, equipos, brackets);
    html += generarCampeon(partidos, equipos, brackets);
    html += '</div>';
    
    html += '</div></div>';
    
    container.innerHTML = html;
}

/**
 * Función para generar los partidos de una fase específica
 */
function generarPartidosFase(fase, partidos, equipos, brackets) {
    // Filtrar brackets por fase
    const bracketsEnFase = brackets ? brackets.filter(b => b.fase === fase) : [];
    
    // Ordenar por ID de bracket
    bracketsEnFase.sort((a, b) => a.bracket_id - b.bracket_id);
    
    let html = '';
    
    // Si no hay brackets para esta fase, mostrar mensaje
    if (bracketsEnFase.length === 0) {
        html += `
            <div class="match-container">
                <div class="match empty-match">
                    <div class="team">
                        <img src="../assets/images/team.png" alt="Por definir" class="team-logo">
                        <div class="team-name">Por definir</div>
                        <div class="team-score">-</div>
                    </div>
                    <div class="team">
                        <img src="../assets/images/team.png" alt="Por definir" class="team-logo">
                        <div class="team-name">Por definir</div>
                        <div class="team-score">-</div>
                    </div>
                </div>
            </div>
        `;
        return html;
    }
    
    // Generar HTML para cada bracket
    bracketsEnFase.forEach(bracket => {
        // Si el bracket tiene un partido asociado
        if (bracket.cod_par) {
            const partido = partidos.find(p => p.cod_par == bracket.cod_par);
            
            if (partido) {
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
                
                // Construir el HTML del partido
                html += `
                    <div class="match-container" data-bracket-id="${bracket.bracket_id}">
                        <div class="match">
                            <div class="team ${equipoGanador === equipoLocal ? 'winner' : ''}">
                                <img src="${equipoLocal ? equipoLocal.escudo_base64 || '../assets/images/team.png' : '../assets/images/team.png'}" 
                                     alt="${equipoLocal ? equipoLocal.nombre : 'Por definir'}" 
                                     class="team-logo">
                                <div class="team-name">${equipoLocal ? equipoLocal.nombre : 'Por definir'}</div>
                                <div class="team-score">${partido.estado === 'finalizado' ? (partido.goles_local || 0) : '-'}</div>
                            </div>
                            <div class="team ${equipoGanador === equipoVisitante ? 'winner' : ''}">
                                <img src="${equipoVisitante ? equipoVisitante.escudo_base64 || '../assets/images/team.png' : '../assets/images/team.png'}" 
                                     alt="${equipoVisitante ? equipoVisitante.nombre : 'Por definir'}" 
                                     class="team-logo">
                                <div class="team-name">${equipoVisitante ? equipoVisitante.nombre : 'Por definir'}</div>
                                <div class="team-score">${partido.estado === 'finalizado' ? (partido.goles_visitante || 0) : '-'}</div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // Partido no encontrado
                html += generarPartidoVacio(bracket.bracket_id);
            }
        } else {
            // Bracket sin partido asociado aún
            html += generarPartidoVacio(bracket.bracket_id);
        }
    });
    
    return html;
}

/**
 * Genera un partido vacío (por definir)
 */
function generarPartidoVacio(bracketId) {
    return `
        <div class="match-container" data-bracket-id="${bracketId}">
            <div class="match empty-match">
                <div class="team">
                    <img src="../assets/images/team.png" alt="Por definir" class="team-logo">
                    <div class="team-name">Por definir</div>
                    <div class="team-score">-</div>
                </div>
                <div class="team">
                    <img src="../assets/images/team.png" alt="Por definir" class="team-logo">
                    <div class="team-name">Por definir</div>
                    <div class="team-score">-</div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Función para generar el campeón
 */
function generarCampeon(partidos, equipos, brackets) {
    // Buscar el bracket de la final
    const bracketFinal = brackets ? brackets.find(b => b.fase === 'final') : null;
    
    if (!bracketFinal || !bracketFinal.cod_par) {
        return '<div class="champion"><div class="champion-placeholder">Campeón por definir</div></div>';
    }
    
    const partidoFinal = partidos.find(p => p.cod_par == bracketFinal.cod_par);
    
    if (!partidoFinal || partidoFinal.estado !== 'finalizado') {
        return '<div class="champion"><div class="champion-placeholder">Campeón por definir</div></div>';
    }
    
    // Determinar el campeón
    let campeon = null;
    const golesLocal = parseInt(partidoFinal.goles_local || 0);
    const golesVisitante = parseInt(partidoFinal.goles_visitante || 0);
    
    if (golesLocal > golesVisitante) {
        campeon = equipos.find(e => e.cod_equ == partidoFinal.equ_local);
    } else if (golesVisitante > golesLocal) {
        campeon = equipos.find(e => e.cod_equ == partidoFinal.equ_visitante);
    }
    
    if (!campeon) {
        return '<div class="champion"><div class="champion-placeholder">Campeón por definir</div></div>';
    }
    
    return `
        <div class="champion">
            <div class="champion-title">Campeón</div>
            <div class="champion-team">
                <img src="${campeon.escudo_base64 || '../assets/images/team.png'}" alt="${campeon.nombre}" class="champion-logo">
                <div class="champion-name">${campeon.nombre}</div>
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

function renderClasificaciones(clasificaciones) {
    console.log('Respuesta clasificaciones:', clasificaciones);
    const container = document.getElementById('clasificaciones-container');
    container.innerHTML = '';

    // Si no hay datos, mostrar mensaje
    if (!clasificaciones || !clasificaciones.cuartos) {
        container.innerHTML = '<div class="no-results">No hay datos de eliminatorias para mostrar.</div>';
        return;
    }

    // Cuartos, Semis, Final
    const fases = [
        { key: 'cuartos', label: 'Cuartos de final', count: 4, extraClass: '' },
        { key: 'semifinales', label: 'Semifinal', count: 2, extraClass: 'semis' },
        { key: 'final', label: 'Final', count: 1, extraClass: 'final' }
    ];

    fases.forEach((fase, idx) => {
        const col = document.createElement('div');
        col.className = 'clasificaciones-col' + (fase.extraClass ? ' ' + fase.extraClass : '');
        if (fase.key !== 'final') {
            const title = document.createElement('div');
            title.className = 'clasificaciones-fase-title';
            title.innerText = fase.label;
            col.appendChild(title);
        }
        for (let i = 0; i < fase.count; i++) {
            let match = null;
            if (fase.key === 'final') {
                match = clasificaciones.final;
            } else {
                match = clasificaciones[fase.key][i] || null;
            }
            let matchClass = 'clasificaciones-match';
            if (fase.key === 'semifinales') matchClass += ' semi-' + (i+1);
            if (fase.key === 'final') matchClass += ' final';
            const matchDiv = document.createElement('div');
            matchDiv.className = matchClass;

            if (!match) {
                matchDiv.innerHTML = `<div class=\"clasificaciones-pending\">Pendiente</div>`;
            } else {
                const local = match.local ? match.local : { nombre: 'Por definir', escudo: '../assets/images/team.png' };
                const visitante = match.visitante ? match.visitante : { nombre: 'Por definir', escudo: '../assets/images/team.png' };
                let marcador_local = match.estado === 'finalizado' ? match.goles_local : '-';
                let marcador_visitante = match.estado === 'finalizado' ? match.goles_visitante : '-';
                matchDiv.innerHTML = `
                    <div class=\"clasificaciones-teams-vertical\">
                        <div class=\"clasificaciones-team-row\">
                            <div class=\"clasificaciones-team\">
                                <img class=\"clasificaciones-escudo\" src=\"${local.escudo || '../assets/images/team.png'}\" alt=\"${local.nombre}\">
                                <span>${local.nombre}</span>
                            </div>
                            <div class=\"clasificaciones-score\">${marcador_local}</div>
                        </div>
                        <div class=\"clasificaciones-team-row\">
                            <div class=\"clasificaciones-team\">
                                <img class=\"clasificaciones-escudo\" src=\"${visitante.escudo || '../assets/images/team.png'}\" alt=\"${visitante.nombre}\">
                                <span>${visitante.nombre}</span>
                            </div>
                            <div class=\"clasificaciones-score\">${marcador_visitante}</div>
                        </div>
                    </div>
                `;
            }
            col.appendChild(matchDiv);
        }
        container.appendChild(col);
    });
} 
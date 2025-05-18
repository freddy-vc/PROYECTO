document.addEventListener('DOMContentLoaded', function() {
    // Cargar los últimos partidos
    cargarUltimosPartidos();
});

/**
 * Función para cargar los últimos partidos desde el servidor
 */
function cargarUltimosPartidos() {
    fetch('../../backend/controllers/inicio_controller.php?accion=ultimos_partidos')
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
                                    <img src="${partido.local_escudo_base64}" alt="${partido.local_nombre}">
                                    <span>${partido.local_nombre}</span>
                                </div>
                                <div class="match-score">
                                    <span>${partido.goles_local} - ${partido.goles_visitante}</span>
                                </div>
                                <div class="team">
                                    <img src="${partido.visitante_escudo_base64}" alt="${partido.visitante_nombre}">
                                    <span>${partido.visitante_nombre}</span>
                                </div>
                            </div>
                            <a href="./frontend/pages/detalle-partido.php?id=${partido.cod_par}" class="btn btn-small">Ver Detalles</a>
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
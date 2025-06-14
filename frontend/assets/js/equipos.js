document.addEventListener('DOMContentLoaded', function() {
    // Cargar equipos al iniciar la página
    cargarEquipos();
    
    // Configurar filtro de búsqueda
    const buscarInput = document.getElementById('buscar-equipo');
    buscarInput.addEventListener('input', function() {
        filtrarEquipos(this.value);
    });
});

/**
 * Función para cargar los equipos desde el servidor
 */
function cargarEquipos() {
    fetch('../../backend/controllers/equipos_controller.php?accion=listar')
        .then(response => {
            // Primero verificamos si la respuesta es correcta
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            // Intenta analizar el texto como JSON
            try {
                const data = JSON.parse(text);
                return data;
            } catch (error) {
                console.error('Error al parsear JSON:', error);
                console.log('Respuesta recibida:', text);
                throw new Error('Error al analizar la respuesta del servidor');
            }
        })
        .then(data => {
            if (data.estado) {
                mostrarEquipos(data.equipos);
            } else {
                document.getElementById('equipos-container').innerHTML = 
                    '<div class="no-results">No hay equipos para mostrar.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('equipos-container').innerHTML = 
                '<div class="no-results">Error al cargar los equipos. Por favor, intenta de nuevo más tarde.</div>';
        });
}

/**
 * Función para mostrar los equipos en la página
 */
function mostrarEquipos(equipos) {
    console.log('Equipos recibidos:', equipos);
    const container = document.getElementById('equipos-container');
    
    if (equipos.length === 0) {
        container.innerHTML = '<div class="no-results">No hay equipos para mostrar.</div>';
        return;
    }
    
    // Usar siempre rutas absolutas desde la raíz del sitio para las imágenes por defecto
    const defaultImagePath = '/PROYECTO/frontend/assets/images/team.png';
    
    let html = '';
    
    equipos.forEach(equipo => {
        let dt = 'No especificado';
        if ((equipo.dt_nombres && equipo.dt_nombres.trim() !== '') || (equipo.dt_apellidos && equipo.dt_apellidos.trim() !== '')) {
            dt = (equipo.dt_nombres ? equipo.dt_nombres : '') + (equipo.dt_apellidos ? ' ' + equipo.dt_apellidos : '');
        }
        
        // Asegurar que siempre haya una imagen
        const escudoUrl = equipo.escudo_base64 || defaultImagePath;
        
        html += `
            <div class="equipo-card" data-nombre="${equipo.nombre.toLowerCase()}">
                <div class="equipo-header">
                    <img src="${escudoUrl}" alt="${equipo.nombre}" onerror="this.src='${defaultImagePath}'">
                    <h2>${equipo.nombre}</h2>
                </div>
                <div class="equipo-info">
                    <p><strong>Ciudad:</strong> ${equipo.ciudad_nombre || 'No especificada'}</p>
                    <p><strong>Director Técnico:</strong> ${dt}</p>
                </div>
                <div class="equipo-footer">
                    <a href="detalle-equipo.php?id=${equipo.cod_equ}">Ver Detalles</a>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Función para filtrar equipos por nombre
 */
function filtrarEquipos(termino) {
    termino = termino.toLowerCase();
    const equipos = document.querySelectorAll('.equipo-card');
    
    let hayResultados = false;
    
    equipos.forEach(equipo => {
        const nombre = equipo.dataset.nombre;
        
        if (nombre.includes(termino)) {
            equipo.style.display = '';
            hayResultados = true;
        } else {
            equipo.style.display = 'none';
        }
    });
    
    // Mostrar mensaje si no hay resultados
    const container = document.getElementById('equipos-container');
    const noResultados = container.querySelector('.no-results');
    
    if (!hayResultados) {
        if (!noResultados) {
            const mensaje = document.createElement('div');
            mensaje.className = 'no-results';
            mensaje.textContent = 'No se encontraron equipos con ese nombre.';
            container.appendChild(mensaje);
        }
    } else if (noResultados) {
        noResultados.remove();
    }
} 
document.addEventListener('DOMContentLoaded', function() {
    // Variable para controlar si ya se cargaron los equipos
    window.equiposCargados = false;
    
    // Cargar jugadores al iniciar la página
    cargarJugadores();
    
    // Cargar equipos para el filtro
    cargarEquiposParaFiltro();
    
    // Configurar filtros
    const buscarInput = document.getElementById('buscar-jugador');
    buscarInput.addEventListener('input', aplicarFiltros);
    
    const filtroEquipo = document.getElementById('filtro-equipo');
    filtroEquipo.addEventListener('change', aplicarFiltros);
    
    const filtroPosicion = document.getElementById('filtro-posicion');
    filtroPosicion.addEventListener('change', aplicarFiltros);
});

/**
 * Función para cargar la lista de jugadores desde el servidor
 */
function cargarJugadores() {
    fetch('../../backend/controllers/jugadores_controller.php?accion=listar')
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta del servidor (jugadores):', data);
            if (data.estado) {
                mostrarJugadores(data.jugadores);
            } else {
                document.getElementById('jugadores-container').innerHTML = 
                    '<div class="no-results">No hay jugadores para mostrar.</div>';
            }
        })
        .catch(error => {
            console.error('Error al cargar jugadores:', error);
            document.getElementById('jugadores-container').innerHTML = 
                '<div class="no-results">No hay jugadores para mostrar.</div>';
        });
}

/**
 * Función para cargar los equipos para el filtro
 */
function cargarEquiposParaFiltro() {
    // Si ya se cargaron los equipos, no volver a cargarlos
    if (window.equiposCargados) {
        console.log('Equipos ya cargados, evitando duplicación');
        return;
    }

    fetch('../../backend/controllers/equipos_controller.php?accion=listar')
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta del servidor (equipos):', data);
            if (data.estado) {
                const selectEquipo = document.getElementById('filtro-equipo');
                
                // Limpiar todas las opciones excepto la primera
                selectEquipo.innerHTML = '<option value="">Todos los equipos</option>';
                
                // Agregar las opciones de equipos
                data.equipos.forEach(equipo => {
                    const option = document.createElement('option');
                    option.value = equipo.cod_equ;
                    option.textContent = equipo.nombre;
                    selectEquipo.appendChild(option);
                });
                
                console.log('Opciones de equipo cargadas:', selectEquipo.options.length - 1);
                
                // Marcar que ya se cargaron los equipos
                window.equiposCargados = true;
            }
        })
        .catch(error => {
            console.error('Error al cargar equipos:', error);
        });
}

/**
 * Función para mostrar los jugadores en la página
 */
function mostrarJugadores(jugadores) {
    const container = document.getElementById('jugadores-container');
    
    if (jugadores.length === 0) {
        container.innerHTML = '<div class="no-results">No hay jugadores para mostrar.</div>';
        return;
    }
    
    // Guardar jugadores para filtrarlos después
    window.todosJugadores = jugadores;
    
    let html = '';
    
    jugadores.forEach(jugador => {
        html += crearTarjetaJugador(jugador);
    });
    
    container.innerHTML = html;
}

/**
 * Función para crear el HTML de la tarjeta de un jugador
 */
function crearTarjetaJugador(jugador) {
    // Formatear posición
    const posicionFormateada = formatearPosicion(jugador.posicion);
    
    // Determinar estadísticas
    const goles = jugador.goles || 0;
    const asistencias = jugador.asistencias || 0;
    const tarjetasAmarillas = jugador.tarjetas_amarillas || 0;
    const tarjetasRojas = jugador.tarjetas_rojas || 0;
    
    // Determinar la ruta de la imagen por defecto según el contexto
    const isAdmin = window.location.pathname.includes('/admin/');
    const defaultImagePath = isAdmin ? '../../assets/images/player.png' : '../assets/images/player.png';
    
    // Asegurar que siempre haya una imagen
    const fotoUrl = jugador.foto_base64 || defaultImagePath;
    
    return `
        <div class="jugador-card" 
             data-nombre="${jugador.nombres.toLowerCase()} ${jugador.apellidos.toLowerCase()}"
             data-equipo="${jugador.cod_equ}"
             data-posicion="${jugador.posicion || ''}">
            <div class="jugador-header">
                <img src="${fotoUrl}" alt="${jugador.nombres}" class="jugador-foto" onerror="this.src='${defaultImagePath}'">
                <img src="${jugador.escudo_equipo}" alt="${jugador.nombre_equipo}" class="jugador-equipo-logo">
            </div>
            <div class="jugador-info">
                <h3 class="jugador-nombre">${jugador.nombres} ${jugador.apellidos}</h3>
                <div class="jugador-posicion">${posicionFormateada}</div>
                ${jugador.dorsal ? `<div class="jugador-dorsal">${jugador.dorsal}</div>` : ''}
                <div class="jugador-stats">
                    <div class="stat-item">
                        <div class="stat-value">${goles}</div>
                        <div class="stat-label">Goles</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">${asistencias}</div>
                        <div class="stat-label">Asist.</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">${tarjetasAmarillas}</div>
                        <div class="stat-label">T. Amarillas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">${tarjetasRojas}</div>
                        <div class="stat-label">T. Rojas</div>
                    </div>
                </div>
            </div>
            <div class="jugador-footer">
                <a href="detalle-jugador.php?id=${jugador.cod_jug}">Ver Perfil</a>
            </div>
        </div>
    `;
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
 * Función para aplicar los filtros de búsqueda
 */
function aplicarFiltros() {
    const termino = document.getElementById('buscar-jugador').value.toLowerCase();
    const equipoId = document.getElementById('filtro-equipo').value;
    const posicion = document.getElementById('filtro-posicion').value;
    
    // Obtener todas las tarjetas de jugadores
    const tarjetas = document.querySelectorAll('.jugador-card');
    
    let hayResultados = false;
    
    tarjetas.forEach(tarjeta => {
        const nombre = tarjeta.dataset.nombre;
        const equipo = tarjeta.dataset.equipo;
        const posicionJugador = tarjeta.dataset.posicion;
        
        // Aplicar todos los filtros
        const coincideNombre = nombre.includes(termino);
        const coincideEquipo = !equipoId || equipo === equipoId;
        const coincidePosicion = !posicion || posicionJugador === posicion;
        
        if (coincideNombre && coincideEquipo && coincidePosicion) {
            tarjeta.style.display = '';
            hayResultados = true;
        } else {
            tarjeta.style.display = 'none';
        }
    });
    
    // Mostrar mensaje si no hay resultados
    const container = document.getElementById('jugadores-container');
    const noResultados = container.querySelector('.no-results');
    
    if (!hayResultados) {
        if (!noResultados) {
            const mensaje = document.createElement('div');
            mensaje.className = 'no-results';
            mensaje.textContent = 'No se encontraron jugadores con los filtros seleccionados.';
            container.appendChild(mensaje);
        }
    } else if (noResultados) {
        noResultados.remove();
    }
} 
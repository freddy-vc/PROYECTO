/**
 * JavaScript para la administración de partidos
 */
let tempStats = {
    goles: window.initialStats ? window.initialStats.goles.map(gol => ({
        ...gol,
        id: gol.cod_gol || ('db_' + gol.cod_gol),
        jugador_id: gol.jugador_id || gol.cod_jug
    })) : [],
    asistencias: window.initialStats ? window.initialStats.asistencias.map(asis => ({
        ...asis,
        id: asis.cod_asis || ('db_' + asis.cod_asis),
        jugador_id: asis.jugador_id || asis.cod_jug
    })) : [],
    faltas: window.initialStats ? window.initialStats.faltas.map(falta => ({
        ...falta,
        id: falta.cod_falta || ('db_' + falta.cod_falta),
        jugador_id: falta.jugador_id || falta.cod_jug
    })) : []
};
let deletedStats = { goles: [], asistencias: [], faltas: [] };
let editingStat = { id: null, tipo: null };

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el elemento del formulario si existe
    const form = document.getElementById('partido-form');
    if (form) {
        initializeFormValidation();
    }
    
    // Inicializar los botones de eliminación si existen
    initializeDeleteButtons();
    
    // Inicializar las pestañas en la sección de estadísticas si existen
    initializeTabs();

    // Función para resetear los formularios después de enviar
    document.querySelectorAll('.stats-form').forEach(form => {
        form.addEventListener('submit', function() {
            setTimeout(() => {
                this.reset();
                this.querySelector('input[name="accion"]').value = this.querySelector('input[name="accion"]').value.replace('actualizar_', 'registrar_');
                this.querySelector('button[type="submit"]').textContent = this.querySelector('button[type="submit"]').textContent.replace('Actualizar', 'Registrar');
                const idInput = this.querySelector('input[name$="_id"]');
                if (idInput) idInput.remove();
            }, 100);
        });
    });

    renderAllStatsTables();
    setupStatsForms();
    calcularMarcador();

    // Botones para abrir modales de agregar (refuerzo el selector y evento)
    setTimeout(function() {
        var btnAddGol = document.getElementById('btn-add-gol');
        if (btnAddGol) btnAddGol.onclick = function() { openModal('modal-gol'); };
        var btnAddAsistencia = document.getElementById('btn-add-asistencia');
        if (btnAddAsistencia) btnAddAsistencia.onclick = function() { openModal('modal-asistencia'); };
        var btnAddFalta = document.getElementById('btn-add-falta');
        if (btnAddFalta) btnAddFalta.onclick = function() { openModal('modal-falta'); };
    }, 100);

    // Formularios de agregar (modales)
    const formAddGol = document.getElementById('form-add-gol');
    if (formAddGol) {
        formAddGol.onsubmit = function(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(this));
            data.id = 'temp_' + Date.now();
            tempStats.goles.push(data);
            updateStatsTable('goles');
            closeModal('modal-gol');
            this.reset();
        };
    }
    const formAddAsistencia = document.getElementById('form-add-asistencia');
    if (formAddAsistencia) {
        formAddAsistencia.onsubmit = function(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(this));
            data.id = 'temp_' + Date.now();
            tempStats.asistencias.push(data);
            updateStatsTable('asistencias');
            closeModal('modal-asistencia');
            this.reset();
        };
    }
    const formAddFalta = document.getElementById('form-add-falta');
    if (formAddFalta) {
        formAddFalta.onsubmit = function(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(this));
            data.id = 'temp_' + Date.now();
            tempStats.faltas.push(data);
            updateStatsTable('faltas');
            closeModal('modal-falta');
            this.reset();
        };
    }
});

/**
 * Inicializar la validación del formulario
 */
function initializeFormValidation() {
    const form = document.getElementById('partido-form');
    
    // Validar el formulario antes de enviarlo
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar fecha
        const fecha = document.getElementById('fecha');
        if (!fecha.value) {
            showError(fecha, 'La fecha es obligatoria');
            isValid = false;
        } else {
            clearError(fecha);
        }
        
        // Validar hora
        const hora = document.getElementById('hora');
        if (!hora.value) {
            showError(hora, 'La hora es obligatoria');
            isValid = false;
        } else {
            clearError(hora);
        }
        
        // Validar cancha
        const cancha = document.getElementById('cancha_id');
        if (!cancha.value) {
            showError(cancha, 'Selecciona una cancha');
            isValid = false;
        } else {
            clearError(cancha);
        }
        
        // Validar equipos si están presentes en el formulario
        const equipoLocal = document.getElementById('equipo_local');
        const equipoVisitante = document.getElementById('equipo_visitante');
        
        if (equipoLocal && equipoVisitante) {
            // Validar equipo local
            if (!equipoLocal.value) {
                showError(equipoLocal, 'Selecciona un equipo local');
                isValid = false;
            } else {
                clearError(equipoLocal);
            }
            
            // Validar equipo visitante
            if (!equipoVisitante.value) {
                showError(equipoVisitante, 'Selecciona un equipo visitante');
                isValid = false;
            } else {
                clearError(equipoVisitante);
            }
            
            // Validar que los equipos sean diferentes
            if (equipoLocal.value && equipoVisitante.value && equipoLocal.value === equipoVisitante.value) {
                showError(equipoVisitante, 'El equipo local y visitante no pueden ser el mismo');
                isValid = false;
            }
        }
        
        // Validar estado si está presente
        const estado = document.getElementById('estado');
        if (estado && !estado.value) {
            showError(estado, 'Selecciona un estado para el partido');
            isValid = false;
        } else if (estado) {
            clearError(estado);
        }
        
        // Prevenir el envío del formulario si hay errores
        if (!isValid) {
            e.preventDefault();
        }
    });
}

/**
 * Inicializar los botones de eliminación
 */
function initializeDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const partidoName = this.getAttribute('data-name');
            
            if (confirm(`¿Estás seguro de que deseas eliminar el partido "${partidoName}"? Esta acción no se puede deshacer.`)) {
                this.closest('form').submit();
            }
        });
    });
}

/**
 * Inicializar las pestañas en la sección de estadísticas
 */
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    if (tabButtons.length > 0) {
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remover la clase active de todos los botones y contenido
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Agregar la clase active al botón actual
                this.classList.add('active');
                
                // Mostrar el contenido correspondiente
                const tabId = this.getAttribute('data-tab');
                document.getElementById('tab-' + tabId).classList.add('active');
            });
        });
    }
}

/**
 * Mostrar un mensaje de error para un campo
 */
function showError(input, message) {
    const errorElement = document.getElementById('error-' + input.id);
    if (errorElement) {
        errorElement.textContent = message;
        input.classList.add('input-error');
    }
}

/**
 * Limpiar un mensaje de error para un campo
 */
function clearError(input) {
    const errorElement = document.getElementById('error-' + input.id);
    if (errorElement) {
        errorElement.textContent = '';
        input.classList.remove('input-error');
    }
}

// Funciones para manejar la edición de estadísticas
function editarGol(gol) {
    // Llenar el formulario de gol con los datos existentes
    document.getElementById('jugador_gol').value = gol.cod_jug;
    document.getElementById('minuto_gol').value = gol.minuto;
    document.getElementById('tipo_gol').value = gol.tipo;
    
    // Cambiar el formulario a modo edición
    const form = document.getElementById('gol-form');
    form.querySelector('input[name="accion"]').value = 'actualizar_gol';
    form.innerHTML += `<input type="hidden" name="gol_id" value="${gol.cod_gol}">`;
    
    // Cambiar el texto del botón
    form.querySelector('button[type="submit"]').textContent = 'Actualizar Gol';
}

function editarAsistencia(asistencia) {
    // Llenar el formulario de asistencia con los datos existentes
    document.getElementById('jugador_asistencia').value = asistencia.cod_jug;
    document.getElementById('minuto_asistencia').value = asistencia.minuto;
    
    // Cambiar el formulario a modo edición
    const form = document.getElementById('asistencia-form');
    form.querySelector('input[name="accion"]').value = 'actualizar_asistencia';
    form.innerHTML += `<input type="hidden" name="asistencia_id" value="${asistencia.cod_asis}">`;
    
    // Cambiar el texto del botón
    form.querySelector('button[type="submit"]').textContent = 'Actualizar Asistencia';
}

function editarFalta(falta) {
    // Llenar el formulario de falta con los datos existentes
    document.getElementById('jugador_falta').value = falta.cod_jug;
    document.getElementById('minuto_falta').value = falta.minuto;
    document.getElementById('tipo_falta').value = falta.tipo_falta;
    
    // Cambiar el formulario a modo edición
    const form = document.getElementById('falta-form');
    form.querySelector('input[name="accion"]').value = 'actualizar_falta';
    form.innerHTML += `<input type="hidden" name="falta_id" value="${falta.cod_falta}">`;
    
    // Cambiar el texto del botón
    form.querySelector('button[type="submit"]').textContent = 'Actualizar Tarjeta';
}

function renderAllStatsTables() {
    updateStatsTable('goles');
    updateStatsTable('asistencias');
    updateStatsTable('faltas');
}

function updateStatsTable(tipo) {
    const tableBody = document.querySelector(`#tabla-${tipo} tbody`);
    if (tableBody) {
        tableBody.innerHTML = '';
        tempStats[tipo].forEach(item => {
            const row = createStatsRow(item, tipo);
            tableBody.appendChild(row);
        });
    }
    if (tipo === 'goles') {
        calcularMarcador();
    }
}

function createStatsRow(item, tipo) {
    const row = document.createElement('tr');
    let jugador = '';
    let equipo = '';
    // Usar window.jugadoresDorsales si existe
    if (window.jugadoresDorsales && item.jugador_id && window.jugadoresDorsales[item.jugador_id]) {
        const jug = window.jugadoresDorsales[item.jugador_id];
        jugador = jug.nombre + (jug.dorsal ? ' (#' + jug.dorsal + ')' : '');
        equipo = jug.equipo;
    } else {
        // Fallback: buscar en el select
        var select = document.querySelector('#form-add-gol select[name="jugador_id"]') || document.querySelector('#form-add-asistencia select[name="jugador_id"]') || document.querySelector('#form-add-falta select[name="jugador_id"]');
        if (item.jugador_id && select) {
            var opt = select.querySelector('option[value="'+item.jugador_id+'"]');
            if (opt) jugador = opt.textContent;
            if (opt && opt.parentElement && opt.parentElement.label) equipo = opt.parentElement.label;
        }
    }
    switch(tipo) {
        case 'goles':
            row.innerHTML = `
                <td>${item.minuto}</td>
                <td>${jugador}</td>
                <td>${equipo}</td>
                <td>${item.tipo}</td>
                <td>
                    <button type="button" onclick="editTempStat('${item.id}', 'goles')" class="action-btn edit" title="Editar"><i class="fas fa-edit"></i></button>
                    <button type="button" onclick="deleteTempStat('${item.id}', 'goles')" class="action-btn delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            `;
            break;
        case 'asistencias':
            row.innerHTML = `
                <td>${item.minuto}</td>
                <td>${jugador}</td>
                <td>${equipo}</td>
                <td>
                    <button type="button" onclick="editTempStat('${item.id}', 'asistencias')" class="action-btn edit" title="Editar"><i class="fas fa-edit"></i></button>
                    <button type="button" onclick="deleteTempStat('${item.id}', 'asistencias')" class="action-btn delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            `;
            break;
        case 'faltas':
            row.innerHTML = `
                <td>${item.minuto}</td>
                <td>${jugador}</td>
                <td>${equipo}</td>
                <td>${item.tipo_falta}</td>
                <td>
                    <button type="button" onclick="editTempStat('${item.id}', 'faltas')" class="action-btn edit" title="Editar"><i class="fas fa-edit"></i></button>
                    <button type="button" onclick="deleteTempStat('${item.id}', 'faltas')" class="action-btn delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            `;
            break;
    }
    return row;
}

function setupStatsForms() {
    // Goles
    const formGol = document.getElementById('gol-form');
    if (formGol) {
        formGol.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => data[key] = value);
            if (editingStat.id && editingStat.tipo === 'goles') {
                const idx = tempStats.goles.findIndex(item => item.id == editingStat.id);
                if (idx !== -1) tempStats.goles[idx] = { ...tempStats.goles[idx], ...data };
                editingStat = { id: null, tipo: null };
            } else {
                const tempId = 'temp_' + Date.now();
                data.id = tempId;
                tempStats.goles.push(data);
            }
            closeModal('tab-goles');
            updateStatsTable('goles');
            this.reset();
            this.querySelector('button[type="submit"]').textContent = 'Registrar Gol';
        });
    }
    // Asistencias
    const formAsis = document.getElementById('asistencia-form');
    if (formAsis) {
        formAsis.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => data[key] = value);
            if (editingStat.id && editingStat.tipo === 'asistencias') {
                const idx = tempStats.asistencias.findIndex(item => item.id == editingStat.id);
                if (idx !== -1) tempStats.asistencias[idx] = { ...tempStats.asistencias[idx], ...data };
                editingStat = { id: null, tipo: null };
            } else {
                const tempId = 'temp_' + Date.now();
                data.id = tempId;
                tempStats.asistencias.push(data);
            }
            closeModal('tab-asistencias');
            updateStatsTable('asistencias');
            this.reset();
            this.querySelector('button[type="submit"]').textContent = 'Registrar Asistencia';
        });
    }
    // Faltas
    const formFalta = document.getElementById('falta-form');
    if (formFalta) {
        formFalta.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => data[key] = value);
            if (editingStat.id && editingStat.tipo === 'faltas') {
                const idx = tempStats.faltas.findIndex(item => item.id == editingStat.id);
                if (idx !== -1) tempStats.faltas[idx] = { ...tempStats.faltas[idx], ...data };
                editingStat = { id: null, tipo: null };
            } else {
                const tempId = 'temp_' + Date.now();
                data.id = tempId;
                tempStats.faltas.push(data);
            }
            closeModal('tab-faltas');
            updateStatsTable('faltas');
            this.reset();
            this.querySelector('button[type="submit"]').textContent = 'Registrar Tarjeta';
        });
    }
    // Al guardar el partido, enviar los arrays temporales y eliminados
    const partidoForm = document.getElementById('partido-form');
    if (partidoForm) {
        partidoForm.addEventListener('submit', function() {
            let statsInput = document.getElementById('estadisticas_temporales');
            if (!statsInput) {
                statsInput = document.createElement('input');
                statsInput.type = 'hidden';
                statsInput.id = 'estadisticas_temporales';
                statsInput.name = 'estadisticas_temporales';
                this.appendChild(statsInput);
            }
            statsInput.value = JSON.stringify(tempStats);
            let deletedInput = document.getElementById('estadisticas_eliminadas');
            if (!deletedInput) {
                deletedInput = document.createElement('input');
                deletedInput.type = 'hidden';
                deletedInput.id = 'estadisticas_eliminadas';
                deletedInput.name = 'estadisticas_eliminadas';
                this.appendChild(deletedInput);
            }
            deletedInput.value = JSON.stringify(deletedStats);
        });
    }
}

function editTempStat(id, tipo) {
    const stat = tempStats[tipo].find(item => item.id == id);
    if (!stat) return;
    if (tipo === 'goles') {
        // Solo el jugador a editar
        let jugador = '';
        if (window.jugadoresDorsales && stat.jugador_id && window.jugadoresDorsales[stat.jugador_id]) {
            const jug = window.jugadoresDorsales[stat.jugador_id];
            jugador = `<option value="${stat.jugador_id}" selected>${jug.nombre} (#${jug.dorsal})</option>`;
        }
        let html = `<label>Jugador</label><select name='jugador_id' required>${jugador}</select>`;
        html += `<label>Minuto</label><input type='number' name='minuto' min='0' max='50' value='${stat.minuto}' required>`;
        html += `<label>Tipo</label><select name='tipo' required>
            <option value='normal' ${stat.tipo=='normal'?'selected':''}>Normal</option>
            <option value='penal' ${stat.tipo=='penal'?'selected':''}>Penal</option>
            <option value='autogol' ${stat.tipo=='autogol'?'selected':''}>Autogol</option>
        </select>`;
        html += `<button type='submit'>Actualizar</button>`;
        document.getElementById('form-edit-gol').innerHTML = html;
        document.getElementById('form-edit-gol').onsubmit = function(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(this));
            Object.assign(stat, data);
            updateStatsTable('goles');
            closeModal('modal-edit-gol');
        };
        openModal('modal-edit-gol');
    } else if (tipo === 'asistencias') {
        let jugador = '';
        if (window.jugadoresDorsales && stat.jugador_id && window.jugadoresDorsales[stat.jugador_id]) {
            const jug = window.jugadoresDorsales[stat.jugador_id];
            jugador = `<option value="${stat.jugador_id}" selected>${jug.nombre} (#${jug.dorsal})</option>`;
        }
        let html = `<label>Jugador</label><select name='jugador_id' required>${jugador}</select>`;
        html += `<label>Minuto</label><input type='number' name='minuto' min='0' max='50' value='${stat.minuto}' required>`;
        html += `<button type='submit'>Actualizar</button>`;
        document.getElementById('form-edit-asistencia').innerHTML = html;
        document.getElementById('form-edit-asistencia').onsubmit = function(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(this));
            Object.assign(stat, data);
            updateStatsTable('asistencias');
            closeModal('modal-edit-asistencia');
        };
        openModal('modal-edit-asistencia');
    } else if (tipo === 'faltas') {
        let jugador = '';
        if (window.jugadoresDorsales && stat.jugador_id && window.jugadoresDorsales[stat.jugador_id]) {
            const jug = window.jugadoresDorsales[stat.jugador_id];
            jugador = `<option value="${stat.jugador_id}" selected>${jug.nombre} (#${jug.dorsal})</option>`;
        }
        let html = `<label>Jugador</label><select name='jugador_id' required>${jugador}</select>`;
        html += `<label>Minuto</label><input type='number' name='minuto' min='0' max='50' value='${stat.minuto}' required>`;
        html += `<label>Tipo</label><select name='tipo_falta' required>
            <option value='amarilla' ${stat.tipo_falta=='amarilla'?'selected':''}>Amarilla</option>
            <option value='roja' ${stat.tipo_falta=='roja'?'selected':''}>Roja</option>
        </select>`;
        html += `<button type='submit'>Actualizar</button>`;
        document.getElementById('form-edit-falta').innerHTML = html;
        document.getElementById('form-edit-falta').onsubmit = function(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(this));
            Object.assign(stat, data);
            updateStatsTable('faltas');
            closeModal('modal-edit-falta');
        };
        openModal('modal-edit-falta');
    }
}

function deleteTempStat(id, tipo) {
    const stat = tempStats[tipo].find(item => item.id == id);
    let html = `<p>¿Estás seguro de que deseas eliminar este elemento?</p><button type='submit'>Eliminar</button>`;
    let formId = '';
    if (tipo === 'goles') formId = 'form-delete-gol';
    if (tipo === 'asistencias') formId = 'form-delete-asistencia';
    if (tipo === 'faltas') formId = 'form-delete-falta';
    document.getElementById(formId).innerHTML = html;
    document.getElementById(formId).onsubmit = function(e) {
        e.preventDefault();
        if (stat && (stat.cod_gol || stat.cod_asis || stat.cod_falta)) {
            if (tipo === 'goles' && stat.cod_gol) deletedStats.goles.push(stat.cod_gol);
            if (tipo === 'asistencias' && stat.cod_asis) deletedStats.asistencias.push(stat.cod_asis);
            if (tipo === 'faltas' && stat.cod_falta) deletedStats.faltas.push(stat.cod_falta);
        }
        tempStats[tipo] = tempStats[tipo].filter(item => item.id != id);
        updateStatsTable(tipo);
        closeModal(formId.replace('form-', 'modal-'));
    };
    openModal(formId.replace('form-', 'modal-'));
}

function closeModal(tabId) {
    // Solo resetea el formulario de la pestaña activa
    let form;
    if (tabId === 'tab-goles') form = document.getElementById('gol-form');
    if (tabId === 'tab-asistencias') form = document.getElementById('asistencia-form');
    if (tabId === 'tab-faltas') form = document.getElementById('falta-form');
    if (form) {
        form.reset();
        const btn = form.querySelector('button[type="submit"]');
        if (btn) btn.textContent = btn.textContent.replace('Actualizar', 'Registrar');
        editingStat = { id: null, tipo: null };
    }
}

function openModal(id) {
    document.getElementById(id).style.display = 'block';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function calcularMarcador() {
    // Obtén los nombres de los equipos
    const localNombre = document.querySelector('.equipo.local .equipo-nombre')?.childNodes[0]?.textContent?.trim();
    const visitanteNombre = document.querySelector('.equipo.visitante .equipo-nombre')?.childNodes[0]?.textContent?.trim();
    let marcadorLocal = 0;
    let marcadorVisitante = 0;

    tempStats.goles.forEach(gol => {
        const jugador = window.jugadoresDorsales[gol.jugador_id];
        if (!jugador) return;
        const equipoJugador = jugador.equipo;

        if (gol.tipo === 'autogol') {
            // Suma al equipo contrario
            if (equipoJugador === localNombre) {
                marcadorVisitante++;
            } else if (equipoJugador === visitanteNombre) {
                marcadorLocal++;
            }
        } else {
            // Suma al equipo del jugador
            if (equipoJugador === localNombre) {
                marcadorLocal++;
            } else if (equipoJugador === visitanteNombre) {
                marcadorVisitante++;
            }
        }
    });

    // Actualiza el DOM
    const marcadorLocalSpan = document.getElementById('marcador-local');
    const marcadorVisitanteSpan = document.getElementById('marcador-visitante');
    if (marcadorLocalSpan) marcadorLocalSpan.textContent = marcadorLocal;
    if (marcadorVisitanteSpan) marcadorVisitanteSpan.textContent = marcadorVisitante;
} 
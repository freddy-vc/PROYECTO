// admin_jugadores_stats.js

document.addEventListener('DOMContentLoaded', function() {
    if (typeof jugadorId === 'undefined') return;
    cargarGoles();
    cargarAsistencias();
    cargarFaltas();

    document.getElementById('btn-add-gol').onclick = mostrarModalAgregarGol;
    document.getElementById('btn-add-asistencia').onclick = mostrarModalAgregarAsistencia;
    document.getElementById('btn-add-falta').onclick = mostrarModalAgregarFalta;
});

function cargarGoles() {
    fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?accion=listar_goles&id=${jugadorId}`)
        .then(r => r.json()).then(data => {
            const tbody = document.querySelector('#tabla-goles tbody');
            tbody.innerHTML = '';
            data.forEach(gol => {
                tbody.innerHTML += `<tr>
                    <td>${gol.equipo_local} vs ${gol.equipo_visitante}</td>
                    <td>${gol.fecha} ${gol.hora}</td>
                    <td>${gol.minuto}'</td>
                    <td>${gol.tipo}</td>
                    <td>
                        <button class='btn-edit' onclick='editarGol(${JSON.stringify(gol)})'>Editar</button>
                        <button class='btn-delete' onclick='eliminarGol(${gol.cod_gol})'>Eliminar</button>
                    </td>
                </tr>`;
            });
        });
}
function cargarAsistencias() {
    fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?accion=listar_asistencias&id=${jugadorId}`)
        .then(r => r.json()).then(data => {
            const tbody = document.querySelector('#tabla-asistencias tbody');
            tbody.innerHTML = '';
            data.forEach(asistencia => {
                tbody.innerHTML += `<tr>
                    <td>${asistencia.equipo_local} vs ${asistencia.equipo_visitante}</td>
                    <td>${asistencia.fecha} ${asistencia.hora}</td>
                    <td>${asistencia.minuto}'</td>
                    <td>
                        <button class='btn-edit' onclick='editarAsistencia(${JSON.stringify(asistencia)})'>Editar</button>
                        <button class='btn-delete' onclick='eliminarAsistencia(${asistencia.cod_asis})'>Eliminar</button>
                    </td>
                </tr>`;
            });
        });
}
function cargarFaltas() {
    fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?accion=listar_faltas&id=${jugadorId}`)
        .then(r => r.json()).then(data => {
            const tbody = document.querySelector('#tabla-faltas tbody');
            tbody.innerHTML = '';
            data.forEach(falta => {
                tbody.innerHTML += `<tr>
                    <td>${falta.equipo_local} vs ${falta.equipo_visitante}</td>
                    <td>${falta.fecha} ${falta.hora}</td>
                    <td>${falta.minuto}'</td>
                    <td>${falta.tipo_falta}</td>
                    <td>
                        <button class='btn-edit' onclick='editarFalta(${JSON.stringify(falta)})'>Editar</button>
                        <button class='btn-delete' onclick='eliminarFalta(${falta.cod_falta})'>Eliminar</button>
                    </td>
                </tr>`;
            });
        });
}

// Utilidad para mostrar modales
function showModal(id, html) {
    const modal = document.getElementById(id);
    modal.innerHTML = `<div class='modal-content'>${html}<span class='modal-close' onclick='closeModal("${id}")'>&times;</span></div>`;
    modal.style.display = 'block';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Cargar partidos para los selects
function cargarPartidosSelect(callback, selected) {
    fetch('../../../backend/controllers/admin/jugadores_stats_controller.php?accion=listar_partidos')
        .then(r => r.json()).then(data => {
            let options = data.map(p => `<option value='${p.cod_par}' ${selected==p.cod_par?'selected':''}>${p.equipo_local} vs ${p.equipo_visitante} (${p.fecha} ${p.hora})</option>`).join('');
            callback(options);
        });
}

// Goles
function mostrarModalAgregarGol() {
    cargarPartidosSelect(options => {
        showModal('modal-gol', `
            <form id='form-gol'>
                <h3>Agregar Gol</h3>
                <label>Partido</label><select name='partido_id' required>${options}</select><br>
                <label>Minuto</label><input type='number' name='minuto' min='0' max='50' required><br>
                <label>Tipo</label><select name='tipo' required><option value='normal'>Normal</option><option value='penal'>Penal</option><option value='autogol'>Autogol</option></select><br>
                <button type='submit'>Guardar</button>
            </form>
        `);
        document.getElementById('form-gol').onsubmit = function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('accion','agregar_gol');
            fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?id=${jugadorId}`, {method:'POST',body:fd})
                .then(r=>r.json()).then(()=>{ closeModal('modal-gol'); cargarGoles(); });
        };
    });
}
function editarGol(gol) {
    cargarPartidosSelect(options => {
        showModal('modal-gol', `
            <form id='form-gol-edit'>
                <h3>Editar Gol</h3>
                <input type='hidden' name='cod_gol' value='${gol.cod_gol}'>
                <label>Partido</label><select name='partido_id' required>${options.replace(`value='${gol.cod_par}'`,`value='${gol.cod_par}' selected`)}</select><br>
                <label>Minuto</label><input type='number' name='minuto' min='0' max='50' value='${gol.minuto}' required><br>
                <label>Tipo</label><select name='tipo' required><option value='normal' ${gol.tipo=='normal'?'selected':''}>Normal</option><option value='penal' ${gol.tipo=='penal'?'selected':''}>Penal</option><option value='autogol' ${gol.tipo=='autogol'?'selected':''}>Autogol</option></select><br>
                <button type='submit'>Actualizar</button>
            </form>
        `);
        document.getElementById('form-gol-edit').onsubmit = function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('accion','editar_gol');
            fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?id=${jugadorId}`, {method:'POST',body:fd})
                .then(r=>r.json()).then(()=>{ closeModal('modal-gol'); cargarGoles(); });
        };
    }, gol.cod_par);
}
function eliminarGol(id) {
    if(confirm('¿Eliminar gol?')){
        const fd = new FormData();
        fd.append('accion','eliminar_gol');
        fd.append('cod_gol',id);
        fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?id=${jugadorId}`, {method:'POST',body:fd})
            .then(r=>r.json()).then(()=>{ cargarGoles(); });
    }
}

// Asistencias
function mostrarModalAgregarAsistencia() {
    cargarPartidosSelect(options => {
        showModal('modal-asistencia', `
            <form id='form-asistencia'>
                <h3>Agregar Asistencia</h3>
                <label>Partido</label><select name='partido_id' required>${options}</select><br>
                <label>Minuto</label><input type='number' name='minuto' min='0' max='50' required><br>
                <button type='submit'>Guardar</button>
            </form>
        `);
        document.getElementById('form-asistencia').onsubmit = function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('accion','agregar_asistencia');
            fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?id=${jugadorId}`, {method:'POST',body:fd})
                .then(r=>r.json()).then(()=>{ closeModal('modal-asistencia'); cargarAsistencias(); });
        };
    });
}
function editarAsistencia(asistencia) {
    cargarPartidosSelect(options => {
        showModal('modal-asistencia', `
            <form id='form-asistencia-edit'>
                <h3>Editar Asistencia</h3>
                <input type='hidden' name='cod_asis' value='${asistencia.cod_asis}'>
                <label>Partido</label><select name='partido_id' required>${options.replace(`value='${asistencia.cod_par}'`,`value='${asistencia.cod_par}' selected`)}</select><br>
                <label>Minuto</label><input type='number' name='minuto' min='0' max='50' value='${asistencia.minuto}' required><br>
                <button type='submit'>Actualizar</button>
            </form>
        `);
        document.getElementById('form-asistencia-edit').onsubmit = function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('accion','editar_asistencia');
            fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?id=${jugadorId}`, {method:'POST',body:fd})
                .then(r=>r.json()).then(()=>{ closeModal('modal-asistencia'); cargarAsistencias(); });
        };
    }, asistencia.cod_par);
}
function eliminarAsistencia(id) {
    if(confirm('¿Eliminar asistencia?')){
        const fd = new FormData();
        fd.append('accion','eliminar_asistencia');
        fd.append('cod_asis',id);
        fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?id=${jugadorId}`, {method:'POST',body:fd})
            .then(r=>r.json()).then(()=>{ cargarAsistencias(); });
    }
}

// Faltas
function mostrarModalAgregarFalta() {
    cargarPartidosSelect(options => {
        showModal('modal-falta', `
            <form id='form-falta'>
                <h3>Agregar Falta</h3>
                <label>Partido</label><select name='partido_id' required>${options}</select><br>
                <label>Minuto</label><input type='number' name='minuto' min='0' max='50' required><br>
                <label>Tipo</label><select name='tipo_falta' required><option value='amarilla'>Amarilla</option><option value='roja'>Roja</option></select><br>
                <button type='submit'>Guardar</button>
            </form>
        `);
        document.getElementById('form-falta').onsubmit = function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('accion','agregar_falta');
            fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?id=${jugadorId}`, {method:'POST',body:fd})
                .then(r=>r.json()).then(()=>{ closeModal('modal-falta'); cargarFaltas(); });
        };
    });
}
function editarFalta(falta) {
    cargarPartidosSelect(options => {
        showModal('modal-falta', `
            <form id='form-falta-edit'>
                <h3>Editar Falta</h3>
                <input type='hidden' name='cod_falta' value='${falta.cod_falta}'>
                <label>Partido</label><select name='partido_id' required>${options.replace(`value='${falta.cod_par}'`,`value='${falta.cod_par}' selected`)}</select><br>
                <label>Minuto</label><input type='number' name='minuto' min='0' max='50' value='${falta.minuto}' required><br>
                <label>Tipo</label><select name='tipo_falta' required><option value='amarilla' ${falta.tipo_falta=='amarilla'?'selected':''}>Amarilla</option><option value='roja' ${falta.tipo_falta=='roja'?'selected':''}>Roja</option></select><br>
                <button type='submit'>Actualizar</button>
            </form>
        `);
        document.getElementById('form-falta-edit').onsubmit = function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('accion','editar_falta');
            fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?id=${jugadorId}`, {method:'POST',body:fd})
                .then(r=>r.json()).then(()=>{ closeModal('modal-falta'); cargarFaltas(); });
        };
    }, falta.cod_par);
}
function eliminarFalta(id) {
    if(confirm('¿Eliminar falta?')){
        const fd = new FormData();
        fd.append('accion','eliminar_falta');
        fd.append('cod_falta',id);
        fetch(`../../../backend/controllers/admin/jugadores_stats_controller.php?id=${jugadorId}`, {method:'POST',body:fd})
            .then(r=>r.json()).then(()=>{ cargarFaltas(); });
    }
} 
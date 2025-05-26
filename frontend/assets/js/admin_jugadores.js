/**
 * JavaScript específico para la gestión de jugadores en el panel de administración
 */
document.addEventListener('DOMContentLoaded', function() {
    // Configuración del formulario de jugador
    setupJugadorForm();
    
    // Configurar filtros específicos para jugadores
    setupJugadoresFilters();
    
    // Configurar formularios de estadísticas
    setupStatsForms();
    
    // Inicializar estadísticas desde PHP si existen
    if (window.initialStats) {
        tempStats.goles = window.initialStats.goles.map(gol => ({...gol, id: gol.cod_gol || ('db_' + gol.cod_gol)}));
        tempStats.asistencias = window.initialStats.asistencias.map(asis => ({...asis, id: asis.cod_asis || ('db_' + asis.cod_asis)}));
        tempStats.faltas = window.initialStats.faltas.map(falta => ({...falta, id: falta.cod_falta || ('db_' + falta.cod_falta)}));
    }
    
    // Renderizar todas las tablas al cargar
    renderAllStatsTables();
});

/**
 * Configura el formulario de jugador con validaciones específicas
 */
function setupJugadorForm() {
    const jugadorForm = document.getElementById('jugador-form');
    const dorsalInput = document.getElementById('dorsal');
    const numeroCamisetaInput = document.getElementById('numero_camiseta');
    const nombresInput = document.getElementById('nombres');
    const apellidosInput = document.getElementById('apellidos');
    const fotoInput = document.getElementById('foto');
    
    // Sincronizar el campo numero_camiseta con el campo dorsal
    if (dorsalInput && numeroCamisetaInput) {
        // Inicializar el valor
        numeroCamisetaInput.value = dorsalInput.value;
        
        // Sincronizar al cambiar
        dorsalInput.addEventListener('change', function() {
            numeroCamisetaInput.value = this.value;
        });
        
        dorsalInput.addEventListener('input', function() {
            numeroCamisetaInput.value = this.value;
            
            // Validación en tiempo real para el dorsal
            if (this.value === '') {
                showError(this, 'El número de camiseta es obligatorio');
            } else if (isNaN(this.value)) {
                showError(this, 'El número de camiseta debe ser un valor numérico');
            } else if (parseInt(this.value) < 1) {
                showError(this, 'El número de camiseta no puede ser negativo o cero');
                this.value = 1; // Corregir automáticamente a valor mínimo permitido
            } else if (parseInt(this.value) > 99) {
                showError(this, 'El número de camiseta no puede ser mayor a 99');
                this.value = 99; // Corregir automáticamente a valor máximo permitido
            } else {
                clearError(this);
            }
        });
    }
    
    // Validación en tiempo real para los campos de texto
    if (nombresInput) {
        nombresInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                showError(this, 'El nombre del jugador es obligatorio');
            } else if (this.value.trim().length < 2) {
                showError(this, 'El nombre debe tener al menos 2 caracteres');
            } else {
                clearError(this);
            }
        });
    }
    
    if (apellidosInput) {
        apellidosInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                showError(this, 'Los apellidos del jugador son obligatorios');
            } else if (this.value.trim().length < 2) {
                showError(this, 'Los apellidos deben tener al menos 2 caracteres');
            } else {
                clearError(this);
            }
        });
    }
    
    // Validación en tiempo real para la imagen
    if (fotoInput) {
        fotoInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                const fileSize = file.size / 1024 / 1024; // tamaño en MB
                const validExtensions = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (!validExtensions.includes(file.type)) {
                    showError(this, 'El formato de imagen no es válido. Sólo se permiten JPG, PNG o GIF');
                } else if (fileSize > 2) {
                    showError(this, 'La imagen es demasiado grande. El tamaño máximo permitido es 2MB');
                } else {
                    clearError(this);
                }
            }
            
            const previewContainer = document.getElementById('foto-preview');
            
            // Crear el contenedor de vista previa si no existe
            if (!previewContainer) {
                const container = document.createElement('div');
                container.id = 'foto-preview';
                container.className = 'image-preview';
                this.parentNode.insertBefore(container, this.nextSibling);
            }
            
            const preview = document.getElementById('foto-preview');
            
            // Limpiar vista previa anterior
            preview.innerHTML = '';
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Vista previa';
                    preview.appendChild(img);
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Añadir evento de clic al botón de guardar
    const btnGuardar = document.getElementById('btn-guardar-jugador');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validar el formulario
            if (validarFormulario()) {
                // Asegurarse de que el campo numero_camiseta tenga el mismo valor que dorsal
                if (dorsalInput && numeroCamisetaInput) {
                    numeroCamisetaInput.value = dorsalInput.value;
                }
                
                // Preparar datos de estadísticas si existen
                if (typeof tempStats !== 'undefined') {
                    // Asegurarse de que todos los elementos tengan partido_id
                    ['goles', 'asistencias', 'faltas'].forEach(tipo => {
                        if (tempStats[tipo] && tempStats[tipo].length > 0) {
                            tempStats[tipo].forEach(item => {
                                if (!item.partido_id && item.cod_par) {
                                    item.partido_id = item.cod_par;
                                }
                            });
                        }
                    });
                    
                    // Crear un campo oculto para las estadísticas temporales
                    if (Object.keys(tempStats).length > 0) {
                        const statsInput = document.createElement('input');
                        statsInput.type = 'hidden';
                        statsInput.name = 'estadisticas_temporales';
                        statsInput.value = JSON.stringify(tempStats);
                        jugadorForm.appendChild(statsInput);
                    }
                    
                    // Crear un campo oculto para las estadísticas eliminadas
                    if (typeof deletedStats !== 'undefined' && (
                        deletedStats.goles.length > 0 || 
                        deletedStats.asistencias.length > 0 || 
                        deletedStats.faltas.length > 0
                    )) {
                        const deletedInput = document.createElement('input');
                        deletedInput.type = 'hidden';
                        deletedInput.name = 'estadisticas_eliminadas';
                        deletedInput.value = JSON.stringify(deletedStats);
                        jugadorForm.appendChild(deletedInput);
                    }
                }
                
                // Enviar el formulario
                jugadorForm.submit();
            }
        });
    }
    
    jugadorForm.addEventListener('submit', function(e) {
        // Si el evento no viene del botón guardar, validar
        if (e.submitter !== btnGuardar) {
            if (!validarFormulario()) {
                e.preventDefault();
            }
        }
    });
    
    // Función para validar el formulario
    function validarFormulario() {
        // Validación de los campos del formulario
        const nombres = document.getElementById('nombres');
        const apellidos = document.getElementById('apellidos');
        const equipo = document.getElementById('equipo_id');
        const posicion = document.getElementById('posicion');
        const dorsal = document.getElementById('dorsal');
        let isValid = true;
        
        // Validar nombre del jugador
        if (!nombres.value.trim()) {
            showError(nombres, 'El nombre del jugador es obligatorio');
            isValid = false;
        } else if (nombres.value.trim().length < 2) {
            showError(nombres, 'El nombre debe tener al menos 2 caracteres');
            isValid = false;
        } else {
            clearError(nombres);
        }
        
        // Validar apellidos del jugador
        if (!apellidos.value.trim()) {
            showError(apellidos, 'Los apellidos del jugador son obligatorios');
            isValid = false;
        } else if (apellidos.value.trim().length < 2) {
            showError(apellidos, 'Los apellidos deben tener al menos 2 caracteres');
            isValid = false;
        } else {
            clearError(apellidos);
        }
        
        // Validar selección de equipo
        if (!equipo.value) {
            showError(equipo, 'Debe seleccionar un equipo para el jugador');
            isValid = false;
        } else {
            clearError(equipo);
        }
        
        // Validar posición
        if (!posicion.value) {
            showError(posicion, 'Debe seleccionar una posición para el jugador');
            isValid = false;
        } else {
            clearError(posicion);
        }
        
        // Validar número de camiseta (dorsal)
        if (!dorsal.value) {
            showError(dorsal, 'El número de camiseta es obligatorio');
            isValid = false;
        } else if (isNaN(dorsal.value)) {
            showError(dorsal, 'El número de camiseta debe ser un valor numérico');
            isValid = false;
        } else if (parseInt(dorsal.value) < 1) {
            showError(dorsal, 'El número de camiseta no puede ser negativo o cero');
            isValid = false;
        } else if (parseInt(dorsal.value) > 99) {
            showError(dorsal, 'El número de camiseta no puede ser mayor a 99');
            isValid = false;
        } else {
            clearError(dorsal);
        }
        
        // Validar imagen si se ha seleccionado
        const foto = document.getElementById('foto');
        if (foto && foto.files.length > 0) {
            const file = foto.files[0];
            const fileSize = file.size / 1024 / 1024; // tamaño en MB
            const validExtensions = ['image/jpeg', 'image/png', 'image/gif'];
            
            // Verificar tipo de archivo
            if (!validExtensions.includes(file.type)) {
                showError(foto, 'El formato de imagen no es válido. Sólo se permiten JPG, PNG o GIF');
                isValid = false;
            } 
            // Verificar tamaño de archivo
            else if (fileSize > 2) {
                showError(foto, 'La imagen es demasiado grande. El tamaño máximo permitido es 2MB');
                isValid = false;
            } else {
                clearError(foto);
            }
        }
        
        return isValid;
    }
}

/**
 * Configura los filtros específicos para la tabla de jugadores
 */
function setupJugadoresFilters() {
    // Filtro por equipo
    const equipoFilter = document.getElementById('filtro-equipo');
    if (equipoFilter) {
        equipoFilter.addEventListener('change', function() {
            const equipoValue = this.value;
            const table = document.getElementById('jugadores-table');
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const equipoCell = row.querySelector('td[data-column="equipo"]');
                    
                    if (equipoValue === '' || (equipoCell && equipoCell.textContent === equipoValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    }
    
    // Filtro por posición
    const posicionFilter = document.getElementById('filtro-posicion');
    if (posicionFilter) {
        posicionFilter.addEventListener('change', function() {
            const posicionValue = this.value;
            const table = document.getElementById('jugadores-table');
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const posicionCell = row.querySelector('td[data-column="posicion"]');
                    
                    if (posicionValue === '' || (posicionCell && posicionCell.textContent === posicionValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    }
}

/**
 * Muestra un mensaje de error para un campo de formulario
 */
function showError(field, message) {
    field.classList.add('error');
    
    // Eliminar mensaje de error previo si existe
    clearError(field);
    
    // Crear nuevo mensaje de error
    const errorMessage = document.createElement('div');
    errorMessage.className = 'error-message';
    errorMessage.textContent = message;
    field.parentNode.insertBefore(errorMessage, field.nextSibling);
}

/**
 * Elimina el mensaje de error de un campo
 */
function clearError(field) {
    field.classList.remove('error');
    
    // Buscar y eliminar mensaje de error si existe
    const errorMessage = field.nextElementSibling;
    if (errorMessage && errorMessage.className === 'error-message') {
        errorMessage.remove();
    }
}

// Array para almacenar temporalmente las estadísticas
let tempStats = {
    goles: [],
    asistencias: [],
    faltas: []
};

// Arrays para almacenar ids de registros eliminados
let deletedStats = {
    goles: [],
    asistencias: [],
    faltas: []
};

// Editar estadística (abrir modal y guardar edición)
let editingStat = { id: null, tipo: null };

// Función para manejar los formularios de estadísticas
function setupStatsForms() {
    // Formulario de goles
    const formGol = document.getElementById('form-add-gol');
    if (formGol) {
        // Validación en tiempo real para el minuto del gol
        const minutoGolInput = formGol.querySelector('input[name="minuto"]');
        if (minutoGolInput) {
            minutoGolInput.addEventListener('input', function() {
                validateMinutoInput(this, 'gol');
            });
        }
        
        formGol.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar el formulario antes de procesar
            if (!validarFormularioEstadistica(this, 'gol')) {
                return;
            }
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => data[key] = value);
            
            // Asegurarse de que partido_id esté presente
            if (!data.partido_id) {
                showNotification('error', 'Debe seleccionar un partido');
                return;
            }
            
            if (editingStat.id && editingStat.tipo === 'goles') {
                // Actualizar
                const idx = tempStats.goles.findIndex(item => item.id == editingStat.id);
                if (idx !== -1) {
                    tempStats.goles[idx] = { ...tempStats.goles[idx], ...data };
                    // Asegurarnos de que cod_par también esté presente si existía
                    if (tempStats.goles[idx].cod_par) {
                        tempStats.goles[idx].partido_id = tempStats.goles[idx].cod_par;
                    }
                }
                editingStat = { id: null, tipo: null };
            } else {
                // Nuevo
                const tempId = 'temp_' + Date.now();
                data.id = tempId;
                tempStats.goles.push(data);
            }
            closeModal('modal-gol');
            updateStatsTable('goles');
            this.reset();
        });
    }

    // Formulario de asistencias
    const formAsistencia = document.getElementById('form-add-asistencia');
    if (formAsistencia) {
        // Validación en tiempo real para el minuto de la asistencia
        const minutoAsistenciaInput = formAsistencia.querySelector('input[name="minuto"]');
        if (minutoAsistenciaInput) {
            minutoAsistenciaInput.addEventListener('input', function() {
                validateMinutoInput(this, 'asistencia');
            });
        }
        
        formAsistencia.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar el formulario antes de procesar
            if (!validarFormularioEstadistica(this, 'asistencia')) {
                return;
            }
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => data[key] = value);
            
            // Asegurarse de que partido_id esté presente
            if (!data.partido_id) {
                showNotification('error', 'Debe seleccionar un partido');
                return;
            }
            
            if (editingStat.id && editingStat.tipo === 'asistencias') {
                const idx = tempStats.asistencias.findIndex(item => item.id == editingStat.id);
                if (idx !== -1) {
                    tempStats.asistencias[idx] = { ...tempStats.asistencias[idx], ...data };
                    // Asegurarnos de que cod_par también esté presente si existía
                    if (tempStats.asistencias[idx].cod_par) {
                        tempStats.asistencias[idx].partido_id = tempStats.asistencias[idx].cod_par;
                    }
                }
                editingStat = { id: null, tipo: null };
            } else {
                const tempId = 'temp_' + Date.now();
                data.id = tempId;
                tempStats.asistencias.push(data);
            }
            closeModal('modal-asistencia');
            updateStatsTable('asistencias');
            this.reset();
        });
    }

    // Formulario de faltas
    const formFalta = document.getElementById('form-add-falta');
    if (formFalta) {
        // Validación en tiempo real para el minuto de la falta
        const minutoFaltaInput = formFalta.querySelector('input[name="minuto"]');
        if (minutoFaltaInput) {
            minutoFaltaInput.addEventListener('input', function() {
                validateMinutoInput(this, 'falta');
            });
        }
        
        formFalta.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar el formulario antes de procesar
            if (!validarFormularioEstadistica(this, 'falta')) {
                return;
            }
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => data[key] = value);
            
            // Asegurarse de que partido_id esté presente
            if (!data.partido_id) {
                showNotification('error', 'Debe seleccionar un partido');
                return;
            }
            
            if (editingStat.id && editingStat.tipo === 'faltas') {
                const idx = tempStats.faltas.findIndex(item => item.id == editingStat.id);
                if (idx !== -1) {
                    tempStats.faltas[idx] = { ...tempStats.faltas[idx], ...data };
                    // Asegurarnos de que cod_par también esté presente si existía
                    if (tempStats.faltas[idx].cod_par) {
                        tempStats.faltas[idx].partido_id = tempStats.faltas[idx].cod_par;
                    }
                }
                editingStat = { id: null, tipo: null };
            } else {
                const tempId = 'temp_' + Date.now();
                data.id = tempId;
                tempStats.faltas.push(data);
            }
            closeModal('modal-falta');
            updateStatsTable('faltas');
            this.reset();
        });
    }
}

/**
 * Valida un campo de minuto de una estadística
 */
function validateMinutoInput(input, tipo) {
    // Asegurarse de que sea un número
    if (input.value === '') {
        showErrorInModal(input, `El minuto del ${tipo} es obligatorio`);
        return false;
    } else if (isNaN(input.value)) {
        showErrorInModal(input, `El minuto debe ser un valor numérico`);
        return false;
    }
    
    // Convertir a número
    const minuto = parseInt(input.value);
    
    // Verificar rango válido (entre 0 y 50 para fútbol sala/futsala)
    if (minuto < 0) {
        showErrorInModal(input, `El minuto no puede ser negativo`);
        input.value = 0; // Corregir automáticamente
        return false;
    } else if (minuto > 50) {
        showErrorInModal(input, `El minuto no puede ser mayor a 50 (duración máxima del partido)`);
        input.value = 50; // Corregir automáticamente
        return false;
    }
    
    // Si llega aquí, es válido
    clearErrorInModal(input);
    return true;
}

/**
 * Muestra un mensaje de error en los formularios de modal
 */
function showErrorInModal(input, message) {
    // Crear o actualizar el mensaje de error
    let errorElement = input.parentNode.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.style.color = 'red';
        errorElement.style.fontSize = '12px';
        errorElement.style.marginTop = '5px';
        input.parentNode.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
    input.classList.add('input-error');
}

/**
 * Limpia un mensaje de error en los formularios de modal
 */
function clearErrorInModal(input) {
    const errorElement = input.parentNode.querySelector('.error-message');
    if (errorElement) {
        errorElement.remove();
    }
    input.classList.remove('input-error');
}

/**
 * Valida un formulario de estadística completo
 */
function validarFormularioEstadistica(form, tipo) {
    let isValid = true;
    
    // Validar selección de partido
    const partidoSelect = form.querySelector('select[name="partido_id"]');
    if (!partidoSelect.value) {
        showErrorInModal(partidoSelect, `Debe seleccionar un partido para registrar el ${tipo}`);
        isValid = false;
    } else {
        clearErrorInModal(partidoSelect);
    }
    
    // Validar jugador
    const jugadorSelect = form.querySelector('select[name="jugador_id"]');
    if (!jugadorSelect.value) {
        showErrorInModal(jugadorSelect, `Debe seleccionar un jugador para registrar el ${tipo}`);
        isValid = false;
    } else {
        clearErrorInModal(jugadorSelect);
    }
    
    // Validar minuto
    const minutoInput = form.querySelector('input[name="minuto"]');
    if (!validateMinutoInput(minutoInput, tipo)) {
        isValid = false;
    }
    
    // Validar tipo específico si aplica
    if (tipo === 'gol') {
        const tipoSelect = form.querySelector('select[name="tipo"]');
        if (!tipoSelect.value) {
            showErrorInModal(tipoSelect, 'Debe seleccionar un tipo de gol');
            isValid = false;
        } else {
            clearErrorInModal(tipoSelect);
        }
    } else if (tipo === 'falta') {
        const tipoFaltaSelect = form.querySelector('select[name="tipo_falta"]');
        if (!tipoFaltaSelect.value) {
            showErrorInModal(tipoFaltaSelect, 'Debe seleccionar un tipo de tarjeta');
            isValid = false;
        } else {
            clearErrorInModal(tipoFaltaSelect);
        }
    }
    
    return isValid;
}

// Función para actualizar la tabla de estadísticas
function updateStatsTable(tipo) {
    const tableBody = document.querySelector(`#tabla-${tipo} tbody`);
    if (tableBody) {
        tableBody.innerHTML = '';
        tempStats[tipo].forEach(item => {
            const row = createStatsRow(item, tipo);
            tableBody.appendChild(row);
        });
    }
}

// Función para crear una fila de estadísticas
function createStatsRow(item, tipo) {
    const row = document.createElement('tr');
    let partido = item.partido_id || item.cod_par || '';
    
    // Si viene de la base de datos, mostrar el nombre del partido
    if (item.equipo_local && item.equipo_visitante) {
        partido = `${item.equipo_local} vs ${item.equipo_visitante}`;
    }
    
    // Asegurarnos de que el item tenga el campo partido_id
    if (!item.partido_id && item.cod_par) {
        item.partido_id = item.cod_par;
    }
    
    switch(tipo) {
        case 'goles':
            row.innerHTML = `
                <td>${partido}</td>
                <td>${item.minuto}</td>
                <td>${item.tipo}</td>
                <td>
                    <button type="button" onclick="editTempStat('${item.id}', 'goles')" class="action-btn edit" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" onclick="deleteTempStat('${item.id}', 'goles')" class="action-btn delete" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            break;
        case 'asistencias':
            row.innerHTML = `
                <td>${partido}</td>
                <td>${item.minuto}</td>
                <td>
                    <button type="button" onclick="editTempStat('${item.id}', 'asistencias')" class="action-btn edit" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" onclick="deleteTempStat('${item.id}', 'asistencias')" class="action-btn delete" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            break;
        case 'faltas':
            row.innerHTML = `
                <td>${partido}</td>
                <td>${item.minuto}</td>
                <td>${item.tipo_falta}</td>
                <td>
                    <button type="button" onclick="editTempStat('${item.id}', 'faltas')" class="action-btn edit" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" onclick="deleteTempStat('${item.id}', 'faltas')" class="action-btn delete" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            break;
    }
    return row;
} 
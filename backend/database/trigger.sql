-- Elimina el trigger y la función si existen
DROP TRIGGER IF EXISTS trigger_avanzar_fase ON Partidos;
DROP FUNCTION IF EXISTS avanzar_fase();

CREATE OR REPLACE FUNCTION avanzar_fase()
RETURNS TRIGGER AS $$
DECLARE
    goles_local INT;
    goles_visitante INT;
    ganador INT;
    bracket_actual RECORD;
    bracket_siguiente_var INT;
    posicion_en_siguiente VARCHAR(10);
    partido_siguiente RECORD;
    id_nueva_cancha INT;
    fecha_siguiente DATE;
    hora_siguiente TIME;
    otro_ganador INT := NULL;
    otro_bracket RECORD;
    otro_partido RECORD;
    goles_otro_local INT;
    goles_otro_visitante INT;
    ganador_decidido BOOLEAN := FALSE;
BEGIN
    -- Registrar información importante del trigger
    RAISE NOTICE '⚠️ TRIGGER activado: Partido % estado OLD: % -> NEW: %', 
                 NEW.cod_par, 
                 COALESCE(OLD.estado, 'NULL'), 
                 COALESCE(NEW.estado, 'NULL');
    
    -- Solo proceder si el partido cambió a estado finalizado
    IF NEW.estado = 'finalizado' AND (OLD.estado IS NULL OR OLD.estado = 'programado') THEN
        RAISE NOTICE '🏁 Partido % cambiado a finalizado', NEW.cod_par;
        RAISE NOTICE '⚽ Equipos - Local: %, Visitante: %', NEW.equ_local, NEW.equ_visitante;
        
        -- Calcular goles local (incluye autogoles del rival)
        SELECT 
            COALESCE((SELECT COUNT(*) FROM Goles WHERE cod_par = NEW.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_local) AND tipo IN ('normal', 'penal')), 0)
            + COALESCE((SELECT COUNT(*) FROM Goles WHERE cod_par = NEW.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_visitante) AND tipo = 'autogol'), 0)
        INTO goles_local;

        -- Calcular goles visitante (incluye autogoles del rival)
        SELECT 
            COALESCE((SELECT COUNT(*) FROM Goles WHERE cod_par = NEW.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_visitante) AND tipo IN ('normal', 'penal')), 0)
            + COALESCE((SELECT COUNT(*) FROM Goles WHERE cod_par = NEW.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_local) AND tipo = 'autogol'), 0)
        INTO goles_visitante;

        RAISE NOTICE '📊 Marcador: Local % - Visitante %', goles_local, goles_visitante;

        -- Determinar el ganador
        IF goles_local > goles_visitante THEN
            ganador := NEW.equ_local;
            ganador_decidido := TRUE;
            RAISE NOTICE '🏆 Ganador: equipo local %', ganador;
        ELSIF goles_visitante > goles_local THEN
            ganador := NEW.equ_visitante;
            ganador_decidido := TRUE;
            RAISE NOTICE '🏆 Ganador: equipo visitante %', ganador;
        ELSE
            -- En caso de empate, verificamos si hay algún gol en absoluto
            IF (goles_local + goles_visitante) = 0 THEN
                RAISE NOTICE '⚠️ No hay goles registrados, no se puede determinar un ganador';
                -- Intentar verificar si alguno de los equipos tiene jugadores que anotaron goles
                DECLARE
                    total_goles_del_partido INT;
                BEGIN
                    SELECT COUNT(*) INTO total_goles_del_partido FROM Goles WHERE cod_par = NEW.cod_par;
                    IF total_goles_del_partido > 0 THEN
                        -- Hay goles pero el cálculo dio 0-0, algo está mal con los jugadores
                        RAISE NOTICE '⚠️ Hay % goles registrados pero el cálculo dio 0-0. Revisar que los jugadores pertenezcan a los equipos correctos', total_goles_del_partido;
                    END IF;
                END;
            ELSE
                -- Hay un empate con goles, necesitamos un desempate
                RAISE NOTICE '⚠️ Empate %-%: necesitamos un desempate', goles_local, goles_visitante;
                -- En un torneo real esto podría ser por penales, pero para este caso, decidimos por el equipo local
                ganador := NEW.equ_local;
                ganador_decidido := TRUE;
                RAISE NOTICE '🏆 Ganador por desempate: equipo local %', ganador;
            END IF;
        END IF;

        -- Solo proceder si hay un ganador
        IF ganador_decidido THEN
            -- Buscar el bracket actual que contiene este partido
            SELECT * INTO bracket_actual FROM Brackets WHERE cod_par = NEW.cod_par;
            
            -- Si encontramos el bracket actual, proceder con la actualización
            IF FOUND THEN
                RAISE NOTICE '📋 Bracket actual: %, fase: %', bracket_actual.bracket_id, bracket_actual.fase;
                
                -- Guardar información para la siguiente fase
                bracket_siguiente_var := bracket_actual.bracket_siguiente;
                posicion_en_siguiente := bracket_actual.posicion_siguiente;
                
                RAISE NOTICE '📋 Bracket siguiente: %, posición: %', bracket_siguiente_var, posicion_en_siguiente;
                
                -- Si hay un bracket siguiente, actualizarlo
                IF bracket_siguiente_var IS NOT NULL THEN
                    -- Buscar si ya existe un partido en el bracket siguiente
                    SELECT b.*, p.* INTO partido_siguiente 
                    FROM Brackets b 
                    LEFT JOIN Partidos p ON b.cod_par = p.cod_par 
                    WHERE b.bracket_id = bracket_siguiente_var;
                    
                    -- Usar la misma cancha para el siguiente partido o elegir la primera disponible
                    id_nueva_cancha := NEW.cod_cancha;
                    
                    -- Establecer fecha y hora para el siguiente partido (7 días después a la misma hora)
                    fecha_siguiente := CURRENT_DATE + INTERVAL '7 days';
                    hora_siguiente := NEW.hora;
                    
                    RAISE NOTICE '🔍 Verificando si ya existe partido en el bracket siguiente';
                    
                    -- Si no existe partido en el bracket siguiente, verificar si ambos equipos están disponibles
                    IF partido_siguiente.cod_par IS NULL THEN
                        RAISE NOTICE '🆕 No existe partido en el bracket siguiente';
                        
                        -- Buscar el otro bracket que alimenta al mismo bracket_siguiente
                        SELECT * INTO otro_bracket
                        FROM Brackets 
                        WHERE bracket_siguiente = bracket_siguiente_var 
                          AND bracket_id != bracket_actual.bracket_id;
                        
                        IF FOUND THEN
                            RAISE NOTICE '📋 Otro bracket encontrado: %', otro_bracket.bracket_id;
                            
                            -- Verificar si el otro bracket ya tiene un partido asignado
                            IF otro_bracket.cod_par IS NOT NULL THEN
                                RAISE NOTICE '🔍 Otro bracket tiene partido asignado: %', otro_bracket.cod_par;
                                
                                -- Buscar el partido del otro bracket
                                SELECT * INTO otro_partido
                                FROM Partidos
                                WHERE cod_par = otro_bracket.cod_par;
                                
                                IF FOUND THEN
                                    RAISE NOTICE '🔍 Otro partido encontrado, estado: %', otro_partido.estado;
                                    
                                    -- Verificar si el otro partido está finalizado
                                    IF otro_partido.estado = 'finalizado' THEN
                                        RAISE NOTICE '🏁 Otro partido está finalizado';
                                        
                                        -- Calcular goles para el otro partido
                                        SELECT 
                                            COALESCE((SELECT COUNT(*) FROM Goles WHERE cod_par = otro_partido.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = otro_partido.equ_local) AND tipo IN ('normal', 'penal')), 0)
                                            + COALESCE((SELECT COUNT(*) FROM Goles WHERE cod_par = otro_partido.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = otro_partido.equ_visitante) AND tipo = 'autogol'), 0)
                                        INTO goles_otro_local;
                                        
                                        SELECT 
                                            COALESCE((SELECT COUNT(*) FROM Goles WHERE cod_par = otro_partido.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = otro_partido.equ_visitante) AND tipo IN ('normal', 'penal')), 0)
                                            + COALESCE((SELECT COUNT(*) FROM Goles WHERE cod_par = otro_partido.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = otro_partido.equ_local) AND tipo = 'autogol'), 0)
                                        INTO goles_otro_visitante;
                                        
                                        RAISE NOTICE '📊 Marcador otro partido: Local % - Visitante %', goles_otro_local, goles_otro_visitante;
                                        
                                        -- Determinar el ganador del otro partido
                                        IF goles_otro_local > goles_otro_visitante THEN
                                            otro_ganador := otro_partido.equ_local;
                                            RAISE NOTICE '🏆 Otro ganador: equipo local %', otro_ganador;
                                        ELSIF goles_otro_visitante > goles_otro_local THEN
                                            otro_ganador := otro_partido.equ_visitante;
                                            RAISE NOTICE '🏆 Otro ganador: equipo visitante %', otro_ganador;
                                        ELSIF (goles_otro_local + goles_otro_visitante) > 0 THEN
                                            -- En caso de empate con goles, decidimos por el local
                                            otro_ganador := otro_partido.equ_local;
                                            RAISE NOTICE '🏆 Otro ganador por desempate: equipo local %', otro_ganador;
                                        ELSE
                                            otro_ganador := NULL;
                                            RAISE NOTICE '⚠️ Empate en el otro partido: no hay ganador definido';
                                        END IF;
                                    END IF;
                                END IF;
                            END IF;
                        END IF;
                        
                        -- Crear el partido solo si tenemos ambos ganadores
                        IF otro_ganador IS NOT NULL THEN
                            RAISE NOTICE '✅ Creando partido con ganadores: % y %', ganador, otro_ganador;
                            
                            -- Crear un nuevo partido con ambos ganadores en las posiciones correspondientes
                            IF posicion_en_siguiente = 'local' THEN
                                RAISE NOTICE '➡️ Insertando: local=%, visitante=%', ganador, otro_ganador;
                                INSERT INTO Partidos (fecha, hora, cod_cancha, equ_local, equ_visitante, estado)
                                VALUES (fecha_siguiente, hora_siguiente, id_nueva_cancha, ganador, otro_ganador, 'programado')
                                RETURNING cod_par INTO partido_siguiente.cod_par;
                            ELSE -- 'visitante'
                                RAISE NOTICE '➡️ Insertando: local=%, visitante=%', otro_ganador, ganador;
                                INSERT INTO Partidos (fecha, hora, cod_cancha, equ_local, equ_visitante, estado)
                                VALUES (fecha_siguiente, hora_siguiente, id_nueva_cancha, otro_ganador, ganador, 'programado')
                                RETURNING cod_par INTO partido_siguiente.cod_par;
                            END IF;
                            
                            -- Actualizar el bracket siguiente con el nuevo partido
                            UPDATE Brackets SET cod_par = partido_siguiente.cod_par WHERE bracket_id = bracket_siguiente_var;
                            RAISE NOTICE '✅ Partido creado y asignado al bracket %', bracket_siguiente_var;
                        ELSE
                            RAISE NOTICE '⚠️ No se creó partido porque falta el otro ganador';
                        END IF;
                    ELSE
                        RAISE NOTICE '🔄 Ya existe partido en el bracket siguiente: %', partido_siguiente.cod_par;
                        
                        -- Si ya existe un partido, actualizar el equipo correspondiente
                        IF posicion_en_siguiente = 'local' THEN
                            UPDATE Partidos SET equ_local = ganador WHERE cod_par = partido_siguiente.cod_par;
                            RAISE NOTICE '✅ Actualizado equipo local del partido existente';
                        ELSE -- 'visitante'
                            UPDATE Partidos SET equ_visitante = ganador WHERE cod_par = partido_siguiente.cod_par;
                            RAISE NOTICE '✅ Actualizado equipo visitante del partido existente';
                        END IF;
                    END IF;
                END IF;
            ELSE
                RAISE NOTICE '⚠️ No se encontró el bracket actual';
                
                -- Si no encontramos el bracket actual, intentar asociar este partido a un bracket
                -- basándonos en el orden (los primeros partidos en cada fase)
                SELECT * INTO bracket_actual FROM Brackets 
                WHERE cod_par IS NULL 
                ORDER BY bracket_id LIMIT 1;
                
                IF FOUND THEN
                    -- Asociar este partido al bracket
                    UPDATE Brackets SET cod_par = NEW.cod_par WHERE bracket_id = bracket_actual.bracket_id;
                    RAISE NOTICE '✅ Partido asociado al bracket %', bracket_actual.bracket_id;
                END IF;
            END IF;
        ELSE
            RAISE NOTICE '⚠️ No hay ganador definido, no se puede avanzar de fase';
        END IF;
    ELSE
        RAISE NOTICE '⚠️ No se procesó el partido porque no cumple las condiciones (estado viejo: %, nuevo: %)', 
                     COALESCE(OLD.estado, 'NULL'), 
                     COALESCE(NEW.estado, 'NULL');
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_avanzar_fase
AFTER UPDATE ON Partidos
FOR EACH ROW
EXECUTE FUNCTION avanzar_fase();
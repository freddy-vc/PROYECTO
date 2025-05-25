-- Elimina el trigger y la función si existen
DROP TRIGGER IF EXISTS trigger_avanzar_fase ON Partidos;
DROP FUNCTION IF EXISTS avanzar_fase();

CREATE OR REPLACE FUNCTION avanzar_fase()
RETURNS TRIGGER AS $$
DECLARE
    goles_local INT;
    goles_visitante INT;
    ganador INT;
    siguiente_fase VARCHAR(50);
    semifinal_num INT;
    partido_semifinal RECORD;
    cuartos_ids INT[];
    idx INT;
BEGIN
    IF NEW.estado = 'finalizado' THEN
        -- Calcular goles local (incluye autogoles del rival)
        SELECT 
            (SELECT COUNT(*) FROM Goles WHERE cod_par = NEW.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_local) AND tipo IN ('normal', 'penal'))
            + (SELECT COUNT(*) FROM Goles WHERE cod_par = NEW.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_visitante) AND tipo = 'autogol')
            - (SELECT COUNT(*) FROM Goles WHERE cod_par = NEW.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_local) AND tipo = 'autogol')
        INTO goles_local;

        -- Calcular goles visitante (incluye autogoles del rival)
        SELECT 
            (SELECT COUNT(*) FROM Goles WHERE cod_par = NEW.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_visitante) AND tipo IN ('normal', 'penal'))
            + (SELECT COUNT(*) FROM Goles WHERE cod_par = NEW.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_local) AND tipo = 'autogol')
            - (SELECT COUNT(*) FROM Goles WHERE cod_par = NEW.cod_par AND cod_jug IN (SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_visitante) AND tipo = 'autogol')
        INTO goles_visitante;

        -- Determinar el ganador
        IF goles_local > goles_visitante THEN
            ganador := NEW.equ_local;
        ELSIF goles_visitante > goles_local THEN
            ganador := NEW.equ_visitante;
        ELSE
            ganador := NULL; -- Empate
        END IF;

        -- Determinar la siguiente fase
        IF NEW.fase = 'cuartos' THEN
            siguiente_fase := 'semis';
        ELSIF NEW.fase = 'semis' THEN
            siguiente_fase := 'final';
        ELSE
            siguiente_fase := NULL;
        END IF;

        -- Solo avanzar si hay un ganador y hay siguiente fase
        IF ganador IS NOT NULL AND siguiente_fase IS NOT NULL THEN
            -- Obtener los cod_par de los partidos de cuartos, ordenados
            SELECT array_agg(cod_par ORDER BY cod_par ASC) INTO cuartos_ids FROM Partidos WHERE fase = 'cuartos';

            -- Buscar el índice del partido actual en el array
            FOR idx IN 1..array_length(cuartos_ids, 1) LOOP
                IF cuartos_ids[idx] = NEW.cod_par THEN
                    EXIT;
                END IF;
            END LOOP;

            -- Calcular a qué semifinal corresponde (1 o 2, etc.)
            semifinal_num := CEIL(idx::NUMERIC / 2);

            -- Buscar el partido de semifinal correspondiente (por orden de cod_par)
            SELECT * INTO partido_semifinal
            FROM Partidos
            WHERE fase = siguiente_fase
            ORDER BY cod_par ASC
            OFFSET semifinal_num - 1 LIMIT 1;

            -- Si no existe, crear el partido de semifinal con el ganador como local
            IF NOT FOUND THEN
                INSERT INTO Partidos (fecha, hora, cod_cancha, equ_local, equ_visitante, estado, fase)
                VALUES (CURRENT_DATE + INTERVAL '1 day', '18:00', NEW.cod_cancha, ganador, NULL, 'programado', siguiente_fase);
            ELSE
                -- Si existe, asignar el ganador como visitante si el local ya está asignado y el visitante está NULL
                IF partido_semifinal.equ_local IS NOT NULL AND partido_semifinal.equ_visitante IS NULL THEN
                    UPDATE Partidos SET equ_visitante = ganador WHERE cod_par = partido_semifinal.cod_par;
                END IF;
            END IF;
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_avanzar_fase ON Partidos;
CREATE TRIGGER trigger_avanzar_fase
AFTER UPDATE ON Partidos
FOR EACH ROW
WHEN (OLD.estado IS DISTINCT FROM NEW.estado)
EXECUTE FUNCTION avanzar_fase();
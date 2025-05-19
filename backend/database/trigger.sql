-- Función para crear el partido de la siguiente fase
CREATE OR REPLACE FUNCTION avanzar_fase()
RETURNS TRIGGER AS $$
DECLARE
    goles_local INT;
    goles_visitante INT;
    ganador INT;
    siguiente_fase VARCHAR(50);
BEGIN
    IF NEW.estado = 'finalizado' THEN
        -- Contar goles del equipo local
        SELECT COUNT(*) INTO goles_local
        FROM Goles
        WHERE cod_par = NEW.cod_par AND cod_jug IN (
            SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_local
        );

        -- Contar goles del equipo visitante
        SELECT COUNT(*) INTO goles_visitante
        FROM Goles
        WHERE cod_par = NEW.cod_par AND cod_jug IN (
            SELECT cod_jug FROM Jugadores WHERE cod_equ = NEW.equ_visitante
        );

        -- Determinar el ganador
        IF goles_local > goles_visitante THEN
            ganador := NEW.equ_local;
        ELSE
            ganador := NEW.equ_visitante;
        END IF;

        -- Determinar la siguiente fase
        IF NEW.fase = 'cuartos' THEN
            siguiente_fase := 'semis';
        ELSIF NEW.fase = 'semis' THEN
            siguiente_fase := 'final';
        ELSE
            -- Si es la final, no hay siguiente fase
            RETURN NEW;
        END IF;

        -- Crear el nuevo partido para la siguiente fase
        INSERT INTO Partidos (fecha, hora, cod_cancha, equ_local, equ_visitante, fase)
        VALUES (CURRENT_DATE + INTERVAL '1 day', '18:00', NEW.cod_cancha, ganador, NULL, siguiente_fase);
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger que llama a la función después de actualizar el estado del partido
CREATE TRIGGER trigger_avanzar_fase
AFTER UPDATE ON Partidos
FOR EACH ROW
WHEN (OLD.estado IS DISTINCT FROM NEW.estado)
EXECUTE FUNCTION avanzar_fase();

-- Insertar 4 ciudades
INSERT INTO Ciudades (nombre) VALUES 
('Bogotá'), ('Medellín'), ('Cali'), ('Barranquilla');

-- Insertar 8 directores técnicos
INSERT INTO Directores (nombres, apellidos) VALUES 
('Carlos', 'Ramírez'),
('Luis', 'Martínez'),
('Andrés', 'Pérez'),
('Juan', 'Gómez'),
('Pedro', 'Suárez'),
('Jorge', 'López'),
('Diego', 'Torres'),
('Santiago', 'Moreno');

-- Insertar 8 equipos, asignando ciudades y directores técnicos
INSERT INTO Equipos (nombre, cod_ciu, cod_dt) VALUES 
('Leones FC', 1, 1),
('Tigres FC', 1, 2),
('Águilas Doradas', 2, 3),
('Dragones FC', 2, 4),
('Halcones FC', 3, 5),
('Pumas FC', 3, 6),
('Toros FC', 4, 7),
('Panteras FC', 4, 8);

-- Insertar 4 canchas
INSERT INTO Canchas (nombre, direccion, capacidad) VALUES 
('Estadio Nacional', 'Calle 123 #45-67', 40000),
('Coliseo Deportivo', 'Carrera 8 #45-10', 30000),
('Arena Metropolitana', 'Avenida 10 #50-12', 35000),
('Campo Central', 'Calle 50 #20-10', 25000);

-- Insertar 10 jugadores por cada uno de los 8 equipos (80 jugadores)
DO $$
DECLARE
    equipo_id INT;
    jugador_num INT;
    posiciones TEXT[] := ARRAY['delantero', 'defensa', 'mediocampista', 'arquero'];
BEGIN
    FOR equipo_id IN 1..8 LOOP
        FOR jugador_num IN 1..10 LOOP
            INSERT INTO Jugadores (nombres, apellidos, posicion, dorsal, cod_equ)
            VALUES (
                'Jugador' || jugador_num || '_E' || equipo_id,
                'Apellido' || jugador_num,
                posiciones[(jugador_num % 4) + 1],
                jugador_num,
                equipo_id
            );
        END LOOP;
    END LOOP;
END
$$;

-- Insertar 4 partidos con los 8 equipos y 4 canchas
INSERT INTO Partidos (fecha, hora, cod_cancha, equ_local, equ_visitante) VALUES 
('2025-06-01', '15:00:00', 1, 1, 2),
('2025-06-01', '18:00:00', 2, 3, 4),
('2025-06-02', '15:00:00', 3, 5, 6),
('2025-06-02', '18:00:00', 4, 7, 8);

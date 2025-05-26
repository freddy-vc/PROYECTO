-- ----------------------
-- 1. Ciudades
-- ----------------------
CREATE TABLE Ciudades (
    cod_ciu SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- ----------------------
-- 2. Directores Técnicos
-- ----------------------
CREATE TABLE Directores (
    cod_dt SERIAL PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL
);

-- ----------------------
-- 3. Equipos
-- ----------------------
CREATE TABLE Equipos (
    cod_equ SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    cod_ciu INT NOT NULL,
    escudo BYTEA DEFAULT NULL,
    cod_dt INT NOT NULL UNIQUE,
    FOREIGN KEY (cod_ciu) REFERENCES Ciudades(cod_ciu)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (cod_dt) REFERENCES Directores(cod_dt)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- ----------------------
-- 4. Jugadores
-- ----------------------
CREATE TABLE Jugadores (
    cod_jug SERIAL PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    posicion VARCHAR(50),
    dorsal INT,
    cod_equ INT NOT NULL,
    foto BYTEA DEFAULT NULL, 
    FOREIGN KEY (cod_equ) REFERENCES Equipos(cod_equ)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CHECK (posicion IN ('delantero', 'defensa', 'mediocampista', 'arquero'))
);

-- ----------------------
-- 5. Canchas
-- ----------------------
CREATE TABLE Canchas (
    cod_cancha SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(255),
    capacidad INT
);

-- ----------------------
-- 6. Partidos
-- ----------------------
CREATE TABLE Partidos (
    cod_par SERIAL PRIMARY KEY,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    cod_cancha INT NOT NULL,
    equ_local INT NOT NULL,
    equ_visitante INT NOT NULL,
    estado VARCHAR(20) DEFAULT 'programado',
    FOREIGN KEY (cod_cancha) REFERENCES Canchas(cod_cancha)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (equ_local) REFERENCES Equipos(cod_equ)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (equ_visitante) REFERENCES Equipos(cod_equ)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CHECK (estado IN ('programado', 'finalizado'))
);

-- ----------------------
-- 6b. Brackets (Estructura del torneo)
-- ----------------------
CREATE TABLE Brackets (
    bracket_id INT PRIMARY KEY,
    fase VARCHAR(50) NOT NULL,
    cod_par INT NULL,
    bracket_siguiente INT NULL,
    posicion_siguiente VARCHAR(10) NULL, -- 'local' o 'visitante'
    CHECK (fase IN ('cuartos', 'semis', 'final')),
    FOREIGN KEY (cod_par) REFERENCES Partidos(cod_par)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    FOREIGN KEY (bracket_siguiente) REFERENCES Brackets(bracket_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- Insertar la estructura de brackets (1-4: cuartos, 5-6: semis, 7: final)
INSERT INTO Brackets (bracket_id, fase, bracket_siguiente, posicion_siguiente) VALUES
(1, 'cuartos', 5, 'local'),
(2, 'cuartos', 5, 'visitante'),
(3, 'cuartos', 6, 'local'),
(4, 'cuartos', 6, 'visitante'),
(5, 'semis', 7, 'local'),
(6, 'semis', 7, 'visitante'),
(7, 'final', NULL, NULL);

-- ----------------------
-- 7. Goles
-- ----------------------
CREATE TABLE Goles (
    cod_gol SERIAL PRIMARY KEY,
    cod_par INT NOT NULL,
    cod_jug INT NOT NULL,
    minuto INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    FOREIGN KEY (cod_par) REFERENCES Partidos(cod_par)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (cod_jug) REFERENCES Jugadores(cod_jug)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CHECK (tipo IN ('normal', 'penal', 'autogol')),
    CHECK (minuto >= 0 AND minuto <= 50)
);

-- ----------------------
-- 8. Asistencias
-- ----------------------
CREATE TABLE Asistencias (
    cod_asis SERIAL PRIMARY KEY,
    cod_par INT NOT NULL,
    cod_jug INT NOT NULL,
    minuto INT NOT NULL,
    FOREIGN KEY (cod_par) REFERENCES Partidos(cod_par)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (cod_jug) REFERENCES Jugadores(cod_jug)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CHECK (minuto >= 0 AND minuto <= 50)
);

-- ----------------------
-- 9. Faltas
-- ----------------------
CREATE TABLE Faltas (
    cod_falta SERIAL PRIMARY KEY,
    cod_par INT NOT NULL,
    cod_jug INT NOT NULL,
    minuto INT NOT NULL,
    tipo_falta VARCHAR(50) NOT NULL,
    FOREIGN KEY (cod_par) REFERENCES Partidos(cod_par)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (cod_jug) REFERENCES Jugadores(cod_jug)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CHECK (tipo_falta IN ('roja', 'amarilla', 'normal')),
    CHECK (minuto >= 0 AND minuto <= 50)
);

-- ----------------------
-- 10. Usuarios
-- ----------------------
CREATE TABLE Usuarios (
    cod_user SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(50) DEFAULT 'usuario',
    foto_perfil BYTEA DEFAULT NULL, 
    CHECK (rol IN ('admin', 'usuario'))
);

-- ----------------------
-- 1. Insertar Ciudades
-- ----------------------
INSERT INTO Ciudades (nombre) VALUES
('Madrid'),
('Barcelona'),
('Buenos Aires'),
('São Paulo'),
('Londres'),
('Munich'),
('Roma'),
('Amsterdam')
RETURNING *;

-- ----------------------
-- 2. Insertar Directores Técnicos
-- ----------------------
INSERT INTO Directores (nombres, apellidos) VALUES
('Carlo', 'Ancelotti'),
('Ronald', 'Koeman'),
('Marcelo', 'Gallardo'),
('Tite', 'da Silva'),
('Pep', 'Guardiola'),
('Hansi', 'Flick'),
('José', 'Mourinho'),
('Frank', 'de Boer')
RETURNING *;

-- ----------------------
-- 3. Insertar Equipos
-- ----------------------
INSERT INTO Equipos (nombre, cod_ciu, cod_dt) VALUES
('Real Madrid', 1, 1),
('FC Barcelona', 2, 2),
('River Plate', 3, 3),
('Corinthians', 4, 4),
('Manchester City', 5, 5),
('Bayern Munich', 6, 6),
('AS Roma', 7, 7),
('Ajax Amsterdam', 8, 8)
RETURNING *;

-- ----------------------
-- 4. Insertar Jugadores por Equipo
-- ----------------------
-- Real Madrid (cod_equ = 1)
INSERT INTO Jugadores (nombres, apellidos, posicion, dorsal, cod_equ) VALUES
('Thibaut', 'Courtois', 'arquero', 1, 1),
('David', 'Alaba', 'defensa', 4, 1),
('Vinicius', 'Jr.', 'delantero', 20, 1),
('Luka', 'Modric', 'mediocampista', 10, 1);

-- FC Barcelona (cod_equ = 2)
INSERT INTO Jugadores (nombres, apellidos, posicion, dorsal, cod_equ) VALUES
('Marc-André', 'ter Stegen', 'arquero', 1, 2),
('Ronald', 'Araújo', 'defensa', 4, 2),
('Robert', 'Lewandowski', 'delantero', 9, 2),
('Frenkie', 'de Jong', 'mediocampista', 21, 2);

-- River Plate (cod_equ = 3)
INSERT INTO Jugadores (nombres, apellidos, posicion, dorsal, cod_equ) VALUES
('Franco', 'Armani', 'arquero', 1, 3),
('Javier', 'Mascherano', 'defensa', 14, 3),
('Enzo', 'Pérez', 'mediocampista', 5, 3),
('Miguel', 'Borja', 'delantero', 9, 3);

-- Corinthians (cod_equ = 4)
INSERT INTO Jugadores (nombres, apellidos, posicion, dorsal, cod_equ) VALUES
('Cássio', 'Ramos', 'arquero', 1, 4),
('Gil', 'Silva', 'defensa', 3, 4),
('Renato', 'Augusto', 'mediocampista', 8, 4),
('Róger', 'Guedes', 'delantero', 11, 4);

-- Manchester City (cod_equ = 5)
INSERT INTO Jugadores (nombres, apellidos, posicion, dorsal, cod_equ) VALUES
('Ederson', 'Moraes', 'arquero', 31, 5),
('John', 'Stones', 'defensa', 5, 5),
('Kevin', 'De Bruyne', 'mediocampista', 17, 5),
('Erling', 'Haaland', 'delantero', 9, 5);

-- Bayern Munich (cod_equ = 6)
INSERT INTO Jugadores (nombres, apellidos, posicion, dorsal, cod_equ) VALUES
('Manuel', 'Neuer', 'arquero', 1, 6),
('Joshua', 'Kimmich', 'defensa', 6, 6),
('Thomas', 'Müller', 'mediocampista', 25, 6),
('Harry', 'Kane', 'delantero', 9, 6);

-- AS Roma (cod_equ = 7)
INSERT INTO Jugadores (nombres, apellidos, posicion, dorsal, cod_equ) VALUES
('Rui', 'Patrício', 'arquero', 1, 7),
('Chris', 'Smalling', 'defensa', 6, 7),
('Nicolò', 'Zaniolo', 'mediocampista', 22, 7),
('Paulo', 'Dybala', 'delantero', 21, 7);

-- Ajax Amsterdam (cod_equ = 8)
INSERT INTO Jugadores (nombres, apellidos, posicion, dorsal, cod_equ) VALUES
('Remko', 'Pasveer', 'arquero', 1, 8),
('Steven', 'Berghuis', 'mediocampista', 10, 8),
('Sebastian', 'Hallér', 'delantero', 9, 8),
('Edson', 'Álvarez', 'defensa', 4, 8);

-- ----------------------
-- 5. Insertar Canchas
-- ----------------------
INSERT INTO Canchas (nombre, direccion, capacidad) VALUES
('Estadio Santiago Bernabéu', 'Av. Concha Espina, Madrid', 81044),
('Camp Nou', 'Carrer d''Arístides Maillol, Barcelona', 99354),
('Estadio Monumental', 'Av. Del Libertador, Buenos Aires', 72000),
('Arena Corinthians', 'Av. Miguel Ignácio Curi, São Paulo', 49205),
('Etihad Stadium', 'Ashton New Road, Manchester', 55097),
('Allianz Arena', 'Werner-Heisenberg-Allee, Munich', 70000),
('Stadio Olimpico', 'Piazza di Spagna, Roma', 72698),
('Johan Cruyff Arena', 'Arenastrat, Ámsterdam', 51568)
RETURNING *;

-- ----------------------
-- 6. Insertar Partidos (fase de cuartos)
-- ----------------------
INSERT INTO Partidos (fecha, hora, cod_cancha, equ_local, equ_visitante, estado) VALUES
('2025-06-20', '15:00', 1, 1, 2, 'programado'),
('2025-06-20', '19:00', 3, 3, 4, 'programado'),
('2025-06-21', '15:00', 5, 5, 6, 'programado'),
('2025-06-21', '19:00', 7, 7, 8, 'programado')
RETURNING *;


INSERT INTO usuarios (cod_user, username, email, password, rol) VALUES
(0, 'admin', 'admin@admin.com', 'admin', 'admin')
RETURNING *;

-- ----------------------
-- 6b. Asociar brackets con partidos iniciales
-- ----------------------
-- Para realizar la asignación de manera más segura, utilizamos los IDs de partidos directamente
-- Declaramos como variables para asignarlas a los brackets
DO $$
DECLARE
    partido1 INT;
    partido2 INT;
    partido3 INT;
    partido4 INT;
BEGIN
    -- Obtenemos los IDs de los 4 partidos creados (en orden)
    SELECT cod_par INTO partido1 FROM Partidos ORDER BY cod_par ASC LIMIT 1 OFFSET 0;
    SELECT cod_par INTO partido2 FROM Partidos ORDER BY cod_par ASC LIMIT 1 OFFSET 1;
    SELECT cod_par INTO partido3 FROM Partidos ORDER BY cod_par ASC LIMIT 1 OFFSET 2;
    SELECT cod_par INTO partido4 FROM Partidos ORDER BY cod_par ASC LIMIT 1 OFFSET 3;
    
    -- Asignamos los partidos a los brackets correspondientes
    UPDATE Brackets SET cod_par = partido1 WHERE bracket_id = 1;
    UPDATE Brackets SET cod_par = partido2 WHERE bracket_id = 2;
    UPDATE Brackets SET cod_par = partido3 WHERE bracket_id = 3;
    UPDATE Brackets SET cod_par = partido4 WHERE bracket_id = 4;
END $$;
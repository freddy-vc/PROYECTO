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
    escudo BYTEA DEFAULT NULL, -- Imagen del equipo (binaria)
    cod_dt INT,
    FOREIGN KEY (cod_ciu) REFERENCES Ciudades(cod_ciu)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (cod_dt) REFERENCES Directores(cod_dt)
        ON DELETE SET NULL
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
    foto BYTEA DEFAULT NULL, -- Foto del jugador (binaria)
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
        ON DELETE RESTRICT
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
        ON DELETE RESTRICT
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
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CHECK (tipo_falta IN ('roja', 'amarilla', 'normal')),
    CHECK (minuto >= 0 AND minuto <= 50)
);

-- ----------------------
-- 10. FaseEquipo
-- ----------------------
CREATE TABLE FaseEquipo (
    cod_equ INT NOT NULL,
    fase VARCHAR(50) NOT NULL,
    clasificado BOOLEAN NOT NULL,
    FOREIGN KEY (cod_equ) REFERENCES Equipos(cod_equ)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    PRIMARY KEY (cod_equ, fase),
    CHECK (fase IN ('cuartos', 'semis', 'final'))
);

-- ----------------------
-- 11. Usuarios
-- ----------------------
CREATE TABLE Usuarios (
    cod_user SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(50) DEFAULT 'usuario',
    foto_perfil BYTEA DEFAULT NULL, -- Foto de perfil del usuario
    CHECK (rol IN ('admin', 'usuario'))
);
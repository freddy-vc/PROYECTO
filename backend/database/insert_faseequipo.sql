-- Script para insertar datos en la tabla FaseEquipo
-- Basado en los resultados de los partidos existentes

-- Equipos en fase de cuartos
-- Partidos finalizados: 
-- Partido 1: Leones FC (1) vs Tigres FC (2) = 2-0 → Leones FC clasifica
-- Partido 2: Águilas Doradas (3) vs Dragones FC (4) = 1-1 → Empate, consideramos que Águilas clasifica

-- Partidos programados:
-- Partido 3: Halcones FC (5) vs Pumas FC (6)
-- Partido 4: Toros FC (7) vs Panteras FC (8)

-- Inserción de equipos en fase de cuartos (todos los equipos)
INSERT INTO FaseEquipo (cod_equ, fase, clasificado) VALUES 
(1, 'cuartos', TRUE),   -- Leones FC (ganó 2-0)
(2, 'cuartos', FALSE),  -- Tigres FC (perdió 0-2)
(3, 'cuartos', TRUE),   -- Águilas Doradas (empató 1-1, consideramos que clasifica)
(4, 'cuartos', FALSE),  -- Dragones FC (empató 1-1, no clasifica)
(5, 'cuartos', FALSE),  -- Halcones FC (partido pendiente)
(6, 'cuartos', FALSE),  -- Pumas FC (partido pendiente)
(7, 'cuartos', FALSE),  -- Toros FC (partido pendiente)
(8, 'cuartos', FALSE);  -- Panteras FC (partido pendiente)

-- Inserción de equipos clasificados a semifinales
INSERT INTO FaseEquipo (cod_equ, fase, clasificado) VALUES 
(1, 'semis', FALSE),   -- Leones FC (clasificado a semis, pero aún no se juega)
(3, 'semis', FALSE);   -- Águilas Doradas (clasificado a semis, pero aún no se juega)

-- Nota: Para completar todas las semifinales, después de los partidos 3 y 4
-- se deberán agregar los equipos ganadores a la fase 'semis'

-- Nota: La fase 'final' se completará después de las semifinales 
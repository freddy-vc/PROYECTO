<?php
require_once __DIR__ . '/../database/connection.php';

/**
 * Clase Equipo
 * 
 * Maneja todas las operaciones relacionadas con equipos
 */
class Equipo
{
    // Conexión PDO
    private $db;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // Obtener la conexión a la base de datos
        $this->db = Conexion::getConexion();
    }
    
    /**
     * Obtener todos los equipos
     */
    public function obtenerTodos()
    {
        try {
            $query = "SELECT e.*, c.nombre as ciudad_nombre, 
                           d.nombres as dt_nombres, d.apellidos as dt_apellidos
                     FROM Equipos e
                     LEFT JOIN Ciudades c ON e.cod_ciu = c.cod_ciu
                     LEFT JOIN Directores d ON e.cod_dt = d.cod_dt
                     ORDER BY e.cod_equ";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar los escudos para mostrarlos como imágenes
            foreach ($equipos as &$equipo) {
                $this->procesarEscudo($equipo);
            }
            
            return $equipos;
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener un equipo por su ID
     */
    public function obtenerPorId($id)
    {
        try {
            $query = "SELECT e.*, c.nombre as ciudad_nombre, d.nombres as dt_nombres, d.apellidos as dt_apellidos FROM Equipos e LEFT JOIN Ciudades c ON e.cod_ciu = c.cod_ciu LEFT JOIN Directores d ON e.cod_dt = d.cod_dt WHERE e.cod_equ = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $equipo = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($equipo) {
                $equipo['id'] = $equipo['cod_equ'];
                if (isset($equipo['ciudad_nombre'])) {
                    $equipo['ciudad_nombre'] = $equipo['ciudad_nombre'];
                } else {
                    $equipo['ciudad_nombre'] = null;
                }
                $this->procesarEscudo($equipo);
            }
            return $equipo;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Crear un nuevo equipo
     */
    public function crear($nombre, $ciudad_id, $director_id = null, $escudo = null)
    {
        try {
            // Verificar si el director técnico ya está asignado a otro equipo
            if ($director_id !== null) {
                $query_check_dt = "SELECT cod_equ FROM Equipos WHERE cod_dt = :cod_dt";
                $stmt_check_dt = $this->db->prepare($query_check_dt);
                $stmt_check_dt->bindParam(':cod_dt', $director_id, PDO::PARAM_INT);
                $stmt_check_dt->execute();
                
                if ($stmt_check_dt->rowCount() > 0) {
                    return [
                        'estado' => false,
                        'mensaje' => 'No es posible asignar este director técnico porque ya está dirigiendo a otro equipo.'
                    ];
                }
            }
            
            // Preparar la consulta SQL
            $query = "INSERT INTO Equipos (nombre, cod_ciu, cod_dt, escudo) 
                     VALUES (:nombre, :cod_ciu, :cod_dt, :escudo)";
            
            $stmt = $this->db->prepare($query);
            
            // Asignar valores a los parámetros
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':cod_ciu', $ciudad_id, PDO::PARAM_INT);
            $stmt->bindParam(':cod_dt', $director_id, PDO::PARAM_INT);
            $stmt->bindParam(':escudo', $escudo, PDO::PARAM_LOB);
            
            // Ejecutar la consulta
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Equipo creado correctamente',
                'id' => $this->db->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            // Capturar el error específico de restricción única en cod_dt
            if (strpos($e->getMessage(), 'uq_director_tecnico') !== false || 
                strpos($e->getMessage(), 'duplicate key') !== false) {
                return [
                    'estado' => false,
                    'mensaje' => 'No es posible asignar este director técnico porque ya está dirigiendo a otro equipo.'
                ];
            }
            
            return [
                'estado' => false,
                'mensaje' => 'Error al crear el equipo: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar un equipo existente
     */
    public function actualizar($id, $nombre, $ciudad_id, $director_id = null, $escudo = null, $actualizar_escudo = false)
    {
        try {
            // Verificar si el director técnico ya está asignado a otro equipo
            if ($director_id !== null) {
                $query_check_dt = "SELECT cod_equ FROM Equipos WHERE cod_dt = :cod_dt AND cod_equ != :id";
                $stmt_check_dt = $this->db->prepare($query_check_dt);
                $stmt_check_dt->bindParam(':cod_dt', $director_id, PDO::PARAM_INT);
                $stmt_check_dt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt_check_dt->execute();
                
                if ($stmt_check_dt->rowCount() > 0) {
                    return [
                        'estado' => false,
                        'mensaje' => 'No es posible asignar este director técnico porque ya está dirigiendo a otro equipo.'
                    ];
                }
            }
            
            // Preparar la consulta SQL base
            $query = "UPDATE Equipos SET 
                     nombre = :nombre, 
                     cod_ciu = :cod_ciu, 
                     cod_dt = :cod_dt";
            
            // Si se debe actualizar el escudo, añadir al query
            if ($actualizar_escudo) {
                $query .= ", escudo = :escudo";
            }
            
            $query .= " WHERE cod_equ = :id";
            
            $stmt = $this->db->prepare($query);
            
            // Asignar valores a los parámetros
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':cod_ciu', $ciudad_id, PDO::PARAM_INT);
            $stmt->bindParam(':cod_dt', $director_id, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            // Si se debe actualizar el escudo, asignar el valor
            if ($actualizar_escudo) {
                $stmt->bindParam(':escudo', $escudo, PDO::PARAM_LOB);
            }
            
            // Ejecutar la consulta
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Equipo actualizado correctamente'
            ];
            
        } catch (PDOException $e) {
            // Capturar el error específico de restricción única en cod_dt
            if (strpos($e->getMessage(), 'uq_director_tecnico') !== false || 
                strpos($e->getMessage(), 'duplicate key') !== false) {
                return [
                    'estado' => false,
                    'mensaje' => 'No es posible asignar este director técnico porque ya está dirigiendo a otro equipo.'
                ];
            }
            
            return [
                'estado' => false,
                'mensaje' => 'Error al actualizar el equipo: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar un equipo
     */
    public function eliminar($id)
    {
        try {
            // Verificar si hay jugadores asociados al equipo
            $query_jugadores = "SELECT COUNT(*) FROM Jugadores WHERE cod_equ = :id";
            $stmt_jugadores = $this->db->prepare($query_jugadores);
            $stmt_jugadores->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_jugadores->execute();
            
            if ($stmt_jugadores->fetchColumn() > 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se puede eliminar el equipo porque tiene jugadores asociados'
                ];
            }
            
            // Verificar si hay partidos asociados al equipo
            $query_partidos = "SELECT COUNT(*) FROM Partidos WHERE equ_local = :id OR equ_visitante = :id";
            $stmt_partidos = $this->db->prepare($query_partidos);
            $stmt_partidos->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_partidos->execute();
            
            if ($stmt_partidos->fetchColumn() > 0) {
                return [
                    'estado' => false,
                    'mensaje' => 'No se puede eliminar el equipo porque tiene partidos asociados'
                ];
            }
            
            // Eliminar el equipo
            $query = "DELETE FROM Equipos WHERE cod_equ = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Equipo eliminado correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al eliminar el equipo: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener jugadores de un equipo
     */
    public function obtenerJugadores($equipo_id)
    {
        try {
            $query = "SELECT j.* 
                     FROM Jugadores j
                     WHERE j.cod_equ = :equipo_id
                     ORDER BY j.apellidos, j.nombres";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':equipo_id', $equipo_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar las fotos para mostrarlas como imágenes
            foreach ($jugadores as &$jugador) {
                if (!empty($jugador['foto'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($jugador['foto'])) {
                        $content = stream_get_contents($jugador['foto']);
                        rewind($jugador['foto']);
                        $jugador['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($jugador['foto']);
                    } else {
                        $jugador['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($jugador['foto']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($jugador['foto']);
                    }
                } else {
                    $jugador['foto_base64'] = '../assets/images/player.png';
                    unset($jugador['foto']);
                }
            }
            
            return $jugadores;
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener partidos de un equipo
     */
    public function obtenerPartidos($cod_equ)
    {
        try {
            // Consulta para obtener partidos donde el equipo es local o visitante
            $sql = "SELECT p.cod_par, p.fecha, p.hora, p.estado,
                    e1.cod_equ as local_id, e1.nombre as local_nombre, e1.escudo as local_escudo,
                    e2.cod_equ as visitante_id, e2.nombre as visitante_nombre, e2.escudo as visitante_escudo,
                    c.nombre as cancha
                    FROM Partidos p
                    JOIN Equipos e1 ON p.equ_local = e1.cod_equ
                    JOIN Equipos e2 ON p.equ_visitante = e2.cod_equ
                    JOIN Canchas c ON p.cod_cancha = c.cod_cancha
                    WHERE p.equ_local = :cod_equ OR p.equ_visitante = :cod_equ
                    ORDER BY p.fecha DESC, p.hora DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cod_equ', $cod_equ);
            $stmt->execute();
            
            $partidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar los escudos para mostrarlos como imágenes
            foreach ($partidos as &$partido) {
                // Procesar escudo del equipo local
                if (!empty($partido['local_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['local_escudo'])) {
                        $content = stream_get_contents($partido['local_escudo']);
                        rewind($partido['local_escudo']);
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['local_escudo']);
                    } else {
                        $partido['local_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['local_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['local_escudo']);
                    }
                } else {
                    $partido['local_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['local_escudo']);
                }
                
                // Procesar escudo del equipo visitante
                if (!empty($partido['visitante_escudo'])) {
                    // Verificar si es un recurso o un string
                    if (is_resource($partido['visitante_escudo'])) {
                        $content = stream_get_contents($partido['visitante_escudo']);
                        rewind($partido['visitante_escudo']);
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                        // Eliminar el recurso del array para evitar problemas con JSON
                        unset($partido['visitante_escudo']);
                    } else {
                        $partido['visitante_escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($partido['visitante_escudo']);
                        // Eliminar la versión binaria para evitar duplicados
                        unset($partido['visitante_escudo']);
                    }
                } else {
                    $partido['visitante_escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
                    unset($partido['visitante_escudo']);
                }
                
                // Formatear fecha para mostrar
                $fecha = new DateTime($partido['fecha']);
                $partido['fecha_formateada'] = $fecha->format('d/m/Y');
            }
            
            return $partidos;
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtiene información de las fases del torneo
     */
    public function obtenerFases() {
        try {
            $sql = "SELECT DISTINCT e.cod_equ, e.nombre, p.fase, 
                   CASE WHEN p.estado = 'finalizado' THEN
                       CASE 
                           WHEN (SELECT COUNT(*) FROM Goles g JOIN Jugadores j ON g.cod_jug = j.cod_jug WHERE g.cod_par = p.cod_par AND j.cod_equ = e.cod_equ) > 
                                (SELECT COUNT(*) FROM Goles g JOIN Jugadores j ON g.cod_jug = j.cod_jug WHERE g.cod_par = p.cod_par AND j.cod_equ != e.cod_equ) 
                           THEN true
                           ELSE false
                       END
                   ELSE false END as clasificado
                   FROM Partidos p
                   JOIN Equipos e ON p.equ_local = e.cod_equ OR p.equ_visitante = e.cod_equ
                   WHERE p.fase IN ('cuartos', 'semis', 'final')
                   ORDER BY p.fase, e.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener fases: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene la tabla de posiciones
     */
    public function obtenerTablaPosiciones() {
        try {
            // Consulta para obtener los datos de la tabla de posiciones
            $sql = "SELECT 
                      e.cod_equ, 
                      e.nombre,
                      e.escudo,
                      COUNT(p.cod_par) as partidos_jugados,
                      SUM(CASE 
                          WHEN (p.equ_local = e.cod_equ AND goles_local > goles_visitante) 
                            OR (p.equ_visitante = e.cod_equ AND goles_visitante > goles_local) 
                          THEN 1 ELSE 0 END) as victorias,
                      SUM(CASE 
                          WHEN (goles_local = goles_visitante) 
                          THEN 1 ELSE 0 END) as empates,
                      SUM(CASE 
                          WHEN (p.equ_local = e.cod_equ AND goles_local < goles_visitante) 
                            OR (p.equ_visitante = e.cod_equ AND goles_visitante < goles_local) 
                          THEN 1 ELSE 0 END) as derrotas,
                      SUM(CASE 
                          WHEN p.equ_local = e.cod_equ THEN goles_local
                          WHEN p.equ_visitante = e.cod_equ THEN goles_visitante
                          ELSE 0 END) as goles_favor,
                      SUM(CASE 
                          WHEN p.equ_local = e.cod_equ THEN goles_visitante
                          WHEN p.equ_visitante = e.cod_equ THEN goles_local
                          ELSE 0 END) as goles_contra
                  FROM Equipos e
                  LEFT JOIN (
                      SELECT 
                          p.cod_par,
                          p.equ_local,
                          p.equ_visitante,
                          p.estado,
                          (SELECT COUNT(*) FROM Goles g JOIN Jugadores j ON g.cod_jug = j.cod_jug 
                           WHERE g.cod_par = p.cod_par AND j.cod_equ = p.equ_local) as goles_local,
                          (SELECT COUNT(*) FROM Goles g JOIN Jugadores j ON g.cod_jug = j.cod_jug 
                           WHERE g.cod_par = p.cod_par AND j.cod_equ = p.equ_visitante) as goles_visitante
                      FROM Partidos p
                      WHERE p.estado = 'finalizado'
                  ) as p ON p.equ_local = e.cod_equ OR p.equ_visitante = e.cod_equ
                  GROUP BY e.cod_equ, e.nombre
                  ORDER BY (SUM(CASE 
                              WHEN (p.equ_local = e.cod_equ AND goles_local > goles_visitante) 
                                OR (p.equ_visitante = e.cod_equ AND goles_visitante > goles_local) 
                              THEN 3 ELSE 0 END) +
                          SUM(CASE 
                              WHEN (goles_local = goles_visitante) 
                              THEN 1 ELSE 0 END)) DESC,
                          (SUM(CASE 
                              WHEN p.equ_local = e.cod_equ THEN goles_local
                              WHEN p.equ_visitante = e.cod_equ THEN goles_visitante
                              ELSE 0 END) -
                          SUM(CASE 
                              WHEN p.equ_local = e.cod_equ THEN goles_visitante
                              WHEN p.equ_visitante = e.cod_equ THEN goles_local
                              ELSE 0 END)) DESC,
                          SUM(CASE 
                              WHEN p.equ_local = e.cod_equ THEN goles_local
                              WHEN p.equ_visitante = e.cod_equ THEN goles_visitante
                              ELSE 0 END) DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular puntos y diferencia de goles, y procesar escudos
            foreach ($equipos as &$equipo) {
                $equipo['puntos'] = ($equipo['victorias'] * 3) + $equipo['empates'];
                $equipo['diferencia_goles'] = $equipo['goles_favor'] - $equipo['goles_contra'];
                
                $this->procesarEscudo($equipo);
            }
            
            return $equipos;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Procesar el escudo de un equipo
     */
    private function procesarEscudo(&$equipo)
    {
        if (!empty($equipo['escudo'])) {
            // Verificar si es un recurso o un string
            if (is_resource($equipo['escudo'])) {
                $content = stream_get_contents($equipo['escudo']);
                rewind($equipo['escudo']);
                $equipo['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($content);
                // Eliminar el recurso del array para evitar problemas con JSON
                unset($equipo['escudo']);
            } else {
                $equipo['escudo_base64'] = 'data:image/jpeg;base64,' . base64_encode($equipo['escudo']);
                // Eliminar la versión binaria para evitar duplicados
                unset($equipo['escudo']);
            }
        } else {
            $equipo['escudo_base64'] = '/PROYECTO/frontend/assets/images/team.png';
            unset($equipo['escudo']);
        }
    }
    
    /**
     * Eliminar el escudo de un equipo
     */
    public function eliminarEscudo($id)
    {
        try {
            // Verificar si el equipo existe
            $equipo = $this->obtenerPorId($id);
            
            if (!$equipo) {
                return [
                    'estado' => false,
                    'mensaje' => 'El equipo no existe'
                ];
            }
            
            // Establecer el escudo como NULL
            $sql = "UPDATE Equipos SET escudo = NULL WHERE cod_equ = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'estado' => true,
                'mensaje' => 'Escudo eliminado correctamente'
            ];
            
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => 'Error al eliminar escudo: ' . $e->getMessage()
            ];
        }
    }
}
?> 
<?php
// Definir parámetros de conexión directamente si no están definidos
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_PORT', '5432');
    define('DB_NAME', 'futsala');
    define('DB_USER', 'postgres');
    define('DB_PASS', 'postgres');
}

/**
 * Clase Conexion para la base de datos
 * 
 * Maneja la conexión con la base de datos PostgreSQL
 */
class Conexion
{
    // Instancia única de la conexión (patrón singleton)
    private static $instancia = null;
    
    // Conexión PDO
    private $conexion;
    
    // Datos de conexión
    private $host;
    private $db;
    private $usuario;
    private $password;
    private $puerto;
    
    /**
     * Constructor privado (patrón singleton)
     */
    private function __construct()
    {
        // Inicializar datos de conexión desde constantes
        $this->host = DB_HOST;
        $this->db = DB_NAME;
        $this->usuario = DB_USER;
        $this->password = DB_PASS;
        $this->puerto = DB_PORT;
        
        try {
            // Crear conexión PDO a PostgreSQL
            $dsn = "pgsql:host={$this->host};port={$this->puerto};dbname={$this->db}";
            
            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                // Asegurar que se manejen correctamente los datos binarios
                PDO::ATTR_STRINGIFY_FETCHES => false
            ];
            
            $this->conexion = new PDO($dsn, $this->usuario, $this->password, $opciones);
            
            // Establecer la codificación de caracteres a UTF-8
            $this->conexion->exec("SET NAMES 'UTF8'");
            
            // Configurar el manejo de datos binarios
            $this->conexion->exec("SET bytea_output = 'escape'");
            
            // Mensaje de éxito (puedes quitar esto en producción)
            // echo "Conexión exitosa a la base de datos";
            
        } catch (PDOException $e) {
            // Mostrar mensaje de error
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener instancia única de conexión
     */
    public static function getConexion()
    {
        // Si no existe la instancia, la creamos
        if (self::$instancia === null) {
            self::$instancia = new Conexion();
        }
        
        // Devolver la conexión PDO
        return self::$instancia->conexion;
    }
    
    /**
     * Cerrar la conexión a la base de datos
     */
    public static function cerrarConexion()
    {
        // Si existe una instancia
        if (self::$instancia !== null) {
            // Asignamos null a la conexión
            self::$instancia->conexion = null;
            // Eliminamos la instancia
            self::$instancia = null;
            // echo "Conexión cerrada correctamente";
        }
    }
    
    /**
     * Evitar que la conexión se pueda clonar
     */
    private function __clone()
    {
        // Dejamos vacío para evitar que se clone la instancia
    }
}
?> 
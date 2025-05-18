<?php

// Incluir el archivo de configuración global si no está incluido
$config_path = dirname(dirname(dirname(__FILE__))) . '/config.php';
if (file_exists($config_path) && !defined('DB_HOST')) {
    include_once $config_path;
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
    
    // Datos de conexión (ahora usando constantes)
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
        // Inicializar datos de conexión desde constantes o valores por defecto
        $this->host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $this->db = defined('DB_NAME') ? DB_NAME : 'futsala';
        $this->usuario = defined('DB_USER') ? DB_USER : 'postgres';
        $this->password = defined('DB_PASS') ? DB_PASS : 'postgres';
        $this->puerto = defined('DB_PORT') ? DB_PORT : '5432';
        
        try {
            // Crear conexión PDO a PostgreSQL
            $dsn = "pgsql:host={$this->host};port={$this->puerto};dbname={$this->db}";
            
            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conexion = new PDO($dsn, $this->usuario, $this->password, $opciones);
            
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
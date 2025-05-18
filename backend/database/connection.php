<?php

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
    private $host = 'localhost';
    private $db = 'futsala';
    private $usuario = 'postgres';
    private $password = 'postgres'; // Cambia esto por tu contraseña real
    private $puerto = '5432';
    
    /**
     * Constructor privado (patrón singleton)
     */
    private function __construct()
    {
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
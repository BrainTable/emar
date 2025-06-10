<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class Conexion {
    private static $pdo = null;

    public static function getPDO() {
        if (self::$pdo === null) {
            $host = 'localhost';
            $db = 'tu_base_de_datos';      // Cambia esto por el nombre real de tu base de datos
            $user = 'tu_usuario';          // Cambia esto por tu usuario de MySQL
            $pass = 'tu_contraseña';       // Cambia esto por tu contraseña de MySQL
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            self::$pdo = new PDO($dsn, $user, $pass);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }
}
?>
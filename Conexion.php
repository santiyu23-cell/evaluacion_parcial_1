<?php
// Conexion.php
declare(strict_types=1);

final class Conexion
{
    private static ?PDO $pdo = null;

    public static function obtener(): PDO
    {
        if (self::$pdo === null) {
            $host = 'localhost';
            $db   = 'gestion_clubes';
            $user = 'root'; // En XAMPP por defecto es 'root'
            $pass = '';     // En XAMPP por defecto va vacío

            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }
}
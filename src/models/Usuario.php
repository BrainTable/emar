<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../database/conexion.php';

class Usuario {
    public static function registrar($pdo, $nombre, $email, $password_plana) {
        $password_cifrada = password_hash($password_plana, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$nombre, $email, $password_cifrada]);
    }

    public static function login($pdo, $email, $password_plana) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario && password_verify($password_plana, $usuario['password'])) {
            return $usuario;
        }
        return false;
    }
}
?>
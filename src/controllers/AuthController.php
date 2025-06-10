<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../database/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Administrador.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data['tipo'] === 'usuario') {
        $usuario = Usuario::login($pdo, $data['email'], $data['password']);
        if ($usuario) {
            $_SESSION['rol'] = 'usuario';
            $_SESSION['usuario_id'] = $usuario['id'];
            echo json_encode(['success' => true, 'rol' => 'usuario']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Credenciales incorrectas']);
        }
    } elseif ($data['tipo'] === 'admin') {
        $admin = Administrador::login($pdo, $data['email'], $data['password']);
        if ($admin) {
            $_SESSION['rol'] = 'admin';
            $_SESSION['admin_id'] = $admin['id'];
            echo json_encode(['success' => true, 'rol' => 'admin']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Credenciales incorrectas']);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    session_destroy();
    echo json_encode(['success' => true]);
}
?>
<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/crear_orden.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    echo json_encode(['success'=>false, 'error'=>'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        echo json_encode(['success'=>false, 'error'=>'Token CSRF inválido']);
        exit;
    }

    $usuario_id = $_SESSION['usuario_id'];
    $tipo = trim($_POST['tipo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($tipo === '' || $descripcion === '') {
        echo json_encode(['success'=>false, 'error'=>'Todos los campos son obligatorios.']);
        exit;
    }

    $mysqli = new mysqli("localhost", "root", "", "emar_db");
    if ($mysqli->connect_errno) {
        echo json_encode(['success'=>false, 'error'=>'Error de conexión a la base de datos.']);
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO ordenes_servicio (usuario_id, tipo, descripcion) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $usuario_id, $tipo, $descripcion);
    if ($stmt->execute()) {
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false, 'error'=>'No se pudo crear la orden.']);
    }
    $stmt->close();
    $mysqli->close();
    exit;
}
echo json_encode(['success'=>false, 'error'=>'Método no permitido.']);
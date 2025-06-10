<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/registrar_medidor_usuario.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    echo json_encode(['success'=>false, 'error'=>'No autorizado']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $codigo = trim($_POST['codigo'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    if ($codigo === '' || $modelo === '') {
        echo json_encode(['success'=>false, 'error'=>'Código y modelo son obligatorios.']);
        exit;
    }
    $mysqli = new mysqli("localhost", "root", "", "emar_db");
    if ($mysqli->connect_errno) {
        echo json_encode(['success'=>false, 'error'=>'Error de conexión.']);
        exit;
    }
    // Verifica que el código no esté ya asignado a otro usuario
    $stmt = $mysqli->prepare("SELECT id FROM medidores WHERE codigo=?");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['success'=>false, 'error'=>'Este código de medidor ya está registrado.']);
        $stmt->close();
        $mysqli->close();
        exit;
    }
    $stmt->close();
    $stmt = $mysqli->prepare("INSERT INTO medidores (usuario_id, codigo, modelo) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $usuario_id, $codigo, $modelo);
    if ($stmt->execute()) {
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false, 'error'=>'No se pudo registrar el medidor.']);
    }
    $stmt->close();
    $mysqli->close();
    exit;
}
echo json_encode(['success'=>false, 'error'=>'Método no permitido.']);
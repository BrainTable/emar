<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/registrar_medidor_inventario.php
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    if ($codigo === '' || $modelo === '') {
        echo json_encode(['success'=>false, 'error'=>'Código y modelo son obligatorios.']);
        exit;
    }

    $mysqli = new mysqli("localhost", "root", "", "emar_db");
    if ($mysqli->connect_errno) {
        echo json_encode(['success'=>false, 'error'=>'Error de conexión.']);
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO inventario_medidores (codigo, modelo, observaciones) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $codigo, $modelo, $observaciones);
    if ($stmt->execute()) {
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false, 'error'=>'Código ya registrado o error en la base de datos.']);
    }
    $stmt->close();
    $mysqli->close();
    exit;
}
echo json_encode(['success'=>false, 'error'=>'Método no permitido.']);
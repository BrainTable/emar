<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/cambiar_estado_medidor.php
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $nuevo_estado = $_POST['estado'] ?? '';
    $permitidos = ['almacen','asignado','defectuoso','baja','reparacion'];
    if ($id <= 0 || !in_array($nuevo_estado, $permitidos)) {
        echo json_encode(['success'=>false, 'error'=>'Datos inválidos.']);
        exit;
    }
    $mysqli = new mysqli("localhost", "root", "", "emar_db");
    if ($mysqli->connect_errno) {
        echo json_encode(['success'=>false, 'error'=>'Error de conexión.']);
        exit;
    }
    $stmt = $mysqli->prepare("UPDATE inventario_medidores SET estado=? WHERE id=?");
    $stmt->bind_param("si", $nuevo_estado, $id);
    if ($stmt->execute()) {
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false, 'error'=>'No se pudo actualizar el estado.']);
    }
    $stmt->close();
    $mysqli->close();
    exit;
}
echo json_encode(['success'=>false, 'error'=>'Método no permitido.']);
<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/alerta_bajo_stock.php
ini_set('display_errors', 1);
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "root", "", "emar_db");
if ($mysqli->connect_errno) {
    echo json_encode(['success'=>false, 'error'=>'Error de conexión.']);
    exit;
}
$res = $mysqli->query("SELECT COUNT(*) as total FROM inventario_medidores WHERE estado='almacen'");
$row = $res->fetch_assoc();
$stock = intval($row['total']);
$limite = 5; // Cambia este valor según tu política de stock mínimo

if ($stock <= $limite) {
    echo json_encode(['success'=>true, 'alerta'=>true, 'stock'=>$stock]);
} else {
    echo json_encode(['success'=>true, 'alerta'=>false, 'stock'=>$stock]);
}
$mysqli->close();
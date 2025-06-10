<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/eliminar_lectura.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol_id'], [1,3])) {
    header("Location: login.php");
    exit;
}

$lectura_id = intval($_GET['id'] ?? 0);
$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Obtener datos para historial
$stmt = $mysqli->prepare("SELECT * FROM lecturas WHERE id=?");
$stmt->bind_param("i", $lectura_id);
$stmt->execute();
$res = $stmt->get_result();
$lectura = $res->fetch_assoc();
$stmt->close();

if ($lectura) {
    // Registrar en historial
    $usuario_id = $_SESSION['usuario_id'];
    $accion = "Eliminación";
    $descripcion = "Lectura eliminada. Valor: {$lectura['valor']}, Fecha: {$lectura['fecha']}, Observaciones: {$lectura['observaciones']}";
    $stmt2 = $mysqli->prepare("INSERT INTO historial_lectura (lectura_id, usuario_id, accion, descripcion) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("iiss", $lectura_id, $usuario_id, $accion, $descripcion);
    $stmt2->execute();
    $stmt2->close();

    // Eliminar lectura
    $stmt = $mysqli->prepare("DELETE FROM lecturas WHERE id=?");
    $stmt->bind_param("i", $lectura_id);
    $stmt->execute();
    $stmt->close();
}

$mysqli->close();
header("Location: lecturas.php");
exit;
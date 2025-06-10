<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../database/conexion.php';
require_once __DIR__ . '/../models/Medidor.php';

$pdo = Connection::getPDO();

// Control de acceso: solo usuarios autenticados
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

header('Content-Type: application/json');

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            echo json_encode(Medidor::obtenerTodos($pdo));
            break;
        case 'POST':
            if (!empty($_FILES['foto'])) {
                $target_dir = __DIR__ . "/../../public/uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $target_file = $target_dir . basename($_FILES["foto"]["name"]);
                if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                    $ruta_foto = '/Proyectos/Emar/public/uploads/' . basename($_FILES["foto"]["name"]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Error al subir la imagen']);
                    exit;
                }
                $data = $_POST;
                $data['foto'] = $ruta_foto;
            } else {
                $data = json_decode(file_get_contents('php://input'), true);
                $data['foto'] = $data['foto'] ?? '';
            }
            $data = Medidor::sanitizarDatos($data);
            echo json_encode(['success' => Medidor::crear($pdo, $data)]);
            break;
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
            if ($id) {
                $input = Medidor::sanitizarDatos($input);
                $input['foto'] = $input['foto'] ?? '';
                echo json_encode(['success' => Medidor::actualizar($pdo, $id, $input)]);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            }
            break;
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
            if ($id) {
                echo json_encode(['success' => Medidor::eliminar($pdo, $id)]);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
}
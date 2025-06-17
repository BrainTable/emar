<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/registro_publico.php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Error de seguridad: token CSRF inválido.']);
        exit;
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_raw = $_POST['password'] ?? '';
    $rol_id = 2; // Usuario normal

    // Validación básica
    if ($nombre === '' || $email === '' || $password_raw === '') {
        echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'El correo no es válido.']);
        exit;
    }
    if (strlen($password_raw) < 6) {
        echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres.']);
        exit;
    }

    // Validación de imagen (opcional)
    $foto_nombre = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        $max_tamano = 2 * 1024 * 1024; // 2MB

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Error al subir la imagen.']);
            exit;
        } elseif (!in_array($_FILES['foto']['type'], $permitidos)) {
            echo json_encode(['success' => false, 'error' => 'Solo se permiten imágenes JPG, PNG o GIF.']);
            exit;
        } elseif ($_FILES['foto']['size'] > $max_tamano) {
            echo json_encode(['success' => false, 'error' => 'El archivo supera el tamaño máximo permitido (2MB).']);
            exit;
        } else {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto_nombre = uniqid('usuario_') . '.' . $ext;
            $ruta_destino = __DIR__ . "/../uploads/usuarios/" . $foto_nombre;
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                echo json_encode(['success' => false, 'error' => 'No se pudo guardar la imagen.']);
                exit;
            }
        }
    }

    $mysqli = new mysqli("localhost", "root", "", "emar_db");
    if ($mysqli->connect_errno) {
        echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
        exit;
    }

    $check = $mysqli->prepare("SELECT id FROM usuarios WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'El correo ya está registrado.']);
        $check->close();
        $mysqli->close();
        exit;
    }

    $password = password_hash($password_raw, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO usuarios (nombre, email, password, rol_id, foto) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $nombre, $email, $password, $rol_id, $foto_nombre);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al registrar usuario.']);
    }
    $stmt->close();
    $check->close();
    $mysqli->close();
    exit;
}

echo json_encode(['success' => false, 'error' => 'Método no permitido']);
exit;
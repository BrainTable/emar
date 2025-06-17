<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $mysqli = new mysqli("localhost", "root", "", "emar_db");
    if ($mysqli->connect_errno) {
        echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos.']);
        exit;
    }
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email y contraseña son obligatorios.']);
        exit;
    }
    $stmt = $mysqli->prepare("SELECT id, nombre, password, rol_id FROM usuarios WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $nombre, $hash, $rol_id);
        $stmt->fetch();
        if (password_verify($password, $hash)) {
            $_SESSION['usuario_id'] = $id;
            $_SESSION['nombre'] = $nombre;
            $_SESSION['rol_id'] = $rol_id;
            // Redirección según rol
            if ($rol_id == 1 || $rol_id == 3) {
                $redirect = "menu.php";
            } else {
                $redirect = "menu_usuario.php";
            }
            echo json_encode(['success' => true, 'redirect' => $redirect]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado.']);
    }
    $stmt->close();
    $mysqli->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - EMAR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #f4f6f8; font-family: Arial, sans-serif; }
        .login-container { max-width: 350px; margin: 200px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        h2 { color:rgb(130, 170, 0); text-align: center; }
        input, button { width: 100%; padding: 10px; margin: 10px 0; border-radius: 4px; border: 1px solid #ccc; }
        .error { color: #c00; text-align: center; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar sesión</h2>
        <div id="mensaje" class="error"></div>
        <form id="loginForm" autocomplete="off">
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
    </div>
    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = e.target;
        var formData = new FormData(form);
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                document.getElementById('mensaje').textContent = data.error;
            }
        })
        .catch(() => {
            document.getElementById('mensaje').textContent = 'Error de conexión.';
        });
    });
    </script>
</body>
</html>
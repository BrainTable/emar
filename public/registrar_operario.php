<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/registrar_operario.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: menu.php");
    exit;
}

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $mensaje = "Error de seguridad: token CSRF inválido.";
    } else {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password_raw = $_POST['password'] ?? '';
        $rol_id = 3; // Operario

        if ($nombre === '' || $email === '' || $password_raw === '') {
            $mensaje = "Todos los campos son obligatorios.";
        } else {
            $mysqli = new mysqli("localhost", "root", "", "emar_db");
            if ($mysqli->connect_errno) {
                $mensaje = "Error de conexión a la base de datos.";
            } else {
                $check = $mysqli->prepare("SELECT id FROM usuarios WHERE email=?");
                $check->bind_param("s", $email);
                $check->execute();
                $check->store_result();
                if ($check->num_rows > 0) {
                    $mensaje = "El correo ya está registrado.";
                } else {
                    $password = password_hash($password_raw, PASSWORD_DEFAULT);
                    $stmt = $mysqli->prepare("INSERT INTO usuarios (nombre, email, password, rol_id) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $nombre, $email, $password, $rol_id);
                    if ($stmt->execute()) {
                        $mensaje = "Operario registrado correctamente.";
                    } else {
                        $mensaje = "Error al registrar operario.";
                    }
                    $stmt->close();
                }
                $check->close();
                $mysqli->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Operario</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, button { margin: 8px 0; padding: 8px; width: 90%; }
        .mensaje { color: #005baa; margin-bottom: 10px; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registrar Operario</h1>
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo (strpos($mensaje, 'correctamente') !== false) ? '' : 'error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="nombre" placeholder="Nombre completo" required><br>
            <input type="email" name="email" placeholder="Correo electrónico" required><br>
            <input type="password" name="password" placeholder="Contraseña" required><br>
            <!-- Campo oculto CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit">Registrar Operario</button>
        </form>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
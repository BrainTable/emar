<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/cambiar_password.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre = $_SESSION['nombre'] ?? '';
$mensaje = "";
$mensaje_tipo = "";

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $mensaje = "Error de seguridad: token CSRF inválido.";
        $mensaje_tipo = "error";
    } else {
        $password_actual = $_POST['password_actual'] ?? '';
        $password_nueva = $_POST['password_nueva'] ?? '';
        $password_confirmar = $_POST['password_confirmar'] ?? '';

        if ($password_actual === '' || $password_nueva === '' || $password_confirmar === '') {
            $mensaje = "Todos los campos son obligatorios.";
            $mensaje_tipo = "error";
        } elseif (strlen($password_nueva) < 6) {
            $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
            $mensaje_tipo = "error";
        } elseif ($password_nueva !== $password_confirmar) {
            $mensaje = "La nueva contraseña y la confirmación no coinciden.";
            $mensaje_tipo = "error";
        } else {
            $mysqli = new mysqli("localhost", "root", "", "emar_db");
            $stmt = $mysqli->prepare("SELECT password FROM usuarios WHERE id=?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $stmt->bind_result($hash_actual);
            $stmt->fetch();
            $stmt->close();

            if (!password_verify($password_actual, $hash_actual)) {
                $mensaje = "La contraseña actual es incorrecta.";
                $mensaje_tipo = "error";
            } else {
                $hash_nuevo = password_hash($password_nueva, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare("UPDATE usuarios SET password=? WHERE id=?");
                $stmt->bind_param("si", $hash_nuevo, $usuario_id);
                if ($stmt->execute()) {
                    $mensaje = "Contraseña cambiada correctamente.";
                    $mensaje_tipo = "exito";
                    // Log de auditoría
                    $usuario = $_SESSION['nombre'] ?? 'Usuario';
                    $accion = "Cambio de contraseña";
                    $detalle = "El usuario cambió su contraseña.";
                    $log = $mysqli->prepare("INSERT INTO logs_auditoria (usuario, accion, detalle, fecha) VALUES (?, ?, ?, NOW())");
                    $log->bind_param("sss", $usuario, $accion, $detalle);
                    $log->execute();
                    $log->close();
                } else {
                    $mensaje = "Error al cambiar la contraseña.";
                    $mensaje_tipo = "error";
                }
                $stmt->close();
            }
            $mysqli->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, button { margin: 8px 0; padding: 8px; width: 90%; border-radius: 4px; border: 1px solid #ccc; }
        .mensaje-exito { color: #0a7d0a; margin-bottom: 10px; }
        .mensaje-error { color: #c00; margin-bottom: 10px; }
    </style>
</head>
<body>
    <?php include 'header_emar.php'; ?>
    <div class="container">
        <h2>Cambiar Contraseña</h2>
        <?php if ($mensaje): ?>
            <div class="<?php echo $mensaje_tipo === 'exito' ? 'mensaje-exito' : 'mensaje-error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <input type="password" name="password_actual" placeholder="Contraseña actual" required>
            <input type="password" name="password_nueva" placeholder="Nueva contraseña" required>
            <input type="password" name="password_confirmar" placeholder="Confirmar nueva contraseña" required>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit">Cambiar Contraseña</button>
        </form>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
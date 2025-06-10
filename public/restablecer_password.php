<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mensaje = "";
$token = $_GET['token'] ?? '';
if (!$token) {
    die("Token inválido.");
}

$mysqli = new mysqli("localhost", "root", "", "emar_db");
$stmt = $mysqli->prepare("SELECT usuario_id FROM recuperacion_password WHERE token=? ORDER BY creado DESC LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 1) {
    $stmt->bind_result($usuario_id);
    $stmt->fetch();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pass1 = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';
        if ($pass1 === "" || $pass2 === "") {
            $mensaje = "Debes ingresar la nueva contraseña dos veces.";
        } elseif ($pass1 !== $pass2) {
            $mensaje = "Las contraseñas no coinciden.";
        } elseif (strlen($pass1) < 8) {
            $mensaje = "La contraseña debe tener al menos 8 caracteres.";
        } else {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
            $up = $mysqli->prepare("UPDATE usuarios SET password=? WHERE id=?");
            $up->bind_param("si", $hash, $usuario_id);
            $up->execute();
            $up->close();
            // Borra el token para que no se reutilice
            $del = $mysqli->prepare("DELETE FROM recuperacion_password WHERE token=?");
            $del->bind_param("s", $token);
            $del->execute();
            $del->close();
            $mensaje = "¡Contraseña restablecida! Ahora puedes <a href='login.php'>iniciar sesión</a>.";
        }
    }
} else {
    $mensaje = "El enlace no es válido o ya fue usado.";
}
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, button { margin: 10px 0; padding: 10px; width: 90%; border-radius: 4px; border: 1px solid #ccc; }
        .msg { color: #005baa; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Restablecer Contraseña</h2>
        <?php if ($mensaje): ?>
            <div class="msg"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if (empty($mensaje) || strpos($mensaje, '¡Contraseña restablecida!') === false): ?>
        <form method="post">
            <input type="password" name="password" placeholder="Nueva contraseña" required>
            <input type="password" name="password2" placeholder="Repite la nueva contraseña" required>
            <button type="submit">Restablecer</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
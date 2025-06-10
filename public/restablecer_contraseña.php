<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mensaje = "";
$token = $_GET['token'] ?? '';
if ($token === '') {
    die("Token inválido.");
}

$mysqli = new mysqli("localhost", "root", "", "emar_db");
$stmt = $mysqli->prepare("SELECT usuario_id, expira FROM recuperaciones WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($usuario_id, $expira);
    $stmt->fetch();
    if (strtotime($expira) < time()) {
        $mensaje = '<p class="error">El enlace ha expirado.</p>';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nueva = $_POST['nueva'] ?? '';
        $repite = $_POST['repite'] ?? '';
        if ($nueva === '' || $repite === '') {
            $mensaje = '<p class="error">Completa ambos campos.</p>';
        } elseif ($nueva !== $repite) {
            $mensaje = '<p class="error">Las contraseñas no coinciden.</p>';
        } else {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $up = $mysqli->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $up->bind_param("si", $hash, $usuario_id);
            $up->execute();
            // Borra el token para que no se reutilice
            $mysqli->query("DELETE FROM recuperaciones WHERE token = '$token'");
            $mensaje = '<p class="success">Contraseña actualizada. <a href="login.php">Iniciar sesión</a></p>';
        }
    }
} else {
    $mensaje = '<p class="error">Enlace inválido.</p>';
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
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Restablecer Contraseña</h1>
        <?php if ($mensaje) echo $mensaje; ?>
        <?php if (!$mensaje || strpos($mensaje, 'actualizada') === false): ?>
        <form method="post">
            <input type="password" name="nueva" placeholder="Nueva contraseña" required><br>
            <input type="password" name="repite" placeholder="Repite la contraseña" required><br>
            <button type="submit">Actualizar contraseña</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
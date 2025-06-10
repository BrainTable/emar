<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email !== '') {
        $mysqli = new mysqli("localhost", "root", "", "emar_db");
        $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            // Generar token seguro
            $token = bin2hex(random_bytes(32));
            $stmt->bind_result($user_id);
            $stmt->fetch();

            // Crear tabla de recuperaciones si no existe
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $mysqli->query("CREATE TABLE IF NOT EXISTS recuperaciones (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                token VARCHAR(64) NOT NULL,
                expira DATETIME NOT NULL,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
            )");
            $ins = $mysqli->prepare("INSERT INTO recuperaciones (usuario_id, token, expira) VALUES (?, ?, ?)");
            $ins->bind_param("iss", $user_id, $token, $expira);
            $ins->execute();

            // Enlace de recuperación
            $enlace = "http://localhost/Proyectos/Emar/public/restablecer_contraseña.php?token=$token";
            $asunto = "Recuperación de contraseña - EMAR";
            $mensajeCorreo = "Hola,\n\nPara restablecer tu contraseña haz clic en el siguiente enlace:\n$enlace\n\nSi no solicitaste este cambio, ignora este mensaje.";
            $cabeceras = "From: no-reply@emar.com\r\n";

            if (mail($email, $asunto, $mensajeCorreo, $cabeceras)) {
                $mensaje = '<p class="success">Si el correo existe, se enviará un enlace de recuperación.</p>';
            } else {
                $mensaje = '<p class="error">No se pudo enviar el correo. Contacta al administrador.</p>';
            }
        } else {
            $mensaje = '<p class="success">Si el correo existe, se enviará un enlace de recuperación.</p>';
        }
        $stmt->close();
        $mysqli->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        .success { color: green; }
        .error { color: red; }
        .inicio { display: inline-block; margin-bottom: 20px; background: #005baa; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; }
        .inicio:hover { background: #003f6d; }
    </style>
</head>
<body>
    <div class="container">
        <a class="inicio" href="index.html">Inicio</a>
        <h1>Recuperar Contraseña</h1>
        <form method="post">
            <label>Email: <input type="email" name="email" required></label><br><br>
            <button type="submit">Enviar enlace de recuperación</button>
        </form>
        <?php if ($mensaje) echo $mensaje; ?>
    </div>
</body>
</html>
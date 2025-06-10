<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email == "") {
        $mensaje = "Por favor, ingresa tu correo.";
    } else {
        $mysqli = new mysqli("localhost", "root", "", "emar_db");
        $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $token = bin2hex(random_bytes(32));
            $stmt->bind_result($user_id);
            $stmt->fetch();
            // Crea la tabla si no existe
            $mysqli->query("CREATE TABLE IF NOT EXISTS recuperacion_password (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT,
                token VARCHAR(64),
                creado DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            // Guarda el token
            $ins = $mysqli->prepare("INSERT INTO recuperacion_password (usuario_id, token) VALUES (?, ?)");
            $ins->bind_param("is", $user_id, $token);
            $ins->execute();
            $ins->close();
            // Enviar correo (debes tener configurado tu servidor para enviar mails)
            $enlace = "http://localhost/Proyectos/Emar/public/restablecer_password.php?token=$token";
            $asunto = "Recupera tu contraseña - EMAR";
            $cuerpo = "Haz clic en el siguiente enlace para restablecer tu contraseña:\n$enlace\n\nSi no solicitaste esto, ignora este mensaje.";
            @mail($email, $asunto, $cuerpo);
            $mensaje = "Si el correo existe, recibirás un enlace para restablecer tu contraseña.";
        } else {
            $mensaje = "Si el correo existe, recibirás un enlace para restablecer tu contraseña.";
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
        .container { max-width: 400px; margin: 80px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, button { margin: 10px 0; padding: 10px; width: 90%; border-radius: 4px; border: 1px solid #ccc; }
        .msg { color: #005baa; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Recuperar Contraseña</h2>
        <?php if ($mensaje): ?>
            <div class="msg"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <button type="submit">Enviar enlace</button>
        </form>
        <p><a href="login.php">Volver al login</a></p>
    </div>
</body>
</html>
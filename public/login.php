<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/login.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Si ya hay sesión, redirige al menú correspondiente
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] == 1) {
        header("Location: menu.php");
    } elseif ($_SESSION['rol_id'] == 2) {
        header("Location: menu_usuario.php");
    } elseif ($_SESSION['rol_id'] == 3) {
        header("Location: menu_operario.php");
    }
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        die("Error de seguridad: token CSRF inválido.");
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $mysqli = new mysqli("localhost", "root", "", "emar_db");
    if (!$mysqli->connect_errno) {
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
                if ($rol_id == 1) {
                    header("Location: menu.php");
                } elseif ($rol_id == 2) {
                    header("Location: menu_usuario.php");
                } elseif ($rol_id == 3) {
                    header("Location: menu_operario.php");
                }
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
        $stmt->close();
        $mysqli->close();
    } else {
        $error = "Error de conexión a la base de datos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, button { margin: 10px 0; padding: 10px; width: 90%; border-radius: 4px; border: 1px solid #ccc; }
        .error { color: red; margin-bottom: 10px; }
        .logo img { height: 70px; margin-bottom: 20px; }
        .recuperar { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="img/logo-emar.jpg" alt="Logo EMAR">
        </div>
        <h2>Iniciar Sesión</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <!-- Campo oculto para el token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit">Iniciar Sesión</button>
        </form>
        <div class="recuperar">
            <a href="recuperar_password.php">¿Olvidaste tu contraseña?</a>
        </div>
        <p><a href="index.html">Volver al inicio</a></p>
    </div>
</body>
</html>
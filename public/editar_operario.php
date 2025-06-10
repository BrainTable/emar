<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/editar_operario.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$mysqli = new mysqli("localhost", "root", "", "emar_db");
$mensaje = "";
$mensaje_tipo = ""; // 'exito' o 'error'

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $mensaje = "Error de seguridad: token CSRF inválido.";
        $mensaje_tipo = "error";
    } else {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Validaciones adicionales
        if ($nombre === '' || $email === '') {
            $mensaje = "Todos los campos son obligatorios.";
            $mensaje_tipo = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "El correo no es válido.";
            $mensaje_tipo = "error";
        } else {
            $stmt = $mysqli->prepare("UPDATE usuarios SET nombre=?, email=? WHERE id=? AND rol_id=3");
            $stmt->bind_param("ssi", $nombre, $email, $id);
            if ($stmt->execute()) {
                $mensaje = "Operario actualizado correctamente.";
                $mensaje_tipo = "exito";
                // Log de auditoría
                $usuario = $_SESSION['nombre'] ?? 'Administrador';
                $accion = "Edición de operario";
                $detalle = "Operario ID: $id, Nombre: $nombre, Email: $email";
                $log = $mysqli->prepare("INSERT INTO logs_auditoria (usuario, accion, detalle, fecha) VALUES (?, ?, ?, NOW())");
                $log->bind_param("sss", $usuario, $accion, $detalle);
                $log->execute();
                $log->close();
            } else {
                $mensaje = "Error al actualizar operario.";
                $mensaje_tipo = "error";
            }
            $stmt->close();
        }
    }
}

// Obtener datos actuales
$stmt = $mysqli->prepare("SELECT nombre, email FROM usuarios WHERE id=? AND rol_id=3");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nombre, $email);
$stmt->fetch();
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Operario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, button { margin: 10px 0; padding: 10px; width: 90%; border-radius: 4px; border: 1px solid #ccc; }
        .mensaje-exito { color: #0a7d0a; margin-bottom: 10px; }
        .mensaje-error { color: #c00; margin-bottom: 10px; }
        @media (max-width: 600px) {
            .container { padding: 10px; max-width: 100% !important; }
            input, button { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Editar Operario</h2>
        <?php if ($mensaje): ?>
            <div class="<?php echo $mensaje_tipo === 'exito' ? 'mensaje-exito' : 'mensaje-error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" placeholder="Nombre" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Correo" required>
            <!-- Campo oculto CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit">Guardar Cambios</button>
        </form>
        <p><a href="ver_operadores.php">Volver a la lista</a></p>
    </div>
</body>
</html>
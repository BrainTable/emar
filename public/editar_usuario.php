<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/editar_usuario.php
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
        $rol_id = intval($_POST['rol_id'] ?? 2);

        // Validaciones adicionales
        if ($nombre === '' || $email === '') {
            $mensaje = "Todos los campos son obligatorios.";
            $mensaje_tipo = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "El correo no es válido.";
            $mensaje_tipo = "error";
        } elseif (!in_array($rol_id, [1, 2, 3])) {
            $mensaje = "El rol seleccionado no es válido.";
            $mensaje_tipo = "error";
        } else {
            $stmt = $mysqli->prepare("UPDATE usuarios SET nombre=?, email=?, rol_id=? WHERE id=?");
            $stmt->bind_param("ssii", $nombre, $email, $rol_id, $id);
            if ($stmt->execute()) {
                $mensaje = "Usuario actualizado correctamente.";
                $mensaje_tipo = "exito";
                // Log de auditoría
                $usuario = $_SESSION['nombre'] ?? 'Administrador';
                $accion = "Edición de usuario";
                $detalle = "Usuario ID: $id, Nombre: $nombre, Email: $email, Rol: $rol_id";
                $log = $mysqli->prepare("INSERT INTO logs_auditoria (usuario, accion, detalle, fecha) VALUES (?, ?, ?, NOW())");
                $log->bind_param("sss", $usuario, $accion, $detalle);
                $log->execute();
                $log->close();
            } else {
                $mensaje = "Error al actualizar usuario.";
                $mensaje_tipo = "error";
            }
            $stmt->close();
        }
    }
}

// Obtener datos actuales
$stmt = $mysqli->prepare("SELECT nombre, email, rol_id FROM usuarios WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nombre, $email, $rol_id);
$stmt->fetch();
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, select, button { margin: 10px 0; padding: 10px; width: 90%; border-radius: 4px; border: 1px solid #ccc; }
        .mensaje-exito { color: #0a7d0a; margin-bottom: 10px; }
        .mensaje-error { color: #c00; margin-bottom: 10px; }
        @media (max-width: 600px) {
            .container { padding: 10px; max-width: 100% !important; }
            input, select, button { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Editar Usuario</h2>
        <?php if ($mensaje): ?>
            <div class="<?php echo $mensaje_tipo === 'exito' ? 'mensaje-exito' : 'mensaje-error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" placeholder="Nombre" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Correo" required>
            <select name="rol_id" required>
                <option value="1" <?php if ($rol_id == 1) echo 'selected'; ?>>Administrador</option>
                <option value="2" <?php if ($rol_id == 2) echo 'selected'; ?>>Usuario</option>
                <option value="3" <?php if ($rol_id == 3) echo 'selected'; ?>>Operario</option>
            </select>
            <!-- Campo oculto CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit">Guardar Cambios</button>
        </form>
        <p><a href="usuarios.php">Volver a la lista</a></p>
    </div>
</body>
</html>
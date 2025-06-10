<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/eliminar_usuario.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
$mensaje = "";
$mensaje_tipo = "";

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $mensaje = "Error de seguridad: token CSRF inválido.";
        $mensaje_tipo = "error";
    } else {
        $mysqli = new mysqli("localhost", "root", "", "emar_db");
        // Registrar en logs de auditoría antes de eliminar
        $usuario = $_SESSION['nombre'] ?? 'Administrador';
        $accion = "Eliminación de usuario";
        $detalle = "Usuario eliminado (ID: $id) por el usuario ID " . $_SESSION['usuario_id'];
        $log = $mysqli->prepare("INSERT INTO logs_auditoria (usuario, accion, detalle, fecha) VALUES (?, ?, ?, NOW())");
        $log->bind_param("sss", $usuario, $accion, $detalle);
        $log->execute();
        $log->close();

        // Eliminar el usuario
        $stmt = $mysqli->prepare("DELETE FROM usuarios WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $mensaje = "Usuario eliminado correctamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "Error al eliminar el usuario.";
            $mensaje_tipo = "error";
        }
        $stmt->close();
        $mysqli->close();

        // Redirigir después de eliminar (opcional)
        header("Location: usuarios.php?msg=" . urlencode($mensaje));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Usuario</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        .mensaje-exito { color: #0a7d0a; margin-bottom: 10px; }
        .mensaje-error { color: #c00; margin-bottom: 10px; }
        button { margin: 10px 5px; padding: 10px 20px; border-radius: 4px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Eliminar Usuario</h2>
        <?php if ($mensaje): ?>
            <div class="<?php echo $mensaje_tipo === 'exito' ? 'mensaje-exito' : 'mensaje-error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php else: ?>
            <p>¿Estás seguro de que deseas eliminar este usuario?</p>
            <form method="post">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit">Sí, eliminar</button>
                <a href="usuarios.php"><button type="button">Cancelar</button></a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
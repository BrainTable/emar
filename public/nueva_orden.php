<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    header("Location: menu.php");
    exit;
}

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = trim($_POST['descripcion'] ?? '');
    $motivo = trim($_POST['motivo'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');
    $usuario_id = $_SESSION['usuario_id'];
    $foto = null;

    // Manejo de la foto (opcional)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nombreFoto = uniqid() . '_' . basename($_FILES['foto']['name']);
        $rutaDestino = 'ordenes/' . $nombreFoto;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
            $foto = $nombreFoto;
        }
    }

    if ($descripcion === '' || $motivo === '') {
        $mensaje = "La descripción y el motivo son obligatorios.";
    } else {
        $mysqli = new mysqli("localhost", "root", "", "emar_db");
        $stmt = $mysqli->prepare("INSERT INTO ordenes_servicio (usuario_id, descripcion, motivo, foto, observaciones) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $usuario_id, $descripcion, $motivo, $foto, $observaciones);
        if ($stmt->execute()) {
            $mensaje = "Orden de servicio creada correctamente.";
        } else {
            $mensaje = "Error al crear la orden.";
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
    <title>Nueva Orden de Servicio</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 500px; margin: 60px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, textarea, select, button { margin: 8px 0; padding: 8px; width: 90%; }
        .mensaje { color: #005baa; margin-bottom: 10px; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Nueva Orden de Servicio</h1>
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo (strpos($mensaje, 'correctamente') !== false) ? '' : 'error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="descripcion" placeholder="Descripción" required><br>
            <input type="text" name="motivo" placeholder="Motivo (ej: fuga, revisión, etc.)" required><br>
            <textarea name="observaciones" placeholder="Observaciones adicionales"></textarea><br>
            <input type="file" name="foto" accept="image/*"><br>
            <button type="submit">Crear Orden</button>
        </form>
        <a href="ordenes_servicio.php">Ver mis órdenes</a> | <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
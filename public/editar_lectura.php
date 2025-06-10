<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/editar_lectura.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol_id'], [1,3])) {
    header("Location: login.php");
    exit;
}

$lectura_id = intval($_GET['id'] ?? 0);
$mysqli = new mysqli("localhost", "root", "", "emar_db");
$mensaje = "";

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Obtener datos actuales
$stmt = $mysqli->prepare("SELECT l.*, m.numero_serie FROM lecturas l INNER JOIN medidores m ON l.medidor_id = m.id WHERE l.id=?");
$stmt->bind_param("i", $lectura_id);
$stmt->execute();
$res = $stmt->get_result();
$lectura = $res->fetch_assoc();
$stmt->close();

if (!$lectura) {
    $mysqli->close();
    die("Lectura no encontrada.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $mensaje = "Error de seguridad: token CSRF inválido.";
    } else {
        $valor = floatval($_POST['valor'] ?? 0);
        $fecha = $_POST['fecha'] ?? '';
        $observaciones = trim($_POST['observaciones'] ?? '');

        // Validación adicional de rango (opcional)
        if ($valor < 0) {
            $mensaje = "El valor de la lectura no puede ser negativo.";
        } else {
            // Guardar cambios
            $stmt = $mysqli->prepare("UPDATE lecturas SET valor=?, fecha=?, observaciones=? WHERE id=?");
            $stmt->bind_param("dssi", $valor, $fecha, $observaciones, $lectura_id);
            if ($stmt->execute()) {
                // Registrar en historial
                $usuario_id = $_SESSION['usuario_id'];
                $accion = "Edición";
                $descripcion = "Lectura editada. Valor: $valor, Fecha: $fecha, Observaciones: $observaciones";
                $stmt2 = $mysqli->prepare("INSERT INTO historial_lectura (lectura_id, usuario_id, accion, descripcion) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("iiss", $lectura_id, $usuario_id, $accion, $descripcion);
                $stmt2->execute();
                $stmt2->close();
                $mensaje = "Lectura actualizada correctamente.";
            } else {
                $mensaje = "Error al actualizar la lectura.";
            }
            $stmt->close();
            // Recargar datos
            $stmt = $mysqli->prepare("SELECT l.*, m.numero_serie FROM lecturas l INNER JOIN medidores m ON l.medidor_id = m.id WHERE l.id=?");
            $stmt->bind_param("i", $lectura_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $lectura = $res->fetch_assoc();
            $stmt->close();
        }
    }
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Lectura</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        .msg { color: #005baa; margin-bottom: 10px; }
        .error { color: #c00; margin-bottom: 10px; }
        input, button { margin: 10px 0; padding: 10px; width: 90%; border-radius: 4px; border: 1px solid #ccc; }
        @media (max-width: 600px) {
            .container { padding: 10px; max-width: 100% !important; }
            input, button { width: 100%; }
        }
    </style>
</head>
<body>
    <?php include 'header_emar.php'; ?>
    <div class="container">
        <h2>Editar Lectura</h2>
        <?php if ($mensaje): ?>
            <div class="<?php echo strpos($mensaje, 'correctamente') !== false ? 'msg' : 'error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <label>Medidor: <b><?php echo htmlspecialchars($lectura['numero_serie']); ?></b></label>
            <label>Fecha: <input type="date" name="fecha" value="<?php echo htmlspecialchars($lectura['fecha']); ?>" required></label>
            <label>Valor: <input type="number" step="0.01" name="valor" value="<?php echo htmlspecialchars($lectura['valor']); ?>" required></label>
            <label>Observaciones: <input type="text" name="observaciones" value="<?php echo htmlspecialchars($lectura['observaciones']); ?>"></label>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit">Guardar Cambios</button>
        </form>
        <br>
        <a href="lecturas.php">Volver a lecturas</a>
    </div>
</body>
</html>
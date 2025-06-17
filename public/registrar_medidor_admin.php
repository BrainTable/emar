<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/registrar_medidor_admin.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: menu.php");
    exit;
}

$mensaje = "";
$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Obtener lista de usuarios para el select
$usuarios = [];
if (!$mysqli->connect_errno) {
    $res = $mysqli->query("SELECT id, nombre, email FROM usuarios ORDER BY nombre");
    while ($row = $res->fetch_assoc()) {
        $usuarios[] = $row;
    }
    $res->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_serie = trim($_POST['numero_serie'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $fecha_instalacion = $_POST['fecha_instalacion'] ?? '';
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    $foto = "";

    // Manejo de foto (opcional)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto_nombre = uniqid('med_') . '_' . basename($_FILES['foto']['name']);
        $ruta_carpeta = "img/medidores/";
        if (!is_dir($ruta_carpeta)) {
            mkdir($ruta_carpeta, 0777, true);
        }
        $ruta_destino = $ruta_carpeta . $foto_nombre;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
            $foto = $foto_nombre;
        }
    }

    if ($numero_serie === '' || $ubicacion === '' || $fecha_instalacion === '' || $usuario_id == 0) {
        $mensaje = "Todos los campos son obligatorios.";
    } else {
        $check = $mysqli->prepare("SELECT id FROM medidores WHERE numero_serie=?");
        $check->bind_param("s", $numero_serie);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $mensaje = "El número de serie ya está registrado.";
        } else {
            $stmt = $mysqli->prepare("INSERT INTO medidores (numero_serie, tipo, ubicacion, estado, foto, usuario_id, fecha_instalacion) VALUES (?, 'agua', ?, 'activo', ?, ?, ?)");
            $stmt->bind_param("sssis", $numero_serie, $ubicacion, $foto, $usuario_id, $fecha_instalacion);
            if ($stmt->execute()) {
                $mensaje = "Medidor registrado correctamente.";
            } else {
                $mensaje = "Error al registrar el medidor.";
            }
            $stmt->close();
        }
        $check->close();
    }
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Medidor (Admin)</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 500px; margin: 60px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, select, button, textarea { margin: 8px 0; padding: 8px; width: 90%; border-radius: 4px; border: 1px solid #ccc; }
        .mensaje { color: #005baa; margin-bottom: 10px; }
        .error { color: red; }
        .form-group { text-align: left; margin-bottom: 15px; }
        label { font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'header_emar.php'; ?>
    <div class="container">
        <h1>Registrar Medidor (Administrador)</h1>
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo (strpos($mensaje, 'correctamente') !== false) ? '' : 'error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="usuario_id">Usuario dueño del medidor:</label>
                <select name="usuario_id" id="usuario_id" required>
                    <option value="">Seleccione usuario...</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?php echo $u['id']; ?>">
                            <?php echo htmlspecialchars($u['nombre'] . " (" . $u['email'] . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="numero_serie">Número de Serie:</label>
                <input type="text" name="numero_serie" id="numero_serie" placeholder="Ej: MED12345" required>
            </div>
            <div class="form-group">
                <label for="ubicacion">Ubicación del Medidor:</label>
                <textarea name="ubicacion" id="ubicacion" rows="2" placeholder="Ej: Calle 10 #20-30, Barrio Centro" required></textarea>
            </div>
            <div class="form-group">
                <label for="fecha_instalacion">Fecha de Instalación:</label>
                <input type="date" name="fecha_instalacion" id="fecha_instalacion" required>
            </div>
            <div class="form-group">
                <label for="foto">Foto del Medidor (opcional):</label>
                <input type="file" name="foto" id="foto" accept="image/*">
            </div>
            <button type="submit">Registrar Medidor</button>
        </form>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
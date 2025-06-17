<?php
// filepath: c:\xampp\htdocs\emar\public\editar_medidor.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    header("Location: login.php");
    exit;
}
$usuario_id = $_SESSION['usuario_id'];
$mysqli = new mysqli("localhost", "root", "", "emar_db");

$id = intval($_GET['id'] ?? 0);
$stmt = $mysqli->prepare("SELECT * FROM medidores WHERE id=? AND usuario_id=?");
$stmt->bind_param("ii", $id, $usuario_id);
$stmt->execute();
$res = $stmt->get_result();
$medidor = $res->fetch_assoc();
$stmt->close();

if (!$medidor) {
    echo "<p>No tienes permiso para editar este medidor.</p>";
    exit;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$errores = [];
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $errores[] = "Error de seguridad: token CSRF inválido.";
    } else {
        $ubicacion = trim($_POST['ubicacion'] ?? '');
        $estado = trim($_POST['estado'] ?? '');
        $fecha_instalacion = $_POST['fecha_instalacion'] ?? '';
        $foto = $medidor['foto'];

        if ($ubicacion === '' || strlen($ubicacion) < 5) $errores[] = "La ubicación es obligatoria.";
        if ($estado === '' || !in_array($estado, ['activo', 'inactivo', 'mantenimiento'])) $errores[] = "El estado es obligatorio y válido.";
        if ($fecha_instalacion === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_instalacion)) $errores[] = "La fecha de instalación es obligatoria y debe tener formato válido.";

        // Validación de imagen
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($_FILES['foto']['type'], $permitidos)) {
                $errores[] = "La foto debe ser JPG, PNG o WEBP.";
            }
            if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                $errores[] = "La foto no debe superar los 2MB.";
            }
        }

        if (empty($errores)) {
            // Procesar imagen nueva si se sube
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $foto_nombre = uniqid('med_') . '.' . $ext;
                $ruta_carpeta = "img/medidores/";
                if (!is_dir($ruta_carpeta)) mkdir($ruta_carpeta, 0777, true);
                $ruta_destino = $ruta_carpeta . $foto_nombre;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                    $foto = $foto_nombre;
                }
            }

            $stmt = $mysqli->prepare("UPDATE medidores SET ubicacion=?, estado=?, fecha_instalacion=?, foto=? WHERE id=? AND usuario_id=?");
            $stmt->bind_param("ssssii", $ubicacion, $estado, $fecha_instalacion, $foto, $id, $usuario_id);
            if ($stmt->execute()) {
                header("Location: mis_medidores.php?msg=editado");
                exit;
            } else {
                $mensaje = "Error al actualizar el medidor.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Medidor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 520px; margin: 60px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, select, button, textarea { margin: 8px 0; padding: 8px; width: 90%; border-radius: 4px; border: 1px solid #ccc; }
        .mensaje { color: #005baa; margin-bottom: 10px; }
        .error { color: red; }
        .form-group { text-align: left; margin-bottom: 15px; }
        label { font-weight: bold; }
        .preview-img { max-width: 120px; margin-top: 10px; border-radius: 8px; }
        ul.error-list { color: red; text-align: left; margin: 0 0 15px 0; padding-left: 20px; }
        @media (max-width: 600px) {
            .container { padding: 10px; max-width: 100% !important; }
            input, select, button, textarea { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Medidor</h1>
        <?php if (!empty($errores)): ?>
            <ul class="error-list">
                <?php foreach($errores as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($mensaje): ?>
            <div class="mensaje error"><?= htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" id="form-medidor">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="form-group">
                <label>Número de Serie:</label>
                <input type="text" value="<?= htmlspecialchars($medidor['numero_serie']) ?>" disabled>
            </div>
            <div class="form-group">
                <label for="ubicacion">Ubicación del Medidor:</label>
                <textarea name="ubicacion" id="ubicacion" rows="2" maxlength="255" required><?= htmlspecialchars($_POST['ubicacion'] ?? $medidor['ubicacion']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="estado">Estado:</label>
                <select name="estado" id="estado" required>
                    <option value="activo" <?= ($medidor['estado']=='activo')?'selected':'' ?>>Activo</option>
                    <option value="inactivo" <?= ($medidor['estado']=='inactivo')?'selected':'' ?>>Inactivo</option>
                    <option value="mantenimiento" <?= ($medidor['estado']=='mantenimiento')?'selected':'' ?>>Mantenimiento</option>
                </select>
            </div>
            <div class="form-group">
                <label for="fecha_instalacion">Fecha de Instalación:</label>
                <input type="date" name="fecha_instalacion" id="fecha_instalacion" required value="<?= htmlspecialchars($_POST['fecha_instalacion'] ?? $medidor['fecha_instalacion']) ?>">
            </div>
            <div class="form-group">
                <label for="foto">Foto del Medidor (opcional, JPG/PNG/WEBP, máx 2MB):</label>
                <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/webp">
                <?php if ($medidor['foto']): ?>
                    <img src="img/medidores/<?= htmlspecialchars($medidor['foto']) ?>" class="preview-img" id="preview" alt="Foto actual">
                <?php else: ?>
                    <img id="preview" class="preview-img" style="display:none;">
                <?php endif; ?>
            </div>
            <button type="submit">Guardar Cambios</button>
        </form>
        <a href="mis_medidores.php">Volver a mis medidores</a>
    </div>
    <script>
    // Vista previa de la imagen
    document.getElementById('foto').onchange = function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('preview');
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    };
    </script>
</body>
</html>
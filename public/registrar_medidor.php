<?php
// filepath: c:\xampp\htdocs\emar\public\registrar_medidor.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: menu.php");
    exit;
}

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$mensaje = "";
$mensaje_tipo = ""; // 'exito' o 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $mensaje = "Error de seguridad: token CSRF inválido.";
        $mensaje_tipo = "error";
    } else {
        $numero_serie = trim($_POST['numero_serie'] ?? '');
        $ubicacion = trim($_POST['ubicacion'] ?? '');
        $estado = trim($_POST['estado'] ?? '');

        // Validaciones adicionales
        if ($numero_serie === '' || $ubicacion === '' || $estado === '') {
            $mensaje = "Todos los campos son obligatorios.";
            $mensaje_tipo = "error";
        } elseif (!preg_match('/^[A-Za-z0-9\-]{3,30}$/', $numero_serie)) {
            $mensaje = "El número de serie debe tener entre 3 y 30 caracteres y solo puede contener letras, números y guiones.";
            $mensaje_tipo = "error";
        } elseif (!in_array($estado, ['activo', 'inactivo', 'mantenimiento'])) {
            $mensaje = "El estado seleccionado no es válido.";
            $mensaje_tipo = "error";
        } else {
            // Procesar la foto si se subió
            $foto_nombre = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
                $max_tamano = 2 * 1024 * 1024; // 2MB

                if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                    $mensaje = "Error al subir la imagen.";
                    $mensaje_tipo = "error";
                } elseif (!in_array($_FILES['foto']['type'], $permitidos)) {
                    $mensaje = "Solo se permiten imágenes JPG, PNG o GIF.";
                    $mensaje_tipo = "error";
                } elseif ($_FILES['foto']['size'] > $max_tamano) {
                    $mensaje = "El archivo supera el tamaño máximo permitido (2MB).";
                    $mensaje_tipo = "error";
                } else {
                    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                    $foto_nombre = uniqid('medidor_') . '.' . $ext;
                    $ruta_destino = __DIR__ . "/img/medidores/" . $foto_nombre;
                    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                        $mensaje = "No se pudo guardar la imagen.";
                        $mensaje_tipo = "error";
                    }
                }
            }

            if ($mensaje_tipo !== "error") {
                $mysqli = new mysqli("localhost", "root", "", "emar_db");
                if ($mysqli->connect_errno) {
                    $mensaje = "Error de conexión a la base de datos.";
                    $mensaje_tipo = "error";
                } else {
                    $check = $mysqli->prepare("SELECT id FROM medidores WHERE numero_serie=?");
                    $check->bind_param("s", $numero_serie);
                    $check->execute();
                    $check->store_result();
                    if ($check->num_rows > 0) {
                        $mensaje = "El número de serie ya está registrado.";
                        $mensaje_tipo = "error";
                    } else {
                        $stmt = $mysqli->prepare("INSERT INTO medidores (numero_serie, ubicacion, estado, foto, usuario_id, fecha_instalacion) VALUES (?, ?, ?, ?, ?, CURDATE())");
                        $usuario_id = $_SESSION['usuario_id'];
                        $stmt->bind_param("ssssi", $numero_serie, $ubicacion, $estado, $foto_nombre, $usuario_id);
                        if ($stmt->execute()) {
                            $mensaje = "Medidor registrado correctamente.";
                            $mensaje_tipo = "exito";
                        } else {
                            $mensaje = "Error al registrar medidor.";
                            $mensaje_tipo = "error";
                        }
                        $stmt->close();
                    }
                    $check->close();
                    $mysqli->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Medidor</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, select, button { margin: 8px 0; padding: 8px; width: 90%; border-radius: 4px; border: 1px solid #ccc; }
        .mensaje-exito { color: #0a7d0a; margin-bottom: 10px; }
        .mensaje-error { color: #c00; margin-bottom: 10px; }
    </style>
</head>
<body>
    <!-- Línea azul y logo -->
    <header style="background:#005baa; padding:0;">
        <div style="max-width:1200px; margin:0 auto; display:flex; align-items:center; height:64px;"></div>
           <img src="img/logo-emar.jpg" alt="Logo Emar" style="height:48px; margin-left:24px;">
            <span style="color:#fff; font-size:2rem; font-weight:bold; letter-spacing:2px;">EMAR</span>
        </div>
    </header>
    <div class="container">
        <h1>Registrar Medidor</h1>
        <?php if ($mensaje): ?>
            <div class="<?php echo $mensaje_tipo === 'exito' ? 'mensaje-exito' : 'mensaje-error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="numero_serie" placeholder="Número de serie" maxlength="30" required><br>
            <input type="text" name="ubicacion" placeholder="Ubicación" maxlength="100" required><br>
            <select name="estado" required>
                <option value="">Seleccione estado</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
                <option value="mantenimiento">Mantenimiento</option>
            </select><br>
            <input type="file" name="foto" accept="image/*"><br>
            <!-- Campo oculto CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit">Registrar Medidor</button>
        </form>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
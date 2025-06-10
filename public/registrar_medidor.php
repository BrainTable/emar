<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/registrar_medidor.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
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
        } elseif (!in_array($estado, ['Activo', 'Inactivo', 'En reparación'])) {
            $mensaje = "El estado seleccionado no es válido.";
            $mensaje_tipo = "error";
        } else {
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
                    $stmt = $mysqli->prepare("INSERT INTO medidores (numero_serie, ubicacion, estado) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $numero_serie, $ubicacion, $estado);
                    if ($stmt->execute()) {
                        $mensaje = "Medidor registrado correctamente.";
                        $mensaje_tipo = "exito";
                        // Log de auditoría
                        $usuario = $_SESSION['nombre'] ?? 'Administrador';
                        $accion = "Registro de medidor";
                        $detalle = "Medidor: $numero_serie, Ubicación: $ubicacion, Estado: $estado";
                        $log = $mysqli->prepare("INSERT INTO logs_auditoria (usuario, accion, detalle, fecha) VALUES (?, ?, ?, NOW())");
                        $log->bind_param("sss", $usuario, $accion, $detalle);
                        $log->execute();
                        $log->close();
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
    <div class="container">
        <h1>Registrar Medidor</h1>
        <?php if ($mensaje): ?>
            <div class="<?php echo $mensaje_tipo === 'exito' ? 'mensaje-exito' : 'mensaje-error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="numero_serie" placeholder="Número de serie" maxlength="30" required><br>
            <input type="text" name="ubicacion" placeholder="Ubicación" maxlength="100" required><br>
            <select name="estado" required>
                <option value="">Seleccione estado</option>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
                <option value="En reparación">En reparación</option>
            </select><br>
            <!-- Campo oculto CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit">Registrar Medidor</button>
        </form>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
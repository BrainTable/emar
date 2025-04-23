<?php
require_once '../src/conexion.php';

function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entradas
    $numero_serie = limpiar($_POST['numero_serie']);
    $tipo = limpiar($_POST['tipo']);
    $ubicacion = limpiar($_POST['ubicacion']);
    $estado = limpiar($_POST['estado']);
    $fecha_instalacion = limpiar($_POST['fecha_instalacion']);

    // Validación
    $errores = [];

    // Validar campos obligatorios
    if ($numero_serie == "") $errores[] = "El número de serie es obligatorio.";
    if ($tipo == "") $errores[] = "El tipo es obligatorio.";
    if ($ubicacion == "") $errores[] = "La ubicación es obligatoria.";
    if ($estado == "") $errores[] = "El estado es obligatorio.";
    if ($fecha_instalacion == "") $errores[] = "La fecha de instalación es obligatoria.";

    // Validar formato de fecha (YYYY-MM-DD)
    if ($fecha_instalacion != "" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_instalacion)) {
        $errores[] = "La fecha debe tener el formato AAAA-MM-DD.";
    }

    // Validar que la fecha no sea futura
    if ($fecha_instalacion != "" && strtotime($fecha_instalacion) > strtotime(date('Y-m-d'))) {
        $errores[] = "La fecha de instalación no puede ser en el futuro.";
    }

    // Validar longitud de campos
    if (strlen($numero_serie) > 30) $errores[] = "El número de serie es demasiado largo.";
    if (strlen($tipo) > 30) $errores[] = "El tipo es demasiado largo.";
    if (strlen($ubicacion) > 50) $errores[] = "La ubicación es demasiado larga.";

    // Validar estado permitido
    $estados_validos = ['activo', 'mantenimiento', 'inactivo'];
    if ($estado != "" && !in_array($estado, $estados_validos)) {
        $errores[] = "El estado seleccionado no es válido.";
    }

    // Validar que el número de serie no exista ya
    if ($numero_serie != "") {
        $stmt_check = $conex->prepare("SELECT id FROM medidores WHERE numero_serie = ?");
        $stmt_check->bind_param("s", $numero_serie);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $errores[] = "Ya existe un medidor con ese número de serie.";
        }
        $stmt_check->close();
    }

    if (count($errores) > 0) {
        foreach ($errores as $error) {
            $mensaje .= "<p style='color:red;'>$error</p>";
        }
    } else {
        // Consulta preparada
        $stmt = $conex->prepare("INSERT INTO medidores (numero_serie, tipo, ubicacion, estado, fecha_instalacion) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $numero_serie, $tipo, $ubicacion, $estado, $fecha_instalacion);
        if ($stmt->execute()) {
            $mensaje = "<p style='color:green;'>Medidor registrado correctamente.</p>";
        } else {
            $mensaje = "<p style='color:red;'>Error al registrar: " . $conex->error . "</p>";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Medidor</title>
</head>
<body>
    <h1>Registrar Medidor</h1>
    <?php if ($mensaje) echo $mensaje; ?>
    <form method="post" autocomplete="off">
        Número de Serie: <input type="text" name="numero_serie" maxlength="30" required><br>
        Tipo: <input type="text" name="tipo" maxlength="30" required><br>
        Ubicación: <input type="text" name="ubicacion" maxlength="50" required><br>
        Estado: 
        <select name="estado" required>
            <option value="">Seleccione...</option>
            <option value="activo">Activo</option>
            <option value="mantenimiento">Mantenimiento</option>
            <option value="inactivo">Inactivo</option>
        </select><br>
        Fecha de Instalación: <input type="date" name="fecha_instalacion" max="<?= date('Y-m-d') ?>" required><br>
        <input type="submit" value="Registrar">
    </form>
    <a href="index.html">Volver al inicio</a>
</body>
</html>
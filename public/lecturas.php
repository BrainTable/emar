<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/lecturas.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$rol_id = $_SESSION['rol_id'];
$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Obtener lista de medidores según el rol
$medidores = [];
if ($rol_id == 1 || $rol_id == 3) { // Admin u Operador
    $res = $mysqli->query("SELECT m.id, m.numero_serie, u.nombre AS usuario_nombre FROM medidores m LEFT JOIN usuarios u ON m.usuario_id = u.id ORDER BY m.id");
} else { // Usuario
    $res = $mysqli->prepare("SELECT m.id, m.numero_serie FROM medidores m WHERE m.usuario_id = ?");
    $res->bind_param("i", $usuario_id);
    $res->execute();
    $res = $res->get_result();
}
while ($row = $res->fetch_assoc()) $medidores[] = $row;
if (isset($res) && gettype($res) !== "boolean") $res->close();

// Registrar nueva lectura (solo admin y operador)
$mensaje = "";
if (($rol_id == 1 || $rol_id == 3) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['medidor_id'])) {
    $medidor_id = intval($_POST['medidor_id']);
    $fecha = $_POST['fecha'] ?? '';
    $valor = floatval($_POST['valor'] ?? 0);
    $observaciones = trim($_POST['observaciones'] ?? '');

    // Validar que no exista otra lectura en el mismo bimestre
    $bimestre_inicio = date('Y-m-01', strtotime($fecha));
    $bimestre_fin = date('Y-m-t', strtotime("+1 month", strtotime($bimestre_inicio)));
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM lecturas WHERE medidor_id=? AND fecha BETWEEN ? AND ?");
    $stmt->bind_param("iss", $medidor_id, $bimestre_inicio, $bimestre_fin);
    $stmt->execute();
    $stmt->bind_result($existe);
    $stmt->fetch();
    $stmt->close();

    if ($existe > 0) {
        $mensaje = "Ya existe una lectura para este medidor en el bimestre seleccionado.";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO lecturas (medidor_id, fecha, valor, usuario_id, observaciones) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $medidor_id, $fecha, $valor, $usuario_id, $observaciones);
        if ($stmt->execute()) {
            $mensaje = "Lectura registrada correctamente.";
        } else {
            $mensaje = "Error al registrar la lectura.";
        }
        $stmt->close();
    }
}

// Filtros para mostrar lecturas
$medidor_filtro = $_GET['medidor_id'] ?? '';
$where = [];
$params = [];
$types = '';

if ($rol_id == 2) { // Usuario solo ve sus medidores
    $where[] = "m.usuario_id = ?";
    $params[] = $usuario_id;
    $types .= 'i';
}
if ($medidor_filtro) {
    $where[] = "l.medidor_id = ?";
    $params[] = $medidor_filtro;
    $types .= 'i';
}

$sql = "SELECT l.*, m.numero_serie, u.nombre AS usuario_nombre
        FROM lecturas l
        INNER JOIN medidores m ON l.medidor_id = m.id
        LEFT JOIN usuarios u ON l.usuario_id = u.id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY l.fecha DESC";

$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$lecturas = [];
while ($row = $res->fetch_assoc()) $lecturas[] = $row;
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lecturas de Medidores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        h1 { color: #005baa; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eaf6ff; }
        .msg { color: #005baa; margin-bottom: 10px; }
        .error { color: #c00; margin-bottom: 10px; }
        .no-data { text-align: center; color: #888; padding: 20px 0; }
        .filtros-form label { margin-right: 15px; }
        @media (max-width: 900px) {
            .container { padding: 10px; max-width: 100% !important; }
            table, thead, tbody, th, td, tr { display: block; width: 100%; }
            th, td { box-sizing: border-box; }
            tr { margin-bottom: 15px; }
            th { background: #eaf6ff; font-weight: bold; }
            td { border: none; border-bottom: 1px solid #ccc; }
            td:before {
                content: attr(data-label);
                font-weight: bold;
                display: block;
                color: #005baa;
            }
            tr:last-child td { border-bottom: none; }
            .filtros-form label, .filtros-form button { display: block; margin-bottom: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Lecturas de Medidores</h1>
        <?php if ($mensaje): ?>
            <div class="<?php echo strpos($mensaje, 'correctamente') !== false ? 'msg' : 'error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de registro de lectura (solo admin y operador) -->
        <?php if ($rol_id == 1 || $rol_id == 3): ?>
        <form method="post" style="margin-bottom:20px;">
            <label>Medidor:
                <select name="medidor_id" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($medidores as $m): ?>
                        <option value="<?php echo $m['id']; ?>">
                            <?php echo htmlspecialchars($m['numero_serie']); ?>
                            <?php if (isset($m['usuario_nombre'])) echo " - " . htmlspecialchars($m['usuario_nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Fecha: <input type="date" name="fecha" required></label>
            <label>Valor: <input type="number" step="0.01" name="valor" required></label>
            <label>Observaciones: <input type="text" name="observaciones"></label>
            <button type="submit">Registrar Lectura</button>
        </form>
        <?php endif; ?>

        <!-- Filtros -->
        <form method="get" class="filtros-form" style="margin-bottom:20px;">
            <label>Medidor:
                <select name="medidor_id">
                    <option value="">Todos</option>
                    <?php foreach ($medidores as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php if ($medidor_filtro == $m['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($m['numero_serie']); ?>
                            <?php if (isset($m['usuario_nombre'])) echo " - " . htmlspecialchars($m['usuario_nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Filtrar</button>
        </form>

        <!-- Tabla de lecturas -->
        <table>
            <tr>
                <th>ID</th>
                <th>Medidor</th>
                <th>Fecha</th>
                <th>Valor</th>
                <th>Observaciones</th>
                <th>Registrado por</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($lecturas as $l): ?>
            <tr>
                <td data-label="ID"><?php echo $l['id']; ?></td>
                <td data-label="Medidor"><?php echo htmlspecialchars($l['numero_serie']); ?></td>
                <td data-label="Fecha"><?php echo $l['fecha']; ?></td>
                <td data-label="Valor"><?php echo $l['valor']; ?></td>
                <td data-label="Observaciones"><?php echo htmlspecialchars($l['observaciones']); ?></td>
                <td data-label="Registrado por"><?php echo htmlspecialchars($l['usuario_nombre']); ?></td>
                <td data-label="Acciones">
                    <?php if ($rol_id == 1 || $rol_id == 3): ?>
                        <a href="editar_lectura.php?id=<?php echo $l['id']; ?>">Editar</a> |
                        <a href="eliminar_lectura.php?id=<?php echo $l['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar esta lectura?')">Eliminar</a> |
                    <?php endif; ?>
                    <a href="historial_lectura.php?id=<?php echo $l['id']; ?>">Ver historial</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($lecturas)): ?>
            <tr>
                <td class="no-data" data-label="Sin datos" colspan="7">No hay lecturas registradas.</td>
            </tr>
            <?php endif; ?>
        </table>
        <br>
        <a href="<?php echo ($rol_id == 2) ? 'menu_usuario.php' : 'menu.php'; ?>">Volver al menú</a>
    </div>
</body>
</html>
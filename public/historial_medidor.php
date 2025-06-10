<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/historial_medidor.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$medidor_id = intval($_GET['id'] ?? 0);
$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Obtener usuarios para filtro
$usuarios = [];
$res_usuarios = $mysqli->query("SELECT DISTINCT u.id, u.nombre FROM historial_medidor h LEFT JOIN usuarios u ON h.usuario_id = u.id WHERE h.medidor_id = $medidor_id ORDER BY u.nombre");
while ($row = $res_usuarios->fetch_assoc()) {
    $usuarios[] = $row;
}
$res_usuarios->close();

// Filtros
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$usuario_filtro = $_GET['usuario_id'] ?? '';

$where = ["h.medidor_id = ?"];
$params = [$medidor_id];
$types = 'i';

if ($fecha_inicio) {
    $where[] = "h.fecha >= ?";
    $params[] = $fecha_inicio;
    $types .= 's';
}
if ($fecha_fin) {
    $where[] = "h.fecha <= ?";
    $params[] = $fecha_fin;
    $types .= 's';
}
if ($usuario_filtro) {
    $where[] = "h.usuario_id = ?";
    $params[] = $usuario_filtro;
    $types .= 'i';
}

$sql = "SELECT h.*, u.nombre AS usuario_nombre
        FROM historial_medidor h
        LEFT JOIN usuarios u ON h.usuario_id = u.id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY h.fecha DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$historial = [];
while ($row = $res->fetch_assoc()) {
    $historial[] = $row;
}
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial del Medidor #<?php echo $medidor_id; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 800px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eaf6ff; }
        .no-data {
            text-align: center;
            color: #888;
            padding: 20px 0;
        }
        .filtros-form label { margin-right: 15px; }
        @media (max-width: 700px) {
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
        <h1>Historial del Medidor #<?php echo $medidor_id; ?></h1>
        <form method="get" class="filtros-form" style="margin-bottom:20px;">
            <input type="hidden" name="id" value="<?php echo $medidor_id; ?>">
            <label>Desde: <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>"></label>
            <label>Hasta: <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>"></label>
            <label>Usuario:
                <select name="usuario_id">
                    <option value="">Todos</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php if ($usuario_filtro == $u['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($u['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Filtrar</button>
        </form>
        <table>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Descripción</th>
            </tr>
            <?php foreach ($historial as $h): ?>
            <tr>
                <td data-label="Fecha"><?php echo $h['fecha']; ?></td>
                <td data-label="Usuario"><?php echo htmlspecialchars($h['usuario_nombre'] ?? 'Desconocido'); ?></td>
                <td data-label="Acción"><?php echo htmlspecialchars($h['accion']); ?></td>
                <td data-label="Descripción"><?php echo htmlspecialchars($h['descripcion']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($historial)): ?>
            <tr>
                <td class="no-data" data-label="Sin datos" colspan="4">No hay historial para este medidor.</td>
            </tr>
            <?php endif; ?>
        </table>
        <br>
        <a href="medidores_admin.php">Volver a los medidores</a>
    </div>
</body>
</html>
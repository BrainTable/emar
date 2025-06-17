<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/ordenes_servicio.php
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

// --- FILTROS AVANZADOS ---
$where = ["o.usuario_id = ?"];
$params = [$usuario_id];
$types = 'i';

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$estado_filtro = $_GET['estado'] ?? '';

if ($fecha_inicio) {
    $where[] = "o.fecha_solicitud >= ?";
    $params[] = $fecha_inicio;
    $types .= 's';
}
if ($fecha_fin) {
    $where[] = "o.fecha_solicitud <= ?";
    $params[] = $fecha_fin;
    $types .= 's';
}
if ($estado_filtro) {
    $where[] = "o.estado = ?";
    $params[] = $estado_filtro;
    $types .= 's';
}

$sql = "SELECT o.id, o.tipo, o.descripcion, o.fecha_solicitud, o.estado, o.observaciones,
               op.nombre AS operario_nombre
        FROM ordenes_servicio o
        LEFT JOIN usuarios op ON o.operario_id = op.id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY o.fecha_solicitud DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$ordenes = [];
while ($row = $res->fetch_assoc()) {
    $ordenes[] = $row;
}
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Órdenes de Servicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .container { max-width: 1100px; margin: 80px auto 0 auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        h1 { color: #005baa; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eaf6ff; }
        a.historial { color: #005baa; text-decoration: underline; }
        .no-data {
            text-align: center;
            color: #888;
            padding: 20px 0;
        }
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
    <?php include 'header_emar.php'; ?>
    <div class="container">
        <h1>Mis Órdenes de Servicio</h1>
        <form method="get" class="filtros-form" style="margin-bottom:20px;">
            <label>Desde: <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>"></label>
            <label>Hasta: <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>"></label>
            <label>Estado:
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="Pendiente" <?php if ($estado_filtro == 'Pendiente') echo 'selected'; ?>>Pendiente</option>
                    <option value="En proceso" <?php if ($estado_filtro == 'En proceso') echo 'selected'; ?>>En proceso</option>
                    <option value="Finalizada" <?php if ($estado_filtro == 'Finalizada') echo 'selected'; ?>>Finalizada</option>
                    <option value="Cancelada" <?php if ($estado_filtro == 'Cancelada') echo 'selected'; ?>>Cancelada</option>
                </select>
            </label>
            <button type="submit">Filtrar</button>
        </form>
        <table>
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Fecha de Solicitud</th>
                <th>Estado</th>
                <th>Observaciones</th>
                <th>Operario Asignado</th>
                <th>Historial</th>
            </tr>
            <?php foreach ($ordenes as $orden): ?>
            <tr>
                <td data-label="ID"><?php echo $orden['id']; ?></td>
                <td data-label="Tipo"><?php echo htmlspecialchars($orden['tipo']); ?></td>
                <td data-label="Descripción"><?php echo htmlspecialchars($orden['descripcion']); ?></td>
                <td data-label="Fecha de Solicitud"><?php echo $orden['fecha_solicitud']; ?></td>
                <td data-label="Estado"><?php echo htmlspecialchars($orden['estado']); ?></td>
                <td data-label="Observaciones"><?php echo htmlspecialchars($orden['observaciones'] ?? ''); ?></td>
                <td data-label="Operario Asignado">
                    <?php echo $orden['operario_nombre'] ? htmlspecialchars($orden['operario_nombre']) : '<span style="color:#888;">Sin asignar</span>'; ?>
                </td>
                <td data-label="Historial">
                    <a class="historial" href="historial_orden.php?id=<?php echo $orden['id']; ?>">Ver historial</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($ordenes)): ?>
            <tr>
                <td class="no-data" data-label="Sin datos" colspan="8">No tienes órdenes registradas.</td>
            </tr>
            <?php endif; ?>
        </table>
        <br>
        <a href="menu_usuario.php">Volver al menú</a>
    </div>
</body>
</html>
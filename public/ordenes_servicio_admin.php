<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/ordenes_servicio_admin.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: menu.php");
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Obtener lista de operarios
$operarios = [];
$res = $mysqli->query("SELECT id, nombre FROM usuarios WHERE rol_id = 3 ORDER BY nombre");
while ($row = $res->fetch_assoc()) {
    $operarios[] = $row;
}
$res->close();

// Procesar cambios de estado, observaciones y asignación de operario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orden_id'])) {
    $orden_id = intval($_POST['orden_id']);
    $nuevo_estado = $_POST['estado'] ?? '';
    $observaciones = trim($_POST['observaciones'] ?? '');
    $operario_id = !empty($_POST['operario_id']) ? intval($_POST['operario_id']) : null;

    // Obtener datos anteriores para comparar y registrar historial
    $stmt_old = $mysqli->prepare("SELECT estado, observaciones, operario_id FROM ordenes_servicio WHERE id=?");
    $stmt_old->bind_param("i", $orden_id);
    $stmt_old->execute();
    $stmt_old->bind_result($old_estado, $old_obs, $old_operario_id);
    $stmt_old->fetch();
    $stmt_old->close();

    // Actualizar la orden
    $stmt = $mysqli->prepare("UPDATE ordenes_servicio SET estado=?, observaciones=?, operario_id=? WHERE id=?");
    $stmt->bind_param("ssii", $nuevo_estado, $observaciones, $operario_id, $orden_id);
    $stmt->execute();
    $stmt->close();

    // Registrar en historial SOLO si hubo cambios
    $cambios = [];
    if ($nuevo_estado !== $old_estado) $cambios[] = "Estado: '$old_estado' → '$nuevo_estado'";
    if ($observaciones !== $old_obs) $cambios[] = "Observaciones modificadas";
    if ($operario_id != $old_operario_id) $cambios[] = "Operario asignado cambiado";

    if ($cambios) {
        $accion = "Actualización";
        $descripcion = implode("; ", $cambios);
        $usuario_id = $_SESSION['usuario_id'];
        $mysqli2 = new mysqli("localhost", "root", "", "emar_db");
        $stmt2 = $mysqli2->prepare("INSERT INTO historial_orden (orden_id, usuario_id, accion, descripcion) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("iiss", $orden_id, $usuario_id, $accion, $descripcion);
        $stmt2->execute();
        $stmt2->close();
        $mysqli2->close();
    }
}

// --- FILTROS AVANZADOS ---
$where = [];
$params = [];
$types = '';

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

$sql = "SELECT o.id, o.tipo, o.descripcion, o.fecha_solicitud, o.estado, o.observaciones, o.operario_id, u.nombre AS usuario_nombre, u.email AS usuario_email
        FROM ordenes_servicio o
        INNER JOIN usuarios u ON o.usuario_id = u.id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY o.fecha_solicitud DESC";

$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
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
    <title>Todas las Órdenes de Servicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 1300px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        h1 { color: #005baa; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eaf6ff; }
        form { margin: 0; }
        select, textarea { width: 100%; }
        .acciones { min-width: 200px; }
        .btn { padding: 6px 16px; background: #005baa; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #003f6d; }
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
    <div class="container">
        <h1>Todas las Órdenes de Servicio</h1>
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
            <button type="submit" class="btn">Filtrar</button>
        </form>
        <table>
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Fecha de Solicitud</th>
                <th>Estado</th>
                <th>Observaciones</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Operario Asignado</th>
                <th class="acciones">Acciones</th>
            </tr>
            <?php foreach ($ordenes as $orden): ?>
            <tr>
                <form method="post">
                    <td data-label="ID"><?php echo $orden['id']; ?></td>
                    <td data-label="Tipo"><?php echo htmlspecialchars($orden['tipo']); ?></td>
                    <td data-label="Descripción"><?php echo htmlspecialchars($orden['descripcion']); ?></td>
                    <td data-label="Fecha de Solicitud"><?php echo $orden['fecha_solicitud']; ?></td>
                    <td data-label="Estado">
                        <select name="estado" required>
                            <option value="Pendiente" <?php if ($orden['estado'] == 'Pendiente') echo 'selected'; ?>>Pendiente</option>
                            <option value="En proceso" <?php if ($orden['estado'] == 'En proceso') echo 'selected'; ?>>En proceso</option>
                            <option value="Finalizada" <?php if ($orden['estado'] == 'Finalizada') echo 'selected'; ?>>Finalizada</option>
                            <option value="Cancelada" <?php if ($orden['estado'] == 'Cancelada') echo 'selected'; ?>>Cancelada</option>
                        </select>
                    </td>
                    <td data-label="Observaciones">
                        <textarea name="observaciones" rows="2"><?php echo htmlspecialchars($orden['observaciones'] ?? ''); ?></textarea>
                    </td>
                    <td data-label="Usuario"><?php echo htmlspecialchars($orden['usuario_nombre']); ?></td>
                    <td data-label="Email"><?php echo htmlspecialchars($orden['usuario_email']); ?></td>
                    <td data-label="Operario Asignado">
                        <select name="operario_id">
                            <option value="">Sin asignar</option>
                            <?php foreach ($operarios as $op): ?>
                                <option value="<?php echo $op['id']; ?>" <?php if ($orden['operario_id'] == $op['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($op['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td data-label="Acciones">
                        <input type="hidden" name="orden_id" value="<?php echo $orden['id']; ?>">
                        <button type="submit" class="btn">Guardar</button>
                        <br>
                        <a href="historial_orden.php?id=<?php echo $orden['id']; ?>">Ver historial</a>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($ordenes)): ?>
            <tr>
                <td class="no-data" data-label="Sin datos" colspan="10">No hay órdenes registradas.</td>
            </tr>
            <?php endif; ?>
        </table>
        <br>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
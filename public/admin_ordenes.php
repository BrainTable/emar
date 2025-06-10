<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/admin_ordenes.php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}
$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Cambiar estado o asignar operario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orden_id'])) {
    $orden_id = intval($_POST['orden_id']);
    $estado = $_POST['estado'] ?? '';
    $operario_id = is_numeric($_POST['operario_id']) ? intval($_POST['operario_id']) : null;
    $stmt = $mysqli->prepare("UPDATE ordenes_servicio SET estado=?, operario_id=? WHERE id=?");
    $stmt->bind_param("sii", $estado, $operario_id, $orden_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_ordenes.php?msg=actualizado");
    exit;
}

$msg = "";
if (isset($_GET['msg']) && $_GET['msg'] == "actualizado") $msg = "Orden actualizada correctamente.";

$ordenes = $mysqli->query("SELECT o.*, u.nombre AS usuario, op.nombre AS operario 
    FROM ordenes_servicio o 
    JOIN usuarios u ON o.usuario_id = u.id 
    LEFT JOIN usuarios op ON o.operario_id = op.id 
    ORDER BY o.fecha_solicitud DESC");
$operarios = $mysqli->query("SELECT id, nombre FROM usuarios WHERE rol_id=3");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Órdenes de Servicio</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; }
        h2 { color: #005baa; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #eaf6ff; }
        .msg { color: green; margin-bottom: 15px; }
        .btn { background: #005baa; color: #fff; border: none; border-radius: 6px; padding: 8px 20px; font-size: 15px; cursor: pointer; text-decoration: none; }
        .btn:hover { background: #003f6d; }
        select { padding: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Órdenes de Servicio</h2>
        <?php if ($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Fecha Solicitud</th>
                <th>Estado</th>
                <th>Operario</th>
                <th>Acciones</th>
            </tr>
            <?php while($row = $ordenes->fetch_assoc()): ?>
            <tr>
                <form method="post">
                    <td><?= $row['id'] ?><input type="hidden" name="orden_id" value="<?= $row['id'] ?>"></td>
                    <td><?= htmlspecialchars($row['usuario']) ?></td>
                    <td><?= htmlspecialchars($row['tipo']) ?></td>
                    <td><?= htmlspecialchars($row['descripcion']) ?></td>
                    <td><?= $row['fecha_solicitud'] ?></td>
                    <td>
                        <select name="estado">
                            <option value="Pendiente" <?= $row['estado']=='Pendiente'?'selected':''; ?>>Pendiente</option>
                            <option value="En proceso" <?= $row['estado']=='En proceso'?'selected':''; ?>>En proceso</option>
                            <option value="Finalizada" <?= $row['estado']=='Finalizada'?'selected':''; ?>>Finalizada</option>
                        </select>
                    </td>
                    <td>
                        <select name="operario_id">
                            <option value="">Sin asignar</option>
                            <?php
                            $operarios->data_seek(0);
                            while($op = $operarios->fetch_assoc()): ?>
                                <option value="<?= $op['id'] ?>" <?= $row['operario_id']==$op['id']?'selected':''; ?>>
                                    <?= htmlspecialchars($op['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td>
                        <button type="submit" class="btn">Actualizar</button>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <a href="admin_panel.php" class="btn">Volver al panel</a>
    </div>
</body>
</html>
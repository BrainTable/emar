<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/admin_inventario.php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}
$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Cambiar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inv_id'])) {
    $inv_id = intval($_POST['inv_id']);
    $estado = $_POST['estado'] ?? 'almacen';
    $stmt = $mysqli->prepare("UPDATE inventario_medidores SET estado=? WHERE id=?");
    $stmt->bind_param("si", $estado, $inv_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_inventario.php?msg=actualizado");
    exit;
}

$msg = "";
if (isset($_GET['msg']) && $_GET['msg'] == "actualizado") $msg = "Inventario actualizado correctamente.";

$inventario = $mysqli->query("SELECT * FROM inventario_medidores ORDER BY fecha_ingreso DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Medidores</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 1000px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; }
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
        <h2>Inventario de Medidores</h2>
        <?php if ($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Modelo</th>
                <th>Estado</th>
                <th>Fecha Ingreso</th>
                <th>Observaciones</th>
                <th>Acciones</th>
            </tr>
            <?php while($row = $inventario->fetch_assoc()): ?>
            <tr>
                <form method="post">
                    <td><?= $row['id'] ?><input type="hidden" name="inv_id" value="<?= $row['id'] ?>"></td>
                    <td><?= htmlspecialchars($row['codigo']) ?></td>
                    <td><?= htmlspecialchars($row['modelo']) ?></td>
                    <td>
                        <select name="estado">
                            <option value="almacen" <?= $row['estado']=='almacen'?'selected':''; ?>>Almacén</option>
                            <option value="asignado" <?= $row['estado']=='asignado'?'selected':''; ?>>Asignado</option>
                            <option value="defectuoso" <?= $row['estado']=='defectuoso'?'selected':''; ?>>Defectuoso</option>
                            <option value="baja" <?= $row['estado']=='baja'?'selected':''; ?>>Baja</option>
                            <option value="reparacion" <?= $row['estado']=='reparacion'?'selected':''; ?>>Reparación</option>
                        </select>
                    </td>
                    <td><?= $row['fecha_ingreso'] ?></td>
                    <td><?= htmlspecialchars($row['observaciones']) ?></td>
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
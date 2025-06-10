<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/mis_medidores.php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    header("Location: login.php");
    exit;
}
$usuario_id = $_SESSION['usuario_id'];
$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Eliminar medidor si se solicita
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    // Solo puede eliminar sus propios medidores
    $mysqli->query("DELETE FROM medidores WHERE id=$id AND usuario_id=$usuario_id");
    header("Location: mis_medidores.php?msg=eliminado");
    exit;
}

// Mensaje de éxito
$msg = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == "eliminado") $msg = "Medidor eliminado correctamente.";
    if ($_GET['msg'] == "editado") $msg = "Medidor editado correctamente.";
}

$result = $mysqli->query("SELECT * FROM medidores WHERE usuario_id = $usuario_id ORDER BY creado_en DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Medidores</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; }
        h2 { color: #005baa; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #eaf6ff; }
        img { max-width: 80px; max-height: 80px; border-radius: 6px; }
        .acciones a { margin: 0 5px; text-decoration: none; color: #005baa; font-weight: bold; }
        .acciones a.eliminar { color: #d32f2f; }
        .msg { color: green; margin-bottom: 15px; }
        .btn { background: #005baa; color: #fff; border: none; border-radius: 6px; padding: 8px 20px; font-size: 15px; cursor: pointer; text-decoration: none; }
        .btn:hover { background: #003f6d; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Mis Medidores Registrados</h2>
        <?php if ($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <a href="registrar_medidor.php" class="btn">Registrar Nuevo Medidor</a>
        <table>
            <tr>
                <th>ID</th>
                <th>Número de Serie</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Diámetro</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Foto</th>
                <th>Fecha Instalación</th>
                <th>Acciones</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['numero_serie']) ?></td>
                <td><?= htmlspecialchars($row['marca']) ?></td>
                <td><?= htmlspecialchars($row['modelo']) ?></td>
                <td><?= htmlspecialchars($row['diametro']) ?> mm</td>
                <td><?= htmlspecialchars($row['ubicacion']) ?></td>
                <td><?= htmlspecialchars($row['estado']) ?></td>
                <td>
                    <?php if ($row['foto']): ?>
                        <img src="img/medidores/<?= htmlspecialchars($row['foto']) ?>" alt="Foto Medidor">
                    <?php else: ?>
                        Sin foto
                    <?php endif; ?>
                </td>
                <td><?= $row['fecha_instalacion'] ?></td>
                <td class="acciones">
                    <a href="editar_medidor.php?id=<?= $row['id'] ?>">Editar</a>
                    <a href="mis_medidores.php?eliminar=<?= $row['id'] ?>" class="eliminar" onclick="return confirm('¿Seguro que deseas eliminar este medidor?');">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows == 0): ?>
            <tr><td colspan="10">No tienes medidores registrados.</td></tr>
            <?php endif; ?>
        </table>
        <br>
        <a href="menu.php" class="btn">Volver al menú</a>
    </div>
</body>
</html>
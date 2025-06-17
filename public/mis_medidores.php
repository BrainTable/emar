<?php
// filepath: c:\xampp\htdocs\emar\public\mis_medidores.php
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
        table { width: 100%; border-collapse: collapse; margin-top: 30px; background: #fff; }
        th, td { border: 1px solid #e0e0e0; padding: 8px 10px; text-align: center; }
        th { background: #005baa; color: #fff; }
        tr:nth-child(even) { background: #f4f6f8; }
        img { max-width: 60px; max-height: 60px; border-radius: 6px; }
        .acciones a { margin: 0 5px; text-decoration: none; color: #005baa; font-weight: bold; }
        .acciones a.eliminar { color: #d32f2f; }
        .msg { color: green; margin-bottom: 15px; }
        .btn { background: #005baa; color: #fff; border: none; border-radius: 6px; padding: 8px 20px; font-size: 15px; cursor: pointer; text-decoration: none; }
        .btn:hover { background: #003f6d; }
    </style>
</head>
<body>
    <!-- Línea azul y logo -->
    <div style="background:#005baa; padding: 0;">
        <div style="max-width:900px; margin:0 auto; display:flex; align-items:center; padding:10px 20px;">
            <img src="img/logo-emar.jpg" alt="Logo Emar" style="height:48px; margin-right:18px;">
            <span style="color:#fff; font-size:2rem; font-weight:bold; letter-spacing:2px;">EMAR</span>
        </div>
    </div>
    <div class="container">
        <h2>Mis Medidores Registrados</h2>
        <?php if ($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <a href="registrar_medidor.php" class="btn">Registrar Nuevo Medidor</a>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Número de Serie</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Foto</th>
                <th>Fecha Instalación</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['numero_serie']) ?></td>
                <td><?= htmlspecialchars($row['ubicacion']) ?></td>
                <td>
                    <span style="color:<?= $row['estado']=='activo' ? '#0a7d0a' : ($row['estado']=='inactivo' ? '#c00' : '#e6a700') ?>">
                        <?= ucfirst($row['estado']) ?>
                    </span>
                </td>
                <td>
                    <?php if (!empty($row['foto']) && file_exists('img/medidores/' . $row['foto'])): ?>
                        <img src="img/medidores/<?= htmlspecialchars($row['foto']) ?>" alt="Foto Medidor">
                    <?php else: ?>
                        <span style="color:#888;">Sin foto</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['fecha_instalacion']) ?></td>
                <td class="acciones">
                    <a href="editar_medidor.php?id=<?= $row['id'] ?>">Editar</a>
                    <a href="mis_medidores.php?eliminar=<?= $row['id'] ?>" class="eliminar" onclick="return confirm('¿Seguro que deseas eliminar este medidor?');">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows == 0): ?>
            <tr><td colspan="7" style="text-align:center;color:#888;">No tienes medidores registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <br>
        <a href="menu.php" class="btn">Volver al menú</a>
    </div>
</body>
</html>
<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/admin_usuarios.php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}
$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Eliminar usuario
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    if ($id != $_SESSION['usuario_id']) { // No puede eliminarse a sí mismo
        $mysqli->query("DELETE FROM usuarios WHERE id=$id");
        header("Location: admin_usuarios.php?msg=eliminado");
        exit;
    }
}

$msg = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == "eliminado") $msg = "Usuario eliminado correctamente.";
}

$result = $mysqli->query("SELECT u.*, r.nombre AS rol FROM usuarios u JOIN roles r ON u.rol_id = r.id ORDER BY u.id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Usuarios</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; }
        h2 { color: #005baa; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #eaf6ff; }
        .acciones a { margin: 0 5px; text-decoration: none; color: #005baa; font-weight: bold; }
        .acciones a.eliminar { color: #d32f2f; }
        .msg { color: green; margin-bottom: 15px; }
        .btn { background: #005baa; color: #fff; border: none; border-radius: 6px; padding: 8px 20px; font-size: 15px; cursor: pointer; text-decoration: none; }
        .btn:hover { background: #003f6d; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Administrar Usuarios</h2>
        <?php if ($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Fecha Registro</th>
                <th>Acciones</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['rol']) ?></td>
                <td><?= $row['creado_en'] ?></td>
                <td class="acciones">
                    <a href="editar_usuario.php?id=<?= $row['id'] ?>">Editar</a>
                    <?php if ($row['id'] != $_SESSION['usuario_id']): ?>
                    <a href="admin_usuarios.php?eliminar=<?= $row['id'] ?>" class="eliminar" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <a href="admin_panel.php" class="btn">Volver al panel</a>
    </div>
</body>
</html>
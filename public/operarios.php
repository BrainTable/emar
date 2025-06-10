<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/operarios.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "emar_db");
$operarios = [];
$res = $mysqli->query("SELECT id, nombre, email FROM usuarios WHERE rol_id = 3 ORDER BY id");
while ($row = $res->fetch_assoc()) {
    $operarios[] = $row;
}
$res->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Operarios</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eaf6ff; }
        a { color: #005baa; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Operarios</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($operarios as $operario): ?>
            <tr>
                <td><?php echo $operario['id']; ?></td>
                <td><?php echo htmlspecialchars($operario['nombre']); ?></td>
                <td><?php echo htmlspecialchars($operario['email']); ?></td>
                <td>
                    <a href="editar_usuario.php?id=<?php echo $operario['id']; ?>">Editar</a> |
                    <a href="eliminar_usuario.php?id=<?php echo $operario['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este operario?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($operarios)): ?>
            <tr><td colspan="4">No hay operarios registrados.</td></tr>
            <?php endif; ?>
        </table>
        <br>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
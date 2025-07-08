<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "emar_db");
if ($mysqli->connect_errno) {
    die("Error de conexión a la base de datos.");
}

// Agrega la columna foto a la consulta
$result = $mysqli->query("SELECT id, nombre, email, rol_id, foto FROM usuarios");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Usuarios</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        table { border-collapse: collapse; width: 80%; margin: 30px auto; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #005baa; color: #fff; }
        tr:nth-child(even) { background: #f2f2f2; }
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        h2 { text-align: center; color: #005baa; }
        a { color: #005baa; text-decoration: none; }
        .foto-inmueble { max-width: 80px; max-height: 80px; border-radius: 8px; }
    </style>
</head>
<body>
    <?php include 'header_emar.php'; ?>
    <h2>Lista de Usuarios</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Foto Inmueble</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['rol_id']) ?></td>
            <td>
                <?php if (!empty($row['foto'])): ?>
                    <img src="<?= htmlspecialchars($row['foto']) ?>" alt="Foto inmueble" class="foto-inmueble">
                <?php else: ?>
                    <span>Sin foto</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <div style="text-align:center;">
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
<?php
$mysqli->close();
?>
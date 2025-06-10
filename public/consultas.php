<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}
$mysqli = new mysqli("localhost", "root", "", "emar_db");
$sql = "SELECT u.nombre, u.email, COUNT(m.id) AS total_medidores
        FROM usuarios u
        LEFT JOIN medidores m ON u.id = m.usuario_id
        GROUP BY u.id";
$result = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consultas</title>
</head>
<body>
    <h1>Usuarios y cantidad de medidores</h1>
    <table border="1" cellpadding="8">
        <tr>
            <th>Usuario</th>
            <th>Email</th>
            <th>Total de Medidores</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo $row['total_medidores']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p><a href="menu.php">Volver al menú</a></p>
</body>
</html>
<?php $mysqli->close(); ?>
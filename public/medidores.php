<?php
// filepath: c:\xampp\htdocs\emar\public\medidores.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: menu.php");
    exit;
}

$medidores = [];
$mysqli = new mysqli("localhost", "root", "", "emar_db");
if (!$mysqli->connect_errno) {
    $sql = "SELECT m.id, m.numero_serie, m.ubicacion, m.estado, m.foto, m.fecha_instalacion, u.nombre AS usuario_nombre, u.email AS usuario_email
            FROM medidores m
            INNER JOIN usuarios u ON m.usuario_id = u.id
            ORDER BY m.fecha_instalacion DESC";
    $res = $mysqli->query($sql);
    while ($row = $res->fetch_assoc()) {
        $medidores[] = $row;
    }
    $res->close();
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Todos los Medidores</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .container { max-width: 1100px; margin: 100px auto 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        h1 { color: #005baa; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eaf6ff; }
        img { border-radius: 6px; max-width: 80px; max-height: 80px; }
    </style>
</head>
<body>
    <!-- Encabezado igual que en index.html -->
    <header>
        <div class="logo">
            <img src="img/logo-emar.jpg" alt="Logo de EMAR">
            <span>EMAR S.A. E.S.P.</span>
        </div>
    </header>
    <div class="container">
        <h1>Todos los Medidores Registrados</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Número de Serie</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Fecha de Instalación</th>
                <th>Foto</th>
                <th>Usuario</th>
                <th>Email</th>
            </tr>
            <?php foreach ($medidores as $medidor): ?>
            <tr>
                <td><?php echo $medidor['id']; ?></td>
                <td><?php echo htmlspecialchars($medidor['numero_serie']); ?></td>
                <td><?php echo htmlspecialchars($medidor['ubicacion']); ?></td>
                <td><?php echo htmlspecialchars($medidor['estado']); ?></td>
                <td><?php echo htmlspecialchars($medidor['fecha_instalacion']); ?></td>
                <td>
                    <?php if (!empty($medidor['foto']) && file_exists("img/medidores/" . $medidor['foto'])): ?>
                        <img src="img/medidores/<?php echo htmlspecialchars($medidor['foto']); ?>" alt="Foto" width="80">
                    <?php else: ?>
                        <span>Sin foto</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($medidor['usuario_nombre']); ?></td>
                <td><?php echo htmlspecialchars($medidor['usuario_email']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($medidores)): ?>
            <tr><td colspan="8">No hay medidores registrados.</td></tr>
            <?php endif; ?>
        </table>
        <br>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
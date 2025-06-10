<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: menu.php");
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "emar_db");
$sql = "SELECT m.*, u.nombre AS usuario FROM medidores m JOIN usuarios u ON m.usuario_id = u.id";
$result = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Medidores (Operario)</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 950px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #005baa; color: #fff; }
        img { border-radius: 6px; }
        .volver { margin-top: 20px; display: inline-block; background: #005baa; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; }
        .volver:hover { background: #003f6d; }
        .foto-form input[type="file"] { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestionar Medidores (Operario)</h1>
        <table>
            <tr>
                <th>Número de Serie</th>
                <th>Usuario</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Foto Actual</th>
                <th>Cargar/Actualizar Foto</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['numero_serie']); ?></td>
                <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                <td><?php echo htmlspecialchars($row['ubicacion']); ?></td>
                <td><?php echo htmlspecialchars($row['estado']); ?></td>
                <td>
                    <?php if ($row['foto']): ?>
                        <img src="medidores/<?php echo htmlspecialchars($row['foto']); ?>" width="80">
                    <?php else: ?>
                        Sin foto
                    <?php endif; ?>
                </td>
                <td>
                    <form class="foto-form" action="subir_foto_medidor.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="medidor_id" value="<?php echo $row['id']; ?>">
                        <input type="file" name="foto" accept="image/*" required>
                        <button type="submit">Subir Foto</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <a class="volver" href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
<?php $mysqli->close(); ?>
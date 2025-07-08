<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}
$mysqli = new mysqli("localhost", "root", "", "emar_db");
// Agrega la columna foto
$res = $mysqli->query("SELECT id, nombre, email, foto FROM usuarios WHERE rol_id=3");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Operadores</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #eaf6ff; }
        .foto-inmueble { max-width: 60px; max-height: 60px; border-radius: 8px; }
    </style>
</head>
<body>
    <?php include 'header_emar.php'; ?>
    <div class="container">
        <h2>Lista de Operadores</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Foto Inmueble</th>
            </tr>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
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
        <br>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
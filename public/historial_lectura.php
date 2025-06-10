<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/historial_lectura.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$lectura_id = intval($_GET['id'] ?? 0);
$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Obtener historial de la lectura
$historial = [];
$sql = "SELECT h.*, u.nombre AS usuario_nombre
        FROM historial_lectura h
        LEFT JOIN usuarios u ON h.usuario_id = u.id
        WHERE h.lectura_id = ?
        ORDER BY h.fecha DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $lectura_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $historial[] = $row;
}
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de la Lectura #<?php echo $lectura_id; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 800px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eaf6ff; }
        .no-data { text-align: center; color: #888; padding: 20px 0; }
        @media (max-width: 700px) {
            .container { padding: 10px; max-width: 100% !important; }
            table, thead, tbody, th, td, tr { display: block; width: 100%; }
            th, td { box-sizing: border-box; }
            tr { margin-bottom: 15px; }
            th { background: #eaf6ff; font-weight: bold; }
            td { border: none; border-bottom: 1px solid #ccc; }
            td:before {
                content: attr(data-label);
                font-weight: bold;
                display: block;
                color: #005baa;
            }
            tr:last-child td { border-bottom: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Historial de la Lectura #<?php echo $lectura_id; ?></h1>
        <table>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Descripción</th>
            </tr>
            <?php foreach ($historial as $h): ?>
            <tr>
                <td data-label="Fecha"><?php echo $h['fecha']; ?></td>
                <td data-label="Usuario"><?php echo htmlspecialchars($h['usuario_nombre'] ?? 'Desconocido'); ?></td>
                <td data-label="Acción"><?php echo htmlspecialchars($h['accion']); ?></td>
                <td data-label="Descripción"><?php echo htmlspecialchars($h['descripcion']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($historial)): ?>
            <tr>
                <td class="no-data" data-label="Sin datos" colspan="4">No hay historial para esta lectura.</td>
            </tr>
            <?php endif; ?>
        </table>
        <br>
        <a href="lecturas.php">Volver a lecturas</a>
    </div>
</body>
</html>
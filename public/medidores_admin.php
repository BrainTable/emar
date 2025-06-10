<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/medidores_admin.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "emar_db");

// --- FILTROS AVANZADOS ---
$where = [];
$params = [];
$types = '';

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$estado_filtro = $_GET['estado'] ?? '';
$usuario_filtro = $_GET['usuario_id'] ?? '';

if ($fecha_inicio) {
    $where[] = "m.fecha_instalacion >= ?";
    $params[] = $fecha_inicio;
    $types .= 's';
}
if ($fecha_fin) {
    $where[] = "m.fecha_instalacion <= ?";
    $params[] = $fecha_fin;
    $types .= 's';
}
if ($estado_filtro) {
    $where[] = "m.estado = ?";
    $params[] = $estado_filtro;
    $types .= 's';
}
if ($usuario_filtro) {
    $where[] = "m.usuario_id = ?";
    $params[] = $usuario_filtro;
    $types .= 'i';
}

// Obtener lista de usuarios para el filtro
$usuarios = [];
$res_usuarios = $mysqli->query("SELECT id, nombre FROM usuarios WHERE rol_id = 2 ORDER BY nombre");
while ($row = $res_usuarios->fetch_assoc()) {
    $usuarios[] = $row;
}
$res_usuarios->close();

$sql = "SELECT m.id, m.numero_serie, m.ubicacion, m.estado, m.fecha_instalacion, u.nombre AS usuario_nombre
        FROM medidores m
        LEFT JOIN usuarios u ON m.usuario_id = u.id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY m.id";

$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$medidores = [];
while ($row = $res->fetch_assoc()) {
    $medidores[] = $row;
}
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Medidores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eaf6ff; }
        a { color: #005baa; text-decoration: underline; }
        .filtros-form label { margin-right: 15px; }
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
            .filtros-form label, .filtros-form button { display: block; margin-bottom: 10px; }
        }
        .no-data {
            text-align: center;
            color: #888;
            padding: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Medidores</h1>
        <form method="get" class="filtros-form" style="margin-bottom:20px;">
            <label>Desde: <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>"></label>
            <label>Hasta: <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>"></label>
            <label>Estado:
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="Activo" <?php if ($estado_filtro == 'Activo') echo 'selected'; ?>>Activo</option>
                    <option value="Inactivo" <?php if ($estado_filtro == 'Inactivo') echo 'selected'; ?>>Inactivo</option>
                    <option value="En mantenimiento" <?php if ($estado_filtro == 'En mantenimiento') echo 'selected'; ?>>En mantenimiento</option>
                </select>
            </label>
            <label>Usuario:
                <select name="usuario_id">
                    <option value="">Todos</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php if ($usuario_filtro == $u['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($u['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Filtrar</button>
        </form>
        <table>
            <tr>
                <th>ID</th>
                <th>Número de Serie</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Fecha de Instalación</th>
                <th>Usuario</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($medidores as $medidor): ?>
            <tr>
                <td data-label="ID"><?php echo $medidor['id']; ?></td>
                <td data-label="Número de Serie"><?php echo htmlspecialchars($medidor['numero_serie']); ?></td>
                <td data-label="Ubicación"><?php echo htmlspecialchars($medidor['ubicacion']); ?></td>
                <td data-label="Estado"><?php echo htmlspecialchars($medidor['estado']); ?></td>
                <td data-label="Fecha de Instalación"><?php echo htmlspecialchars($medidor['fecha_instalacion']); ?></td>
                <td data-label="Usuario"><?php echo htmlspecialchars($medidor['usuario_nombre']); ?></td>
                <td data-label="Acciones">
                    <a href="editar_medidor.php?id=<?php echo $medidor['id']; ?>">Editar</a> |
                    <a href="eliminar_medidor.php?id=<?php echo $medidor['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este medidor?')">Eliminar</a> |
                    <a href="historial_medidor.php?id=<?php echo $medidor['id']; ?>">Ver historial</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($medidores)): ?>
            <tr>
                <td class="no-data" data-label="Sin datos" colspan="7">No hay medidores registrados.</td>
            </tr>
            <?php endif; ?>
        </table>
        <br>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/usuarios.php
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

$nombre_filtro = $_GET['nombre'] ?? '';
$email_filtro = $_GET['email'] ?? '';
$rol_filtro = $_GET['rol_id'] ?? '';

if ($nombre_filtro) {
    $where[] = "nombre LIKE ?";
    $params[] = "%$nombre_filtro%";
    $types .= 's';
}
if ($email_filtro) {
    $where[] = "email LIKE ?";
    $params[] = "%$email_filtro%";
    $types .= 's';
}
if ($rol_filtro) {
    $where[] = "rol_id = ?";
    $params[] = $rol_filtro;
    $types .= 'i';
}

$sql = "SELECT id, nombre, email, rol_id FROM usuarios";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY id";

$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$usuarios = [];
while ($row = $res->fetch_assoc()) {
    $usuarios[] = $row;
}
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
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
        <h1>Usuarios</h1>
        <form method="get" class="filtros-form" style="margin-bottom:20px;">
            <label>Nombre: <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre_filtro); ?>"></label>
            <label>Email: <input type="text" name="email" value="<?php echo htmlspecialchars($email_filtro); ?>"></label>
            <label>Rol:
                <select name="rol_id">
                    <option value="">Todos</option>
                    <option value="1" <?php if ($rol_filtro == '1') echo 'selected'; ?>>Administrador</option>
                    <option value="2" <?php if ($rol_filtro == '2') echo 'selected'; ?>>Usuario</option>
                    <option value="3" <?php if ($rol_filtro == '3') echo 'selected'; ?>>Operario</option>
                </select>
            </label>
            <button type="submit">Filtrar</button>
        </form>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td data-label="ID"><?php echo $usuario['id']; ?></td>
                <td data-label="Nombre"><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                <td data-label="Email"><?php echo htmlspecialchars($usuario['email']); ?></td>
                <td data-label="Rol"><?php
                    if ($usuario['rol_id'] == 1) echo "Administrador";
                    elseif ($usuario['rol_id'] == 2) echo "Usuario";
                    elseif ($usuario['rol_id'] == 3) echo "Operario";
                    else echo "Desconocido";
                ?></td>
                <td data-label="Acciones">
                    <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>">Editar</a> |
                    <a href="eliminar_usuario.php?id=<?php echo $usuario['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($usuarios)): ?>
            <tr>
                <td class="no-data" data-label="Sin datos" colspan="5">No hay usuarios registrados.</td>
            </tr>
            <?php endif; ?>
        </table>
        <br>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
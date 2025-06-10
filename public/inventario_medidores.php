<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/inventario_medidores.php
session_start();
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol_id'] != 1 && $_SESSION['rol_id'] != 3)) {
    header("Location: login.php");
    exit;
}
$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Filtros
$filtro_estado = $_GET['estado'] ?? '';
$filtro_modelo = $_GET['modelo'] ?? '';
$where = [];
$params = [];
$types = '';

if ($filtro_estado !== '') {
    $where[] = "estado = ?";
    $params[] = $filtro_estado;
    $types .= 's';
}
if ($filtro_modelo !== '') {
    $where[] = "modelo LIKE ?";
    $params[] = "%$filtro_modelo%";
    $types .= 's';
}
$sql = "SELECT * FROM inventario_medidores";
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY fecha_ingreso DESC";
$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Medidores</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; }
        h3 { color: #005baa; }
        input, button, select { margin: 10px 0; padding: 10px; border-radius: 4px; border: 1px solid #ccc; }
        button { background: #005baa; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #003f6d; }
        #msg-inv-medidor { margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #eaf6ff; }
        .filtros { margin-bottom: 20px; }
        .filtros label { margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <form id="form-inv-medidor" autocomplete="off" style="margin-bottom:30px;">
            <h3>Registrar Medidor en Inventario</h3>
            <input type="text" name="codigo" placeholder="Código único del medidor" required>
            <input type="text" name="modelo" placeholder="Modelo" required>
            <input type="text" name="observaciones" placeholder="Observaciones">
            <button type="submit">Registrar</button>
            <div id="msg-inv-medidor"></div>
        </form>

        <form class="filtros" method="get">
            <label>
                Estado:
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="almacen" <?= $filtro_estado=='almacen'?'selected':'' ?>>Almacén</option>
                    <option value="asignado" <?= $filtro_estado=='asignado'?'selected':'' ?>>Asignado</option>
                    <option value="defectuoso" <?= $filtro_estado=='defectuoso'?'selected':'' ?>>Defectuoso</option>
                    <option value="baja" <?= $filtro_estado=='baja'?'selected':'' ?>>Baja</option>
                    <option value="reparacion" <?= $filtro_estado=='reparacion'?'selected':'' ?>>Reparación</option>
                </select>
            </label>
            <label>
                Modelo:
                <input type="text" name="modelo" value="<?= htmlspecialchars($filtro_modelo) ?>" placeholder="Buscar modelo">
            </label>
            <button type="submit">Filtrar</button>
            <a href="inventario_medidores.php" style="margin-left:10px;">Limpiar</a>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Modelo</th>
                <th>Estado</th>
                <th>Fecha Ingreso</th>
                <th>Observaciones</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['codigo']) ?></td>
                <td><?= htmlspecialchars($row['modelo']) ?></td>
                <td><?= htmlspecialchars($row['estado']) ?></td>
                <td><?= $row['fecha_ingreso'] ?></td>
                <td><?= htmlspecialchars($row['observaciones']) ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows == 0): ?>
            <tr><td colspan="6">No hay medidores registrados con esos filtros.</td></tr>
            <?php endif; ?>
        </table>
    </div>
    <script>
    document.getElementById('form-inv-medidor').onsubmit = function(e){
        e.preventDefault();
        var data = new FormData(this);
        fetch('registrar_medidor_inventario.php', {
            method: 'POST',
            body: data
        })
        .then(r => r.json())
        .then(res => {
            if(res.success){
                document.getElementById('msg-inv-medidor').innerHTML = '<span style="color:green">¡Medidor registrado!</span>';
                document.getElementById('form-inv-medidor').reset();
                setTimeout(()=>{ location.reload(); }, 1200);
            } else {
                document.getElementById('msg-inv-medidor').innerHTML = '<span style="color:red">'+res.error+'</span>';
            }
        });
    };
    </script>
</body>
</html>
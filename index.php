<?php
include 'conexion.php';
$result = $conn->query("SELECT * FROM medidores");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventario de Medidores</title>
</head>
<body>
    <h1>Inventario de Medidores</h1>
    <a href="agregar.php">Agregar Medidor</a>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Número de Serie</th>
            <th>Tipo</th>
            <th>Ubicación</th>
            <th>Estado</th>
            <th>Fecha Instalación</th>
            <th>Acciones</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['numero_serie'] ?></td>
            <td><?= $row['tipo'] ?></td>
            <td><?= $row['ubicacion'] ?></td>
            <td><?= $row['estado'] ?></td>
            <td><?= $row['fecha_instalacion'] ?></td>
            <td>
                <a href="editar.php?id=<?= $row['id'] ?>">Editar</a> | 
                <a href="eliminar.php?id=<?= $row['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este medidor?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
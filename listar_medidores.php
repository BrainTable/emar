<?php
require_once '../src/conexion.php';

// Consultar los medidores
$query = "SELECT * FROM medidores";
$result = mysqli_query($conex, $query);

echo "<h1>Inventario de Medidores</h1>";
echo "<table border='1'>
        <tr>
            <th>ID</th>
            <th>Número de Serie</th>
            <th>Tipo</th>
            <th>Ubicación</th>
            <th>Estado</th>
            <th>Fecha de Instalación</th>
            <th>Última Actualización</th>
        </tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['numero_serie']}</td>
            <td>{$row['tipo']}</td>
            <td>{$row['ubicacion']}</td>
            <td>{$row['estado']}</td>
            <td>{$row['fecha_instalacion']}</td>
            <td>{$row['fecha_actualizacion']}</td>
          </tr>";
}

echo "</table>";

// Cerrar la conexión
mysqli_close($conex);
?>
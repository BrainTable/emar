<?php
include 'conexion.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $numero_serie = $_POST['numero_serie'];
    $tipo = $_POST['tipo'];
    $ubicacion = $_POST['ubicacion'];
    $estado = $_POST['estado'];
    $fecha_instalacion = $_POST['fecha_instalacion'];
    $sql = "INSERT INTO medidores (numero_serie, tipo, ubicacion, estado, fecha_instalacion) VALUES ('$numero_serie', '$tipo', '$ubicacion', '$estado', '$fecha_instalacion')";
    $conn->query($sql);
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Agregar Medidor</title>
</head>
<body>
    <h1>Agregar Medidor</h1>
    <form method="post">
        Número de Serie: <input type="text" name="numero_serie" required><br>
        Tipo: <input type="text" name="tipo" required><br>
        Ubicación: <input type="text" name="ubicacion"><br>
        Estado: 
        <select name="estado">
            <option value="activo">Activo</option>
            <option value="mantenimiento">Mantenimiento</option>
            <option value="inactivo">Inactivo</option>
        </select><br>
        Fecha de Instalación: <input type="date" name="fecha_instalacion"><br>
        <input type="submit" value="Agregar">
    </form>
    <a href="index.php">Volver</a>
</body>
</html>
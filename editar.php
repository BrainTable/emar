<?php
<?php
include 'conexion.php';
$id = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $numero_serie = $_POST['numero_serie'];
    $tipo = $_POST['tipo'];
    $ubicacion = $_POST['ubicacion'];
    $estado = $_POST['estado'];
    $fecha_instalacion = $_POST['fecha_instalacion'];
    $sql = "UPDATE medidores SET numero_serie='$numero_serie', tipo='$tipo', ubicacion='$ubicacion', estado='$estado', fecha_instalacion='$fecha_instalacion' WHERE id=$id";
    $conn->query($sql);
    header("Location: index.php");
}
$result = $conn->query("SELECT * FROM medidores WHERE id=$id");
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Editar Medidor</title>
</head>
<body>
    <h1>Editar Medidor</h1>
    <form method="post">
        Número de Serie: <input type="text" name="numero_serie" value="<?= $row['numero_serie'] ?>" required><br>
        Tipo: <input type="text" name="tipo" value="<?= $row['tipo'] ?>" required><br>
        Ubicación: <input type="text" name="ubicacion" value="<?= $row['ubicacion'] ?>"><br>
        Estado: 
        <select name="estado">
            <option value="activo" <?= $row['estado']=='activo'?'selected':'' ?>>Activo</option>
            <option value="mantenimiento" <?= $row['estado']=='mantenimiento'?'selected':'' ?>>Mantenimiento</option>
            <option value="inactivo" <?= $row['estado']=='inactivo'?'selected':'' ?>>Inactivo</option>
        </select><br>
        Fecha de Instalación: <input type="date" name="fecha_instalacion" value="<?= $row['fecha_instalacion'] ?>"><br>
        <input type="submit" value="Actualizar">
    </form>
    <a href="index.php">Volver</a>
</body>
</html>
<?php
<?php
include 'conexion.php';
$id = $_GET['id'];
$conn->query("DELETE FROM medidores WHERE id=$id");
header("Location: index.php");
?>
<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "InventarioMedidores";

$conex = new mysqli($host, $user, $pass, $db);

if ($conex->connect_error) {
    die("Conexión fallida: " . $conex->connect_error);
}
?>
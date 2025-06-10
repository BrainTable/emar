<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    die("No autorizado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $medidor_id = intval($_POST['medidor_id']);
    $foto = $_FILES['foto'];
    $nombre_archivo = uniqid() . '_' . basename($foto['name']);
    $ruta_destino = __DIR__ . '/medidores/' . $nombre_archivo;

    // Validar tipo de archivo (solo imágenes)
    $permitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!in_array($foto['type'], $permitidos)) {
        die("Solo se permiten imágenes JPG, PNG o GIF.");
    }

    if (move_uploaded_file($foto['tmp_name'], $ruta_destino)) {
        $mysqli = new mysqli("localhost", "root", "", "emar_db");
        $stmt = $mysqli->prepare("UPDATE medidores SET foto=? WHERE id=?");
        $stmt->bind_param("si", $nombre_archivo, $medidor_id);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
        header("Location: gestionar_medidores.php?ok=1");
        exit;
    } else {
        echo "Error al subir la foto.";
    }
} else {
    echo "Solicitud inválida.";
}
?>
<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/admin_panel.php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 600px; margin: 60px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        h1 { color: #005baa; }
        nav { margin: 30px 0 0 0; display: flex; flex-direction: column; gap: 15px; }
        nav a { display: inline-block; padding: 15px 30px; background: #005baa; color: #fff; border-radius: 6px; text-decoration: none; font-weight: bold; transition: background 0.2s; }
        nav a:hover { background: #003f6d; }
    </style>
</head>
<body>
    <?php include 'header_emar.php'; ?>
    <div class="container">
        <h1>Panel de Administración</h1>
        <nav>
            <a href="admin_usuarios.php">Gestión de Usuarios</a>
            <a href="admin_ordenes.php">Gestión de Órdenes de Servicio</a>
            <a href="admin_inventario.php">Inventario de Medidores</a>
            <a href="logout.php">Cerrar Sesión</a>
        </nav>
    </div>
</body>
</html>
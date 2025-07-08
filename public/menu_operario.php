<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    $_SESSION['logout_msg'] = "Tu sesión ha expirado o fue cerrada. Por favor, inicia sesión de nuevo.";
    header("Location: login.php");
    exit;
}
$nombre = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>EMAR | Menú Operario</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin:0; }
        .container { max-width: 600px; margin: 60px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        nav { margin: 30px 0 0 0; display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; }
        nav a { display: inline-block; padding: 15px 30px; background: #005baa; color: #fff; border-radius: 6px; text-decoration: none; font-weight: bold; transition: background 0.2s; }
        nav a:hover { background: #003f6d; }
        .bienvenida { margin-top: 20px; font-size: 18px; color: #333; }
        .guia { margin-top: 30px; background: #eaf6ff; border-radius: 8px; padding: 18px; color: #005baa; font-size: 16px; }
        .header { text-align: right; margin-bottom: 10px; }
        .header span { color: #005baa; font-weight: bold; margin-right: 15px; }
    </style>
</head>
<body>
    <!-- Barra superior/franja azul -->
    <div class="barra-superior" style="background:#005baa; color:#fff; padding:12px 0; text-align:center; font-size:20px; font-weight:bold;">
        <img src="img/logo-emar.jpg" alt="Logo EMAR" style="height:38px; vertical-align:middle; margin-right:10px;">
        EMAR S.A. E.S.P. - Menú Operario
    </div>
    <?php include 'header_emar.php'; ?>
    <div class="container">
        <div class="header">
            <span>Operario: <?php echo htmlspecialchars($nombre); ?></span>
            <a href="logout.php" style="color:#d32f2f;">Cerrar sesión</a>
        </div>
        <h1>Bienvenido, <?php echo htmlspecialchars($nombre); ?> (Operario)</h1>
        <div class="bienvenida">
            Selecciona una opción del menú para gestionar tus tareas.
        </div>
        <nav>
            <a href="index.html">Inicio</a>
            <a href="ordenes_servicio_operario.php">Órdenes Asignadas</a>
            <a href="consultas.php">Consultas</a>
            <a href="cambiar_password.php">Cambiar contraseña</a>
            <a href="logout.php">Cerrar Sesión</a>
        </nav>
        <div class="guia">
            <b>¿Qué puedes hacer?</b><br>
            - Ver y gestionar órdenes de servicio asignadas.<br>
            - Consultar información relevante.<br>
            - Cambiar tu contraseña.<br>
            - Cerrar sesión al terminar.
        </div>
    </div>
</body>
</html>
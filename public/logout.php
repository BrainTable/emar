<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/logout.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sesión cerrada</title>
    <meta http-equiv="refresh" content="2;url=index.html">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; text-align: center; margin-top: 100px; }
        .msg { background: #fff; display: inline-block; padding: 40px 60px; border-radius: 10px; box-shadow: 0 2px 8px #0002; }
        .logo img { height: 70px; margin-bottom: 20px; }
        h2 { color: #005baa; }
        a { color: #005baa; text-decoration: underline; }
    </style>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sesión cerrada',
            text: 'Tu sesión se ha cerrado correctamente.',
            showConfirmButton: false,
            timer: 1800
        });
    </script>
    <div class="msg">
        <div class="logo">
            <img src="img/logo-emar.jpg" alt="Logo EMAR">
        </div>
        <h2>¡Gracias por usar EMAR!</h2>
        <p>Tu sesión se ha cerrado correctamente.</p>
        <p>Serás redirigido al inicio en unos segundos...</p>
        <p><a href="index.html">Ir al inicio ahora</a></p>
    </div>
</body>
</html>
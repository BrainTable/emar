<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/registrar_publico.php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 40px 30px; text-align: center; }
        input, button { margin: 10px 0; padding: 10px; width: 90%; border-radius: 4px; border: 1px solid #ccc; }
        .msg { color: #005baa; margin-bottom: 10px; }
        .error { color: #c00; margin-bottom: 10px; }
        @media (max-width: 600px) {
            .container { padding: 10px; max-width: 100% !important; }
            input, button { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registro de Usuario</h2>
        <form id="registroForm" enctype="multipart/form-data" method="post">
            <input type="text" name="nombre" placeholder="Nombre" maxlength="100" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="file" name="foto" accept="image/jpeg,image/png,image/gif">
            <!-- Campo oculto CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit">Registrarse</button>
        </form>
        <div id="respuesta"></div>
    </div>
    <script>
        document.getElementById('registroForm').onsubmit = async function(e) {
            e.preventDefault();
            const form = e.target;
            const data = new FormData(form);
            const respuesta = document.getElementById('respuesta');
            respuesta.textContent = '';
            const res = await fetch('registro_publico.php', {
                method: 'POST',
                body: data
            });
            const json = await res.json();
            if(json.success) {
                respuesta.innerHTML = '<div class="msg">¡Registro exitoso!</div>';
                form.reset();
            } else {
                respuesta.innerHTML = '<div class="error">' + json.error + '</div>';
            }
        }
    </script>
</body>
</html>
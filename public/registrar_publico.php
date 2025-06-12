<?php
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
</head>
<body>
    <form id="registroForm" enctype="multipart/form-data" method="post">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <input type="file" name="foto" accept="image/jpeg,image/png,image/gif">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <button type="submit">Registrarse</button>
    </form>
    <div id="respuesta"></div>
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
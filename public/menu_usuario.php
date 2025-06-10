<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/menu_usuario.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    $_SESSION['logout_msg'] = "Tu sesión ha expirado o fue cerrada. Por favor, inicia sesión de nuevo.";
    header("Location: login.php");
    exit;
}
$nombre = $_SESSION['nombre'];

// CSRF token para AJAX
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Consulta si el usuario tiene medidores registrados
$usuario_id = $_SESSION['usuario_id'];
$mysqli = new mysqli("localhost", "root", "", "emar_db");
$tiene_medidores = false;
if (!$mysqli->connect_errno) {
    $res = $mysqli->query("SELECT id FROM medidores WHERE usuario_id = $usuario_id LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $tiene_medidores = true;
    }
    $res->close();
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>EMAR | Menú Usuario</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        /* Modal estilos */
        .modal { display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); }
        .modal-content { background: #fff; margin: 10% auto; padding: 30px 20px; border-radius: 10px; max-width: 500px; position: relative; }
        .close { position: absolute; right: 15px; top: 10px; font-size: 24px; cursor: pointer; }
        .form-group { margin-bottom: 18px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        .success-msg { color: green; margin-top: 10px; }
        .error-msg { color: red; margin-top: 10px; }
        .swal2-title { color: #005baa !important; }
    </style>
</head>
<body>
    <?php include 'header_emar.php'; ?>
    <div class="container">
        <div class="header">
            <span>Usuario: <?php echo htmlspecialchars($nombre); ?></span>
            <a href="logout.php" style="color:#d32f2f;">Cerrar sesión</a>
        </div>
        <h1>Bienvenido, <?php echo htmlspecialchars($nombre); ?> (Usuario)</h1>
        <div class="bienvenida">
            Selecciona una opción del menú para gestionar tus servicios.
        </div>
        <nav>
            <a href="index.html">Inicio</a>
            <a href="registrar_medidor.php">Registrar Medidor</a>
            <a href="#" id="ver-medidores">Ver Mis Medidores</a>
            <a href="ordenes_servicio.php">Mis Órdenes de Servicio</a>
            <a href="#" id="crear-orden">Crear Orden de Servicio</a>
            <a href="consultas.php">Consultas</a>
            <a href="cambiar_password.php">Cambiar contraseña</a>
            <a href="logout.php">Cerrar Sesión</a>
        </nav>
        <div class="guia">
            <b>¿Qué puedes hacer?</b><br>
            - Registrar y consultar tus medidores.<br>
            - Registrar y consultar tus órdenes de servicio.<br>
            - Crear nuevas órdenes de servicio.<br>
            - Consultar información relevante.<br>
            - Cambiar tu contraseña.<br>
            - Cerrar sesión al terminar.
        </div>
    </div>

    <!-- Modal para crear orden de servicio -->
    <div id="modal-orden" class="modal">
      <div class="modal-content">
        <span class="close" id="close-orden">&times;</span>
        <h2>Crear Orden de Servicio</h2>
        <form id="form-orden">
          <div class="form-group">
            <label for="tipo">Tipo de Servicio</label>
            <select name="tipo" id="tipo" required>
              <option value="">Seleccione...</option>
              <option value="Reparación de fuga">Reparación de fuga</option>
              <option value="Instalación de medidor">Instalación de medidor</option>
              <option value="Revisión de consumo">Revisión de consumo</option>
              <option value="Mantenimiento de alcantarillado">Mantenimiento de alcantarillado</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
          <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion" rows="3" placeholder="Describe el problema o solicitud" required></textarea>
          </div>
          <!-- Campo oculto CSRF -->
          <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $csrf_token; ?>">
          <button type="submit" class="btn">Enviar Solicitud</button>
          <div id="orden-msg"></div>
        </form>
      </div>
    </div>

    <script>
    // Alerta creativa si no tiene medidores
    document.getElementById('ver-medidores').onclick = function(e) {
        e.preventDefault();
        <?php if (!$tiene_medidores): ?>
        Swal.fire({
            icon: 'info',
            title: '¡Aún no tienes medidores!',
            html: `
                <b>No tienes ningún medidor registrado.</b><br>
                ¿Te gustaría registrar uno ahora?<br><br>
                <img src="img/medidor.png" alt="Medidor" style="width:60px;margin:10px 0;">
                <div style="margin-top:10px;color:#005baa;">
                    <i class="fa fa-tint" style="font-size:22px;"></i>
                    <span style="font-size:15px;">¡Recuerda que tu medidor es la llave para un servicio eficiente y transparente!</span>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar Medidor',
            cancelButtonText: 'Más tarde',
            confirmButtonColor: '#005baa',
            cancelButtonColor: '#aaa',
            background: '#eaf6ff'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'registrar_medidor.php';
            }
        });
        <?php else: ?>
        window.location.href = 'mis_medidores.php';
        <?php endif; ?>
    };

    // Mostrar y ocultar modal de orden
    document.getElementById('crear-orden').onclick = function(e){
        e.preventDefault();
        document.getElementById('modal-orden').style.display = 'block';
    };
    document.getElementById('close-orden').onclick = function(){
        document.getElementById('modal-orden').style.display = 'none';
    };
    window.onclick = function(event) {
        if (event.target == document.getElementById('modal-orden')) {
            document.getElementById('modal-orden').style.display = 'none';
        }
    }

    // Envío AJAX de la orden (el CSRF se envía automáticamente con FormData)
    document.getElementById('form-orden').onsubmit = function(e){
        e.preventDefault();
        var data = new FormData(this);
        fetch('crear_orden.php', {
            method: 'POST',
            body: data
        })
        .then(r => r.json())
        .then(res => {
            if(res.success){
                document.getElementById('orden-msg').innerHTML = '<span class="success-msg">¡Orden creada exitosamente!</span>';
                document.getElementById('form-orden').reset();
            } else {
                document.getElementById('orden-msg').innerHTML = '<span class="error-msg">'+res.error+'</span>';
            }
        })
        .catch(() => {
            document.getElementById('orden-msg').innerHTML = '<span class="error-msg">Error de conexión.</span>';
        });
    };
    </script>
    <!-- Si quieres usar el ícono de gota de agua, incluye FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</body>
</html>
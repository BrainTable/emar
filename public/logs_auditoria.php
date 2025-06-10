<?php
// filepath: /opt/lampp/htdocs/Proyectos/Emar/public/logs_auditoria.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "emar_db");

// Filtros
$filtro_usuario = trim($_GET['usuario'] ?? '');
$filtro_accion = trim($_GET['accion'] ?? '');
$filtro_fecha = trim($_GET['fecha'] ?? '');

// Construir WHERE dinámico
$where = [];
$params = [];
$types = '';

if ($filtro_usuario !== '') {
    $where[] = "usuario LIKE ?";
    $params[] = "%$filtro_usuario%";
    $types .= 's';
}
if ($filtro_accion !== '') {
    $where[] = "accion LIKE ?";
    $params[] = "%$filtro_accion%";
    $types .= 's';
}
if ($filtro_fecha !== '') {
    $where[] = "DATE(fecha) = ?";
    $params[] = $filtro_fecha;
    $types .= 's';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Paginación
$por_pagina = 20;
$pagina = intval($_GET['pagina'] ?? 1);
$inicio = ($pagina - 1) * $por_pagina;

// Contar total de logs filtrados
$sql_total = "SELECT COUNT(*) AS total FROM logs_auditoria $where_sql";
$stmt_total = $mysqli->prepare($sql_total);
if ($params) $stmt_total->bind_param($types, ...$params);
$stmt_total->execute();
$res_total = $stmt_total->get_result();
$total_logs = $res_total->fetch_assoc()['total'];
$total_paginas = ceil($total_logs / $por_pagina);
$stmt_total->close();

// Obtener logs filtrados
$sql = "SELECT usuario, accion, detalle, fecha FROM logs_auditoria $where_sql ORDER BY fecha DESC LIMIT ?, ?";
$stmt = $mysqli->prepare($sql);
if ($params) {
    $params2 = $params;
    $types2 = $types . 'ii';
    $params2[] = $inicio;
    $params2[] = $por_pagina;
    $stmt->bind_param($types2, ...$params2);
} else {
    $stmt->bind_param("ii", $inicio, $por_pagina);
}
$stmt->execute();
$res = $stmt->get_result();

// Exportar a Excel (CSV)
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=logs_auditoria.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Usuario', 'Acción', 'Detalle', 'Fecha']);
    // Repetimos la consulta de logs filtrados
    if ($params) {
        $stmt_export = $mysqli->prepare("SELECT usuario, accion, detalle, fecha FROM logs_auditoria $where_sql ORDER BY fecha DESC");
        $stmt_export->bind_param($types, ...$params);
        $stmt_export->execute();
        $res_export = $stmt_export->get_result();
    } else {
        $res_export = $mysqli->query("SELECT usuario, accion, detalle, fecha FROM logs_auditoria ORDER BY fecha DESC");
    }
    while($log = $res_export->fetch_assoc()) {
        fputcsv($output, [$log['usuario'], $log['accion'], $log['detalle'], $log['fecha']]);
    }
    fclose($output);
    exit;
}

// Exportar a PDF (usando FPDF)
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    require_once(__DIR__ . '/../vendor/autoload.php'); // Si usas Composer
    if (!class_exists('FPDF')) {
        require_once(__DIR__ . '/../fpdf/fpdf.php'); // Si tienes FPDF manualmente
    }
    $pdf = new FPDF();
    $pdf->AddPage('L');
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(45,10,'Usuario',1);
    $pdf->Cell(45,10,'Accion',1);
    $pdf->Cell(120,10,'Detalle',1);
    $pdf->Cell(45,10,'Fecha',1);
    $pdf->Ln();
    $pdf->SetFont('Arial','',10);
    if ($params) {
        $stmt_export = $mysqli->prepare("SELECT usuario, accion, detalle, fecha FROM logs_auditoria $where_sql ORDER BY fecha DESC");
        $stmt_export->bind_param($types, ...$params);
        $stmt_export->execute();
        $res_export = $stmt_export->get_result();
    } else {
        $res_export = $mysqli->query("SELECT usuario, accion, detalle, fecha FROM logs_auditoria ORDER BY fecha DESC");
    }
    while($log = $res_export->fetch_assoc()) {
        $pdf->Cell(45,8,utf8_decode($log['usuario']),1);
        $pdf->Cell(45,8,utf8_decode($log['accion']),1);
        $pdf->Cell(120,8,utf8_decode(substr($log['detalle'],0,60)),1);
        $pdf->Cell(45,8,$log['fecha'],1);
        $pdf->Ln();
    }
    $pdf->Output('D', 'logs_auditoria.pdf');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Logs de Auditoría</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 950px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0002; padding: 30px 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #005baa; color: #fff; }
        tr:nth-child(even) { background: #f4f6f8; }
        .paginacion { margin: 20px 0; text-align: center; }
        .paginacion a { margin: 0 5px; text-decoration: none; color: #005baa; font-weight: bold; }
        .paginacion .actual { color: #c00; }
        .filtros { margin-bottom: 20px; }
        .filtros input { padding: 6px; margin-right: 8px; border-radius: 4px; border: 1px solid #ccc; }
        .filtros button { padding: 6px 14px; border-radius: 4px; border: 1px solid #005baa; background: #005baa; color: #fff; }
        @media (max-width: 700px) {
            .container { padding: 10px; }
            table, th, td { font-size: 13px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Panel de Logs de Auditoría</h2>
        <form class="filtros" method="get">
            <input type="text" name="usuario" placeholder="Usuario" value="<?= htmlspecialchars($filtro_usuario) ?>">
            <input type="text" name="accion" placeholder="Acción" value="<?= htmlspecialchars($filtro_accion) ?>">
            <input type="date" name="fecha" value="<?= htmlspecialchars($filtro_fecha) ?>">
            <button type="submit">Filtrar</button>
            <a href="logs_auditoria.php" style="margin-left:10px;">Limpiar</a>
            <button type="submit" name="export" value="excel" style="margin-left:10px;">Exportar Excel</button>
            <button type="submit" name="export" value="pdf" style="margin-left:5px;">Exportar PDF</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Detalle</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php while($log = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($log['usuario']) ?></td>
                    <td><?= htmlspecialchars($log['accion']) ?></td>
                    <td><?= htmlspecialchars($log['detalle']) ?></td>
                    <td><?= htmlspecialchars($log['fecha']) ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if ($res->num_rows === 0): ?>
                <tr><td colspan="4" style="text-align:center;">No hay registros para los filtros seleccionados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="paginacion">
            <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                <?php
                $params_url = $_GET;
                $params_url['pagina'] = $i;
                $url = '?' . http_build_query($params_url);
                ?>
                <?php if ($i == $pagina): ?>
                    <span class="actual"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $url ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>
<?php
$stmt->close();
$mysqli->close();
?>
<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];

$month = $_GET['month'] ?? (new DateTime())->format('Y-m');
$report_data = get_month_report_data($month);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reporte - LaMolienda</title>
  <link rel="stylesheet" href="/styles.css">
  <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;max-width:900px;margin:auto}</style>
</head>
<body>
  <h1>Reporte mensual - Usuario: <?php echo htmlspecialchars($user['username']); ?> (<?php echo $user['role'] === 'admin' ? 'Admin' : 'Viewer'; ?>)</h1>
  <p><a href="/">Volver</a> | <a href="/logout.php">Cerrar sesión</a></p>
  <form method="get">
    <label>Mes: <input type="month" name="month" value="<?php echo htmlspecialchars($month); ?>"></label>
    <button type="submit">Mostrar</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Venta del día</th>
        <th>Venta total hasta ese día</th>
        <th>Gastos del día</th>
        <th>Saldo a favor hasta ese día</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($report_data as $row): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['date']); ?></td>
        <td><?php echo number_format($row['sale_today'], 2); ?></td>
        <td><?php echo number_format($row['sales_accum'], 2); ?></td>
        <td><?php echo number_format($row['expenses_today'], 2); ?></td>
        <td><?php echo number_format($row['balance_favor'], 2); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <p><a href="/">Volver</a></p>
</body>
</html>

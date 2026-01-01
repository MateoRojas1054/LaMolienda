<?php
require_once __DIR__ . '/db.php';

$month = $_GET['month'] ?? (new DateTime())->format('Y-m');
$year = substr($month, 0, 4);
$mon = substr($month, 5, 2);
$days_in_month = date('t', strtotime($month . '-01'));

$report_data = [];
for ($day = 1; $day <= $days_in_month; $day++) {
    $date = sprintf('%04d-%02d-%02d', $year, $mon, $day);
    $sale_today = get_sale_for_date($date);
    $sales_accum = get_sales_accumulated_month($date);
    $expenses_today = get_expenses_total_by_date($date);
    $balance_favor_month = get_balance_favor_month($date);
    $report_data[] = [
        'date' => $date,
        'sale_today' => $sale_today,
        'sales_accum' => $sales_accum,
        'expenses_today' => $expenses_today,
        'balance_favor' => $balance_favor_month,
    ];
}
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
  <h1>Reporte mensual</h1>
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

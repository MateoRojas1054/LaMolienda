<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];
$is_admin = $user['role'] === 'admin';

// Manejo de formularios: venta del día y gastos (solo admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
  $action = $_POST['action'] ?? '';
  $date = $_POST['date'] ?? null; // opcional
  $targetDate = $date ? substr($date,0,10) : (new DateTime())->format('Y-m-d');

  if ($action === 'set_sale') {
    $amount = floatval(str_replace(',', '.', ($_POST['sale_amount'] ?? '0')));
    if ($amount < 0) {
      $error = 'La venta no puede ser negativa.';
    } else {
      set_sale_for_date($targetDate, $amount);
      header('Location: /'); exit;
    }
  }

  if ($action === 'add_expense') {
    $amount = floatval(str_replace(',', '.', ($_POST['amount'] ?? '0')));
    $created_at = $_POST['date'] ?? null;
    if ($created_at) {
      $created_at .= ' 12:00:00'; // default time for date-only input
    }
    if ($amount <= 0) {
      $error = 'El importe debe ser mayor que 0.';
    } else {
      add_entry('expense', $amount, '', $created_at);
      header('Location: /'); exit;
    }
  }
}

$today = (new DateTime())->format('Y-m-d');
$sale_today = get_sale_for_date($today);
$sales_accum = get_sales_accumulated_month($today);
$expenses_today = get_expenses_total_by_date($today);
$balance_favor_daily = get_balance_favor_daily($today);
$balance_favor_month = get_balance_favor_month($today);
$entries = get_entries_by_date($today);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LaMolienda - Caja</title>
  <link rel="stylesheet" href="/styles.css">
  <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;max-width:900px;margin:auto}</style>
</head>
<body>
  <h1>LaMolienda — Caja del día (<?php echo htmlspecialchars($today); ?>) - Usuario: <?php echo htmlspecialchars($user['username']); ?> (<?php echo $is_admin ? 'Admin' : 'Viewer'; ?>)</h1>
  <p><a href="/logout.php">Cerrar sesión</a></p>

  <div class="cards">
    <div class="card">
      <h3>Venta del día</h3>
      <p class="big"><?php echo number_format($sale_today, 2); ?></p>
    </div>
    <div class="card">
      <h3>Venta total (mes)</h3>
      <p class="big"><?php echo number_format($sales_accum, 2); ?></p>
    </div>
    <div class="card">
      <h3>Saldo a favor (día)</h3>
      <p class="big"><?php echo number_format($balance_favor_daily, 2); ?></p>
    </div>
    <div class="card">
      <h3>Saldo a favor (mes)</h3>
      <p class="big"><?php echo number_format($balance_favor_month, 2); ?></p>
    </div>
  </div>

  <section>
    <h2>Registrar venta del día</h2>
    <?php if ($is_admin): ?>
    <form method="post">
      <input type="hidden" name="action" value="set_sale">
      <label>Fecha (opcional): <input type="date" name="date" value="<?php echo htmlspecialchars($today); ?>"></label>
      <label>Venta total del día: <input name="sale_amount" value="<?php echo htmlspecialchars(number_format($sale_today,2,'.','')); ?>" required></label>
      <button type="submit">Guardar venta</button>
    </form>
    <?php else: ?>
    <p>Solo admins pueden registrar ventas.</p>
    <?php endif; ?>
  </section>

  <section>
    <h2>Añadir gasto</h2>
    <?php if ($is_admin): ?>
    <form method="post">
      <input type="hidden" name="action" value="add_expense">
      <label>Monto: <input name="amount" required></label>
      <label>Fecha (opcional): <input type="date" name="date" value="<?php echo htmlspecialchars($today); ?>"></label>
      <button type="submit">Agregar gasto</button>
    </form>
    <?php else: ?>
    <p>Solo admins pueden añadir gastos.</p>
    <?php endif; ?>
  </section>

  <section>
    <h2>Movimientos del día</h2>
    <?php if (count($entries) === 0): ?>
      <p>No hay movimientos hoy.</p>
    <?php else: ?>
      <table>
        <thead><tr><th>Hora</th><th>Tipo</th><th>Importe</th></tr></thead>
        <tbody>
        <?php foreach ($entries as $e): ?>
          <tr>
            <td><?php echo htmlspecialchars((new DateTime($e['created_at']))->format('H:i:s')); ?></td>
            <td><?php echo $e['type'] === 'expense' ? 'Gasto' : htmlspecialchars($e['type']); ?></td>
            <td><?php echo ($e['type'] === 'expense' ? '-' : '') . number_format($e['amount'], 2); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <p><a href="/report.php">Ver reporte por fecha</a></p>

</body>
</html>

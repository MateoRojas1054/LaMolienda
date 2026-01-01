<?php
function get_db(): PDO {
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $path = $dir . '/cafeteria.sqlite';
    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tabla si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS entries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT NOT NULL, -- 'sale' o 'expense'
        amount NUMERIC NOT NULL,
        description TEXT,
        created_at TEXT NOT NULL
    )");

    // Tabla para guardar venta del día
    $pdo->exec("CREATE TABLE IF NOT EXISTS daily_sales (
        date TEXT PRIMARY KEY,
        sale_amount NUMERIC NOT NULL
    )");

    return $pdo;
}

function add_entry(string $type, float $amount, string $description = '', string $created_at = null) {
    $pdo = get_db();
    $stmt = $pdo->prepare('INSERT INTO entries (type, amount, description, created_at) VALUES (:type, :amount, :description, :created_at)');
    if ($created_at === null) $created_at = (new DateTime())->format('Y-m-d H:i:s');
    $stmt->execute([
        ':type' => $type,
        ':amount' => $amount,
        ':description' => $description,
        ':created_at' => $created_at,
    ]);
}

function set_sale_for_date(string $date, float $amount) {
    $pdo = get_db();
    $stmt = $pdo->prepare("INSERT INTO daily_sales (date, sale_amount) VALUES (:date, :sale_amount)
        ON CONFLICT(date) DO UPDATE SET sale_amount = excluded.sale_amount");
    $stmt->execute([':date' => $date, ':sale_amount' => $amount]);
}

function get_sale_for_date(string $date) {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT sale_amount FROM daily_sales WHERE date = :date');
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (float)$row['sale_amount'] : 0.0;
}

function get_sales_accumulated_month(string $date) {
    $pdo = get_db();
    $start = date('Y-m-01', strtotime($date));
    $end = $date;
    $stmt = $pdo->prepare("SELECT IFNULL(SUM(sale_amount), 0) as total FROM daily_sales WHERE date BETWEEN :start AND :end");
    $stmt->execute([':start' => $start, ':end' => $end]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float)($r['total'] ?? 0);
}

function get_expenses_total_by_date(string $date) {
    $pdo = get_db();
    $start = $date . ' 00:00:00';
    $end = $date . ' 23:59:59';
    $stmt = $pdo->prepare("SELECT IFNULL(SUM(amount), 0) as total FROM entries WHERE type = 'expense' AND created_at BETWEEN :start AND :end");
    $stmt->execute([':start' => $start, ':end' => $end]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float)($r['total'] ?? 0);
}

function get_balance_favor_daily(string $date) {
    $sales = get_sale_for_date($date);
    $expenses = get_expenses_total_by_date($date);
    return $sales - $expenses;
}

function get_balance_favor_month(string $date) {
    $pdo = get_db();
    $start = date('Y-m-01', strtotime($date));
    $end = $date;
    $stmt = $pdo->prepare("SELECT date FROM daily_sales WHERE date BETWEEN :start AND :end ORDER BY date");
    $stmt->execute([':start' => $start, ':end' => $end]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $total = 0.0;
    foreach ($dates as $d) {
        $total += get_balance_favor_daily($d);
    }
    return $total;
}

function get_totals_by_date(string $date) {
    $pdo = get_db();
    $start = $date . ' 00:00:00';
    $end = $date . ' 23:59:59';
    $stmt = $pdo->prepare("SELECT type, IFNULL(SUM(amount), 0) as total FROM entries WHERE created_at BETWEEN :start AND :end GROUP BY type");
    $stmt->execute([':start' => $start, ':end' => $end]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totals = ['sale' => 0.0, 'expense' => 0.0];
    foreach ($rows as $r) {
        $totals[$r['type']] = (float)$r['total'];
    }
    return $totals;
}

function get_entries_by_date(string $date) {
    $pdo = get_db();
    $start = $date . ' 00:00:00';
    $end = $date . ' 23:59:59';
    $stmt = $pdo->prepare('SELECT * FROM entries WHERE created_at BETWEEN :start AND :end ORDER BY created_at DESC');
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

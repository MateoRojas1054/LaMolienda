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

    // Tabla para usuarios
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL -- 'admin' o 'viewer'
    )");

    // Insertar usuarios por defecto si no existen
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
    $stmt->execute(['viewer', password_hash('viewer123', PASSWORD_DEFAULT), 'viewer']);

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

function get_month_report_data(string $month) {
    $pdo = get_db();
    $start = $month . '-01';
    $end = date('Y-m-t', strtotime($start)); // último día del mes

    // Obtener todas las ventas del mes
    $stmt_sales = $pdo->prepare("SELECT date, sale_amount FROM daily_sales WHERE date BETWEEN :start AND :end ORDER BY date");
    $stmt_sales->execute([':start' => $start, ':end' => $end]);
    $sales = $stmt_sales->fetchAll(PDO::FETCH_KEY_PAIR); // date => amount

    // Obtener todos los gastos del mes agrupados por fecha
    $stmt_expenses = $pdo->prepare("SELECT DATE(created_at) as date, SUM(amount) as total FROM entries WHERE type = 'expense' AND DATE(created_at) BETWEEN :start AND :end GROUP BY DATE(created_at)");
    $stmt_expenses->execute([':start' => $start, ':end' => $end]);
    $expenses = $stmt_expenses->fetchAll(PDO::FETCH_KEY_PAIR); // date => total

    $report_data = [];
    $sales_accum = 0.0;
    $balance_accum = 0.0;
    $days_in_month = (int)date('t', strtotime($start));

    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf('%s-%02d', $month, $day);
        $sale_today = $sales[$date] ?? 0.0;
        $sales_accum += $sale_today;
        $expenses_today = $expenses[$date] ?? 0.0;
        $balance_today = $sale_today - $expenses_today;
        $balance_accum += $balance_today;

        $report_data[] = [
            'date' => $date,
            'sale_today' => $sale_today,
            'sales_accum' => $sales_accum,
            'expenses_today' => $expenses_today,
            'balance_favor' => $balance_accum,
        ];
    }
    return $report_data;
}

function authenticate_user(string $username, string $password) {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
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

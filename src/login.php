<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $user = authenticate_user($username, $password);
    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: /');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - LaMolienda</title>
  <link rel="stylesheet" href="/styles.css">
  <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;max-width:400px;margin:auto;text-align:center}</style>
</head>
<body>
  <h1>Login</h1>
  <?php if (isset($error)): ?>
    <p style="color:red"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>
  <form method="post">
    <label>Usuario: <input name="username" required></label><br>
    <label>Contraseña: <input type="password" name="password" required></label><br>
    <button type="submit">Iniciar sesión</button>
  </form>
  <p>Usuarios por defecto:<br>Admin: admin/admin123<br>Viewer: viewer/viewer123</p>
</body>
</html>
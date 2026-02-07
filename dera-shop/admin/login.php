<?php
require '../config.php';
if (is_admin()) {
    header('Location: index.php');
    exit;
}
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role='admin'");
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && hash('sha256',$pass) === $user['password']) {
        $_SESSION['user'] = $user;
        header('Location: index.php');
        exit;
    } else {
        $msg = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Login - Dera Shop</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-wrapper" style="max-width:400px;">
  <h2>Admin Login</h2>
  <?php if ($msg): ?><p style="color:#fca5a5;"><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>
  <form method="post">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
</div>
</body>
</html>

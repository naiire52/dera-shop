<?php
require 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'customer'");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($user && hash('sha256', $password) === $user['password']) {
        $_SESSION['user'] = $user;
        if (isset($_SESSION['login_redirect'])) {
            $redirect = $_SESSION['login_redirect'];
            unset($_SESSION['login_redirect']);
            header("Location: $redirect");
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $message = 'Invalid email or password';
    }
}

$redirect = $_SESSION['login_redirect'] ?? '/index.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - Dera Shop</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="modal active" id="loginModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeLogin()">&times;</span>
            <h2>Login to Continue</h2>
            <?php if ($message): ?>
                <div class="message error"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email address" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Login</button>
                    <a href="register.php" class="btn btn-secondary">Create Account</a>
                </div>
            </form>
            <div class="login-link">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
            </div>
        </div>
    </div>

    <script>
        function closeLogin() {
            window.history.back();
        }
        // Auto-close on outside click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeLogin();
            }
        }
    </script>
</body>
</html>

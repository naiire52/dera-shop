<?php
require 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    
    if (!$stmt->get_result()->num_rows) {
        $hashed_password = hash('sha256', $password);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
        $stmt->bind_param('sss', $name, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $_SESSION['user'] = ['id' => $conn->insert_id, 'name' => $name, 'email' => $email, 'role' => 'customer'];
            header('Location: index.php');
            exit;
        } else {
            $message = 'Registration failed. Try again.';
        }
    } else {
        $message = 'Email already registered.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register - Dera Shop</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="checkout-section">
            <h1>Create Account</h1>
            <?php if ($message): ?>
                <div class="message error"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Full name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email address" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary">Create Account</button>
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="login.php">Already have account? Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

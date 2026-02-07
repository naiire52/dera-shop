<?php 
// Cart setup (config.php already called in main file)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart_count = count($_SESSION['cart']);
$is_logged_in = is_customer_logged_in();
?>

<header class="header">
    <div class="header-inner">
        <a href="index.php" class="logo">DeraShop</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="product.php">Products</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li>
                <a href="cart.php" class="cart-icon">
                    🛒 Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php if ($is_logged_in): ?>
                <li>
                    <a href="logout.php">
                        Logout (<?php echo htmlspecialchars(substr($_SESSION['user']['name'], 0, 8)); ?>)
                    </a>
                </li>
            <?php else: ?>
                <li><a href="login.php" class="btn-login">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

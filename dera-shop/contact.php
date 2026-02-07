<?php
require 'config.php';
$message = '';
if ($_POST) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message_text = trim($_POST['message']);
    
    // Save to database or send email here
    $message = 'Thank you! Your message has been sent. We will contact you within 24 hours.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - DeraShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="hero" style="padding: 4rem 2rem; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); color: #1a1a1a;">
        <div class="hero-content">
            <h1>Get In Touch</h1>
            <p>Have questions? We're here to help</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: start;">
                <!-- Contact Form -->
                <div class="checkout-form">
                    <h2>Contact Us</h2>
                    <?php if($message): ?>
                        <div style="background: #d1fae5; color: #065f46; padding: 1.5rem; border-radius: 16px; margin-bottom: 2rem;">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone">
                        </div>
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="order-summary">
                    <h2>Visit Us</h2>
                    <div style="margin-bottom: 2rem;">
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <div style="width: 50px; height: 50px; background: #2563eb; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; margin-right: 1rem;">📍</div>
                            <div>
                                <h4 style="margin-bottom: 0.5rem;">Nairobi, Kenya</h4>
                                <p style="color: #6b7280; margin: 0;">Westlands, Nairobi County</p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <div style="width: 50px; height: 50px; background: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; margin-right: 1rem;">📱</div>
                            <div>
                                <h4 style="margin-bottom: 0.5rem;">+254 700 123 456</h4>
                                <p style="color: #6b7280; margin: 0;">Available 24/7</p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <div style="width: 50px; height: 50px; background: #f59e0b; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; margin-right: 1rem;">✉️</div>
                            <div>
                                <h4 style="margin-bottom: 0.5rem;">hello@derashop.co.ke</h4>
                                <p style="color: #6b7280; margin: 0;">Response within 2 hours</p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: #f8fafc; padding: 2rem; border-radius: 16px;">
                        <h3 style="margin-bottom: 1rem;">Opening Hours</h3>
                        <p><strong>Monday - Saturday:</strong> 8AM - 8PM</p>
                        <p><strong>Sunday:</strong> 10AM - 6PM</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2026 DeraShop. Premium Shopping Experience | Nairobi, Kenya</p>
        </div>
    </footer>
</body>
</html><?php
require 'config.php';

// Cart setup (for header)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart_count = count($_SESSION['cart']);
$is_logged_in = is_customer_logged_in();

// Handle form submission
$success_message = '';
$error_message = '';

if ($_POST) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    if (!empty($name) && !empty($email) && !empty($message)) {
        // Here you can save to database or send email
        $success_message = "Thank you, $name! Your message has been sent. We'll reply within 24 hours.";
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - DeraShop | Nairobi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- HEADER -->
    <header class="header">
        <div class="header-inner">
            <a href="index.php" class="logo">DeraShop</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>
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

    <!-- CONTACT HERO -->
    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <h1>Get In Touch</h1>
                <p>Have questions? We're here to help. Reach out via form, WhatsApp, or visit us!</p>
            </div>
        </div>
    </section>

    <!-- CONTACT MAIN -->
    <section class="contact-main">
        <div class="container">
            <div class="contact-grid">
                
                <!-- CONTACT FORM -->
                <div class="contact-form-section">
                    <h2 class="contact-title">Send us a message</h2>
                    <p class="contact-subtitle">We'll respond within 24 hours</p>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="contact-form">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" required placeholder="Your full name">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" required placeholder="your@email.com">
                        </div>
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message" rows="5" required placeholder="Tell us how we can help..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-large">Send Message</button>
                    </form>
                </div>

                <!-- CONTACT INFO -->
                <div class="contact-info-section">
                    <h2 class="contact-title">Visit Us</h2>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>
                            <h4>Online store</h4>
                            <p>Baringo County<br>Mogotio, Kenya</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">📱</div>
                        <div>
                            <h4>WhatsApp</h4>
                            <p>+254 115121372<br>Message us directly</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div>
                            <h4>Email</h4>
                            <p>brionaiire@gmail.com</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">🕒</div>
                        <div>
                            <h4>Business Hours</h4>
                            <p>Mon-Sat: 9AM - 7PM<br>Sunday: 11AM - 5PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>DeraShop</h4>
                    <p>Your trusted online store in Nairobi for premium women's fashion.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="products.php">All Products</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="cart.php">Shopping Cart</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <ul>
                        <li>Nairobi, Kenya</li>
                        <li>📱 WhatsApp: 07xx xxx xxx</li>
                        <li>✉️ support@derashop.ke</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 DeraShop. All rights reserved | Premium Women's Fashion</p>
            </div>
        </div>
    </footer>

</body>
</html>


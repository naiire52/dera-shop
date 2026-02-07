<?php 
require 'config.php';  // ← ADD THIS LINE (fixes the error)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - DeraShop | Nairobi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- HEADER -->
    <?php include 'header.php'; ?>

    <!-- ABOUT HERO -->
    <section class="about-hero">
        <div class="container">
            <div class="hero-content">
                <h1>About DeraShop</h1>
                <p>Your trusted premium shopping destination in Nairobi</p>
            </div>
        </div>
    </section>

    <!-- MAIN ABOUT CONTENT -->
    <section class="about-main">
        <div class="container">
            <div class="about-grid">
                <!-- LEFT CONTENT -->
                <div class="about-content">
                    <h2 class="about-title">Premium Quality, Fast Delivery</h2>
                    <p class="about-text">
                        DeraShop brings you the best premium women's fashion with lightning-fast delivery across Nairobi. 
                        We partner with top brands to ensure you get authentic, high-quality dresses, tops & accessories every time.
                    </p>
                    
                    <!-- STATS -->
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">24h</div>
                            <div class="stat-label">Fast Delivery</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">100%</div>
                            <div class="stat-label">Authentic</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">365</div>
                            <div class="stat-label">Returns</div>
                        </div>
                    </div>
                </div>

                <!-- IMAGE PLACEHOLDER -->
                <div class="about-image">
                    <div class="image-placeholder">
                        <span>👗</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY CHOOSE US -->
    <section class="why-choose-section">
        <div class="container">
            <h2 class="section-title">Why Choose DeraShop?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Lightning Fast Delivery</h3>
                    <p>Get your orders within 24 hours anywhere in Nairobi.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">✅</div>
                    <h3>100% Authentic Products</h3>
                    <p>Partnered with official distributors for guaranteed quality.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Best Prices</h3>
                    <p>Competitive pricing with regular discounts and offers.</p>
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

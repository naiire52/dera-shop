<?php
require 'config.php';

// Cart setup
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart_count = count($_SESSION['cart']);

$is_logged_in = is_customer_logged_in();

// Get ALL available products and split them
$all_stmt = $conn->query("SELECT * FROM products WHERE stock > 0 ORDER BY id DESC");
$all_products = [];
while ($product = $all_stmt->fetch_assoc()) {
    $all_products[] = $product;
}

// Split: First 8 = Latest (4x2 grid), Rest = Featured
$latest_products = array_slice($all_products, 0, 8);  // CHANGED: 8 products for 4 columns
$featured_products = array_slice($all_products, 8, 8);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeraShop - Premium Women's Fashion | Nairobi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- MAIN CSS FILE -->
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
                <li><a href="#about">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li>
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if ($is_logged_in): ?>
                    <li><a href="logout.php" style="color: #ef4444;">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <!-- HERO (IMAGE ONLY + SHOP NOW BUTTON) -->
    <section class="hero">
        <a href="#latest" class="btn-shop-now">Shop Now</a>  <!-- Fixed class name -->
    </section>

    <!-- FEATURES -->
    <section class="features-section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-item">
                    <span class="feature-icon">🚚</span>
                    <h4>Lightning Fast Delivery</h4>
                    <p>Same-day delivery across Nairobi. Order before 2PM, get same day!</p>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✅</span>
                    <h4>Premium Quality</h4>
                    <p>Handpicked fashion from trusted brands. Quality guaranteed.</p>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">💳</span>
                    <h4>Secure Checkout</h4>
                    <p>MPesa, cards & cash on delivery. 100% secure transactions.</p>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">⭐</span>
                    <h4>Trusted by 5K+</h4>
                    <p>Thousands of happy customers trust DeraShop daily.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT -->
    <section id="about" class="about-section">
        <div class="about-content">
            <div class="about-text">
                <h2>Why Choose to Shop Dera Online</h2>
                <p>Raiine Online Dera Shop will delivered  your parcel to your door within 24hrs fastest service. Curated collections by expert stylists.</p>
                <div class="about-stats">
                    <div class="stat-item">
                        <div class="stat-number">5K+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24H</div>
                        <div class="stat-label">Delivery</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Secure</div>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <img src="images/rsz_chatgpt_image_feb_3_2026_12_58_36_pm.png" alt="Fashion" onerror="this.src='https://via.placeholder.com/600x500/E31E24/f8fafc?text=DeraShop'">
            </div>
        </div>
    </section>

    <!-- LATEST PRODUCTS - NOW 4 COLUMNS -->
    <section id="latest" class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Latest Arrivals</h2>
                <p class="section-subtitle">Fresh from our stylists - this week's must-haves</p>
            </div>
            <div class="products-grid products-grid-latest">  <!-- Added specific class -->
                <?php if (!empty($latest_products)): ?>
                    <?php foreach ($latest_products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="images/<?php echo htmlspecialchars($product['image'] ?: 'placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/300x250/f8fafc/E31E24?text=No+Image'">
                                <div class="product-badge">New</div>
                            </div>
                            <div class="product-content">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                <div class="product-price">KSh <?php echo number_format($product['price'], 0); ?></div>
                                <div class="product-actions">
                                    <form method="post" action="cart.php" style="display:flex; gap:0.75rem; flex:1;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock']; ?>" class="quantity-input">
                                        <button type="submit" name="add_to_cart" class="btn-cart">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </form>
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn-view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align:center; padding:4rem; color:#6b7280;">
                        <i class="fas fa-box" style="font-size:4rem; color:#d1d5db;"></i>
                        <h3>No products available</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FEATURED PRODUCTS -->
    <section id="featured" class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Collection</h2>
                <p class="section-subtitle">Customer favorites & best sellers</p>
            </div>
            <div class="products-grid">  <!-- Regular 3-column grid -->
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="images/<?php echo htmlspecialchars($product['image'] ?: 'placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/300x250/f59e0b/f8fafc?text=Featured'">
                                <div class="product-badge featured-badge">★ Featured</div>
                            </div>
                            <div class="product-content">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>
                                <div class="product-price">KSh <?php echo number_format($product['price'], 0); ?></div>
                                <div class="product-actions">
                                    <form method="post" action="cart.php" style="display:flex; gap:0.75rem; flex:1;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock']; ?>" class="quantity-input">
                                        <button type="submit" name="add_to_cart" class="btn-cart">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align:center; padding:4rem; color:#6b7280;">
                        <i class="fas fa-crown" style="font-size:4rem; color:#d1d5db;"></i>
                        <h3>No featured products yet</h3>
                        <p>Add more products to see featured collection!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>DeraShop</h4>
                    <p style="color: #d1d5db;">Shop Dera Online by Raiine Store. Fast delivery, secure payments.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="cart.php">Cart</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li>Online shop, Kenya</li>
                        <li>📱 WhatsApp Orders</li>
                        <li>🕒 Same Day Delivery</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2026 DeraShop. All rights reserved | by Raiine</p>
            </div>
        </div>
    </footer>

    <script>
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    </script>
</body>
</html>

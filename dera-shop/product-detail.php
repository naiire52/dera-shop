<?php
require 'config.php';

// Get product ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$product_id = (int)$_GET['id'];

// Fetch single product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND stock > 0");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: index.php');
    exit();
}

// Cart setup
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart_count = count($_SESSION['cart']);
$is_logged_in = is_customer_logged_in();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - DeraShop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f8fafc;
            color: #1f2937;
            line-height: 1.5;
        }
        
        /* HEADER */
        .header {
            background: white;
            padding: 0.75rem 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }
        .nav-links {
            display: flex;
            list-style: none;
            gap: 1rem;
            align-items: center;
        }
        .nav-links a {
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
            transition: color 0.3s;
        }
        .nav-links a:hover { color: #10b981; }
        .cart-icon {
            position: relative;
            padding: 0.5rem;
            border-radius: 50%;
            background: #f3f4f6;
            text-decoration: none;
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-login {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white !important;
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none !important;
        }
        
        /* BREADCRUMBS */
        .breadcrumbs {
            background: white;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .breadcrumbs-nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
        }
        .breadcrumbs-nav a {
            color: #10b981;
            text-decoration: none;
        }
        .breadcrumbs-nav span { color: #6b7280; }
        
        /* PRODUCT DETAIL */
        .product-detail {
            padding: 2.5rem 1rem;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }
        
        .product-gallery {
            position: sticky;
            top: 120px;
        }
        
        /* MAIN IMAGE - FULL VISIBLE NO CROPPING */
        .main-image {
            width: 100%;
            height: 500px;
            border-radius: 16px;
            object-fit: contain; /* FULL IMAGE VISIBLE */
            object-position: center;
            background: #f8fafc;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: block;
        }
        
        .product-info {
            padding: 1rem 0;
        }
        .product-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
            line-height: 1.3;
        }
        .product-price {
            font-size: 2.2rem;
            font-weight: 800;
            color: #10b981;
            margin-bottom: 1rem;
        }
        .product-stock {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #10b981;
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .product-description {
            color: #374151;
            line-height: 1.7;
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .product-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        .quantity-input {
            width: 80px;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            text-align: center;
        }
        .btn-cart {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.9rem 2rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        .btn-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16,185,129,0.4);
        }
        .btn-buy {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 0.9rem 2rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        .btn-buy:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(239,68,68,0.4);
        }
        
        /* RELATED PRODUCTS - FULL IMAGES */
        .related-section {
            grid-column: 1/-1;
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid #e2e8f0;
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }
        .related-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 320px;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
        }
        .related-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }
        
        /* RELATED IMAGES - FULL VISIBLE */
        .related-image {
            height: 180px;
            overflow: hidden;
            background: #f8fafc;
        }
        .related-image img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* FULL IMAGE VISIBLE */
            object-position: center;
            transition: transform 0.3s ease;
        }
        .related-card:hover .related-image img {
            transform: scale(1.05);
        }
        .related-content {
            padding: 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .related-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1f2937;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .related-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #10b981;
            margin-bottom: auto;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* FOOTER */
        .footer {
            background: #1f2937;
            color: white;
            padding: 2rem 1rem;
            margin-top: 4rem;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .product-gallery { position: static; }
            .product-title { font-size: 1.6rem; }
            .product-price { font-size: 1.8rem; }
            .product-actions { flex-direction: column; }
            .btn-cart, .btn-buy { flex: none; }
            .related-grid { grid-template-columns: repeat(2, 1fr); }
            .main-image { height: 400px; }
        }
        @media (max-width: 1200px) {
            .related-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 900px) {
            .related-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
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
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <!-- BREADCRUMBS -->
    <nav class="breadcrumbs">
        <div class="breadcrumbs-nav">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <span>/</span>
            <a href="products.php">Products</a>
            <span>/</span>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>
    </nav>

    <!-- PRODUCT DETAIL -->
    <section class="product-detail">
        <div class="product-gallery">
            <img src="images/<?php echo htmlspecialchars($product['image'] ?: 'placeholder.jpg'); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="main-image"
                 onerror="this.src='https://via.placeholder.com/500x500/f8fafc/10b981?text=No+Image'">
        </div>
        
        <div class="product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-price">KSh <?php echo number_format($product['price'], 0); ?></div>
            
            <div class="product-stock">
                <i class="fas fa-check-circle"></i>
                <?php echo $product['stock']; ?> in stock
            </div>
            
            <div class="product-description">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>
            
            <form method="post" action="cart.php" class="product-actions">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock']; ?>" 
                       class="quantity-input" required>
                <button type="submit" name="add_to_cart" class="btn-cart">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
                <a href="checkout.php?product_id=<?php echo $product['id']; ?>" class="btn-buy">
                    <i class="fas fa-credit-card"></i> Buy Now
                </a>
            </form>
        </div>
    </section>

    <!-- RELATED PRODUCTS -->
    <section class="related-section">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 2rem; font-size: 1.6rem; color: #1f2937; font-weight: 700;">
                You Might Also Like
            </h2>
            <?php
            $related_stmt = $conn->query("SELECT * FROM products WHERE id != $product_id AND stock > 0 ORDER BY id DESC LIMIT 8");
            if ($related_stmt && $related_stmt->num_rows > 0):
            ?>
            <div class="related-grid">
                <?php while ($related = $related_stmt->fetch_assoc()): ?>
                <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="related-card">
                    <div class="related-image">
                        <img src="images/<?php echo htmlspecialchars($related['image'] ?: 'placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($related['name']); ?>"
                             onerror="this.src='https://via.placeholder.com/280x180/f8fafc/10b981?text=Image'">
                    </div>
                    <div class="related-content">
                        <div class="related-title"><?php echo htmlspecialchars(substr($related['name'], 0, 40)); ?></div>
                        <div class="related-price">KSh <?php echo number_format($related['price'], 0); ?></div>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <p style="text-align: center; color: #9ca3af; font-size: 0.9rem;">© 2026 DeraShop. Premium Women's Fashion | Nairobi</p>
        </div>
    </footer>
</body>
</html>

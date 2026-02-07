<?php 
require 'config.php';

// Cart setup
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart_count = count($_SESSION['cart']);
$is_logged_in = is_customer_logged_in();

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$products = [];

// Simple query
if ($search) {
    $query = "SELECT * FROM products WHERE stock > 0 AND name LIKE '%$search%' ORDER BY id DESC";
} else {
    $query = "SELECT * FROM products WHERE stock > 0 ORDER BY id DESC";
}

$result = $conn->query($query);
$product_count = $result ? $result->num_rows : 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - DeraShop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f8fafc;
            color: #1f2937;
            line-height: 1.5;
            overflow-x: hidden;
        }

        /* HEADER */
        .header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-inner {
            max-width: 1300px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }
        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }
        .nav-links a {
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .nav-links a:hover { color: #10b981; }
        .cart-icon {
            position: relative;
            padding: 0.75rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-login {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white !important;
            padding: 0.75rem 1.75rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none !important;
        }

        /* HERO */
        .products-hero {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
        }
        .products-hero h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        .products-hero p {
            font-size: 1.3rem;
            opacity: 0.95;
        }

        /* SEARCH */
        .products-filters {
            padding: 3rem 2rem;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .search-form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .search-input {
            padding: 1.25rem 2rem;
            width: 450px;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-size: 1rem;
            background: #f8fafc;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16,185,129,0.1);
            background: white;
        }
        .btn {
            padding: 1.25rem 2.5rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        /* PRODUCTS */
        .section {
            padding: 4rem 2rem;
        }
        .container {
            max-width: 1300px;
            margin: 0 auto;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }
        .product-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 480px;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255,255,255,0.8);
        }
        .product-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.15);
        }
        .product-image {
            height: 280px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            transition: all 0.4s ease;
        }
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        .product-badge {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .badge-instock { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .badge-lowstock { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
        .badge-soldout { background: linear-gradient(135deg, #6b7280, #4b5563); color: white; }
        .product-content {
            padding: 2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .product-content h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
            line-height: 1.4;
        }
        .product-content p {
            color: #6b7280;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            flex: 1;
        }
        .product-price {
            font-size: 1.6rem;
            font-weight: 800;
            color: #10b981;
            margin-bottom: 1.5rem;
        }
        .product-actions {
            display: flex;
            gap: 1rem;
        }
        .quantity-input {
            flex: 1;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .quantity-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
        }
        .btn-cart {
            flex: 2;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
        }
        .btn-view {
            flex: 1;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            color: #374151;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .btn-view:hover {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-color: #10b981;
        }
        .stock-info {
            font-size: 0.9rem;
            color: #10b981;
            font-weight: 600;
            margin-top: 1rem;
        }

        /* FOOTER */
        .footer {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white;
            padding: 3rem 2rem 1.5rem;
            margin-top: 4rem;
        }
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
            margin-bottom: 2rem;
            max-width: 1300px;
            margin: 0 auto 2rem;
        }
        .footer-section h4 {
            margin-bottom: 1.5rem;
            color: #10b981;
            font-size: 1.3rem;
        }
        .footer-section ul { list-style: none; }
        .footer-section li { margin-bottom: 0.75rem; }
        .footer-section a {
            color: #d1d5db;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .footer-section a:hover { color: #10b981; }
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #374151;
            color: #9ca3af;
            max-width: 1300px;
            margin: 0 auto;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 6rem 2rem;
            color: #6b7280;
        }
        .empty-state i {
            font-size: 6rem;
            color: #d1d5db;
            margin-bottom: 2rem;
        }

        /* RESPONSIVE */
        @media (max-width: 1200px) {
            .products-grid { grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
        }
        @media (max-width: 900px) {
            .products-grid { grid-template-columns: repeat(2, 1fr); }
            .search-form { flex-direction: column; align-items: stretch; }
            .search-input { width: 100%; max-width: 500px; }
            .products-hero h1 { font-size: 2.5rem; }
        }
        @media (max-width: 768px) {
            .products-grid { grid-template-columns: 1fr; }
            .header, .products-filters, .section, .footer { padding-left: 1rem; padding-right: 1rem; }
            .product-actions { flex-direction: column; }
            .product-card { height: 500px; }
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
                    <li><a href="logout.php" style="color: #ef4444; font-weight: 500;">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <!-- HERO -->
    <section class="products-hero">
        <div class="container">
            <h1>Our Collection</h1>
            <p><?php echo $product_count; ?> Premium Products Available</p>
        </div>
    </section>

    <!-- SEARCH -->
    <section class="products-filters">
        <div class="container">
            <form method="GET" class="search-form">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       class="search-input" placeholder="🔍 Search dresses, tops, accessories...">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if ($search): ?>
                    <a href="products.php" class="btn btn-danger">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </section>

    <!-- PRODUCTS -->
    <section class="section">
        <div class="container">
            <?php if ($product_count > 0): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php 
                                $image_filename = $product['image'] ?: 'placeholder.jpg';
                                $image_path = "images/" . $image_filename;
                                ?>
                                
                                <?php if (file_exists($image_path)): ?>
                                    <img src="<?php echo $image_path; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div style="width:100%; height:100%; background: linear-gradient(135deg, #f8fafc, #e2e8f0); display:flex; align-items:center; justify-content:center; font-size:4rem; color:#94a3b8;">
                                        👗
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-badge <?php echo $product['stock'] > 5 ? 'badge-instock' : ($product['stock'] > 0 ? 'badge-lowstock' : 'badge-soldout'); ?>">
                                    <?php echo $product['stock'] > 5 ? 'In Stock' : ($product['stock'] > 0 ? 'Low Stock' : 'Sold Out'); ?>
                                </div>
                            </div>
                            
                            <div class="product-content">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <?php if (!empty($product['description'])): ?>
                                    <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                <?php endif; ?>
                                <div class="product-price">KSh <?php echo number_format($product['price']); ?></div>
                                
                                <div class="product-actions">
                                    <?php if ($product['stock'] > 0): ?>
                                        <form method="post" action="cart.php" style="display:flex; gap:1rem; flex:1;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                                            <button type="submit" name="add_to_cart" class="btn btn-cart">
                                                <i class="fas fa-shopping-cart"></i> Add to Cart
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                                
                                <div class="stock-info">
                                    <i class="fas fa-check-circle"></i> <?php echo $product['stock']; ?> in stock
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h2>No products found</h2>
                    <p><?php echo $search ? 'No products match "' . htmlspecialchars($search) . '"' : 'No products available yet'; ?></p>
                    <a href="products.php" class="btn btn-primary" style="margin-top: 2rem;">Browse All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>DeraShop</h4>
                <p>Nairobi's premium destination for women's fashion. Fast delivery, secure payments, exceptional style.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">All Products</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="cart.php">Shopping Cart</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact Info</h4>
                <ul>
                    <li>Nairobi, Kenya</li>
                    <li><i class="fab fa-whatsapp"></i> WhatsApp Orders</li>
                    <li><i class="fas fa-shipping-fast"></i> Same Day Delivery</li>
                    <li><i class="fas fa-credit-card"></i> MPesa & Cards</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 DeraShop. Premium Women's Fashion | All rights reserved | Made with ❤️ in Nairobi</p>
        </div>
    </footer>
</body>
</html>

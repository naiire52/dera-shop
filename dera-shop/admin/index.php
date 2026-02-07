<?php
require '../config.php';
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: add-product.php');
    exit;
}

// LIVE STATS
$products_count = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'] ?? 0;
$orders_count   = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'] ?? 0;
$pending_count  = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='pending'")->fetch_assoc()['c'] ?? 0;
$total_revenue  = $conn->query("SELECT SUM(total) AS r FROM orders")->fetch_assoc()['r'] ?? 0;

// RECENT ORDERS
$recent_orders = $conn->query("SELECT id, customer_name, total, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// LOW STOCK PRODUCTS
$low_stock = $conn->query("SELECT id, name, stock FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DeraShop Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #1f2937;
            font-size: 14px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
            display: flex;
            gap: 2rem;
            min-height: 100vh;
        } 
        
        /* SIDEBAR */
        .sidebar {
            width: 250px;
            background: #ffffff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: sticky;
            top: 1.5rem;
            height: fit-content;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .sidebar-logo {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .sidebar-title { font-size: 1.2rem; font-weight: 700; }
        .sidebar-subtitle { font-size: 0.85rem; color: #6b7280; }
        
        .nav-menu { list-style: none; }
        .nav-item { margin-bottom: 8px; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #6b7280;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            transform: translateX(4px);
        }
        
        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        /* HEADER */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f1f5f9;
        }
        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .page-subtitle { color: #6b7280; font-size: 0.95rem; margin-top: 4px; }
        
        /* STATS CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        .stat-products::before { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .stat-orders::before { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-pending::before { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-revenue::before { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        .stat-primary { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #3b82f6; }
        .stat-success { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #10b981; }
        .stat-warning { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #f59e0b; }
        .stat-purple { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #8b5cf6; }
        
        .stat-number { font-size: 2.25rem; font-weight: 800; color: #1f2937; margin-bottom: 0.5rem; }
        .stat-label { color: #6b7280; font-weight: 600; font-size: 0.95rem; }
        
        /* RECENT ORDERS */
        .section {
            background: #fafbfc;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .section-title { font-size: 1.25rem; font-weight: 700; color: #1f2937; }
        
        .orders-table, .lowstock-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        .orders-table th, .lowstock-table th {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }
        .orders-table td, .lowstock-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
        }
        .orders-table tr:hover, .lowstock-table tr:hover { background: #f9fafb; }
        
        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.paid { background: #d1fae5; color: #065f46; }
        
        .btn { 
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(16,185,129,0.4); }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .container { flex-direction: column; padding: 1rem; }
            .sidebar { width: 100%; position: static; margin-bottom: 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-logo">👗</div>
                <div>
                    <div class="sidebar-title">DeraShop Admin</div>
                    <div class="sidebar-subtitle">Clothing Store</div>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="add-product.php" class="nav-link">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link">
                        <i class="fas fa-list"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a href="orders.php" class="nav-link">
                        <i class="fas fa-shopping-bag"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?logout=1" class="nav-link" onclick="return confirm('Logout?')">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Dashboard Overview</h1>
                    <div class="page-subtitle">Welcome back! Here's what's happening in your store today.</div>
                </div>
                <a href="add-product.php" class="btn">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
            
            <!-- STATS -->
            <div class="stats-grid">
                <div class="stat-card stat-products">
                    <div class="stat-icon stat-primary">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div class="stat-number"><?php echo $products_count; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                
                <div class="stat-card stat-orders">
                    <div class="stat-icon stat-success">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-number"><?php echo $orders_count; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                
                <div class="stat-card stat-pending">
                    <div class="stat-icon stat-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?php echo $pending_count; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                
                <div class="stat-card stat-revenue">
                    <div class="stat-icon stat-purple">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-number">KSh <?php echo number_format($total_revenue, 0); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- RECENT ORDERS -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Orders</h2>
                        <a href="orders.php" class="btn" style="font-size: 0.8rem; padding: 6px 12px;">View All</a>
                    </div>
                    <?php if (!empty($recent_orders)): ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recent_orders, 0, 5) as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td>KSh <?php echo number_format($order['total'], 0); ?></td>
                                        <td><span class="status <?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                        <td><?php echo date('M j', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #6b7280;">
                            <i class="fas fa-shopping-cart" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            <p>No orders yet</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- LOW STOCK -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title">Low Stock Alerts</h2>
                        <a href="products.php" class="btn" style="font-size: 0.8rem; padding: 6px 12px;">View All</a>
                    </div>
                    <?php if (!empty($low_stock)): ?>
                        <table class="lowstock-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td style="color: #ef4444; font-weight: 700;"><?php echo $product['stock']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #10b981;">
                            <i class="fas fa-check-circle" style="font-size: 3rem; opacity: 0.7; display: block; margin-bottom: 1rem;"></i>
                            <p>All products in stock!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

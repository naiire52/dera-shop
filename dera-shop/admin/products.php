<?php
require '../config.php';
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: add-product.php');
    exit;
}

$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products_count = $result ? $result->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - DeraShop Admin</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
            display: flex;
            gap: 2rem;
            min-height: 100vh;
        }
        
        /* SIDEBAR - LEFT ALIGNED */
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
        
        .nav-menu {
            list-style: none;
        }
        .nav-item {
            margin-bottom: 8px;
        }
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
        .nav-link i { font-size: 1rem; width: 20px; }
        
        /* MAIN CONTENT - FULL WIDTH CENTER */
        .main-content {
            flex: 1;
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        /* TOP HEADER */
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
        .page-subtitle {
            color: #6b7280;
            font-size: 0.95rem;
            margin-top: 4px;
        }
        
        .btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 12px 24px;
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
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16,185,129,0.4);
        }
        
        /* PRODUCTS TABLE */
        .table-container {
            background: #fafbfc;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        
        .products-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        
        .products-table th {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .products-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        .products-table tr:hover {
            background: #f9fafb;
        }
        
        /* PRODUCT PREVIEW */
        .product-preview {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .product-image {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }
        .no-image {
            width: 48px;
            height: 48px;
            background: #f3f4f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .product-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #1f2937;
            margin-bottom: 4px;
        }
        .product-desc {
            font-size: 0.8rem;
            color: #6b7280;
        }
        
        .price {
            font-weight: 700;
            color: #10b981;
            font-size: 1rem;
        }
        .stock {
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        .stock-low { background: #fef3c7; color: #92400e; }
        .stock-critical { background: #fee2e2; color: #dc2626; }
        .stock-good { background: #d1fae5; color: #065f46; }
        
        .date {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        /* ACTION BUTTONS */
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn-action {
            padding: 8px 12px;
            font-size: 0.8rem;
            border-radius: 6px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
        }
        .btn-view { background: #3b82f6; color: white; }
        .btn-edit { background: #f59e0b; color: white; }
        .btn-delete { background: #ef4444; color: white; }
        .btn-action:hover { transform: translateY(-1px); opacity: 0.9; }
        
        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }
        .empty-state i {
            font-size: 4rem;
            opacity: 0.3;
            display: block;
            margin-bottom: 1rem;
        }
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 1rem;
            }
            .sidebar {
                width: 100%;
                position: static;
                margin-bottom: 1rem;
            }
            .main-content {
                padding: 1.5rem;
            }
            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
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
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="add-product.php" class="nav-link">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link active">
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
                    <h1 class="page-title">Products Management</h1>
                    <div class="page-subtitle"><?php echo $products_count; ?> items in catalog</div>
                </div>
                <a href="add-product.php" class="btn">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
            
            <div class="table-container">
                <?php if ($result && $result->num_rows > 0): ?>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $result->data_seek(0); while ($p = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($p['id'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td>
                                        <div class="product-preview">
                                            <?php if ($p['image'] && file_exists('../images/' . $p['image'])): ?>
                                                <img src="../images/<?php echo htmlspecialchars($p['image']); ?>" 
                                                     class="product-image" alt="<?php echo htmlspecialchars($p['name']); ?>">
                                            <?php else: ?>
                                                <div class="no-image">No img</div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="product-name"><?php echo htmlspecialchars($p['name']); ?></div>
                                                <?php if ($p['description']): ?>
                                                    <div class="product-desc"><?php echo substr(htmlspecialchars($p['description']), 0, 40); ?>...</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="price">KSh <?php echo number_format($p['price'], 0); ?></span></td>
                                    <td>
                                        <span class="stock <?php 
                                            if($p['stock'] <= 2) echo 'stock-critical';
                                            elseif($p['stock'] <= 10) echo 'stock-low';
                                            else echo 'stock-good';
                                        ?>">
                                            <?php echo $p['stock']; ?>
                                        </span>
                                    </td>
                                    <td><div class="date"><?php echo date('M j', strtotime($p['created_at'])); ?></div></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="../product-detail.php?id=<?php echo $p['id']; ?>" 
                                               class="btn-action btn-view" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_product.php?id=<?php echo $p['id']; ?>" 
                                               class="btn-action btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_product.php?id=<?php echo $p['id']; ?>" 
                                               class="btn-action btn-delete"
                                               onclick="return confirm('Delete <?php echo addslashes($p['name']); ?>?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-tshirt"></i>
                        <h3>No products yet</h3>
                        <p>Start by adding your first clothing item to the store</p>
                        <a href="add-product.php" class="btn">+ Add First Product</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

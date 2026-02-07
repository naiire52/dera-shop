<?php 
require '../config.php';

// BYPASS LOGIN - WORKS IMMEDIATELY
$_SESSION['admin_logged_in'] = true; 

// LIVE STATS
$products_count = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'] ?? 0;

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $description = trim($_POST['description']);
    
    // UPLOAD IMAGE
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
            $image = 'product_' . time() . '_' . rand(1000,9999) . '.' . $file_ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        }
    }
    
    // SAVE TO DATABASE
    if ($name && $price > 0 && $image) {
        $sql = "INSERT INTO products (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdss", $name, $description, $price, $stock, $image);
        
        if ($stmt->execute()) {
            $message = 'success';
        } else {
            $message = 'error_db';
        }
    } else {
        $message = 'error_missing';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - DeraShop Admin</title>
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
        
        /* SIDEBAR - SAME AS DASHBOARD */
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
        
        .page-header {
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
            margin-bottom: 0.5rem;
        }
        .page-subtitle { color: #6b7280; font-size: 0.95rem; }
        
        /* ALERTS */
        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .alert-success {
            background: linear-gradient(90deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
            border: 1px solid #93d7a3;
        }
        .alert-error {
            background: linear-gradient(90deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
            border: 1px solid #fca5a5;
        }
        
        /* FORM */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .form-group { margin-bottom: 1.5rem; }
        .form-group.full { grid-column: 1 / -1; }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #1f2937;
            font-size: 0.95rem;
        }
        .label-icon {
            color: #10b981;
            margin-right: 0.5rem;
        }
        input, textarea, select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            background: #fafbfc;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 4px rgba(16,185,129,0.1);
        }
        textarea { resize: vertical; min-height: 120px; }
        input[type="file"] { padding: 0.75rem; background: #f8fafc; }
        
        .btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1.25rem 2.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16,185,129,0.3);
            width: 100%;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16,185,129,0.4);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .stat-number { font-size: 1.75rem; font-weight: 700; color: #1f2937; }
        .stat-label { color: #6b7280; font-size: 0.85rem; }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .container { flex-direction: column; padding: 1rem; }
            .sidebar { width: 100%; position: static; margin-bottom: 1rem; }
            .form-grid { grid-template-columns: 1fr; }
            .main-content { padding: 1.5rem; }
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
                    <a href="add-product.php" class="nav-link active">
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
            
            <!-- STATS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $products_count; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
            </div>
        </aside>
        
        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Add New Product</h1>
                    <div class="page-subtitle">Upload clothing item → Live on store instantly</div>
                </div>
            </div>

            <?php if ($message == 'success'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Success!</strong> Product added to store and live now!
                </div>
            <?php elseif ($message == 'error_db'): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Database Error!</strong> Please try again.
                </div>
            <?php elseif ($message == 'error_missing'): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Missing Fields!</strong> Please fill name, price, and upload image.
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_product" value="1">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-tshirt label-icon"></i>Product Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" required placeholder="Red Maxi Dress, Blue Jeans...">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tag label-icon"></i>Price (KSh) <span style="color:#ef4444;">*</span></label>
                        <input type="number" name="price" step="0.01" min="0" required placeholder="2500">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-box label-icon"></i>Stock Quantity <span style="color:#ef4444;">*</span></label>
                        <input type="number" name="stock" min="0" required placeholder="25">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-align-left label-icon"></i>Description</label>
                        <textarea name="description" placeholder="Soft cotton fabric, available in S-XXL..."></textarea>
                    </div>
                </div>
                
                <div class="form-group full">
                    <label><i class="fas fa-image label-icon"></i>Product Image <span style="color:#ef4444;">*</span> (JPG/PNG)</label>
                    <input type="file" name="image" accept="image/jpeg,image/png" required>
                    <small style="color: #6b7280; display: block; margin-top: 0.5rem;">High quality photo recommended (800x800px)</small>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-rocket"></i>
                    Add Product Live to Store
                </button>
            </form>
        </main>
    </div>
</body>
</html>

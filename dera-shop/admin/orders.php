<?php
require '../config.php';
// Simple admin check (admin/admin123)
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: add-product.php');
    exit;
}

$result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
$total_orders = $result ? $result->num_rows : 0;
$total_revenue = 0;
$recent_orders = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $total_revenue += $row['total'];
        $recent_orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Dashboard - DeraShop Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --primary-dark: linear-gradient(135deg, #059669 0%, #047857 100%);
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --purple: #8b5cf6;
            --shadow: 0 10px 40px rgba(0,0,0,0.08);
            --shadow-lg: 0 20px 60px rgba(0,0,0,0.12);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg-primary); 
            color: var(--text-primary);
            line-height: 1.6;
        }
        .dashboard { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        
        /* HEADER */
        .admin-header {
            background: var(--bg-secondary); 
            border-radius: 20px; 
            padding: 2rem; 
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
        .header-left h1 { 
            font-size: 2.25rem; 
            font-weight: 800; 
            background: linear-gradient(135deg, var(--text-primary), #374151);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            margin-bottom: 0.25rem;
        }
        .header-left p { color: var(--text-secondary); font-size: 1.1rem; }
        .logout-btn { 
            background: #ef4444; 
            color: white; 
            padding: 1rem 2rem; 
            border-radius: 12px; 
            text-decoration: none; 
            font-weight: 600; 
            display: inline-flex; 
            align-items: center; 
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .logout-btn:hover { background: #dc2626; transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        
        /* STATS */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 1.5rem; 
            margin-bottom: 2.5rem; 
        }
        .stat-card { 
            background: var(--bg-secondary); 
            padding: 2rem; 
            border-radius: 20px; 
            border: 1px solid var(--border); 
            transition: all 0.3s ease;
            position: relative; 
            overflow: hidden;
        }
        .stat-card::before {
            content: ''; 
            position: absolute; 
            top: 0; 
            left: 0; 
            right: 0; 
            height: 4px;
        }
        .stat-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg); }
        .stat-revenue::before { background: var(--primary); }
        .stat-orders::before { background: var(--purple); }
        .stat-pending::before { background: var(--warning); }
        .stat-icon { 
            width: 64px; height: 64px; 
            border-radius: 16px; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 1.75rem; 
            margin-bottom: 1rem;
        }
        .stat-primary { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; }
        .stat-success { background: var(--primary); color: white; }
        .stat-warning { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
        .stat-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
        .stat-number { font-size: 2.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem; }
        .stat-label { color: var(--text-secondary); font-weight: 600; font-size: 1rem; }
        .stat-change { font-size: 0.9rem; margin-top: 0.25rem; }
        .change-up { color: var(--success); }
        .change-down { color: var(--error); }
        
        /* ORDERS TABLE */
        .table-container { 
            background: var(--bg-secondary); 
            border-radius: 24px; 
            padding: 2.5rem; 
            box-shadow: var(--shadow); 
            overflow: hidden;
        }
        .table-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 2rem; 
            flex-wrap: wrap; 
            gap: 1rem;
        }
        .table-title { font-size: 1.75rem; font-weight: 800; color: var(--text-primary); }
        .table-actions { display: flex; gap: 1rem; }
        .btn { 
            background: var(--primary); 
            color: white; 
            padding: 0.875rem 1.5rem; 
            border: none; 
            border-radius: 12px; 
            font-size: 0.95rem; 
            font-weight: 600; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 0.5rem; 
            transition: all 0.3s ease;
            font-family: inherit;
        }
        .btn:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: var(--shadow); }
        .btn-outline { background: transparent; color: var(--text-primary); border: 2px solid var(--border); }
        .btn-outline:hover { background: var(--bg-primary); }
        
        .orders-table { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0; 
            font-size: 0.95rem;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .orders-table th { 
            background: #f8fafc; 
            padding: 1.25rem 1.5rem; 
            text-align: left; 
            font-weight: 600; 
            color: var(--text-primary); 
            font-size: 0.85rem; 
            text-transform: uppercase; 
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--border);
        }
        .orders-table td { 
            padding: 1.25rem 1.5rem; 
            border-bottom: 1px solid #f1f5f9;
        }
        .orders-table tr:hover { background: #f8fafc; }
        .orders-table tbody tr:last-child td { border-bottom: none; }
        
        .status { 
            padding: 0.5rem 1rem; 
            border-radius: 20px; 
            font-size: 0.8rem; 
            font-weight: 600; 
            text-transform: uppercase;
        }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.paid { background: #d1fae5; color: #065f46; }
        .status.shipped { background: #dbeafe; color: #1e40af; }
        .status.delivered { background: #f0fdf4; color: #166534; }
        .status.cancelled { background: #fee2e2; color: #dc2626; }
        
        .customer-info { display: flex; align-items: center; gap: 0.75rem; }
        .customer-avatar { 
            width: 40px; height: 40px; 
            border-radius: 50%; 
            background: linear-gradient(135deg, #10b981, #059669); 
            display: flex; align-items: center; justify-content: center; 
            color: white; font-weight: 600; font-size: 0.9rem;
        }
        .customer-name { font-weight: 600; }
        .customer-meta { font-size: 0.85rem; color: var(--text-secondary); }
        
        .price { font-weight: 700; color: var(--success); font-size: 1.1rem; }
        .date { color: var(--text-secondary); font-size: 0.9rem; }
        
        .action-btn { 
            padding: 0.5rem 1rem; 
            border-radius: 8px; 
            text-decoration: none; 
            font-size: 0.85rem; 
            font-weight: 500; 
            margin-right: 0.5rem; 
            margin-bottom: 0.25rem; 
            display: inline-block;
        }
        .btn-view { background: var(--purple); color: white; }
        .btn-update { background: #f59e0b; color: white; }
        .btn-view:hover, .btn-update:hover { transform: translateY(-1px); opacity: 0.9; }
        
        .no-orders { 
            text-align: center; 
            padding: 4rem 2rem; 
            color: var(--text-secondary);
        }
        .no-orders i { font-size: 4rem; opacity: 0.3; margin-bottom: 1rem; display: block; }
        
        @media (max-width: 768px) {
            .dashboard { padding: 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .admin-header { flex-direction: column; gap: 1rem; text-align: center; }
            .table-header { flex-direction: column; align-items: stretch; }
            .orders-table { font-size: 0.85rem; }
            .orders-table th, .orders-table td { padding: 1rem 0.75rem; }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- HEADER -->
        <div class="admin-header">
            <div class="header-left">
                <h1><i class="fas fa-shopping-bag" style="color: #10b981; margin-right: 1rem;"></i>Orders Dashboard</h1>
                <p>Manage customer orders and track deliveries</p>
            </div>
            <a href="?logout=1" class="logout-btn" onclick="return confirm('Logout from admin?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        
        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card stat-revenue">
                <div class="stat-icon stat-success">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-number">KSh <?php echo number_format($total_revenue, 0); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card stat-orders">
                <div class="stat-icon stat-purple">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card stat-pending">
                <div class="stat-icon stat-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $total_orders * 0.3; // Demo ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
        </div>
        
        <!-- ORDERS TABLE -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">
                    <i class="fas fa-list" style="margin-right: 0.5rem; color: #10b981;"></i>
                    Recent Orders
                </h2>
                <div class="table-actions">
                    <button class="btn btn-outline">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                    <a href="add-product.php" class="btn">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                </div>
            </div>
            
            <?php if (empty($recent_orders)): ?>
                <div class="no-orders">
                    <i class="fas fa-inbox"></i>
                    <h3>No orders yet</h3>
                    <p>Your first customer order will appear here.</p>
                    <a href="../products.php" class="btn" style="margin-top: 1rem;">Visit Store</a>
                </div>
            <?php else: ?>
                <div class="orders-table-container">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Order ID</th>
                                <th><i class="fas fa-user"></i> Customer</th>
                                <th><i class="fas fa-tag"></i> Total</th>
                                <th><i class="fas fa-circle-notch"></i> Status</th>
                                <th><i class="fas fa-calendar"></i> Date</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td>
                                        <div class="customer-info">
                                            <div class="customer-avatar">
                                                <?php echo strtoupper(substr($order['customer_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                                <div class="customer-meta"><?php echo htmlspecialchars($order['customer_phone'] ?: $order['customer_email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="price">KSh <?php echo number_format($order['total'], 0); ?></span></td>
                                    <td>
                                        <span class="status <?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date">
                                            <?php 
                                            $date = new DateTime($order['created_at']);
                                            echo $date->format('M j, Y'); 
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="invoice.php?id=<?php echo $order['id']; ?>" class="action-btn btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="update_order.php?id=<?php echo $order['id']; ?>" class="action-btn btn-update">
                                            <i class="fas fa-edit"></i> Update
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

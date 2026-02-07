<?php
require '../config.php';
if (!isset($_SESSION['admin_logged_in'])) { 
    header('Location: add-product.php'); 
    exit; 
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) die('Order not found');

$items = [];
$stmt2 = $conn->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE order_id = ?");
$stmt2->bind_param('i', $id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($r = $res2->fetch_assoc()) $items[] = $r;

// Calculate totals
$total_items = count($items);
$total_quantity = array_sum(array_column($items, 'quantity'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order['id']; ?> - DeraShop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f8fafc;
            color: #1f2937;
            padding: 2rem;
            line-height: 1.6;
        }
        
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 2.5rem;
            text-align: center;
        }
        .invoice-logo {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .invoice-title {
            font-size: 1.5rem;
            font-weight: 700;
            opacity: 0.95;
        }
        .invoice-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 1rem 0;
            letter-spacing: 2px;
        }
        .invoice-date {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .invoice-body {
            padding: 3rem;
        }
        
        .invoice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 2.5rem;
        }
        
        .customer-info, .store-info {
            background: #f8fafc;
            padding: 2rem;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
        }
        
        .info-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-item {
            display: flex;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        .info-label {
            font-weight: 600;
            color: #374151;
            min-width: 80px;
        }
        .info-value {
            color: #6b7280;
            flex: 1;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        
        .items-section {
            background: #fafbfc;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2.5rem;
            border: 1px solid #e2e8f0;
        }
        
        .items-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .items-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }
        .items-summary {
            font-size: 0.95rem;
            color: #6b7280;
        }
        
        .items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        .items-table th {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 1.25rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }
        .items-table td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .items-table tr:hover td {
            background: #f9fafb;
        }
        .product-name {
            font-weight: 600;
            color: #1f2937;
        }
        .price { font-weight: 600; color: #10b981; }
        
        .totals-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            border: 2px solid #10b981;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            font-size: 1.1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .total-row:last-child {
            border-bottom: none;
            font-size: 1.5rem;
            font-weight: 800;
            color: #10b981;
            padding-top: 1.5rem;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            padding: 2rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        .btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16,185,129,0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280, #4b5563);
        }
        
        @media print {
            body { padding: 0; background: white; }
            .invoice-container { box-shadow: none; }
            .actions { display: none; }
        }
        
        @media (max-width: 768px) {
            body { padding: 1rem; }
            .invoice-grid { grid-template-columns: 1fr; gap: 1.5rem; }
            .invoice-body { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- HEADER -->
        <div class="invoice-header">
            <div class="invoice-logo">👗 DeraShop</div>
            <div class="invoice-title">Professional Clothing Store</div>
            <div class="invoice-number">INVOICE #<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></div>
            <div class="invoice-date">
                <i class="fas fa-calendar"></i> 
                <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
            </div>
        </div>
        
        <!-- BODY -->
        <div class="invoice-body">
            <div class="invoice-grid">
                <!-- CUSTOMER INFO -->
                <div class="customer-info">
                    <div class="info-title">
                        <i class="fas fa-user"></i> Bill To
                    </div>
                    <div class="info-item">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Address:</span>
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></span>
                    </div>
                </div>
                
                <!-- STORE INFO & STATUS -->
                <div class="store-info">
                    <div class="info-title">
                        <i class="fas fa-store"></i> Store Info
                    </div>
                    <div class="info-item">
                        <span class="info-label">Store:</span>
                        <span class="info-value">DeraShop Clothing</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value">hello@derashop.com</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone:</span>
                        <span class="info-value">+254 700 123 456</span>
                    </div>
                    <div style="margin-top: 2rem;">
                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                            <i class="fas fa-circle"></i>
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- ITEMS -->
            <div class="items-section">
                <div class="items-header">
                    <div class="items-title">
                        <i class="fas fa-boxes"></i> Order Items
                    </div>
                    <div class="items-summary">
                        <?php echo $total_items; ?> items | <?php echo $total_quantity; ?> total quantity
                    </div>
                </div>
                
                <?php if (!empty($items)): ?>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td class="product-name"><?php echo htmlspecialchars($it['name']); ?></td>
                                    <td><strong><?php echo (int)$it['quantity']; ?></strong></td>
                                    <td class="price">KSh <?php echo number_format($it['price'], 0); ?></td>
                                    <td class="price">KSh <?php echo number_format($it['price'] * $it['quantity'], 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: #6b7280; padding: 2rem;">
                        <i class="fas fa-shopping-cart" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                        No items in this order
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- TOTALS -->
            <div class="totals-section">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>KSh <?php echo number_format($order['total'], 0); ?></span>
                </div>
                <div class="total-row">
                    <span>Total Amount</span>
                    <span>KSh <?php echo number_format($order['total'], 0); ?></span>
                </div>
            </div>
        </div>
        
        <!-- ACTIONS -->
        <div class="actions">
            <a href="javascript:window.print()" class="btn">
                <i class="fas fa-print"></i> Print Invoice
            </a>
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>
</body>
</html>

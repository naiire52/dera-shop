<?php 
require 'config.php';

// ========================================
// PROCESS CART FOR DISPLAY
// ========================================
$items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', array_map('intval', $product_ids));
    
    $query = "SELECT id, name, price FROM products WHERE id IN ($ids_string)";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $product_id = $row['id'];
        $quantity = $_SESSION['cart'][$product_id]['quantity'];
        
        $row['quantity'] = $quantity;
        $row['subtotal'] = $quantity * $row['price'];
        $total += $row['subtotal'];
        $items[] = $row;
    }
}

if (empty($items)) {
    header('Location: cart.php?empty=1');
    exit;
}

// ========================================
// HANDLE CASH ON DELIVERY FORM
// ========================================
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['mpesa_payment'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($address)) $errors[] = "Delivery address is required";
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("ssssd", $name, $email, $phone, $address, $total);
        
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $item_stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }
            
            $_SESSION['cart'] = [];
            header("Location: invoice.php?id=$order_id");
            exit;
        } else {
            $errors[] = "Order creation failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - DeraShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div style="text-align: center; margin: 2rem 0;">
            <h1 style="color: #2d3748; margin-bottom: 0.5rem;">🛒 Checkout</h1>
            <p style="color: #718096;">Order Total: <strong>KSh <?php echo number_format($total, 0); ?></strong></p>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background: #fed7d7; color: #c53030; padding: 1rem; border-radius: 12px; margin-bottom: 2rem;">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 3rem; align-items: start;">
            <!-- CHECKOUT FORM -->
            <div style="background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
                <h3 style="margin-bottom: 1.5rem; color: #2d3748;">Delivery Details</h3>
                
                <form method="POST" id="codForm">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #4a5568;">Full Name *</label>
                        <input type="text" name="name" required 
                               style="width: 100%; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem;"
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #4a5568;">Email *</label>
                        <input type="email" name="email" required 
                               style="width: 100%; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem;"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #4a5568;">M-Pesa Phone Number *</label>
                        <input type="tel" name="phone" id="mpesaPhone" required 
                               style="width: 100%; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem;"
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="254712345678">
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #4a5568;">Delivery Address *</label>
                        <textarea name="address" required rows="4"
                                  style="width: 100%; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; font-family: inherit; resize: vertical;"
                                  placeholder="House number, street name, estate, Nairobi"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>

                    <!-- PAYMENT OPTIONS -->
                    <div style="background: #f7fafc; padding: 1.5rem; border-radius: 16px; margin-bottom: 2rem;">
                        <h4 style="color: #2d3748; margin-bottom: 1rem;">💳 Payment Method</h4>
                        
                        <!-- CASH ON DELIVERY -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: white; border-radius: 12px; margin-bottom: 1rem; border: 2px solid #e2e8f0;">
                            <input type="radio" name="payment_method" value="cod" id="cod" checked style="width: 20px; height: 20px;">
                            <label for="cod" style="margin: 0; font-weight: 500; color: #4a5568; cursor: pointer; flex: 1;">
                                💰 Cash on Delivery
                            </label>
                        </div>

                        <!-- M-PESA WITH LOGO -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-radius: 12px; border: 2px solid #bbf7d0;">
                            <input type="radio" name="payment_method" value="mpesa" id="mpesa" style="width: 20px; height: 20px;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
                                <!-- M-PESA LOGO (SVG) -->
                                <svg width="28" height="28" viewBox="0 0 100 100" style="flex-shrink: 0;">
                                    <rect width="100" height="100" rx="12" fill="#059669"/>
                                    <text x="50" y="55" text-anchor="middle" font-size="18" font-weight="700" fill="white" font-family="Arial, sans-serif">M-PESA</text>
                                    <circle cx="20" cy="25" r="6" fill="#047857"/>
                                    <circle cx="35" cy="20" r="4" fill="#047857"/>
                                    <circle cx="80" cy="30" r="5" fill="#047857"/>
                                </svg>
                                <span style="font-weight: 500; color: #166534;">Pay via M-Pesa</span>
                            </div>
                        </div>
                    </div>

                    <!-- SUBMIT BUTTONS -->
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" id="codBtn" class="btn btn-primary" 
                                style="flex: 1; padding: 1.2rem; font-size: 1.1rem; font-weight: 700; background: linear-gradient(135deg, #ed64a6, #d53f8c); border: none; border-radius: 12px; color: white; cursor: pointer;">
                            💰 Order on Delivery
                        </button>
                        
                        <button type="button" id="mpesaBtn" onclick="payWithMpesa()" 
                                style="flex: 1; padding: 1.2rem; font-size: 1.1rem; font-weight: 700; background: linear-gradient(135deg, #059669, #047857); color: white; border: none; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                            <!-- M-PESA LOGO ON BUTTON -->
                            <svg width="22" height="22" viewBox="0 0 100 100">
                                <rect width="100" height="100" rx="10" fill="#34d399"/>
                                <text x="50" y="55" text-anchor="middle" font-size="14" font-weight="700" fill="#047857" font-family="Arial, sans-serif">M-PESA</text>
                            </svg>
                            Pay KSh <?php echo number_format($total, 0); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ORDER SUMMARY -->
            <div style="background: white; padding: 2rem; border-radius: 24px; box-shadow: 0 15px 50px rgba(0,0,0,0.08); height: fit-content;">
                <h3 style="margin-bottom: 1.5rem; color: #2d3748; text-align: center;">Order Summary</h3>
                
                <div style="border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem; margin-bottom: 1rem;">
                    <?php foreach ($items as $item): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem;">
                            <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                            <span>KSh <?php echo number_format($item['subtotal'], 0); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="display: flex; justify-content: space-between; font-size: 1.4rem; font-weight: 700; color: #2d3748; padding: 1rem 0; border-top: 2px solid #e2e8f0;">
                    <span>Total:</span>
                    <span>KSh <?php echo number_format($total, 0); ?></span>
                </div>
                
                <div style="background: #f0fff4; padding: 1rem; border-radius: 12px; margin-top: 1.5rem;">
                    <h4 style="color: #22543d; margin-bottom: 0.5rem;">Delivery Info</h4>
                    <p style="color: #4a5568; font-size: 0.9rem; margin: 0;">
                        🚚 Same-day delivery in Nairobi<br>
                        📱 SMS updates via WhatsApp<br>
                        💳 M-Pesa Paybill: 174379
                    </p>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer" style="margin-top: 4rem;">
        <div class="container">
            <p>&copy; 2026 DeraShop | Premium Women's Fashion | Nairobi</p>
        </div>
    </footer>

    <script>
    function payWithMpesa() {
        const phone = document.getElementById('mpesaPhone').value;
        
        if (!phone || phone.length < 10) {
            alert('Please enter a valid M-Pesa phone number (2547XXXXXXXX)');
            return;
        }
        
        if (confirm(`📱 M-PESA Payment\n\nPay KSh ${<?php echo number_format($total, 0); ?>} to:\n\nPaybill: 174379\nAccount: DeraShop\nAmount: KSh ${<?php echo number_format($total, 0); ?>}\nPhone: ${phone}\n\nEnter your M-Pesa PIN to confirm.\n\nClick OK to complete order.`)) {
            document.getElementById('codForm').submit();
            alert('✅ Order created successfully! Payment instructions sent to your phone.');
        }
    }
    </script>
</body>
</html>

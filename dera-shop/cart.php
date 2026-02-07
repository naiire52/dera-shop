<?php
// config.php already starts session, so NO session_start() here
require 'config.php';

// ========================================
// CART LOGIC
// ========================================
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle REMOVE item
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    header('Location: cart.php');
    exit;
}

// Build cart display data
$cart_items = [];
$cart_total = 0;

foreach ($_SESSION['cart'] as $product_id => $item_data) {
    // Get product details from database
    $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
        $cart_items[] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $item_data['quantity'],
            'subtotal' => $product['price'] * $item_data['quantity']
        ];
        $cart_total += $product['price'] * $item_data['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛒 Cart - DeraShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h2 style="margin: 2rem 0 1rem; text-align: center; color: #2d3748;">Your Shopping Cart</h2>
        <p style="text-align: center; color: #718096; margin-bottom: 2rem;">
            <?php echo count($cart_items); ?> items
        </p>
        
        <?php if (empty($cart_items)): ?>
            <div style="background: linear-gradient(135deg, #f7fafc, #edf2f7); padding: 3rem; border-radius: 24px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🛍️</div>
                <h3 style="color: #4a5568; margin-bottom: 1rem;">Your cart is empty</h3>
                <p style="color: #a0aec0;">No products added yet. Start shopping!</p>
                <a href="products.php" class="btn btn-primary" style="margin-top: 1.5rem; padding: 1rem 2rem; display: inline-block;">
                    Start Shopping →
                </a>
            </div>
        <?php else: ?>
            <div style="background: white; padding: 2rem; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
                <!-- CART TABLE -->
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f7fafc, #edf2f7);">
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #4a5568;">Product</th>
                            <th style="padding: 1rem; font-weight: 600; color: #4a5568;">Price</th>
                            <th style="padding: 1rem; font-weight: 600; color: #4a5568;">Qty</th>
                            <th style="padding: 1rem; font-weight: 600; color: #4a5568;">Total</th>
                            <th style="padding: 1rem; font-weight: 600; color: #4a5568;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 1rem;">
                                <strong style="color: #2d3748;"><?php echo htmlspecialchars($item['name']); ?></strong>
                            </td>
                            <td style="padding: 1rem; color: #4a5568;">
                                KSh <?php echo number_format($item['price'], 0); ?>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="background: #f7fafc; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600;">
                                    <?php echo $item['quantity']; ?>
                                </span>
                            </td>
                            <td style="padding: 1rem; font-weight: 700; color: #2d3748;">
                                KSh <?php echo number_format($item['subtotal'], 0); ?>
                            </td>
                            <td style="padding: 1rem;">
                                <a href="?remove=<?php echo $item['id']; ?>" 
                                   style="color: #e53e3e; text-decoration: none; font-weight: 500; padding: 0.5rem 1rem; border-radius: 20px; border: 1px solid #feb2b2; background: #fff5f5;"
                                   onclick="return confirm('Remove <?php echo htmlspecialchars($item['name']); ?>?')">
                                   Remove
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- TOTAL & CHECKOUT -->
                <div style="padding: 1.5rem; background: linear-gradient(135deg, #f7fafc, #edf2f7); border-radius: 16px; margin-top: 2rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong><?php echo count($cart_items); ?> items</strong>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; margin-bottom: 1rem;">
                            TOTAL: KSh <?php echo number_format($cart_total, 0); ?>
                        </div>
                        <a href="checkout.php" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1.1rem;">
                            Proceed to Checkout ➡️
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- CONTINUE SHOPPING BUTTON -->
        <div style="text-align: center; margin: 2rem 0;">
            <a href="products.php" class="btn" style="background: linear-gradient(135deg, #48bb78, #38a169); color: white; padding: 1rem 2rem; text-decoration: none;">
                Continue Shopping 🛍️
            </a>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer" style="background: linear-gradient(135deg, #2d3748, #1a202c); color: #e2e8f0; padding: 2rem; text-align: center; margin-top: 4rem;">
        <div class="container">
            <p style="margin: 0;">&copy; 2026 DeraShop | Premium Women's Fashion | Nairobi, Kenya</p>
            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; opacity: 0.8;">Fast delivery across Nairobi</p>
        </div>
    </footer>

</body>
</html>

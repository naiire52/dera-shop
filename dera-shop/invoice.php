<?php
require 'config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param('i',$id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) die('Order not found');

$items = [];
$stmt2 = $conn->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE order_id = ?");
$stmt2->bind_param('i',$id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($r = $res2->fetch_assoc()) $items[] = $r;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Invoice #<?php echo $order['id']; ?> - Dera Shop</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <h2>Invoice #<?php echo $order['id']; ?></h2>
  <p>Date: <?php echo $order['created_at']; ?></p>
  <p>Customer: <?php echo htmlspecialchars($order['customer_name']); ?> (<?php echo htmlspecialchars($order['customer_email']); ?>)</p>
  <p>Address: <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>

  <table style="margin-top:15px;">
    <tr>
      <th>Product</th>
      <th>Qty</th>
      <th>Price (KSh)</th>
      <th>Subtotal</th>
    </tr>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?php echo htmlspecialchars($it['name']); ?></td>
        <td><?php echo (int)$it['quantity']; ?></td>
        <td><?php echo number_format($it['price'],2); ?></td>
        <td><?php echo number_format($it['price'] * $it['quantity'],2); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <p style="margin-top:10px; text-align:right;">
    Total: <strong>KSh <?php echo number_format($order['total'],2); ?></strong>
  </p>
  <p style="margin-top:15px;"><a href="index.php">Back to Home</a></p>
</div>
</body>
</html>

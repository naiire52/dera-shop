<?php
require '../config.php';
if (!is_admin()) { header('Location: login.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) die('Order not found');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'pending';
    $stmt2 = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt2->bind_param('si',$status,$id);
    $stmt2->execute();
    header('Location: orders.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Update Order - Dera Shop</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-wrapper">
  <h1>Update Order #<?php echo $order['id']; ?></h1>
  <nav>
    <a href="index.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="orders.php">Orders</a>
    <a href="logout.php">Logout</a>
  </nav>
  <form method="post" style="max-width:300px; margin-top:15px;">
    <label>Status</label>
    <select name="status">
      <?php foreach (['pending','processing','completed','cancelled'] as $st): ?>
        <option value="<?php echo $st; ?>" <?php if ($order['status']===$st) echo 'selected'; ?>>
          <?php echo ucfirst($st); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Save</button>
  </form>
</div>
</body>
</html>

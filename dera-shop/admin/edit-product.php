<?php
require '../config.php';
if (!is_admin()) { header('Location: login.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) die('Product not found');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);

    $image = $product['image'];
    if (!empty($_FILES['image']['name'])) {
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "../images/".$image);
    }

    if ($name && $price > 0) {
        $stmt2 = $conn->prepare("UPDATE products SET name=?,description=?,price=?,image=?,stock=? WHERE id=?");
        $stmt2->bind_param('ssdsii',$name,$desc,$price,$image,$stock,$id);
        $stmt2->execute();
        header('Location: products.php');
        exit;
    } else {
        $msg = 'Name and price are required.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit Product - Dera Shop</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-wrapper">
  <h1>Edit Product</h1>
  <nav>
    <a href="index.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="orders.php">Orders</a>
    <a href="logout.php">Logout</a>
  </nav>
  <?php if ($msg): ?><p style="color:#fca5a5;"><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>

  <form method="post" enctype="multipart/form-data" style="max-width:500px; margin-top:15px;">
    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
    <textarea name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
    <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
    <input type="number" name="stock" value="<?php echo $product['stock']; ?>">
    <p>Current image: <?php echo htmlspecialchars($product['image']); ?></p>
    <input type="file" name="image" accept="image/*">
    <button type="submit">Update</button>
  </form>
</div>
</body>
</html>

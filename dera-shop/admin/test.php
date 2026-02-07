<?php
require '../config.php';
echo "<h1>✅ DeraShop Test</h1>";
echo "<p>Database: OK</p>";
echo "<p>Products table: " . ($conn->query("SELECT COUNT(*) FROM products")->num_rows > 0 ? "✅ FOUND" : "❌ EMPTY") . "</p>";

$result = $conn->query("SELECT * FROM products LIMIT 3");
if ($result->num_rows > 0) {
    echo "<h3>Products:</h3><ul>";
    while($row = $result->fetch_assoc()) {
        echo "<li>" . $row['name'] . " - KSh " . $row['price'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No products yet</p>";
}
?>
<a href="add-product.php">→ Add Product</a>
r 
<?php
echo "<h2>🖼️ IMAGE DIAGNOSTIC TOOL</h2>";
echo "<h3>1. Folder Check:</h3>";
echo "dera-shop/images/ exists: " . (is_dir('images') ? '✅ YES' : '❌ NO') . "<br>";

// Check all images
echo "<h3>2. Image Files Found:</h3>";
if (is_dir('images')) {
    $images = glob('images/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    echo "Found " . count($images) . " images:<br>";
    foreach ($images as $img) {
        echo "✅ " . basename($img) . "<br>";
    }
} else {
    echo "❌ images folder missing!<br>";
}

echo "<h3>3. Database Check:</h3>";
require 'config.php';
$result = $conn->query("SELECT id, name, image FROM products LIMIT 5");
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Image Filename</th><th>Exists?</th></tr>";
while ($row = $result->fetch_assoc()) {
    $path = "images/" . ($row['image'] ?: 'placeholder.jpg');
    $exists = file_exists($path) ? '✅ YES' : '❌ NO';
    echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['image']}</td><td>$exists</td></tr>";
}
echo "</table>";
?>

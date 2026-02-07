<?php
require '../config.php';
if (!is_admin()) { header('Location: login.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
}
header('Location: products.php');
exit;

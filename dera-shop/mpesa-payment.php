<?php
require 'config.php';
require_once 'mpesa.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$phone = $input['phone'] ?? '';
$amount = (int)$input['amount'];
$total = (float)$input['total'];

// Validate
if (!$phone || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone or amount']);
    exit;
}

// Create pending order first
$name = $_POST['name'] ?? 'Customer';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';

$stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total, status) VALUES (?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("ssssd", $name, $email, $phone, $address, $total);
$stmt->execute();
$order_id = $conn->insert_id;

// Save order items
foreach ($_SESSION['cart'] as $product_id => $item) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $order_id, $product_id, $item['quantity'], $item['price']);
    $stmt->execute();
}

// Initiate STK Push
$mpesa = new Mpesa();
$response = $mpesa->stkPush($phone, $amount, "DeraShop Order #$order_id", $order_id);

if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
    $_SESSION['pending_order'] = $order_id;
    echo json_encode(['success' => true, 'message' => 'M-Pesa sent! Checkout your phone.']);
} else {
    echo json_encode(['success' => false, 'message' => 'M-Pesa request failed']);
}
?>

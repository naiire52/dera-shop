<?php
require 'config.php';

$callback_data = json_decode(file_get_contents('php://input'), true);
file_put_contents('mpesa_logs.txt', date('Y-m-d H:i:s') . ' - ' . json_encode($callback_data) . "\n", FILE_APPEND);

if (isset($callback_data['Body']['stkCallback']['ResultCode'] == 0)) {
    $result = $callback_data['Body']['stkCallback']['CallbackMetadata'];
    
    $amount = $result[0]->Value;
    $receipt = $result[1]->Value;
    $phone = $result[4]->Value;
    
    // Update order as PAID
    if (isset($_SESSION['pending_order'])) {
        $order_id = $_SESSION['pending_order'];
        $stmt = $conn->prepare("UPDATE orders SET status = 'paid', mpesa_code = ? WHERE id = ? AND total = ?");
        $stmt->bind_param("sid", $receipt, $order_id, $amount);
        $stmt->execute();
        
        unset($_SESSION['pending_order']);
        unset($_SESSION['cart']);
    }
}

http_response_code(200);
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
?>

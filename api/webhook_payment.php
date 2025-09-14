<?php
require_once __DIR__ . '/../includes/db.php';

// Daraja sends POST request here
$input = json_decode(file_get_contents('php://input'), true);

// Example: verify transaction status
if (isset($input['Body']['stkCallback'])) {
    $callback = $input['Body']['stkCallback'];
    $status = $callback['ResultCode']; // 0 = success
    $checkoutId = $callback['CheckoutRequestID'];
    $amount = $callback['CallbackMetadata']['Item'][0]['Value'];
    $phone = $callback['CallbackMetadata']['Item'][4]['Value'];

    if ($status == 0) {
        // payment successful
        // find transaction in DB by CheckoutRequestID
        $stmt = $pdo->prepare("UPDATE transactions SET status='completed' WHERE checkout_id=?");
        $stmt->execute([$checkoutId]);

        // optionally provision WiFi (add user to radcheck)
    } else {
        // payment failed
        $stmt = $pdo->prepare("UPDATE transactions SET status='failed' WHERE checkout_id=?");
        $stmt->execute([$checkoutId]);
    }
}

// respond to Safaricom
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Accepted"]);

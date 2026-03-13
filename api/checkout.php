<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login', 'redirect' => '/login?redirect=/checkout']);
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch cart items
$items = getCartItems($userId);
if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty', 'redirect' => '/cart']);
    exit();
}

// Collect and sanitize shipping/contact data
$firstName = sanitize($_POST['first_name'] ?? '');
$lastName  = sanitize($_POST['last_name'] ?? '');
$email     = sanitize($_POST['email'] ?? '');
$phone     = sanitize($_POST['phone'] ?? '');
$address   = sanitize($_POST['address'] ?? '');
$city      = sanitize($_POST['city'] ?? '');
$state     = sanitize($_POST['state'] ?? '');
$zip       = sanitize($_POST['zip_code'] ?? '');
$notes     = sanitize($_POST['order_notes'] ?? '');
$shippingMethod = sanitize($_POST['shipping_method'] ?? 'standard');

if (!$firstName || !$lastName || !$email || !$phone || !$address || !$city || !$state || !$zip) {
    echo json_encode(['success' => false, 'message' => 'Please complete all required fields.']);
    exit();
}

// Calculate totals and validate stock
$subtotal = 0;
foreach ($items as $item) {
    if ($item['stock_quantity'] < $item['quantity']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock for ' . $item['title']]);
        exit();
    }
    $price = calculateDiscount($item['price'], $item['discount']);
    $subtotal += $price * $item['quantity'];
}

$shippingCost = ($shippingMethod === 'express') ? 15 : ($subtotal >= 50 ? 0 : 5);
$tax = $subtotal * 0.08;
$grandTotal = $subtotal + $shippingCost + $tax;

$shippingAddress = "{$firstName} {$lastName}\n{$address}\n{$city}, {$state} {$zip}\nPhone: {$phone}";

try {
    $pdo->beginTransaction();

    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, order_status, payment_status) VALUES (?, ?, ?, 'pending', 'pending')");
    $stmt->execute([$userId, $grandTotal, $shippingAddress]);
    $orderId = $pdo->lastInsertId();

    // Insert order items & update stock
    $oi = $pdo->prepare("INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stockUpdate = $pdo->prepare("UPDATE books SET stock_quantity = stock_quantity - ? WHERE book_id = ?");

    foreach ($items as $item) {
        $price = calculateDiscount($item['price'], $item['discount']);
        $oi->execute([$orderId, $item['book_id'], $item['quantity'], $price]);
        $stockUpdate->execute([$item['quantity'], $item['book_id']]);
    }

    // Clear cart
    $clear = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear->execute([$userId]);

    // Optionally save address back to profile if requested
    if (isset($_POST['save_address'])) {
        $save = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, city = ?, state = ?, zip_code = ? WHERE user_id = ?");
        $save->execute([
            trim($firstName . ' ' . $lastName),
            $phone,
            $address,
            $city,
            $state,
            $zip,
            $userId
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $orderId,
        'redirect' => '/profile?tab=orders'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Checkout failed: ' . $e->getMessage()]);
}
?>


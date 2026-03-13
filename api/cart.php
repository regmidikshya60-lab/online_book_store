<?php
session_start();

require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$book_id = $_GET['book_id'] ?? 0;
$cart_id = $_GET['cart_id'] ?? 0;
$quantity = $_GET['quantity'] ?? 1;

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login first',
        'redirect' => true
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'add':
            if ($book_id) {
                // Check if book exists and is in stock
                $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
                $stmt->execute([$book_id]);
                $book = $stmt->fetch();
                
                if (!$book) {
                    echo json_encode(['success' => false, 'message' => 'Book not found']);
                    exit();
                }
                
                if ($book['stock_quantity'] <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Book out of stock']);
                    exit();
                }
                
                // Check if already in cart
                $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND book_id = ?");
                $stmt->execute([$user_id, $book_id]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Update quantity
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ?");
                    $stmt->execute([$existing['cart_id']]);
                } else {
                    // Add new item
                    $stmt = $pdo->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, 1)");
                    $stmt->execute([$user_id, $book_id]);
                }
                
                echo json_encode(['success' => true, 'message' => 'Book added to cart']);
            }
            break;
            
        case 'remove':
            if ($cart_id) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
                $stmt->execute([$cart_id, $user_id]);
                echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            }
            break;
            
        case 'update':
            if ($cart_id && $quantity > 0) {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
                $stmt->execute([$quantity, $cart_id, $user_id]);
                echo json_encode(['success' => true, 'message' => 'Quantity updated']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
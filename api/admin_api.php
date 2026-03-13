<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check if admin
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'add_category':
        $category_name = sanitize($_POST['category_name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        
        if (empty($category_name)) {
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            exit();
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
            $stmt->execute([$category_name, $description]);
            echo json_encode(['success' => true, 'message' => 'Category added successfully']);
        } catch (PDOException $e) {
            error_log("Add category error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to add category']);
        }
        break;
        
    case 'update_order_status':
        $order_id = intval($_GET['id'] ?? 0);
        $status = sanitize($_GET['status'] ?? '');
        
        $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if ($order_id <= 0 || !in_array($status, $allowed_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit();
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
            $stmt->execute([$status, $order_id]);
            echo json_encode(['success' => true, 'message' => 'Order status updated']);
        } catch (PDOException $e) {
            error_log("Update order status error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
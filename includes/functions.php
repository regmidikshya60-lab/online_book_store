<?php
// Helper functions

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function getCategories() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get categories error: " . $e->getMessage());
        return [];
    }
}

function getAllBooks($limit = null) {
    global $pdo;
    try {
        $sql = "SELECT b.*, c.category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.category_id 
                ORDER BY b.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get books error: " . $e->getMessage());
        return [];
    }
}

function getBookById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT b.*, c.category_name 
                              FROM books b 
                              LEFT JOIN categories c ON b.category_id = c.category_id 
                              WHERE b.book_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get book by ID error: " . $e->getMessage());
        return null;
    }
}

function getCartCount($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Get cart count error: " . $e->getMessage());
        return 0;
    }
}

function getCartItems($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT c.*, b.title, b.author, b.price, b.image_url, b.stock_quantity 
                              FROM cart c 
                              JOIN books b ON c.book_id = b.book_id 
                              WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get cart items error: " . $e->getMessage());
        return [];
    }
}
?>
<?php
// Start session ONLY HERE at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define BASE_PATH for consistent file includes
define('BASE_PATH', __DIR__);

// Include configuration and functions
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';

// Get page from URL or default to home
$page = $_GET['page'] ?? 'home';

// Define valid pages and their file paths
$validPages = [
    'home' => 'pages/home.php',
    'books' => 'pages/books.php',
    'book' => 'pages/book.php',
    'login' => 'pages/login.php',
    'register' => 'pages/register.php',
    'logout' => 'pages/logout.php',
    'profile' => 'pages/profile.php',
    'cart' => 'pages/cart.php',
    'checkout' => 'pages/checkout.php',
    
    // Admin pages
    'admin/dashboard' => 'admin/dashboard.php',
    'admin/books' => 'admin/books.php',
    'admin/manage_books' => 'admin/manage_books.php',
    'admin/categories' => 'admin/categories.php',
    'admin/orders' => 'admin/orders.php',
    'admin/users' => 'admin/users.php'
];

// Get the actual file path
$pageFile = $validPages[$page] ?? 'pages/home.php';
$fullPath = BASE_PATH . '/' . $pageFile;

// Check if file exists
if (!file_exists($fullPath)) {
    $fullPath = BASE_PATH . '/pages/home.php';
}

// Include the page
require_once $fullPath;
?>
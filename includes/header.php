<?php
// DO NOT start session here - it's already started in index.php

if (!isset($pageTitle)) {
    $pageTitle = "Online Book Store";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Online Book Store</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #f8f9fa;
            --dark: #343a40;
            --radius: 8px;
            --shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .navbar {
            background: var(--secondary);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .hero {
            background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(52, 152, 219, 0.8));
            color: white;
            padding: 5rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .book-card {
            border: 1px solid #ddd;
            border-radius: var(--radius);
            overflow: hidden;
            transition: transform 0.3s;
            background: white;
            height: 100%;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }
        
        .book-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .book-content {
            padding: 1rem;
        }
        
        .book-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .book-author {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .form-container {
            max-width: 400px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book"></i> BookStore
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=books">
                            <i class="fas fa-book-open"></i> Books
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=admin/dashboard">
                                    <i class="fas fa-tachometer-alt"></i> Admin Panel
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=cart">
                                    <i class="fas fa-shopping-cart"></i> Cart
                                    <?php
                                    $cart_count = getCartCount($_SESSION['user_id']);
                                    if ($cart_count > 0): ?>
                                    <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['full_name'] ?? $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="index.php?page=profile">
                                    <i class="fas fa-user-circle"></i> Profile
                                </a></li>
                                <?php if (!isAdmin()): ?>
                                <li><a class="dropdown-item" href="index.php?page=cart">
                                    <i class="fas fa-shopping-cart"></i> My Cart
                                </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="index.php?page=logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=login">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=register">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <main>
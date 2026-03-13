<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'online_book_store');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Create connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Database Setup</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body { background: #f8f9fa; padding: 50px; }
            .container { max-width: 800px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='card shadow'>
                <div class='card-header bg-primary text-white'>
                    <h3 class='mb-0'>Database Setup</h3>
                </div>
                <div class='card-body'>";
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        phone VARCHAR(20),
        profile_picture VARCHAR(255) DEFAULT 'default.jpg',
        address TEXT,
        city VARCHAR(50),
        state VARCHAR(50),
        zip_code VARCHAR(20),
        user_type ENUM('admin', 'customer') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    echo "<div class='alert alert-success'>✓ Users table created</div>";
    
    // Create categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    echo "<div class='alert alert-success'>✓ Categories table created</div>";
    
    // Create books table
    $pdo->exec("CREATE TABLE IF NOT EXISTS books (
        book_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category_id INT,
        stock_quantity INT NOT NULL,
        image_url VARCHAR(255),
        isbn VARCHAR(20),
        publisher VARCHAR(100),
        publication_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    echo "<div class='alert alert-success'>✓ Books table created</div>";
    
    // Create cart table
    $pdo->exec("CREATE TABLE IF NOT EXISTS cart (
        cart_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        quantity INT DEFAULT 1,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    echo "<div class='alert alert-success'>✓ Cart table created</div>";
    
    // Create orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        order_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total_amount DECIMAL(10,2) NOT NULL,
        shipping_address TEXT NOT NULL,
        order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    echo "<div class='alert alert-success'>✓ Orders table created</div>";
    
    // Create order_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        order_item_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        book_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES books(book_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    echo "<div class='alert alert-success'>✓ Order items table created</div>";
    
    // Create admin user (password: 1234)
    $admin_password = password_hash('1234', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, user_type) 
                           VALUES ('admin', 'admin@bookstore.com', ?, 'Administrator', 'admin') 
                           ON DUPLICATE KEY UPDATE password = ?");
    $stmt->execute([$admin_password, $admin_password]);
    
    echo "<div class='alert alert-success'>✓ Admin user created/updated (username: admin, email: admin@bookstore.com, password: 1234)</div>";
    
    echo "<div class='alert alert-info mt-3'><strong>Setup completed successfully!</strong></div>";
    echo "<a href='index.php' class='btn btn-primary'>Go to Homepage</a>";
    
    echo "</div></div></div></body></html>";
    
} catch (PDOException $e) {
    die("<div class='alert alert-danger'><strong>Setup failed:</strong> " . htmlspecialchars($e->getMessage()) . "</div>");
}
?>
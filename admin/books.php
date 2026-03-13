<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/database.php';
$pdo = getPDOConnection();

// Handle book deletion
if (isset($_GET['delete'])) {
    $book_id = intval($_GET['delete']);
    
    try {
        // First delete from cart (due to foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM cart WHERE book_id = ?");
        $stmt->execute([$book_id]);
        
        // Then delete the book
        $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
        $stmt->execute([$book_id]);
        
        $_SESSION['success'] = "Book deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting book: " . $e->getMessage();
    }
    
    header('Location: books.php');
    exit();
}

// Get all books
$stmt = $pdo->query("SELECT b.*, c.category_name 
                     FROM books b 
                     LEFT JOIN categories c ON b.category_id = c.category_id 
                     ORDER BY b.created_at DESC");
$books = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - BookStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
        }
        
        .sidebar {
            background-color: #2c3e50;
            color: white;
            min-height: 100vh;
            padding: 0;
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            background-color: rgba(0,0,0,0.1);
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            border-left: 3px solid transparent;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
            border-left-color: #3498db;
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .header {
            background: white;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .book-image-small {
            width: 50px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="sidebar-brand">
                    <h4><i class="fas fa-book"></i> BookStore Admin</h4>
                    <small class="text-muted">Welcome, <?php echo $_SESSION['full_name']; ?></small>
                </div>
                
                <div class="sidebar-nav">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="books.php">
                                <i class="fas fa-book"></i> Books
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-cart"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-home"></i> View Site
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../pages/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="header">
                    <h2 class="mb-0"><i class="fas fa-book"></i> Manage Books</h2>
                    <a href="manage_books.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Book
                    </a>
                </div>
                
                <div class="main-content">
                    <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($books)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No books found</td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td>
                                                <img src="../uploads/books/<?php echo htmlspecialchars($book['image_url'] ?? 'default-book.jpg'); ?>" 
                                                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                                                     class="book-image-small"
                                                     onerror="this.src='../assets/images/placeholder.jpg'">
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                                <small class="text-muted">ISBN: <?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                                            <td><?php echo htmlspecialchars($book['category_name'] ?? 'Uncategorized'); ?></td>
                                            <td>$<?php echo number_format($book['price'], 2); ?></td>
                                            <td>
                                                <span class="badge <?php echo $book['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $book['stock_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="../pages/book.php?id=<?php echo $book['book_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="manage_books.php?action=edit&id=<?php echo $book['book_id']; ?>" 
                                                       class="btn btn-sm btn-outline-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="books.php?delete=<?php echo $book['book_id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this book?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
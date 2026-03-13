<?php
$pageTitle = "Home";

// Include header (NO session_start() here)
require_once __DIR__ . '/../includes/header.php';

// Get PDO connection
require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();
?>

<div class="hero">
    <div class="container">
        <h1 class="display-4">Welcome to BookStore</h1>
        <p class="lead">Discover thousands of books at amazing prices. From bestsellers to hidden gems.</p>
        <a href="index.php?page=books" class="btn btn-primary btn-lg">
            <i class="fas fa-book"></i> Browse Books
        </a>
    </div>
</div>

<div class="container py-5">
    <h2 class="text-center mb-5">Latest Books</h2>
    
    <div class="row">
        <?php
        try {
            $stmt = $pdo->query("SELECT b.*, c.category_name 
                                FROM books b 
                                LEFT JOIN categories c ON b.category_id = c.category_id 
                                ORDER BY b.created_at DESC 
                                LIMIT 8");
            $books = $stmt->fetchAll();
            
            if (empty($books)) {
                echo '<div class="col-12 text-center py-5">
                        <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
                        <h3>No books available yet</h3>
                        <p class="text-muted">Check back soon for amazing books!</p>
                      </div>';
            } else {
                foreach ($books as $book):
        ?>
        <div class="col-md-3 mb-4">
            <div class="book-card h-100">
                <img src="uploads/books/<?php echo htmlspecialchars($book['image_url'] ?? 'default-book.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                     class="book-image"
                     onerror="this.src='assets/images/placeholder.jpg'">
                
                <div class="book-content">
                    <h5 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                    <p class="book-author text-muted">By <?php echo htmlspecialchars($book['author']); ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <h5 class="text-primary mb-0">$<?php echo number_format($book['price'], 2); ?></h5>
                        </div>
                        
                        <div class="btn-group">
                            <button onclick="addToCart(<?php echo $book['book_id']; ?>)" 
                                    class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                            <a href="index.php?page=book&id=<?php echo $book['book_id']; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
                endforeach;
            }
        } catch (PDOException $e) {
            echo '<div class="col-12 text-center py-5">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p class="mb-0">Unable to load books. Please check database setup.</p>
                    </div>
                  </div>';
        }
        ?>
    </div>
    
    <?php if (!empty($books)): ?>
    <div class="text-center mt-5">
        <a href="index.php?page=books" class="btn btn-primary btn-lg">
            <i class="fas fa-book-open"></i> View All Books
        </a>
    </div>
    <?php endif; ?>
</div>

<?php
// Include footer
require_once __DIR__ . '/../includes/footer.php';
?>
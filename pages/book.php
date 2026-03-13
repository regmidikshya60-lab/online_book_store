<?php
if (!isset($_GET['id'])) {
    header('Location: index.php?page=books');
    exit();
}

$book_id = intval($_GET['id']);

require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

try {
    $stmt = $pdo->prepare("SELECT b.*, c.category_name 
                          FROM books b 
                          LEFT JOIN categories c ON b.category_id = c.category_id 
                          WHERE b.book_id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        header('Location: index.php?page=books');
        exit();
    }
} catch (PDOException $e) {
    header('Location: index.php?page=books');
    exit();
}

$pageTitle = $book['title'];

// Include header
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-5">
            <img src="uploads/books/<?php echo htmlspecialchars($book['image_url'] ?? 'default-book.jpg'); ?>" 
                 alt="<?php echo htmlspecialchars($book['title']); ?>"
                 class="img-fluid rounded shadow"
                 onerror="this.src='assets/images/placeholder.jpg'">
        </div>
        <div class="col-md-7">
            <h1 class="mb-3"><?php echo htmlspecialchars($book['title']); ?></h1>
            <p class="lead">by <?php echo htmlspecialchars($book['author']); ?></p>
            
            <?php if ($book['category_name']): ?>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category_name']); ?></p>
            <?php endif; ?>
            
            <p><strong>Price:</strong> <span class="text-primary h4">$<?php echo number_format($book['price'], 2); ?></span></p>
            
            <p><strong>Stock:</strong> 
                <span class="badge <?php echo $book['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                    <?php echo $book['stock_quantity'] > 0 ? 'In Stock (' . $book['stock_quantity'] . ' available)' : 'Out of Stock'; ?>
                </span>
            </p>
            
            <?php if ($book['isbn']): ?>
            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
            <?php endif; ?>
            
            <?php if ($book['publisher']): ?>
            <p><strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisher']); ?></p>
            <?php endif; ?>
            
            <?php if ($book['publication_date']): ?>
            <p><strong>Publication Date:</strong> <?php echo date('F j, Y', strtotime($book['publication_date'])); ?></p>
            <?php endif; ?>
            
            <div class="mt-4">
                <?php if ($book['stock_quantity'] > 0): ?>
                <button onclick="addToCart(<?php echo $book['book_id']; ?>)" 
                        class="btn btn-primary btn-lg">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
                <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>
                    <i class="fas fa-cart-plus"></i> Out of Stock
                </button>
                <?php endif; ?>
                <a href="index.php?page=books" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-arrow-left"></i> Back to Books
                </a>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-12">
            <h3>Description</h3>
            <div class="card">
                <div class="card-body">
                    <?php if ($book['description']): ?>
                    <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                    <?php else: ?>
                    <p class="text-muted">No description available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../includes/footer.php';
?>
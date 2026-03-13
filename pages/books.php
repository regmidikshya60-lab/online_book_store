<?php
$pageTitle = "Books";

// Include header (NO session_start() here)
require_once __DIR__ . '/../includes/header.php';

// Get PDO connection
require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

// Handle filters
$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query
$where = [];
$params = [];

if ($search) {
    $where[] = "(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($category_id) {
    $where[] = "b.category_id = ?";
    $params[] = $category_id;
}

$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Get total count
$countSql = "SELECT COUNT(*) as total FROM books b $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalBooks = $countStmt->fetch()['total'];
$totalPages = ceil($totalBooks / $limit);

// Get books
$booksSql = "SELECT b.*, c.category_name 
             FROM books b 
             LEFT JOIN categories c ON b.category_id = c.category_id 
             $whereClause 
             ORDER BY b.created_at DESC 
             LIMIT $limit OFFSET $offset";
$booksStmt = $pdo->prepare($booksSql);
$booksStmt->execute($params);
$books = $booksStmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
?>

<div class="container py-5">
    <h1 class="text-center mb-5">Browse Books</h1>
    
    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <input type="hidden" name="page" value="books">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search books..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="category" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" 
                                <?php echo ($category_id == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <?php if ($search || $category_id): ?>
                        <a href="index.php?page=books" class="btn btn-outline-secondary w-100">
                            Clear Filters
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Books Grid -->
    <div class="row">
        <?php if (empty($books)): ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
            <h3>No books found</h3>
            <p class="text-muted">Try adjusting your search or filter</p>
        </div>
        <?php else: ?>
        <?php foreach ($books as $book): ?>
        <div class="col-md-3 mb-4">
            <div class="book-card h-100">
                <img src="uploads/books/<?php echo htmlspecialchars($book['image_url'] ?? 'default-book.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                     class="book-image"
                     onerror="this.src='assets/images/placeholder.jpg'">
                
                <div class="book-content">
                    <h5 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                    <p class="book-author text-muted">By <?php echo htmlspecialchars($book['author']); ?></p>
                    
                    <?php if ($book['category_name']): ?>
                    <small class="text-primary d-block mb-2">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($book['category_name']); ?>
                    </small>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between align-items-center">
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
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="index.php?page=books&page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>">
                    Previous
                </a>
            </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="index.php?page=books&page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="index.php?page=books&page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>">
                    Next
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php
// Include footer
require_once __DIR__ . '/../includes/footer.php';
?>
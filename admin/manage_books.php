<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/database.php';
$pdo = getPDOConnection();

$action = $_GET['action'] ?? 'add';
$book_id = $_GET['id'] ?? 0;

// Get categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();

// Get book data if editing
$book = null;
if ($action === 'edit' && $book_id) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        $_SESSION['error'] = "Book not found!";
        header('Location: books.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = $_POST['category_id'] ? intval($_POST['category_id']) : null;
    $stock_quantity = intval($_POST['stock_quantity']);
    $isbn = trim($_POST['isbn']);
    $publisher = trim($_POST['publisher']);
    $publication_date = $_POST['publication_date'];
    
    try {
        if ($action === 'edit' && $book_id) {
            // Update existing book
            $stmt = $pdo->prepare("UPDATE books SET 
                                   title = ?, author = ?, description = ?, price = ?, 
                                   category_id = ?, stock_quantity = ?, isbn = ?, 
                                   publisher = ?, publication_date = ?, updated_at = NOW() 
                                   WHERE book_id = ?");
            $stmt->execute([
                $title, $author, $description, $price, $category_id, 
                $stock_quantity, $isbn, $publisher, $publication_date, $book_id
            ]);
            
            $_SESSION['success'] = "Book updated successfully!";
        } else {
            // Insert new book
            $stmt = $pdo->prepare("INSERT INTO books (title, author, description, price, 
                                   category_id, stock_quantity, isbn, publisher, publication_date) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $title, $author, $description, $price, $category_id, 
                $stock_quantity, $isbn, $publisher, $publication_date
            ]);
            
            $_SESSION['success'] = "Book added successfully!";
            $book_id = $pdo->lastInsertId();
        }
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/books/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $fileName;
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    // Update book with new image
                    $stmt = $pdo->prepare("UPDATE books SET image_url = ? WHERE book_id = ?");
                    $stmt->execute([$fileName, $book_id]);
                }
            }
        }
        
        header('Location: books.php');
        exit();
        
    } catch (PDOException $e) {
        $error = "Error saving book: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action === 'edit' ? 'Edit' : 'Add'; ?> Book - BookStore Admin</title>
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
        
        .main-content {
            padding: 2rem;
        }
        
        .header {
            background: white;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 2rem;
        }
        
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .image-preview {
            width: 200px;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
            display: none;
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
                            <a class="nav-link" href="books.php">
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
                    <h2 class="mb-0">
                        <i class="fas fa-<?php echo $action === 'edit' ? 'edit' : 'plus'; ?>"></i>
                        <?php echo $action === 'edit' ? 'Edit Book' : 'Add New Book'; ?>
                    </h2>
                </div>
                
                <div class="main-content">
                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-container">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo $book['title'] ?? ''; ?>" required>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="author" class="form-label">Author *</label>
                                            <input type="text" class="form-control" id="author" name="author" 
                                                   value="<?php echo $book['author'] ?? ''; ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="isbn" class="form-label">ISBN</label>
                                            <input type="text" class="form-control" id="isbn" name="isbn" 
                                                   value="<?php echo $book['isbn'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="price" class="form-label">Price *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="price" name="price" 
                                                       value="<?php echo $book['price'] ?? '0.00'; ?>" step="0.01" min="0" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                                   value="<?php echo $book['stock_quantity'] ?? '0'; ?>" min="0" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="category_id" class="form-label">Category</label>
                                            <select class="form-select" id="category_id" name="category_id">
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['category_id']; ?>"
                                                    <?php echo ($book['category_id'] ?? '') == $category['category_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="publication_date" class="form-label">Publication Date</label>
                                            <input type="date" class="form-control" id="publication_date" name="publication_date" 
                                                   value="<?php echo $book['publication_date'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="publisher" class="form-label">Publisher</label>
                                        <input type="text" class="form-control" id="publisher" name="publisher" 
                                               value="<?php echo $book['publisher'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo $book['description'] ?? ''; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Book Cover</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        
                                        <?php if ($action === 'edit' && !empty($book['image_url'])): ?>
                                        <div class="mt-3">
                                            <p class="mb-1">Current Image:</p>
                                            <img src="../uploads/books/<?php echo htmlspecialchars($book['image_url']); ?>" 
                                                 alt="Current Image" 
                                                 class="img-thumbnail"
                                                 style="max-width: 200px;"
                                                 onerror="this.src='../assets/images/placeholder.jpg'">
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="mt-3">
                                            <img id="imagePreview" class="image-preview" alt="Image Preview">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="books.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Books
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo $action === 'edit' ? 'Update Book' : 'Add Book'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>s
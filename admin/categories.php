<?php
if (!isLoggedIn() || !isAdmin()) {
    header('Location: /');
    exit();
}

$pageTitle = "Manage Categories";
$breadcrumbs = [
    ['link' => '/admin', 'text' => 'Dashboard'],
    ['link' => '/admin/categories', 'text' => 'Categories']
];

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

require_once '../includes/header.php';
?>

<div class="container">
    <div class="admin-header">
        <h1>Manage Categories</h1>
        <div class="admin-actions">
            <a href="/admin/categories?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Category
            </a>
            <a href="/admin" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <?php if ($action == 'list'): ?>
    <!-- Category List -->
    <div class="admin-content">
        <div class="card">
            <div class="card-header">
                <h3>All Categories</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="categoriesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Books Count</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT c.*, 
                                       COUNT(b.book_id) as book_count 
                                FROM categories c 
                                LEFT JOIN books b ON c.category_id = b.category_id 
                                GROUP BY c.category_id 
                                ORDER BY c.category_name
                            ");
                            $categories = $stmt->fetchAll();
                            
                            foreach ($categories as $category):
                            ?>
                            <tr>
                                <td>#<?php echo $category['category_id']; ?></td>
                                <td>
                                    <strong><?php echo $category['category_name']; ?></strong>
                                </td>
                                <td><?php echo $category['description'] ?: 'No description'; ?></td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $category['book_count']; ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/admin/categories?action=edit&id=<?php echo $category['category_id']; ?>" 
                                           class="btn btn-sm btn-outline">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteCategory(<?php echo $category['category_id']; ?>)" 
                                                class="btn btn-sm btn-danger"
                                                <?php echo $category['book_count'] > 0 ? 'disabled title="Cannot delete category with books"' : ''; ?>>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($action == 'add' || $action == 'edit'): 
        $category = null;
        if ($action == 'edit' && $id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
            $stmt->execute([$id]);
            $category = $stmt->fetch();
            
            if (!$category) {
                header('Location: /admin/categories');
                exit();
            }
        }
    ?>
    <!-- Add/Edit Category Form -->
    <div class="admin-content">
        <div class="card">
            <div class="card-header">
                <h3><?php echo $action == 'add' ? 'Add New Category' : 'Edit Category'; ?></h3>
            </div>
            <div class="card-body">
                <form id="categoryForm">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <input type="hidden" name="category_id" value="<?php echo $id; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category Name *</label>
                                <input type="text" 
                                       name="category_name" 
                                       class="form-control" 
                                       value="<?php echo $category['category_name'] ?? ''; ?>"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" 
                                  class="form-control" 
                                  rows="4"><?php echo $category['description'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> 
                            <?php echo $action == 'add' ? 'Add Category' : 'Update Category'; ?>
                        </button>
                        <a href="/admin/categories" class="btn btn-outline btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Delete category function
function deleteCategory(categoryId) {
    if (confirm('Are you sure you want to delete this category?')) {
        fetch(`/api/admin.php?action=delete_category&id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
    }
}

// Category form submission
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/api/admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/admin/categories';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
<?php
// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$pageTitle = "Admin Dashboard";

// Include header from parent directory
require_once '../includes/header.php';

// Get PDO connection
require_once '../config/database.php';
$pdo = getPDOConnection();
?>

<style>
    .admin-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: var(--primary);
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin: 0.5rem 0;
    }
    
    .admin-content {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
</style>

<div class="container py-5">
    <h1 class="mb-4"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
    
    <!-- Statistics -->
    <div class="admin-stats">
        <?php
        // Get statistics
        $stats = [
            'books' => ['icon' => 'fa-book', 'query' => "SELECT COUNT(*) as count FROM books"],
            'users' => ['icon' => 'fa-users', 'query' => "SELECT COUNT(*) as count FROM users"],
            'orders' => ['icon' => 'fa-shopping-cart', 'query' => "SELECT COUNT(*) as count FROM orders"],
            'categories' => ['icon' => 'fa-tags', 'query' => "SELECT COUNT(*) as count FROM categories"],
        ];
        
        foreach ($stats as $key => $stat):
            $stmt = $pdo->query($stat['query']);
            $result = $stmt->fetch();
        ?>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas <?php echo $stat['icon']; ?>"></i>
            </div>
            <div class="stat-number"><?php echo $result['count']; ?></div>
            <div class="stat-label"><?php echo ucfirst($key); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Quick Actions -->
    <div class="admin-content">
        <h3 class="mb-4">Quick Actions</h3>
        <div class="row">
            <div class="col-md-3 mb-3">
                <a href="index.php?page=admin/books" class="btn btn-primary w-100">
                    <i class="fas fa-book"></i> Manage Books
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="index.php?page=admin/categories" class="btn btn-success w-100">
                    <i class="fas fa-tags"></i> Manage Categories
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="index.php?page=admin/orders" class="btn btn-warning w-100">
                    <i class="fas fa-shopping-cart"></i> View Orders
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="index.php?page=admin/users" class="btn btn-info w-100">
                    <i class="fas fa-users"></i> Manage Users
                </a>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="admin-content mt-4">
        <h3 class="mb-4">Recent Orders</h3>
        <?php
        $stmt = $pdo->query("SELECT o.*, u.full_name 
                            FROM orders o 
                            JOIN users u ON o.user_id = u.user_id 
                            ORDER BY o.order_date DESC 
                            LIMIT 5");
        $orders = $stmt->fetchAll();
        ?>
        
        <?php if (empty($orders)): ?>
        <p class="text-muted">No orders yet.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): 
                        $statusClass = '';
                        switch ($order['order_status']) {
                            case 'pending': $statusClass = 'badge bg-warning'; break;
                            case 'processing': $statusClass = 'badge bg-info'; break;
                            case 'shipped': $statusClass = 'badge bg-primary'; break;
                            case 'delivered': $statusClass = 'badge bg-success'; break;
                            case 'cancelled': $statusClass = 'badge bg-danger'; break;
                            default: $statusClass = 'badge bg-secondary';
                        }
                    ?>
                    <tr>
                        <td>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><span class="<?php echo $statusClass; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <a href="index.php?page=admin/orders" class="btn btn-outline-primary">View All Orders</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>
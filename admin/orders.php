<?php
if (!isLoggedIn() || !isAdmin()) {
    header('Location: /');
    exit();
}

$pageTitle = "Manage Orders";
$breadcrumbs = [
    ['link' => '/admin', 'text' => 'Dashboard'],
    ['link' => '/admin/orders', 'text' => 'Orders']
];

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

require_once '../includes/header.php';
?>

<div class="container">
    <div class="admin-header">
        <h1>Manage Orders</h1>
        <div class="admin-actions">
            <a href="/admin" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <?php if ($action == 'list'): ?>
    <!-- Order List -->
    <div class="admin-content">
        <div class="card">
            <div class="card-header">
                <h3>All Orders</h3>
                <div class="filter-options">
                    <select id="statusFilter" class="form-control" style="max-width: 200px;">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT o.*, 
                                       u.username, 
                                       u.email,
                                       u.phone,
                                       u.full_name,
                                       COUNT(oi.order_item_id) as item_count 
                                FROM orders o 
                                JOIN users u ON o.user_id = u.user_id 
                                LEFT JOIN order_items oi ON o.order_id = oi.order_id 
                                GROUP BY o.order_id 
                                ORDER BY o.order_date DESC
                            ");
                            $orders = $stmt->fetchAll();
                            
                            foreach ($orders as $order):
                            ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo $order['full_name'] ?: $order['username']; ?></strong>
                                        <br><small class="text-muted"><?php echo $order['email']; ?></small>
                                    </div>
                                </td>
                                <td><?php echo $order['phone'] ?: '—'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $order['item_count']; ?> items</span>
                                </td>
                                <td>
                                    <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $order['order_status']; ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $order['payment_status'] == 'paid' ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/admin/orders?action=view&id=<?php echo $order['order_id']; ?>" 
                                           class="btn btn-sm btn-outline">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <button class="btn btn-sm btn-success" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'processing')">
                                            Confirm
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-primary dropdown-toggle" 
                                                    type="button" 
                                                    data-bs-toggle="dropdown">
                                                Update
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'shipped')">
                                                    Mark as Shipped
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'delivered')">
                                                    Mark as Delivered
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="#" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'cancelled')">
                                                    Cancel Order
                                                </a>
                                            </div>
                                        </div>
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
    
    <?php elseif ($action == 'view' && $id > 0): 
        // Get order details
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email, u.phone, u.full_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.user_id 
            WHERE o.order_id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            header('Location: /admin/orders');
            exit();
        }
        
        // Get order items
        $stmt = $pdo->prepare("
            SELECT oi.*, b.title, b.author, b.image_url 
            FROM order_items oi 
            JOIN books b ON oi.book_id = b.book_id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$id]);
        $orderItems = $stmt->fetchAll();
    ?>
    <!-- Order Details -->
    <div class="admin-content">
        <div class="row">
            <div class="col-lg-8">
                <!-- Order Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></h3>
                        <div class="order-status">
                            <span class="status-badge <?php echo $order['order_status']; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="order-items">
                            <h4>Order Items</h4>
                            <?php foreach ($orderItems as $item): ?>
                            <div class="order-item-detail">
                                <div class="item-image">
                                    <img src="/assets/images/books/<?php echo $item['image_url']; ?>" 
                                         alt="<?php echo $item['title']; ?>"
                                         style="width: 60px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="item-details">
                                    <h5><?php echo $item['title']; ?></h5>
                                    <p class="text-muted">By <?php echo $item['author']; ?></p>
                                    <p>Quantity: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <div class="item-total">
                                    <strong>$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></strong>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Shipping Address -->
                <div class="card">
                    <div class="card-header">
                        <h4>Shipping Address</h4>
                    </div>
                    <div class="card-body">
                        <address>
                            <strong><?php echo $order['full_name']; ?></strong><br>
                            <?php echo nl2br($order['shipping_address']); ?><br>
                            Phone: <?php echo $order['phone']; ?><br>
                            Email: <?php echo $order['email']; ?>
                        </address>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Order Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Order Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'processing')">
                                Mark as Processing
                            </button>
                            <button class="btn btn-success" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'shipped')">
                                Mark as Shipped
                            </button>
                            <button class="btn btn-info" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'delivered')">
                                Mark as Delivered
                            </button>
                            <button class="btn btn-danger" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'cancelled')">
                                Cancel Order
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Order Information -->
                <div class="card">
                    <div class="card-header">
                        <h4>Order Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="order-info">
                            <div class="info-item">
                                <span>Order Date:</span>
                                <strong><?php echo date('F d, Y H:i', strtotime($order['order_date'])); ?></strong>
                            </div>
                            <div class="info-item">
                                <span>Customer:</span>
                                <strong><?php echo $order['username']; ?></strong>
                            </div>
                            <div class="info-item">
                                <span>Email:</span>
                                <strong><?php echo $order['email']; ?></strong>
                            </div>
                            <div class="info-item">
                                <span>Payment Status:</span>
                                <span class="badge <?php echo $order['payment_status'] == 'paid' ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span>Order Status:</span>
                                <span class="badge status-badge <?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="order-totals">
                            <div class="total-item">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                            <div class="total-item">
                                <span>Shipping:</span>
                                <span>$0.00</span>
                            </div>
                            <div class="total-item">
                                <span>Tax:</span>
                                <span>$<?php echo number_format($order['total_amount'] * 0.08, 2); ?></span>
                            </div>
                            <div class="total-item grand-total">
                                <span>Total:</span>
                                <span>$<?php echo number_format($order['total_amount'] + ($order['total_amount'] * 0.08), 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.filter-options {
    display: flex;
    gap: var(--space-sm);
}

.order-item-detail {
    display: flex;
    gap: var(--space-md);
    padding: var(--space-md);
    border-bottom: 1px solid var(--gray-light);
    align-items: center;
}

.order-item-detail:last-child {
    border-bottom: none;
}

.item-details {
    flex: 1;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-totals {
    margin-top: var(--space-md);
}

.total-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-sm) 0;
}

.total-item.grand-total {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
    border-top: 2px solid var(--gray-light);
    margin-top: var(--space-sm);
    padding-top: var(--space-md);
}

.dropdown {
    display: inline-block;
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    display: none;
    min-width: 160px;
    padding: 0.5rem 0;
    background: white;
    border: 1px solid var(--gray-light);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.dropdown:hover .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    clear: both;
    color: var(--dark);
    text-align: inherit;
    text-decoration: none;
    background: none;
    border: none;
    cursor: pointer;
}

.dropdown-item:hover {
    background: var(--light);
}
</style>

<script>
// Filter orders by status
document.getElementById('statusFilter').addEventListener('change', function(e) {
    const status = e.target.value;
    const rows = document.querySelectorAll('#ordersTable tbody tr');
    
    rows.forEach(row => {
        if (!status || row.querySelector('.status-badge').textContent.toLowerCase().includes(status)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Update order status
function updateOrderStatus(orderId, status) {
    if (confirm(`Are you sure you want to mark this order as ${status}?`)) {
        fetch(`/api/admin.php?action=update_order_status&id=${orderId}&status=${status}`)
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
</script>

<?php require_once '../includes/footer.php'; ?>
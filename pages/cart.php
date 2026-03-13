<?php
$pageTitle = "Shopping Cart";

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit();
}

// Redirect admin users
if (isAdmin()) {
    header('Location: index.php?page=admin/dashboard');
    exit();
}

require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($user_id);
$cart_total = 0;

foreach ($cart_items as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}

// Include header
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4"><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
        <h3>Your cart is empty</h3>
        <p class="text-muted">Add some books to your cart to see them here.</p>
        <a href="index.php?page=books" class="btn btn-primary">
            <i class="fas fa-book"></i> Browse Books
        </a>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="uploads/books/<?php echo htmlspecialchars($item['image_url'] ?? 'default-book.jpg'); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                 class="me-3"
                                                 style="width: 60px; height: 80px; object-fit: cover;"
                                                 onerror="this.src='assets/images/placeholder.jpg'">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                <small class="text-muted">by <?php echo htmlspecialchars($item['author']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <div class="input-group" style="width: 120px;">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" 
                                                    onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="text" class="form-control form-control-sm text-center" 
                                                   value="<?php echo $item['quantity']; ?>" readonly>
                                            <button class="btn btn-outline-secondary btn-sm" type="button" 
                                                    onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] + 1; ?>)"
                                                    <?php echo ($item['quantity'] >= $item['stock_quantity']) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" 
                                                onclick="removeFromCart(<?php echo $item['cart_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span>$5.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Tax</span>
                        <span>$<?php echo number_format($cart_total * 0.08, 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <h5>Total</h5>
                        <h5>$<?php echo number_format($cart_total + 5 + ($cart_total * 0.08), 2); ?></h5>
                    </div>
                    
                    <a href="index.php?page=checkout" class="btn btn-primary w-100 btn-lg">
                        <i class="fas fa-shopping-bag"></i> Proceed to Checkout
                    </a>
                    
                    <a href="index.php?page=books" class="btn btn-outline-primary w-100 mt-2">
                        <i class="fas fa-book"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(cartId, quantity) {
    if (quantity < 1) {
        removeFromCart(cartId);
        return;
    }
    
    fetch('api/cart.php?action=update&cart_id=' + cartId + '&quantity=' + quantity)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update quantity');
            }
        });
}

function removeFromCart(cartId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }
    
    fetch('api/cart.php?action=remove&cart_id=' + cartId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to remove item');
            }
        });
}
</script>

<?php
// Include footer
require_once __DIR__ . '/../includes/footer.php';
?>
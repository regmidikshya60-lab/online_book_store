<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ../pages/login.php');
    exit();
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];
    
    if ($quantity <= 0) {
        // Remove item
        $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $_SESSION['user_id']]);
    } else {
        // Update quantity
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
    }
}

// Handle remove item
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    header('Location: cart.php');
    exit();
}

// Get cart items
$stmt = $pdo->prepare("SELECT c.*, b.title, b.author, b.price, b.image_url, b.stock_quantity 
                       FROM cart c 
                       JOIN books b ON c.book_id = b.book_id 
                       WHERE c.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

require_once '../includes/header.php';
?>

<div class="container" style="margin-top: 80px;">
    <h1>Your Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <div style="text-align: center; padding: 3rem;">
            <h3>Your cart is empty</h3>
            <a href="../pages/books.php" class="btn">Browse Books</a>
        </div>
    <?php else: ?>
        <table class="cart-table">
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
                        <div style="display: flex; align-items: center;">
                            <img src="../assets/images/books/<?php echo $item['image_url']; ?>" 
                                 alt="<?php echo $item['title']; ?>" 
                                 style="width: 80px; height: 100px; object-fit: cover; margin-right: 1rem;">
                            <div>
                                <h4><?php echo $item['title']; ?></h4>
                                <p>By <?php echo $item['author']; ?></p>
                            </div>
                        </div>
                    </td>
                    <td>$<?php echo $item['price']; ?></td>
                    <td>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                   min="1" max="<?php echo $item['stock_quantity']; ?>" 
                                   class="quantity-input">
                            <button type="submit" name="update_quantity" class="btn btn-sm">Update</button>
                        </form>
                    </td>
                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    <td>
                        <a href="cart.php?remove=<?php echo $item['cart_id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Remove this item from cart?')">
                            Remove
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="text-align: right; margin-top: 2rem;">
            <h3>Total: $<?php echo number_format($total, 2); ?></h3>
            <a href="../pages/checkout.php" class="btn btn-lg">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
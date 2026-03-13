<?php
if (!isLoggedIn()) {
    header('Location: /login?redirect=/checkout');
    exit();
}

$pageTitle = "Checkout";
$breadcrumbs = [
    ['link' => '/', 'text' => 'Home'],
    ['link' => '/cart', 'text' => 'Cart'],
    ['link' => '/checkout', 'text' => 'Checkout']
];

$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);
$cartCount = getCartCount($_SESSION['user_id']);

if ($cartCount == 0) {
    header('Location: /cart');
    exit();
}

// Get user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$shipping = $cartTotal >= 50 ? 0 : 5;
$tax = $cartTotal * 0.08;
$grandTotal = $cartTotal + $shipping + $tax;

require_once '../includes/header.php';
?>

<div class="container">
    <div class="checkout-header">
        <h1>Checkout</h1>
        <div class="checkout-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Cart</div>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-label">Information</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Shipping</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Payment</div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Checkout Form -->
        <div class="col-lg-8">
            <form id="checkout-form" method="POST" action="/api/checkout.php">
                <!-- Contact Information -->
                <div class="checkout-section">
                    <div class="section-header">
                        <h3><i class="fas fa-user"></i> Contact Information</h3>
                    </div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>First Name *</label>
                                    <input type="text" 
                                           name="first_name" 
                                           class="form-control" 
                                           value="<?php echo explode(' ', $user['full_name'])[0] ?? ''; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Name *</label>
                                    <input type="text" 
                                           name="last_name" 
                                           class="form-control" 
                                           value="<?php echo explode(' ', $user['full_name'])[1] ?? ''; ?>"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="<?php echo $user['email']; ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="tel" 
                                   name="phone" 
                                   class="form-control" 
                                   value="<?php echo $user['phone']; ?>"
                                   required>
                        </div>
                    </div>
                </div>
                
                <!-- Shipping Address -->
                <div class="checkout-section">
                    <div class="section-header">
                        <h3><i class="fas fa-map-marker-alt"></i> Shipping Address</h3>
                    </div>
                    <div class="section-body">
                        <div class="form-group">
                            <label>Address *</label>
                            <input type="text" 
                                   name="address" 
                                   class="form-control" 
                                   value="<?php echo $user['address']; ?>"
                                   required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>City *</label>
                                    <input type="text" 
                                           name="city" 
                                           class="form-control" 
                                           value="<?php echo $user['city']; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>State *</label>
                                    <input type="text" 
                                           name="state" 
                                           class="form-control" 
                                           value="<?php echo $user['state']; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Zip Code *</label>
                                    <input type="text" 
                                           name="zip_code" 
                                           class="form-control" 
                                           value="<?php echo $user['zip_code']; ?>"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="save_address" 
                                   name="save_address"
                                   class="form-check-input" checked>
                            <label for="save_address" class="form-check-label">
                                Save this address for future orders
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Shipping Method -->
                <div class="checkout-section">
                    <div class="section-header">
                        <h3><i class="fas fa-shipping-fast"></i> Shipping Method</h3>
                    </div>
                    <div class="section-body">
                        <div class="shipping-options">
                            <div class="shipping-option">
                                <input type="radio" 
                                       id="shipping_standard" 
                                       name="shipping_method" 
                                       value="standard"
                                       checked
                                       data-price="<?php echo $shipping; ?>">
                                <label for="shipping_standard">
                                    <div class="option-content">
                                        <h5>Standard Shipping</h5>
                                        <p>3-5 business days</p>
                                        <span class="option-price">
                                            <?php if ($shipping == 0): ?>
                                            <span class="text-success">FREE</span>
                                            <?php else: ?>
                                            $<?php echo number_format($shipping, 2); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="shipping-option">
                                <input type="radio" 
                                       id="shipping_express" 
                                       name="shipping_method" 
                                       value="express"
                                       data-price="15">
                                <label for="shipping_express">
                                    <div class="option-content">
                                        <h5>Express Shipping</h5>
                                        <p>1-2 business days</p>
                                        <span class="option-price">$15.00</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="checkout-section">
                    <div class="section-header">
                        <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                    </div>
                    <div class="section-body">
                        <div class="payment-tabs">
                            <div class="tab-header">
                                <button type="button" class="tab-btn active" data-tab="credit-card">
                                    <i class="fas fa-credit-card"></i> Credit Card
                                </button>
                                <button type="button" class="tab-btn" data-tab="paypal">
                                    <i class="fab fa-paypal"></i> PayPal
                                </button>
                            </div>
                            
                            <div class="tab-content">
                                <!-- Credit Card -->
                                <div class="tab-pane active" id="credit-card">
                                    <div class="form-group">
                                        <label>Card Number *</label>
                                        <input type="text" 
                                               name="card_number" 
                                               class="form-control" 
                                               placeholder="1234 5678 9012 3456"
                                               maxlength="19">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Expiration Date *</label>
                                                <input type="text" 
                                                       name="exp_date" 
                                                       class="form-control" 
                                                       placeholder="MM/YY">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>CVV *</label>
                                                <input type="text" 
                                                       name="cvv" 
                                                       class="form-control" 
                                                       placeholder="123"
                                                       maxlength="4">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Name on Card *</label>
                                        <input type="text" 
                                               name="card_name" 
                                               class="form-control" 
                                               placeholder="John Doe">
                                    </div>
                                    
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               id="save_card" 
                                               name="save_card"
                                               class="form-check-input">
                                        <label for="save_card" class="form-check-label">
                                            Save card for future purchases
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- PayPal -->
                                <div class="tab-pane" id="paypal">
                                    <div class="paypal-info">
                                        <p>You will be redirected to PayPal to complete your payment securely.</p>
                                        <button type="button" class="btn btn-paypal">
                                            <i class="fab fa-paypal"></i> Pay with PayPal
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Notes -->
                <div class="checkout-section">
                    <div class="section-header">
                        <h3><i class="fas fa-sticky-note"></i> Order Notes (Optional)</h3>
                    </div>
                    <div class="section-body">
                        <textarea name="order_notes" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Special instructions for your order..."></textarea>
                    </div>
                </div>
                
                <input type="hidden" name="total_amount" value="<?php echo $grandTotal; ?>">
            </form>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="order-summary-sticky">
                <div class="order-summary">
                    <div class="summary-header">
                        <h3>Order Summary</h3>
                    </div>
                    
                    <div class="summary-items">
                        <?php foreach ($cartItems as $item): 
                            $itemPrice = calculateDiscount($item['price'], $item['discount']);
                            $itemTotal = $itemPrice * $item['quantity'];
                        ?>
                        <div class="summary-item">
                            <div class="item-info">
                                <span class="item-name"><?php echo $item['title']; ?></span>
                                <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                            </div>
                            <span class="item-price">$<?php echo number_format($itemTotal, 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($cartTotal, 2); ?></span>
                        </div>
                        <div class="total-row shipping-row">
                            <span>Shipping</span>
                            <span id="shipping-display">
                                <?php if ($shipping == 0): ?>
                                <span class="text-success">FREE</span>
                                <?php else: ?>
                                $<?php echo number_format($shipping, 2); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="total-row">
                            <span>Tax</span>
                            <span id="tax-display">$<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total</span>
                            <span id="grand-total">$<?php echo number_format($grandTotal, 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="summary-footer">
                        <button type="submit" 
                                form="checkout-form" 
                                class="btn btn-primary btn-block btn-lg" id="place-order-btn">
                            <i class="fas fa-lock"></i> Place Order
                        </button>
                        
                        <div class="secure-checkout">
                            <i class="fas fa-shield-alt"></i>
                            <span>Secure checkout - 256-bit SSL encryption</span>
                        </div>
                        
                        <div class="return-policy">
                            <p>
                                <i class="fas fa-undo-alt"></i>
                                30-day return policy. Read our 
                                <a href="/returns">return policy</a> for details.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-header {
    text-align: center;
    margin-bottom: var(--space-xl);
    padding: var(--space-lg);
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
}

.checkout-steps {
    display: flex;
    justify-content: center;
    gap: var(--space-xl);
    margin-top: var(--space-lg);
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-sm);
    position: relative;
}

.step::after {
    content: '';
    position: absolute;
    top: 20px;
    right: -50px;
    width: 40px;
    height: 2px;
    background: var(--gray-light);
}

.step:last-child::after {
    display: none;
}

.step.active .step-number {
    background: var(--primary);
    color: white;
}

.step.active .step-label {
    color: var(--primary);
    font-weight: 600;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--gray-light);
    color: var(--gray);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.step-label {
    color: var(--gray);
    font-size: 0.875rem;
}

.checkout-section {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    margin-bottom: var(--space-lg);
    overflow: hidden;
}

.section-header {
    padding: var(--space-lg);
    background: var(--light);
    border-bottom: 1px solid var(--gray-light);
}

.section-body {
    padding: var(--space-lg);
}

.shipping-options {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.shipping-option {
    position: relative;
}

.shipping-option input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.shipping-option label {
    display: block;
    padding: var(--space-lg);
    border: 2px solid var(--gray-light);
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
}

.shipping-option input[type="radio"]:checked + label {
    border-color: var(--primary);
    background: rgba(67, 97, 238, 0.05);
}

.shipping-option label:hover {
    border-color: var(--primary);
}

.option-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.option-content h5 {
    margin: 0;
}

.option-content p {
    margin: 0;
    color: var(--gray);
    font-size: 0.875rem;
}

.option-price {
    font-weight: 600;
    color: var(--primary);
}

.payment-tabs {
    border: 2px solid var(--gray-light);
    border-radius: var(--radius);
    overflow: hidden;
}

.payment-tabs .tab-header {
    display: flex;
    background: var(--light);
    border-bottom: 2px solid var(--gray-light);
}

.payment-tabs .tab-btn {
    flex: 1;
    padding: var(--space-md);
    background: none;
    border: none;
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-sm);
}

.payment-tabs .tab-btn.active {
    background: white;
    color: var(--primary);
    border-bottom: 2px solid var(--primary);
    margin-bottom: -2px;
}

.payment-tabs .tab-content {
    padding: var(--space-lg);
}

.payment-tabs .tab-pane {
    display: none;
}

.payment-tabs .tab-pane.active {
    display: block;
}

.paypal-info {
    text-align: center;
    padding: var(--space-xl) 0;
}

.btn-paypal {
    background: #003087;
    color: white;
    padding: 1rem 2rem;
    font-size: 1.25rem;
    margin-top: var(--space-lg);
}

.btn-paypal:hover {
    background: #001f5c;
    color: white;
}

.order-summary-sticky {
    position: sticky;
    top: var(--space-xl);
}

.order-summary {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.summary-header {
    padding: var(--space-lg);
    background: var(--gradient-primary);
    color: white;
}

.summary-items {
    padding: var(--space-lg);
    max-height: 300px;
    overflow-y: auto;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-sm) 0;
    border-bottom: 1px solid var(--gray-light);
}

.summary-item:last-child {
    border-bottom: none;
}

.item-info {
    display: flex;
    flex-direction: column;
}

.item-name {
    font-weight: 500;
}

.item-quantity {
    font-size: 0.875rem;
    color: var(--gray);
}

.item-price {
    font-weight: 600;
    color: var(--primary);
}

.summary-totals {
    padding: var(--space-lg);
    border-top: 1px solid var(--gray-light);
    background: var(--light);
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-sm) 0;
}

.shipping-row,
.grand-total {
    border-top: 1px solid var(--gray-light);
    padding-top: var(--space-md);
    margin-top: var(--space-md);
}

.grand-total {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.summary-footer {
    padding: var(--space-lg);
    border-top: 1px solid var(--gray-light);
}

.secure-checkout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-sm);
    margin-top: var(--space-lg);
    padding: var(--space-md);
    background: var(--light);
    border-radius: var(--radius);
    color: var(--success);
    font-weight: 600;
}

.return-policy {
    margin-top: var(--space-lg);
    padding: var(--space-md);
    background: var(--light);
    border-radius: var(--radius);
    font-size: 0.875rem;
    color: var(--gray);
}

.return-policy a {
    color: var(--primary);
    text-decoration: underline;
}
</style>

<script>
const subtotalVal = <?php echo json_encode($cartTotal); ?>;

// Update totals when shipping method changes
document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
    radio.addEventListener('change', updateOrderSummary);
});

function updateOrderSummary() {
    const shippingMethod = document.querySelector('input[name="shipping_method"]:checked');
    const shippingCost = parseFloat(shippingMethod.dataset.price);
    const tax = subtotalVal * 0.08;
    const grandTotal = subtotalVal + shippingCost + tax;
    
    // Update display
    document.getElementById('shipping-display').innerHTML = 
        shippingCost === 0 ? 
        '<span class="text-success">FREE</span>' : 
        '$' + shippingCost.toFixed(2);
    
    document.getElementById('tax-display').textContent = '$' + tax.toFixed(2);
    document.getElementById('grand-total').textContent = '$' + grandTotal.toFixed(2);
}

updateOrderSummary();

// Handle checkout submit via fetch
const checkoutForm = document.getElementById('checkout-form');
const placeOrderBtn = document.getElementById('place-order-btn');
checkoutForm.addEventListener('submit', function(e) {
    e.preventDefault();
    placeOrderBtn.disabled = true;
    placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing order...';
    
    const formData = new FormData(checkoutForm);
    fetch('/api/checkout.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect || '/profile?tab=orders';
        } else if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'Checkout failed. Please try again.');
        }
    })
    .catch(() => {
        alert('Checkout failed. Please try again.');
    })
    .finally(() => {
        placeOrderBtn.disabled = false;
        placeOrderBtn.innerHTML = '<i class="fas fa-lock"></i> Place Order';
    });
});

// Payment tabs
document.querySelectorAll('.payment-tabs .tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tabId = this.dataset.tab;
        
        // Update active tab button
        document.querySelectorAll('.payment-tabs .tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Show active tab content
        document.querySelectorAll('.payment-tabs .tab-pane').forEach(pane => pane.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
    });
});

// Card number formatting
document.querySelector('input[name="card_number"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    let formatted = '';
    
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formatted += ' ';
        }
        formatted += value[i];
    }
    
    e.target.value = formatted.substring(0, 19);
});

// Expiration date formatting
document.querySelector('input[name="exp_date"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    if (value.length >= 2) {
        e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
    } else {
        e.target.value = value;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
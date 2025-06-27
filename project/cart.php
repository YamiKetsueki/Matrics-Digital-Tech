<?php
require_once 'config/database.php';
require_once 'config/session.php';

$page_title = 'Shopping Cart - USB Store';

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image_url, p.quantity_available 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.session_id = ?
");
$stmt->execute([$_SESSION['cart_session_id']]);
$cart_items = $stmt->fetchAll();

$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

include 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Shopping Cart</h1>
    </div>
</div>

<section class="cart-section">
    <div class="container">
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty.</p>
                <a href="product_order.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="item-price">₹<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <div class="item-quantity">
                                <label>Quantity:</label>
                                <select onchange="updateCartItem(<?php echo $item['cart_id']; ?>, this.value)">
                                    <?php for ($i = 1; $i <= min(10, $item['quantity_available']); $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $i == $item['quantity'] ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="item-total">
                                ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                            <div class="item-actions">
                                <button onclick="removeFromCart(<?php echo $item['cart_id']; ?>)" class="btn btn-danger btn-small">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Total Items:</span>
                        <span><?php echo array_sum(array_column($cart_items, 'quantity')); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Amount:</span>
                        <span>₹<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    
                    <div class="checkout-actions">
                        <a href="product_order.php" class="btn btn-secondary">Continue Shopping</a>
                        <?php if (isLoggedIn()): ?>
                            <a href="contact_form.php" class="btn btn-primary">Proceed to Checkout</a>
                        <?php else: ?>
                            <p class="login-message">Please <a href="login.php">login</a> to proceed with checkout.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
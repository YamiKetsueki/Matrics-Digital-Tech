<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$page_title = 'Confirm Order - USB Store';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_payment'])) {
        // Process the order
        $user_id = getCurrentUserId();
        $full_name = $_POST['full_name'];
        $mobile = $_POST['mobile'];
        $secondary_mobile = $_POST['secondary_mobile'] ?? '';
        $address = $_POST['address'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $pin_code = $_POST['pin_code'];
        $payment_method = $_POST['payment_method'];
        $total_amount = $_POST['total_amount'];
        
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, payment_method, customer_name, customer_mobile, 
                                  customer_secondary_mobile, delivery_address, delivery_city, delivery_state, delivery_pin_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $total_amount, $payment_method, $full_name, $mobile, $secondary_mobile, $address, $city, $state, $pin_code]);
            $order_id = $pdo->lastInsertId();
            
            // Get cart items and add to order
            $stmt = $pdo->prepare("
                SELECT c.*, p.price, p.quantity_available 
                FROM cart c 
                JOIN products p ON c.product_id = p.product_id 
                WHERE c.session_id = ?
            ");
            $stmt->execute([$_SESSION['cart_session_id']]);
            $cart_items = $stmt->fetchAll();
            
            foreach ($cart_items as $item) {
                // Add to order items
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                // Update product quantity
                $stmt = $pdo->prepare("UPDATE products SET quantity_available = quantity_available - ? WHERE product_id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
            $stmt->execute([$_SESSION['cart_session_id']]);
            
            $pdo->commit();
            
            $_SESSION['order_success'] = $order_id;
            header('Location: order_success.php');
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to process order. Please try again.";
        }
    }
    
    // Store form data
    $form_data = $_POST;
} else {
    header('Location: contact_form.php');
    exit;
}

// Get cart items for display
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price 
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
        <h1>Confirm Your Order</h1>
    </div>
</div>

<section class="confirm-order-section">
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="order-confirmation">
            <div class="order-details">
                <h3>Order Summary</h3>
                <div class="order-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <span><?php echo htmlspecialchars($item['name']); ?></span>
                            <span>Qty: <?php echo $item['quantity']; ?></span>
                            <span>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="order-total">
                    <strong>Total: ₹<?php echo number_format($total_amount, 2); ?></strong>
                </div>
            </div>
            
            <div class="delivery-details">
                <h3>Delivery Details</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($form_data['full_name']); ?></p>
                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($form_data['mobile']); ?></p>
                <?php if (!empty($form_data['secondary_mobile'])): ?>
                    <p><strong>Secondary Mobile:</strong> <?php echo htmlspecialchars($form_data['secondary_mobile']); ?></p>
                <?php endif; ?>
                <p><strong>Address:</strong><br>
                   <?php echo nl2br(htmlspecialchars($form_data['address'])); ?><br>
                   <?php echo htmlspecialchars($form_data['city']); ?>, <?php echo htmlspecialchars($form_data['state']); ?> - <?php echo htmlspecialchars($form_data['pin_code']); ?>
                </p>
            </div>
            
            <div class="payment-section">
                <h3>Payment Method</h3>
                <form action="" method="POST" class="payment-form">
                    <!-- Hidden fields to preserve data -->
                    <?php foreach ($form_data as $key => $value): ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                    <?php endforeach; ?>
                    <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
                    
                    <div class="payment-options">
                        <div class="payment-option">
                            <input type="radio" id="upi" name="payment_method" value="UPI" required>
                            <label for="upi">UPI Payment</label>
                            <div class="payment-details" id="upi-details" style="display: none;">
                                <select name="upi_app">
                                    <option value="gpay">Google Pay</option>
                                    <option value="paytm">Paytm</option>
                                    <option value="phonepe">PhonePe</option>
                                </select>
                                <input type="text" name="upi_id" placeholder="Enter UPI ID">
                            </div>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" id="card" name="payment_method" value="Card" required>
                            <label for="card">Credit/Debit Card</label>
                            <div class="payment-details" id="card-details" style="display: none;">
                                <input type="text" name="card_number" placeholder="Card Number" pattern="[0-9]{16}">
                                <div class="card-row">
                                    <input type="text" name="expiry" placeholder="MM/YY" pattern="[0-9]{2}/[0-9]{2}">
                                    <input type="text" name="cvv" placeholder="CVV" pattern="[0-9]{3}">
                                </div>
                                <input type="text" name="card_holder" placeholder="Card Holder Name">
                            </div>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" id="cod" name="payment_method" value="Cash on Delivery" required>
                            <label for="cod">Cash on Delivery</label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="contact_form.php" class="btn btn-secondary">Back</a>
                        <button type="submit" name="confirm_payment" class="btn btn-primary">Confirm & Pay</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Hide all payment details
        document.querySelectorAll('.payment-details').forEach(details => {
            details.style.display = 'none';
        });
        
        // Show selected payment details
        if (this.value === 'UPI') {
            document.getElementById('upi-details').style.display = 'block';
        } else if (this.value === 'Card') {
            document.getElementById('card-details').style.display = 'block';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
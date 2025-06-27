<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn() || !isset($_SESSION['order_success'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_SESSION['order_success'];
unset($_SESSION['order_success']);

$page_title = 'Order Confirmed - USB Store';

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(CONCAT(p.name, ' (Qty: ', oi.quantity, ')') SEPARATOR ', ') as items
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.product_id
    WHERE o.order_id = ? AND o.user_id = ?
    GROUP BY o.order_id
");
$stmt->execute([$order_id, getCurrentUserId()]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Order Confirmed!</h1>
    </div>
</div>

<section class="order-success-section">
    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <svg width="64" height="64" fill="#059669" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
            </div>
            
            <h2>Thank you for your order!</h2>
            <p>Your order has been confirmed and will be processed shortly.</p>
            
            <div class="order-summary">
                <h3>Order Details</h3>
                <div class="summary-row">
                    <span>Order ID:</span>
                    <span>#<?php echo $order['order_id']; ?></span>
                </div>
                <div class="summary-row">
                    <span>Items:</span>
                    <span><?php echo htmlspecialchars($order['items']); ?></span>
                </div>
                <div class="summary-row">
                    <span>Total Amount:</span>
                    <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Payment Method:</span>
                    <span><?php echo htmlspecialchars($order['payment_method']); ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery Address:</span>
                    <span>
                        <?php echo htmlspecialchars($order['customer_name']); ?><br>
                        <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?><br>
                        <?php echo htmlspecialchars($order['delivery_city']); ?>, <?php echo htmlspecialchars($order['delivery_state']); ?> - <?php echo htmlspecialchars($order['delivery_pin_code']); ?>
                    </span>
                </div>
            </div>
            
            <div class="next-steps">
                <h3>What's Next?</h3>
                <ul>
                    <li>You will receive an order confirmation email shortly</li>
                    <li>Your order will be processed within 1-2 business days</li>
                    <li>Standard delivery takes 3-5 business days</li>
                    <li>You can track your order in the "My Orders" section</li>
                </ul>
            </div>
            
            <div class="action-buttons">
                <a href="my_orders.php" class="btn btn-primary">View My Orders</a>
                <a href="product_order.php" class="btn btn-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
</section>

<style>
.order-success-section {
    padding: 2rem 0;
}

.success-card {
    max-width: 600px;
    margin: 0 auto;
    background: white;
    padding: 3rem 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-align: center;
}

.success-icon {
    margin-bottom: 1.5rem;
}

.success-card h2 {
    color: #059669;
    margin-bottom: 1rem;
}

.success-card > p {
    color: #6b7280;
    margin-bottom: 2rem;
}

.order-summary {
    background: #f8fafc;
    padding: 1.5rem;
    border-radius: 8px;
    margin: 2rem 0;
    text-align: left;
}

.order-summary h3 {
    margin-bottom: 1rem;
    color: #1f2937;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    align-items: flex-start;
}

.summary-row:last-child {
    margin-bottom: 0;
}

.next-steps {
    text-align: left;
    margin: 2rem 0;
}

.next-steps h3 {
    margin-bottom: 1rem;
    color: #1f2937;
}

.next-steps ul {
    list-style-type: disc;
    padding-left: 1.5rem;
}

.next-steps li {
    margin-bottom: 0.5rem;
    color: #4b5563;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .success-card {
        padding: 2rem 1rem;
    }
    
    .summary-row {
        flex-direction: column;
        gap: 0.2rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
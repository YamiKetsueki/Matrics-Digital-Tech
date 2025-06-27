<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$page_title = 'My Orders - USB Store';

// Get user orders
$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') SEPARATOR ', ') as items
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.product_id
    WHERE o.user_id = ?
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
");
$stmt->execute([getCurrentUserId()]);
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>My Orders</h1>
    </div>
</div>

<section class="orders-section">
    <div class="container">
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <p>You haven't placed any orders yet.</p>
                <a href="product_order.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <h3>Order #<?php echo $order['order_id']; ?></h3>
                            <span class="order-status status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        
                        <div class="order-details">
                            <p><strong>Items:</strong> <?php echo htmlspecialchars($order['items']); ?></p>
                            <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></p>
                            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                            <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                        </div>
                        
                        <div class="delivery-info">
                            <p><strong>Delivery Address:</strong><br>
                               <?php echo htmlspecialchars($order['customer_name']); ?><br>
                               <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?><br>
                               <?php echo htmlspecialchars($order['delivery_city']); ?>, <?php echo htmlspecialchars($order['delivery_state']); ?> - <?php echo htmlspecialchars($order['delivery_pin_code']); ?>
                            </p>
                        </div>
                        
                        <?php if ($order['status'] === 'pending'): ?>
                            <div class="order-actions">
                                <button onclick="cancelOrder(<?php echo $order['order_id']; ?>)" class="btn btn-danger">Cancel Order</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.orders-section {
    padding: 2rem 0;
}

.empty-orders {
    text-align: center;
    padding: 3rem 0;
}

.order-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.order-status {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-confirmed {
    background: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.order-details, .delivery-info {
    margin-bottom: 1rem;
}

.order-details p, .delivery-info p {
    margin-bottom: 0.5rem;
}

.order-actions {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}
</style>

<?php include 'includes/footer.php'; ?>
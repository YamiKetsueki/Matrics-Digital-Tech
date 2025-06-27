<?php
require_once '../../config/database.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

$order_id = (int)$_GET['order_id'];

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Get order details
    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    
    // Generate HTML
    $html = '
        <h2>Order #' . $order['order_id'] . '</h2>
        
        <div class="order-info">
            <h3>Customer Information</h3>
            <p><strong>Username:</strong> ' . htmlspecialchars($order['username']) . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($order['email']) . '</p>
            <p><strong>Name:</strong> ' . htmlspecialchars($order['customer_name']) . '</p>
            <p><strong>Mobile:</strong> ' . htmlspecialchars($order['customer_mobile']) . '</p>';
    
    if ($order['customer_secondary_mobile']) {
        $html .= '<p><strong>Secondary Mobile:</strong> ' . htmlspecialchars($order['customer_secondary_mobile']) . '</p>';
    }
    
    $html .= '
        </div>
        
        <div class="order-info">
            <h3>Delivery Address</h3>
            <p>' . nl2br(htmlspecialchars($order['delivery_address'])) . '<br>
               ' . htmlspecialchars($order['delivery_city']) . ', ' . htmlspecialchars($order['delivery_state']) . ' - ' . htmlspecialchars($order['delivery_pin_code']) . '</p>
        </div>
        
        <div class="order-info">
            <h3>Order Items</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="padding: 0.5rem; text-align: left; border: 1px solid #e5e7eb;">Product</th>
                        <th style="padding: 0.5rem; text-align: center; border: 1px solid #e5e7eb;">Quantity</th>
                        <th style="padding: 0.5rem; text-align: right; border: 1px solid #e5e7eb;">Price</th>
                        <th style="padding: 0.5rem; text-align: right; border: 1px solid #e5e7eb;">Total</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($items as $item) {
        $html .= '
                    <tr>
                        <td style="padding: 0.5rem; border: 1px solid #e5e7eb;">' . htmlspecialchars($item['name']) . '</td>
                        <td style="padding: 0.5rem; text-align: center; border: 1px solid #e5e7eb;">' . $item['quantity'] . '</td>
                        <td style="padding: 0.5rem; text-align: right; border: 1px solid #e5e7eb;">₹' . number_format($item['price'], 2) . '</td>
                        <td style="padding: 0.5rem; text-align: right; border: 1px solid #e5e7eb;">₹' . number_format($item['price'] * $item['quantity'], 2) . '</td>
                    </tr>';
    }
    
    $html .= '
                </tbody>
            </table>
        </div>
        
        <div class="order-info">
            <h3>Payment Information</h3>
            <p><strong>Payment Method:</strong> ' . htmlspecialchars($order['payment_method']) . '</p>
            <p><strong>Total Amount:</strong> ₹' . number_format($order['total_amount'], 2) . '</p>
            <p><strong>Status:</strong> <span class="status-' . $order['status'] . '">' . ucfirst($order['status']) . '</span></p>
            <p><strong>Order Date:</strong> ' . date('F j, Y, g:i a', strtotime($order['order_date'])) . '</p>
        </div>
    ';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
}  catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
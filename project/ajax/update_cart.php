<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$cart_id = (int)$_POST['cart_id'];
$quantity = (int)$_POST['quantity'];

if ($cart_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item or quantity']);
    exit;
}

try {
    // Check if cart item belongs to current session and get product info
    $stmt = $pdo->prepare("
        SELECT c.*, p.quantity_available 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.cart_id = ? AND c.session_id = ?
    ");
    $stmt->execute([$cart_id, $_SESSION['cart_session_id']]);
    $cart_item = $stmt->fetch();
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }
    
    if ($quantity > $cart_item['quantity_available']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit;
    }
    
    // Update quantity
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $stmt->execute([$quantity, $cart_id]);
    
    echo json_encode(['success' => true, 'message' => 'Cart updated']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
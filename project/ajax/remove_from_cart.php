<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$cart_id = (int)$_POST['cart_id'];

if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit;
}

try {
    // Check if cart item belongs to current session
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE cart_id = ? AND session_id = ?");
    $stmt->execute([$cart_id, $_SESSION['cart_session_id']]);
    $cart_item = $stmt->fetch();
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }
    
    // Remove item
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ?");
    $stmt->execute([$cart_id]);
    
    echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
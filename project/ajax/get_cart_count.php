<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE session_id = ?");
    $stmt->execute([$_SESSION['cart_session_id']]);
    $result = $stmt->fetch();
    
    echo json_encode(['count' => (int)$result['count']]);
    
} catch (Exception $e) {
    echo json_encode(['count' => 0]);
}
?>
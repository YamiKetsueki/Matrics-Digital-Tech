<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Manage Orders - Admin';

$stmt = $pdo->prepare("
    SELECT o.*, u.username,
           GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') SEPARATOR ', ') as items
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.product_id
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
");
$stmt->execute();
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">

<!-- Navigation -->
<nav class="bg-white shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-xl font-semibold">Order Management</h1>
        <div class="space-x-4">
            <a href="dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <a href="products.php" class="hover:text-blue-600">Products</a>
            <a href="orders.php" class="text-blue-600 font-bold">Orders</a>
            <a href="users.php" class="hover:text-blue-600">Users</a>
            <a href="../logout.php" class="text-red-500 hover:text-red-700">Log Out</a>

        </div>
    </div>
</nav>

<!-- Orders Table -->
<div class="max-w-6xl mx-auto px-4">
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">All Orders</h2>

        <?php if (empty($orders)): ?>
            <p class="text-gray-500">No orders found.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-2">Order ID</th>
                            <th class="px-4 py-2">Customer</th>
                            <th class="px-4 py-2">Items</th>
                            <th class="px-4 py-2">Amount</th>
                            <th class="px-4 py-2">Payment</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-4 py-2">#<?= $order['order_id'] ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($order['username']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($order['items']) ?></td>
                                <td class="px-4 py-2">₹<?= number_format($order['total_amount'], 2) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($order['payment_method']) ?></td>
                                <td class="px-4 py-2">
                                    <span class="
                                        inline-block px-2 py-1 rounded-full text-xs font-medium
                                        <?= $order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                        <?= $order['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : '' ?>
                                        <?= $order['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' ?>
                                    ">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2"><?= date('M j, Y g:i A', strtotime($order['order_date'])) ?></td>
                                <td class="px-4 py-2">
                                    <button onclick="showOrderDetails(<?= $order['order_id'] ?>)"
                                            class="text-blue-600 hover:underline">
                                        View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-2xl w-full relative">
        <button onclick="closeModal()" class="absolute top-2 right-3 text-gray-600 text-xl hover:text-black">&times;</button>
        <div id="orderDetails" class="text-sm"></div>
    </div>
</div>

<script>
function showOrderDetails(orderId) {
    fetch(`ajax/get_order_details.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('orderDetails').innerHTML = data.html;
                document.getElementById('orderModal').classList.remove('hidden');
                document.getElementById('orderModal').classList.add('flex');
            } else {
                alert('Failed to load order details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading order details');
        });
}

function closeModal() {
    document.getElementById('orderModal').classList.add('hidden');
    document.getElementById('orderModal').classList.remove('flex');
}

// Close modal if clicking outside content
window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>

</body>
</html>

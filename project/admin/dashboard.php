<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Admin Dashboard - USB Store';

// Get statistics
$stats = [];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $stmt->fetch()['count'];

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $stmt->fetch()['count'];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $stmt->fetch()['count'];

// Total revenue
$stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE status != 'cancelled'");
$stats['revenue'] = $stmt->fetch()['revenue'];

// Recent orders
$stmt = $pdo->prepare("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.order_date DESC 
    LIMIT 5
");
$stmt->execute();
$recent_orders = $stmt->fetchAll();
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
        <h1 class="text-xl font-semibold">Admin Dashboard</h1>
        <div class="space-x-4">
            <a href="dashboard.php" class="text-blue-600 font-medium">Dashboard</a>
            <a href="products.php" class="hover:text-blue-500">Products</a>
            <a href="orders.php" class="hover:text-blue-500">Orders</a>
            <a href="users.php" class="hover:text-blue-500">Users</a>
            <a href="../logout.php" class="text-red-500 hover:text-red-700">Log Out</a>
        </div>
    </div>
</nav>

<!-- Stats -->
<div class="max-w-5xl mx-auto px-4 space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-2xl font-bold"><?= $stats['products'] ?></div>
            <div class="text-sm text-gray-600">Total Products</div>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-2xl font-bold"><?= $stats['orders'] ?></div>
            <div class="text-sm text-gray-600">Total Orders</div>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-2xl font-bold"><?= $stats['users'] ?></div>
            <div class="text-sm text-gray-600">Total Users</div>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-2xl font-bold">₹<?= number_format($stats['revenue'], 2) ?></div>
            <div class="text-sm text-gray-600">Total Revenue</div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Recent Orders</h2>
        <?php if (empty($recent_orders)): ?>
            <p class="text-gray-500">No recent orders found.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-2">Order ID</th>
                            <th class="px-4 py-2">Customer</th>
                            <th class="px-4 py-2">Amount</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td class="px-4 py-2">#<?= $order['order_id'] ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($order['username']) ?></td>
                                <td class="px-4 py-2">₹<?= number_format($order['total_amount'], 2) ?></td>
                                <td class="px-4 py-2"><?= ucfirst($order['status']) ?></td>
                                <td class="px-4 py-2"><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

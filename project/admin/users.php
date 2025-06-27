<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Manage Users - Admin';

$stmt = $pdo->prepare("
    SELECT u.*, 
           COUNT(o.order_id) as total_orders,
           COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN o.total_amount ELSE 0 END), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.user_id = o.user_id
    GROUP BY u.user_id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll();
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
        <h1 class="text-xl font-semibold">User Management</h1>
        <div class="space-x-4">
            <a href="dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <a href="products.php" class="hover:text-blue-600">Products</a>
            <a href="orders.php" class="hover:text-blue-600">Orders</a>
            <a href="users.php" class="text-blue-600 font-bold">Users</a>
            <a href="../logout.php" class="text-red-500 hover:text-red-700">Log Out</a>
        </div>
    </div>
</nav>

<!-- User Table -->
<div class="max-w-6xl mx-auto px-4">
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">All Users</h2>

        <?php if (empty($users)): ?>
            <p class="text-gray-500">No users found.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-2">User ID</th>
                            <th class="px-4 py-2">Username</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">Full Name</th>
                            <th class="px-4 py-2">Mobile</th>
                            <th class="px-4 py-2">Total Orders</th>
                            <th class="px-4 py-2">Total Spent</th>
                            <th class="px-4 py-2">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-4 py-2"><?= $user['user_id'] ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($user['username']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($user['full_name']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($user['mobile']) ?></td>
                                <td class="px-4 py-2"><?= $user['total_orders'] ?></td>
                                <td class="px-4 py-2">₹<?= number_format($user['total_spent'], 2) ?></td>
                                <td class="px-4 py-2"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
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

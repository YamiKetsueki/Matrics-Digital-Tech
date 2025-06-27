<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Manage Products - Admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, quantity_available, image_url) VALUES (?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['quantity'],
            $_POST['image_url']
        ]) ? "Product added successfully!" : "Failed to add product.";
    } elseif (isset($_POST['update_product'])) {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, quantity_available = ?, image_url = ? WHERE product_id = ?");
        $success = $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['quantity'],
            $_POST['image_url'],
            $_POST['product_id']
        ]) ? "Product updated successfully!" : "Failed to update product.";
    }
}

$stmt = $pdo->query("SELECT * FROM products ORDER BY name");
$products = $stmt->fetchAll();

$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch();
}
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
        <h1 class="text-xl font-semibold">Product Management</h1>
        <div class="space-x-4">
            <a href="dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <a href="products.php" class="text-blue-600 font-bold">Products</a>
            <a href="orders.php" class="hover:text-blue-600">Orders</a>
            <a href="users.php" class="hover:text-blue-600">Users</a>
            <a href="../logout.php" class="text-red-500 hover:text-red-700">Log Out</a>
        </div>
    </div>
</nav>

<!-- Main Container -->
<div class="max-w-5xl mx-auto px-4 space-y-8">

    <!-- Feedback Message -->
    <?php if (isset($success)): ?>
        <div class="bg-green-100 text-green-800 p-3 rounded shadow"><?= $success ?></div>
    <?php endif; ?>

    <!-- Product Form -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4"><?= $edit_product ? 'Edit Product' : 'Add New Product' ?></h2>
        <form method="POST" class="space-y-4">
            <?php if ($edit_product): ?>
                <input type="hidden" name="product_id" value="<?= $edit_product['product_id'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium mb-1">Product Name</label>
                    <input type="text" name="name" id="name" required class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>">
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium mb-1">Price (₹)</label>
                    <input type="number" step="0.01" name="price" id="price" required class="w-full border px-3 py-2 rounded" value="<?= $edit_product['price'] ?? '' ?>">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full border px-3 py-2 rounded"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="quantity" class="block text-sm font-medium mb-1">Quantity Available</label>
                    <input type="number" name="quantity" id="quantity" required class="w-full border px-3 py-2 rounded" value="<?= $edit_product['quantity_available'] ?? '' ?>">
                </div>
                <div>
                    <label for="image_url" class="block text-sm font-medium mb-1">Image URL</label>
                    <input type="url" name="image_url" id="image_url" class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($edit_product['image_url'] ?? '') ?>">
                </div>
            </div>

            <div class="flex space-x-4 pt-2">
                <?php if ($edit_product): ?>
                    <a href="products.php" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</a>
                    <button type="submit" name="update_product" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
                <?php else: ?>
                    <button type="submit" name="add_product" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add Product</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Product Table -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">All Products</h2>

        <?php if (empty($products)): ?>
            <p class="text-gray-500">No products found.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="px-4 py-2">Image</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Price</th>
                            <th class="px-4 py-2">Stock</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="px-4 py-2">
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Product" class="h-12 w-12 object-cover rounded">
                                </td>
                                <td class="px-4 py-2"><?= htmlspecialchars($product['name']) ?></td>
                                <td class="px-4 py-2">₹<?= number_format($product['price'], 2) ?></td>
                                <td class="px-4 py-2"><?= $product['quantity_available'] ?></td>
                                <td class="px-4 py-2 space-x-2">
                                    <a href="?edit=<?= $product['product_id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                                    <button onclick="deleteProduct(<?= $product['product_id'] ?>)" class="text-red-600 hover:underline">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteProduct(id) {
    if (confirm("Are you sure you want to delete this product?")) {
        // Implement deletion via AJAX or redirect
        alert("Delete functionality not yet implemented.");
    }
}
</script>

</body>
</html>

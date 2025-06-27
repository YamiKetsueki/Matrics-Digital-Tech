<?php
require_once 'config/database.php';
require_once 'config/session.php';

$page_title = 'Products - MATRICS DIGITAL TECHNOLOGY';

// Handle search
$search = $_GET['search'] ?? '';
$where_clause = '';
$params = [];

if ($search) {
    $where_clause = "WHERE name LIKE ? OR description LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Get all products
$sql = "SELECT * FROM products $where_clause ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Our Products</h1>
        <?php if ($search): ?>
            <p>Search results for: <strong><?php echo htmlspecialchars($search); ?></strong></p>
        <?php endif; ?>
    </div>
</div>

<section class="products-section">
    <div class="container">
        <?php if (empty($products)): ?>
            <div class="no-products">
                <p>No products found.</p>
                <?php if ($search): ?>
                    <a href="product_order.php" class="btn btn-primary">View All Products</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                            <div class="product-stock">
                                <?php if ($product['quantity_available'] > 0): ?>
                                    <span class="in-stock">In Stock (<?php echo $product['quantity_available']; ?>)</span>
                                <?php else: ?>
                                    <span class="out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <?php if ($product['quantity_available'] > 0): ?>
                                    <div class="quantity-selector">
                                        <label>Quantity:</label>
                                        <select id="quantity_<?php echo $product['product_id']; ?>">
                                            <?php for ($i = 1; $i <= min(10, $product['quantity_available']); $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <button onclick="addToCart(<?php echo $product['product_id']; ?>)" class="btn btn-primary">Add to Cart</button>
                                <?php else: ?>
                                    <button class="btn btn-disabled" disabled>Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
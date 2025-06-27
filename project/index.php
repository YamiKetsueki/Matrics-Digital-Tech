<?php
require_once 'config/database.php';
require_once 'config/session.php';

$page_title = 'USB Store - Premium Flash Drives';

// Get featured products
$stmt = $pdo->prepare("SELECT * FROM products WHERE quantity_available > 0 ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$featured_products = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Premium USB Flash Drives</h1>
            <p>Discover our wide range of high-quality USB storage solutions for all your needs</p>
            <a href="product_order.php" class="btn btn-primary btn-large">Shop Now</a>
        </div>
    </div>
</div>

<section class="featured-products">
    <div class="container">
        <h2>Featured Products</h2>
        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                        <div class="product-actions">
                            <button onclick="addToCart(<?php echo $product['product_id']; ?>)" class="btn btn-primary">Add to Cart</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'USB Store - Premium Flash Drives'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><a href="index.php">USB Store</a></h1>
                </div>
                
                <nav class="nav">
                    <a href="index.php">Home</a>
                    <a href="product_order.php">Products</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="my_orders.php">My Orders</a>
                    <?php endif; ?>
                </nav>

                <div class="header-actions">
                    <div class="search-box">
                        <form action="product_order.php" method="GET">
                            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button type="submit">Search</button>
                        </form>
                    </div>
                    
                    <div class="cart-icon">
                        <a href="cart.php">Cart <span class="cart-count" id="cartCount">0</span></a>
                    </div>

                    <div class="auth-buttons">
                        <?php if (isLoggedIn()): ?>
                            <span>Welcome</span>
                            <a href="profile_edit.php" class="btn btn-secondary">Profile</a>
                            <a href="logout.php" class="btn btn-secondary">Logout</a>
                        <?php elseif (isAdminLoggedIn()): ?>
                            <span>Admin Panel</span>
                            <a href="admin/dashboard.php" class="btn btn-primary">Dashboard</a>
                            <a href="logout.php" class="btn btn-secondary">Logout</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Login</a>
                            <a href="register.php" class="btn btn-secondary">Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
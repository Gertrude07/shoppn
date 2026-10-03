<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shoppn</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="brand"><a href="<?= BASE_URL ?>/index.php">Shoppn</a></div>

    <form class="search-form" action="<?= BASE_URL ?>/views/search_results.php" method="GET">
        <input type="text" name="user_query" placeholder="Search products...">
        <button type="submit">Search</button>
    </form>

    <div class="page-tools">
        <button class="back-page-btn" type="button" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '<?= BASE_URL ?>/index.php'; }">Back</button>
    </div>

    <nav class="main-nav">
        <ul>
            <?php if (is_logged_in()): ?>
                <li>Welcome <?php echo htmlspecialchars($_SESSION['customer_name'] ?? ''); ?></li>
                <?php if (!is_admin()): ?>
                    <li><a href="<?= BASE_URL ?>/views/shop.php">Shop</a></li>
                <?php endif; ?>
                <li><a href="<?= BASE_URL ?>/views/account/my_account.php">My Account</a></li>
                <?php if (is_admin()): ?>
                    <li><a href="<?= BASE_URL ?>/views/admin/brand.php">Brands</a></li>
                    <li><a href="<?= BASE_URL ?>/views/admin/category.php">Categories</a></li>
                    <li><a href="<?= BASE_URL ?>/views/admin/product.php">Products</a></li>
                <?php endif; ?>
                <li><a href="<?= BASE_URL ?>/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>/views/register.php">Register</a></li>
                <li><a href="<?= BASE_URL ?>/views/login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main class="site-main">

<?php
require __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$dashboardController = new ProductController();
$dashboardBrands = $dashboardController->getAllBrands();
$dashboardCategories = $dashboardController->getAllCategories();
$dashboardProducts = $dashboardController->getAllProducts();
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<section class="admin-dashboard">
    <div class="admin-dashboard-hero">
        <div>
            <p class="eyebrow">Admin area</p>
            <h1>Store overview</h1>
            <p class="admin-dashboard-welcome">Welcome, <?= htmlspecialchars($_SESSION['customer_name'] ?? 'Admin') ?></p>
        </div>
        <img src="<?= BASE_URL ?>/images/home-products.jpg" alt="Admin dashboard banner">
    </div>

    <div class="admin-stat-grid">
        <a class="admin-stat" href="<?= BASE_URL ?>/views/admin/product.php">
            <span>Products</span>
            <strong><?= count($dashboardProducts) ?></strong>
        </a>
        <a class="admin-stat" href="<?= BASE_URL ?>/views/admin/category.php">
            <span>Categories</span>
            <strong><?= count($dashboardCategories) ?></strong>
        </a>
        <a class="admin-stat" href="<?= BASE_URL ?>/views/admin/brand.php">
            <span>Brands</span>
            <strong><?= count($dashboardBrands) ?></strong>
        </a>
    </div>

    <div class="admin-dashboard-actions">
        <a href="<?= BASE_URL ?>/views/admin/product.php">Manage products</a>
        <a href="<?= BASE_URL ?>/views/admin/category.php">Manage categories</a>
        <a href="<?= BASE_URL ?>/views/admin/brand.php">Manage brands</a>
    </div>
</section>

<?php require __DIR__ . '/../layout/sidebar.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>

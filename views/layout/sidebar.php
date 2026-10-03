<?php
require_once __DIR__ . '/../../controllers/ProductController.php';

$sidebarController = new ProductController();
$sidebarCategories = $sidebarController->getAllCategories();
$sidebarBrands = $sidebarController->getAllBrands();
?>

<aside class="sidebar">
    <h3>Categories</h3>
    <?php if ($sidebarCategories): ?>
        <ul>
            <?php foreach ($sidebarCategories as $category): ?>
                <li><?= htmlspecialchars($category['cat_name']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="sidebar-placeholder">No categories available.</p>
    <?php endif; ?>

    <h3>Brands</h3>
    <?php if ($sidebarBrands): ?>
        <ul>
            <?php foreach ($sidebarBrands as $brand): ?>
                <li><?= htmlspecialchars($brand['brand_name']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="sidebar-placeholder">No brands available.</p>
    <?php endif; ?>
</aside>

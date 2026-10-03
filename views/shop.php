<?php
require __DIR__ . '/../core/core.php';
require_login();
if (is_admin()) {
    redirect(BASE_URL . '/views/admin/dashboard.php');
}
require_once __DIR__ . '/../controllers/ProductController.php';

$shopController = new ProductController();
$shopProducts = $shopController->getAllProducts();
$shopCategories = $shopController->getAllCategories();
$shopBrands = $shopController->getAllBrands();
?>
<?php require __DIR__ . '/layout/header.php'; ?>

<section class="shop-page">
    <div class="shop-notice">
        <span>Fresh finds for everyday living</span>
        <span><?= count($shopProducts) ?> products available</span>
    </div>

    <div class="shop-hero">
        <div class="shop-hero-copy">
            <p class="eyebrow">Shoppn marketplace</p>
            <h1>Everything you need, in one easy place.</h1>
            <p>Browse useful things from brands you can come back to.</p>
        </div>
        <div class="shop-hero-images" aria-label="Featured products">
            <img class="shop-hero-image-main" src="<?= BASE_URL ?>/images/products/OIP.webp" alt="Featured product">
            <img class="shop-hero-image-small" src="<?= BASE_URL ?>/images/products/OIP%20(3).webp" alt="Another featured product">
        </div>
    </div>

    <div class="shop-layout">
        <aside class="shop-categories">
            <h2>Shop by category</h2>
            <ul>
                <?php foreach ($shopCategories as $category): ?>
                    <li><a href="#products"><?= htmlspecialchars($category['cat_name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php if ($shopBrands): ?>
                <h2>Popular brands</h2>
                <ul>
                    <?php foreach (array_slice($shopBrands, 0, 5) as $brand): ?>
                        <li><a href="#products"><?= htmlspecialchars($brand['brand_name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </aside>

        <div class="shop-products" id="products">
            <div class="shop-toolbar">
                <div>
                    <p class="eyebrow">Our collection</p>
                    <h2>Shop all products</h2>
                </div>
                <span><?= count($shopProducts) ?> item<?= count($shopProducts) === 1 ? '' : 's' ?></span>
            </div>

            <?php if ($shopProducts): ?>
                <div class="product-grid shop-product-grid">
                    <?php foreach ($shopProducts as $product): ?>
                        <article class="product-card shop-product-card">
                            <img src="<?= BASE_URL ?>/images/products/<?= rawurlencode($product['product_image'] ?: 'OIP.webp') ?>" alt="<?= htmlspecialchars($product['product_title']) ?>">
                            <div class="product-card-body">
                                <p class="product-card-meta"><?= htmlspecialchars($product['brand_name']) ?> / <?= htmlspecialchars($product['cat_name']) ?></p>
                                <h2><?= htmlspecialchars($product['product_title']) ?></h2>
                                <p class="product-card-description"><?= htmlspecialchars($product['product_desc'] ?? '') ?></p>
                                <strong class="product-card-price"><?= format_price($product['product_price'], $product['product_currency'] ?? 'USD') ?></strong>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="catalog-empty shop-empty">
                    <img src="<?= BASE_URL ?>/images/products/OIP%20(3).webp" alt="Products coming soon">
                    <div>
                        <h2>The collection is getting ready.</h2>
                        <p>New products will appear here soon.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

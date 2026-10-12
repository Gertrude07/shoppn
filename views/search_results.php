<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/ProductController.php';

$query = trim(strip_tags($_GET['user_query'] ?? ''));
$controller = new ProductController();
$products = $query === '' ? [] : $controller->searchProducts($query);
?>
<?php require __DIR__ . '/layout/header.php'; ?>

<section class="catalog-page">
    <h1>Search results</h1>
    <?php if ($query === ''): ?>
        <p>Enter a search term to find products.</p>
    <?php elseif (!$products): ?>
        <p>No products matched <strong><?= htmlspecialchars($query) ?></strong>.</p>
    <?php else: ?>
        <p><?= count($products) ?> result<?= count($products) === 1 ? '' : 's' ?> for <strong><?= htmlspecialchars($query) ?></strong>.</p>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <img src="<?= BASE_URL ?>/images/products/<?= rawurlencode($product['product_image'] ?: 'OIP.webp') ?>" alt="<?= htmlspecialchars($product['product_title']) ?>">
                    <div class="product-card-body">
                        <p><?= htmlspecialchars($product['brand_name']) ?> / <?= htmlspecialchars($product['cat_name']) ?></p>
                        <h2><?= htmlspecialchars($product['product_title']) ?></h2>
                        <p><?= htmlspecialchars($product['product_desc']) ?></p>
                        <strong><?= format_price($product['product_price'], $product['product_currency'] ?? 'USD') ?></strong>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

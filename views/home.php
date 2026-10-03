<?php
require_once __DIR__ . '/../controllers/ProductController.php';
$homeProducts = is_logged_in() ? (new ProductController())->getAllProducts() : [];
?>
<?php require __DIR__ . '/layout/header.php'; ?>

<?php if (is_logged_in()): ?>
<section class="catalog-page">
	<div class="catalog-heading">
		<div>
			<p class="eyebrow">Your Shoppn catalogue</p>
			<h1>Find something for every day</h1>
			<p>Browse the latest products and discover something you will love.</p>
		</div>
		<a class="catalog-account-link" href="<?= BASE_URL ?>/views/account/my_account.php">My account</a>
	</div>

	<?php if ($homeProducts): ?>
		<div class="product-grid">
			<?php foreach ($homeProducts as $product): ?>
				<article class="product-card">
					<?php if (!empty($product['product_image'])): ?>
						<img src="<?= BASE_URL ?>/images/products/<?= rawurlencode($product['product_image']) ?>" alt="<?= htmlspecialchars($product['product_title']) ?>">
					<?php else: ?>
						<div class="product-image-placeholder">No image</div>
					<?php endif; ?>
					<div class="product-card-body">
						<p class="product-card-meta"><?= htmlspecialchars($product['brand_name']) ?> · <?= htmlspecialchars($product['cat_name']) ?></p>
						<h2><?= htmlspecialchars($product['product_title']) ?></h2>
						<p class="product-card-description"><?= htmlspecialchars($product['product_desc'] ?? '') ?></p>
						<strong class="product-card-price"><?= format_price($product['product_price'], $product['product_currency'] ?? 'USD') ?></strong>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	<?php else: ?>
		<div class="catalog-empty">
			<h2>Your catalogue is ready for its first products.</h2>
			<p>Check back soon for new products.</p>
		</div>
	<?php endif; ?>
</section>
<?php else: ?>
<section class="home-welcome">
	<div class="home-welcome-copy">
		<?php if (is_logged_in()): ?>
			<p class="eyebrow">Welcome back</p>
			<h1>Welcome back, <?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?></h1>
			<p>Continue exploring Shoppn and manage your account whenever you are ready.</p>
			<a class="home-link" href="<?= BASE_URL ?>/views/account/my_account.php">View your account</a>
		<?php else: ?>
			<p class="eyebrow">A simpler way to shop</p>
			<h1>Welcome to Shoppn</h1>
			<p>Find something you will love, with a friendly shopping experience made for everyday essentials and thoughtful finds.</p>
			<a class="home-link" href="<?= BASE_URL ?>/views/register.php">Create your account</a>
		<?php endif; ?>
	</div>
	<img class="home-welcome-image" src="<?= BASE_URL ?>/images/home-shopping.jpg" alt="Shopping bags on a shop counter">
</section>
<?php endif; ?>

<?php if (!is_logged_in()): ?>
<section class="home-highlights" aria-label="Shoppn highlights">
	<article class="home-highlight">
		<img src="<?= BASE_URL ?>/images/home-products.jpg" alt="Products arranged for shopping">
		<div>
			<h2>Made for your everyday</h2>
			<p>Browse at your own pace and enjoy a warm, welcoming place to start your next shop.</p>
		</div>
	</article>
	<article class="home-highlight home-highlight-soft">
		<img src="<?= BASE_URL ?>/images/home-packages.jpg" alt="A product ready to be enjoyed">
		<div>
			<h2>Ready when you are</h2>
			<p>Register today and come back whenever you are ready to explore Shoppn.</p>
		</div>
	</article>
</section>
<?php endif; ?>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

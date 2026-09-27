<?php require __DIR__ . '/layout/header.php'; ?>

<section class="home-welcome">
	<div class="home-welcome-copy">
		<p class="eyebrow">A simpler way to shop</p>
		<h1>Welcome to Shoppn</h1>
		<p>Find something you will love, with a friendly shopping experience made for everyday essentials and thoughtful finds.</p>
		<a class="home-link" href="<?= BASE_URL ?>/views/register.php">Create your account</a>
	</div>
	<img class="home-welcome-image" src="<?= BASE_URL ?>/images/home-shopping.jpg" alt="Shopping bags on a shop counter">
</section>

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

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

<?php
require __DIR__ . '/../core/core.php';
require_customer();
require __DIR__ . '/layout/header.php';
?>

<section class="checkout-page">
    <h1>Payment Successful</h1>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <p>Your order has been placed successfully.</p>
    <p><a href="<?= BASE_URL ?>/views/shop.php">Continue shopping</a></p>
</section>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

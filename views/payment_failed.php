<?php
require __DIR__ . '/../core/core.php';
require_customer();
?>
<?php require __DIR__ . '/layout/header.php'; ?>
<section class="checkout-page">
    <h1>Payment Failed</h1>
    <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error'] ?? 'Payment could not be completed.') ?></div>
    <?php unset($_SESSION['error']); ?>
    <p><a href="<?= BASE_URL ?>/views/payment.php">Try again</a></p>
</section>
<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

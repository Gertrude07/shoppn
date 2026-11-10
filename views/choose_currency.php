<?php
require __DIR__ . '/../core/core.php';
require_customer();

$current = preferred_currency();
$returnTo = (string) ($_GET['return'] ?? '');
?>
<?php require __DIR__ . '/layout/header.php'; ?>

<section class="checkout-page">
    <h1>Choose your currency</h1>
    <p class="page-subtitle">Every price in the shop will be shown in the currency you pick, even when the seller listed it in another one. You can change this at any time from the menu.</p>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form class="checkout-box" method="POST" action="<?= BASE_URL ?>/actions/set_currency_action.php">
        <input type="hidden" name="return" value="<?= htmlspecialchars($returnTo) ?>">
        <div class="currency-options">
            <?php foreach (SUPPORTED_CURRENCIES as $code): ?>
                <label class="currency-option">
                    <input type="radio" name="currency" value="<?= htmlspecialchars($code) ?>" required <?= $current === $code ? 'checked' : '' ?>>
                    <span><strong><?= htmlspecialchars($code) ?></strong> (<?= htmlspecialchars(get_currency_symbol($code)) ?>) <?= htmlspecialchars(currency_name($code)) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="button primary full-width">Save currency</button>
        <p class="rates-note">Payments are charged in <?= htmlspecialchars(SHOP_CURRENCY) ?>. If you pick another currency, you will see the exact <?= htmlspecialchars(SHOP_CURRENCY) ?> amount before you pay.</p>
    </form>
</section>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

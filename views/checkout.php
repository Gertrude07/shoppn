<?php
require __DIR__ . '/../core/core.php';
require_customer();
require __DIR__ . '/../controllers/CartController.php';

$controller = new CartController();
$customerId = (int) ($_SESSION['customer_id'] ?? 0);
$items = $controller->getCartItems($customerId);
$rates = get_rates_array();
$display = preferred_currency() ?? SHOP_CURRENCY;
$view = cart_view($items, $display, $rates);
$chargeView = cart_view($items, SHOP_CURRENCY, $rates);
if (!empty($items) && !$chargeView['ok']) {
    $_SESSION['error'] = 'Exchange rates are unavailable right now, so prices cannot be converted. Please try again in a few minutes.';
    redirect(BASE_URL . '/views/cart.php');
}
?>
<?php require __DIR__ . '/layout/header.php'; ?>

<section class="checkout-page">
    <h1>Checkout</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="catalog-empty">
            <h2>Your cart is empty.</h2>
            <p><a href="<?= BASE_URL ?>/views/shop.php">Go to shop</a></p>
        </div>
    <?php else: ?>
        <div class="checkout-layout">
            <div class="checkout-summary">
                <h2>Order Summary</h2>
                <ul>
                    <?php foreach ($view['lines'] as $line): ?>
                        <li>
                            <span><?= htmlspecialchars($line['item']['product_title']) ?> × <?= (int) $line['qty'] ?></span>
                            <span><?= format_price($line['line'], $display) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="checkout-total"><strong>Total:</strong> <?= format_price($view['total'], $display) ?></p>
                <?php if ($display !== SHOP_CURRENCY): ?>
                    <p class="rates-note">You will be charged <strong><?= format_price($chargeView['total'], SHOP_CURRENCY) ?></strong>, because payments are processed in <?= htmlspecialchars(SHOP_CURRENCY) ?>.</p>
                <?php endif; ?>
                <?php if ($view['converted'] || $display !== SHOP_CURRENCY): ?>
                    <p class="rates-note"><?= htmlspecialchars(rates_note()) ?></p>
                <?php endif; ?>
            </div>

            <div class="checkout-box">
                <h2>Customer Details</h2>
                <p><strong>Name:</strong> <?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['customer_email'] ?? '') ?></p>
                <br>
                <a class="button primary full-width" href="<?= BASE_URL ?>/views/payment.php">Proceed to Payment</a>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

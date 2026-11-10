<?php
require __DIR__ . '/../core/core.php';
require_customer();
require __DIR__ . '/../controllers/CartController.php';

$controller = new CartController();
$customerId = (int) ($_SESSION['customer_id'] ?? 0);
$items = $controller->getCartItems($customerId);
if (empty($items)) {
    $_SESSION['error'] = 'Your cart is empty.';
    redirect(BASE_URL . '/views/cart.php');
}

// The customer sees prices in their chosen currency. The charge is made in SHOP_CURRENCY.
$rates = get_rates_array();
$display = preferred_currency() ?? SHOP_CURRENCY;
$view = cart_view($items, $display, $rates);
$chargeView = cart_view($items, SHOP_CURRENCY, $rates);
if (!$view['ok'] || !$chargeView['ok']) {
    $_SESSION['error'] = 'Exchange rates are unavailable right now, so prices cannot be converted. Please try again in a few minutes.';
    redirect(BASE_URL . '/views/cart.php');
}

// Paystack works in the smallest unit (pesewas for GHS). The action checks the verified amount against this,
// and re-prices the cart with the same exchange rates so a rate change cannot break a payment in progress.
$_SESSION['checkout_total_kobo'] = (int) round($chargeView['total'] * 100);
$_SESSION['checkout_rates'] = is_array($rates) ? $rates : [];
$email = (string) ($_SESSION['customer_email'] ?? '');
$publicKey = get_paystack_key('public');
?>
<?php require __DIR__ . '/layout/header.php'; ?>

<section class="checkout-page">
    <h1>Payment</h1>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if ($publicKey === ''): ?>
        <div class="alert alert-error">Payments are not configured yet. Add your Paystack keys in core/paystack_cred.php.</div>
    <?php endif; ?>
    <div class="checkout-box">
        <h2>Order Summary</h2>
        <ul>
            <?php foreach ($view['lines'] as $line): ?>
                <li>
                    <span><?= htmlspecialchars($line['item']['product_title']) ?> × <?= (int) $line['qty'] ?> @ <?= format_price($line['unit'], $display) ?></span>
                    <span><?= format_price($line['line'], $display) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Total:</strong> <?= format_price($view['total'], $display) ?></p>
        <?php if ($display !== SHOP_CURRENCY): ?>
            <p><strong>You will be charged:</strong> <?= format_price($chargeView['total'], SHOP_CURRENCY) ?></p>
            <p class="rates-note">Payments are processed in <?= htmlspecialchars(SHOP_CURRENCY) ?>. <?= htmlspecialchars(rates_note()) ?></p>
        <?php elseif ($view['converted']): ?>
            <p class="rates-note"><?= htmlspecialchars(rates_note()) ?></p>
        <?php endif; ?>
        <button id="payButton" type="button" class="button primary full-width"<?= $publicKey === '' ? ' disabled' : '' ?>>Pay Now</button>
    </div>
</section>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php if ($publicKey !== ''): ?>
<script src="https://js.paystack.co/v2/inline.js"></script>
<script>
document.getElementById('payButton').addEventListener('click', function () {
    var button = this;
    var popup = new PaystackPop();
    popup.newTransaction({
        key: <?= json_encode($publicKey, JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        email: <?= json_encode($email, JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        amount: <?= (int) $_SESSION['checkout_total_kobo'] ?>,
        currency: <?= json_encode(SHOP_CURRENCY) ?>,
        onSuccess: function (transaction) {
            // Hand the reference to the server, which verifies it with Paystack before recording anything.
            button.disabled = true;
            button.textContent = 'Confirming payment...';
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = <?= json_encode(BASE_URL . '/actions/process_payment_action.php', JSON_HEX_TAG | JSON_HEX_AMP) ?>;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'reference';
            input.value = transaction.reference;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        },
        onCancel: function () { alert('Payment cancelled.'); },
        onError: function (error) { alert('Payment could not start: ' + (error && error.message ? error.message : 'unknown error')); }
    });
});
</script>
<?php endif; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

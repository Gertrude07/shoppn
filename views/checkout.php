<?php
require __DIR__ . '/../core/core.php';
require_customer();
require __DIR__ . '/../controllers/CartController.php';

$controller = new CartController();
$customerId = (int) ($_SESSION['customer_id'] ?? 0);
$totals = $controller->getCartTotals($customerId);
$items = $totals['items'];
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
                    <?php foreach ($items as $item): ?>
                        <?php
                            $unitPrice = (float) ($item['product_price'] ?? 0);
                            $qty = (int) ($item['qty'] ?? 1);
                            $currency = (string) ($item['product_currency'] ?? 'USD');
                        ?>
                        <li>
                            <span><?= htmlspecialchars($item['product_title']) ?> × <?= $qty ?></span>
                            <span><?= format_price($unitPrice * $qty, $currency) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="checkout-total"><strong>Total:</strong> <?= format_price((float) $totals['total'], 'USD') ?></p>
            </div>

            <div class="checkout-box">
                <h2>Customer Details</h2>
                <p><strong>Name:</strong> <?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['customer_email'] ?? '') ?></p>
                <br>
                <form method="POST" action="<?= BASE_URL ?>/actions/process_payment_action.php">
                    <button type="submit" class="button primary full-width">Proceed to Payment</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

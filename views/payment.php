<?php
require __DIR__ . '/../core/core.php';
require_customer();
require __DIR__ . '/../controllers/CartController.php';

$controller = new CartController();
$customerId = (int) ($_SESSION['customer_id'] ?? 0);
$totals = $controller->getCartTotals($customerId);
?>
<?php require __DIR__ . '/layout/header.php'; ?>

<section class="checkout-page">
    <h1>Payment</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="checkout-box">
        <p><strong>Customer:</strong> <?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?></p>
        <p><strong>Amount:</strong> <?= format_price((float) $totals['total'], 'USD') ?></p>

        <form method="POST" action="<?= BASE_URL ?>/actions/process_payment_action.php">
            <label for="payment-method">Payment Method</label>
            <select id="payment-method" name="payment_method" required>
                <option value="Card">Card</option>
                <option value="Mobile Money">Mobile Money</option>
                <option value="Cash">Cash</option>
            </select>
            <button type="submit" class="button primary full-width">Pay Now</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

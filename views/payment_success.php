<?php
require __DIR__ . '/../core/core.php';
require_customer();
require __DIR__ . '/../controllers/CartController.php';

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
$controller = new CartController();
$order = $orderId ? $controller->getOrderById($orderId) : false;
$customerId = (int) ($_SESSION['customer_id'] ?? 0);
if (!$order || (int) $order['customer_id'] !== $customerId) {
    $_SESSION['error'] = 'Invoice not found.';
    redirect(BASE_URL . '/views/cart.php');
}
?>
<?php require __DIR__ . '/layout/header.php'; ?>
<section class="checkout-page">
    <h1>Payment Successful</h1>
    <p>Your order has been placed successfully.</p>
    <p><strong>Order number:</strong> <?= (int) $order['order_id'] ?></p>
    <p><strong>Invoice:</strong> <?= (int) $order['invoice_no'] ?></p>
    <p><strong>Order date:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
    <p><strong>Payment reference:</strong> <?= htmlspecialchars((string) $order['reference']) ?></p>
    <ul>
        <?php foreach ($order['items'] as $item): ?>
            <?php $qty = (int) $item['qty']; $unitPrice = (float) $item['product_price']; $currency = $item['product_currency'] ?? SHOP_CURRENCY; ?>
            <li>
                <?= htmlspecialchars($item['product_title']) ?> × <?= $qty ?>
                @ <?= format_price($unitPrice, $currency) ?>
                — <?= format_price($unitPrice * $qty, $currency) ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <p><strong>Total paid:</strong> <?= format_price((float) $order['amount'], SHOP_CURRENCY) ?></p>
    <p class="rates-note">Item prices above are the seller's listed prices. Your card was charged in <?= htmlspecialchars(SHOP_CURRENCY) ?> at the exchange rate shown when you paid.</p>
    <p><a href="<?= BASE_URL ?>/views/shop.php">Continue shopping</a></p>
</section>
<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>

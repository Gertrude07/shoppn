<?php
require __DIR__ . '/../core/core.php';
require_customer();
require __DIR__ . '/../controllers/CartController.php';

$controller = new CartController();
$customerId = (int) ($_SESSION['customer_id'] ?? 0);
$items = $controller->getCartItems($customerId);
$display = preferred_currency() ?? SHOP_CURRENCY;
$view = cart_view($items, $display, get_rates_array());
?>
<?php require __DIR__ . '/layout/header.php'; ?>

<section class="cart-page">
    <h1>Your Cart</h1>
    <p class="page-subtitle">Change a quantity and your total updates straight away.</p>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="catalog-empty">
            <h2>Your cart is empty.</h2>
            <p>Looks like you have not added anything yet.</p>
            <p><a class="button primary" href="<?= BASE_URL ?>/views/shop.php">Continue shopping</a></p>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($view['lines'] as $line): ?>
                    <?php
                        $item = $line['item'];
                        $qty = $line['qty'];
                        // If a rate is missing, fall back to the seller's own price and currency for this row.
                        $currency = $line['unit'] === null ? $line['native_currency'] : $display;
                        $unitPrice = $line['unit'] === null ? (float) $item['product_price'] : $line['unit'];
                        $lineTotal = $line['unit'] === null ? $unitPrice * $qty : $line['line'];
                    ?>
                    <article class="cart-item" data-cart-row data-unit-price="<?= htmlspecialchars((string) $unitPrice) ?>" data-currency="<?= htmlspecialchars($currency) ?>" data-symbol="<?= htmlspecialchars(get_currency_symbol($currency)) ?>">
                        <div class="cart-item-image">
                            <?php if (!empty($item['product_image'])): ?>
                                <img class="cart-product-img" width="110" height="110" src="<?= BASE_URL ?>/images/products/<?= rawurlencode($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_title']) ?>">
                            <?php else: ?>
                                <div class="cart-image-placeholder">No image</div>
                            <?php endif; ?>
                        </div>

                        <div class="cart-item-info">
                            <h3><?= htmlspecialchars($item['product_title']) ?></h3>
                            <p class="cart-item-brand"><?= htmlspecialchars($item['brand_name'] ?? '') ?></p>
                            <p class="cart-item-unit"><?= price_html($item['product_price'], $line['native_currency']) ?> each</p>

                            <div class="cart-item-controls">
                                <form method="POST" action="<?= BASE_URL ?>/actions/update_qty_action.php" class="qty-form">
                                    <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                                    <div class="qty-stepper">
                                        <button type="button" class="qty-btn" data-step="-1" aria-label="Decrease quantity">&minus;</button>
                                        <input type="number" name="qty" min="1" value="<?= $qty ?>" data-original="<?= $qty ?>" aria-label="Quantity">
                                        <button type="button" class="qty-btn" data-step="1" aria-label="Increase quantity">+</button>
                                    </div>
                                    <button type="submit" class="update-btn">Update</button>
                                </form>

                                <form method="POST" action="<?= BASE_URL ?>/actions/remove_from_cart_action.php">
                                    <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                                    <button type="submit" class="link-danger">Remove</button>
                                </form>
                            </div>
                        </div>

                        <div class="cart-item-total" data-line-total><?= format_price($lineTotal, $currency) ?></div>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="cart-summary" data-cart-summary data-symbol="<?= htmlspecialchars(get_currency_symbol($display)) ?>" data-currency="<?= htmlspecialchars($display) ?>"<?= $view['ok'] ? '' : ' data-no-total' ?>>
                <h2>Order summary</h2>
                <p class="summary-row"><span>Items</span> <strong data-summary-qty><?= (int) $view['qty'] ?></strong></p>
                <p class="summary-row summary-total"><span>Subtotal</span> <strong data-summary-total><?= $view['ok'] ? format_price($view['total'], $display) : 'Unavailable' ?></strong></p>
                <?php if (!$view['ok']): ?>
                    <div class="alert alert-error">Exchange rates are unavailable right now, so prices cannot be converted. Please try again in a few minutes.</div>
                <?php elseif ($view['converted']): ?>
                    <p class="rates-note"><?= htmlspecialchars(rates_note()) ?></p>
                <?php endif; ?>
                <div class="cart-actions">
                    <?php if ($view['ok']): ?>
                        <a class="button primary" href="<?= BASE_URL ?>/views/checkout.php">Checkout</a>
                    <?php endif; ?>
                    <a class="button secondary" href="<?= BASE_URL ?>/views/shop.php">Continue shopping</a>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
<script src="<?= BASE_URL ?>/js/cart.js"></script>

<?php
require __DIR__ . '/../core/core.php';
require_customer();
require __DIR__ . '/../controllers/CartController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/cart.php');
}

$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
$customerId = (int) ($_SESSION['customer_id'] ?? 0);

if ($customerId < 1 || $productId === false || $productId < 1) {
    $_SESSION['error'] = 'Please select a valid product.';
    redirect(BASE_URL . '/views/cart.php');
}

$cartController = new CartController();
if ($cartController->removeFromCart($customerId, $productId)) {
    $_SESSION['success'] = 'Item removed from cart.';
} else {
    $_SESSION['error'] = 'Unable to remove item from cart.';
}

redirect(BASE_URL . '/views/cart.php');

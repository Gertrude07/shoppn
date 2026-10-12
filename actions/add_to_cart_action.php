<?php
require __DIR__ . '/../core/core.php';
require_customer();
require __DIR__ . '/../controllers/CartController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/shop.php');
}

$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
$qty = filter_var($_POST['qty'] ?? 1, FILTER_VALIDATE_INT);
$customerId = (int) ($_SESSION['customer_id'] ?? 0);

if ($customerId < 1 || $productId === false || $productId < 1 || $qty === false || $qty < 1) {
    $_SESSION['error'] = 'Please select a valid product to add.';
    redirect(BASE_URL . '/views/shop.php');
}

$cartController = new CartController();
if ($cartController->addToCart($customerId, $productId, $qty)) {
    $_SESSION['success'] = 'Product added to cart.';
} else {
    $_SESSION['error'] = 'Unable to add product to cart.';
}

redirect(BASE_URL . '/views/shop.php');

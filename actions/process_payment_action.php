<?php
require __DIR__ . '/../core/core.php';
require_customer();
require __DIR__ . '/../controllers/CartController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/cart.php');
}

$customerId = (int) ($_SESSION['customer_id'] ?? 0);
if ($customerId < 1) {
    $_SESSION['error'] = 'Please log in to continue.';
    redirect(BASE_URL . '/views/login.php');
}

$cartController = new CartController();
$totals = $cartController->getCartTotals($customerId);
if (empty($totals['items'])) {
    $_SESSION['error'] = 'Your cart is empty.';
    redirect(BASE_URL . '/views/cart.php');
}

if ($cartController->clearCart($customerId)) {
    $_SESSION['success'] = 'Payment processed successfully.';
    redirect(BASE_URL . '/views/payment_success.php');
}

$_SESSION['error'] = 'Payment could not be processed.';
redirect(BASE_URL . '/views/payment.php');

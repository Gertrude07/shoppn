<?php
require __DIR__ . '/../core/core.php';
require_customer();
require __DIR__ . '/../controllers/CartController.php';

// Last-resort safety net: any unexpected crash ends on the failure page with the payment reference,
// never on a blank "HTTP 500" page after the customer may already have been charged.
set_exception_handler(function ($exception) use (&$reference) {
    error_log('process_payment_action crashed: ' . get_class($exception) . ': ' . $exception->getMessage()
        . ' in ' . $exception->getFile() . ':' . $exception->getLine());
    $message = 'Something went wrong while recording your order.';
    if (!empty($reference)) {
        $message .= ' If you were charged, please keep your payment reference (' . $reference . ') and contact support.';
    }
    if (APP_DEBUG) {
        $message .= ' [Debug: ' . $exception->getMessage() . ']';
    }
    $_SESSION['error'] = $message;
    redirect(BASE_URL . '/views/payment_failed.php');
});

function payment_fail($message) {
    $_SESSION['error'] = $message;
    redirect(BASE_URL . '/views/payment_failed.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['ref'])) {
    redirect(BASE_URL . '/views/payment.php');
}

$customerId = (int) ($_SESSION['customer_id'] ?? 0);
if ($customerId < 1) {
    $_SESSION['error'] = 'Please log in to continue.';
    redirect(BASE_URL . '/views/login.php');
}

$reference = trim((string) ($_POST['reference'] ?? $_GET['ref'] ?? ''));
$expectedAmount = filter_var($_SESSION['checkout_total_kobo'] ?? null, FILTER_VALIDATE_INT);
$secretKey = get_paystack_key('secret');
$rateSnapshot = $_SESSION['checkout_rates'] ?? null;
$cartController = new CartController();

// A refresh or double submit of a reference we already recorded goes straight to its invoice.
if ($reference !== '') {
    $existingOrderId = $cartController->getOrderIdByReference($customerId, $reference);
    if ($existingOrderId) {
        redirect(BASE_URL . '/views/payment_success.php?order_id=' . (int) $existingOrderId);
    }
}

if (!preg_match('/^[A-Za-z0-9._=\-]{1,100}$/', $reference) || $expectedAmount === false || $expectedAmount < 1 || !$secretKey || !is_array($rateSnapshot)) {
    payment_fail('Payment could not be verified.');
}

// Verify the transaction with Paystack from the server. The browser callback is never trusted on its own.
$httpResponse = http_get('https://api.paystack.co/transaction/verify/' . rawurlencode($reference), [
    'Authorization: Bearer ' . $secretKey,
    'Content-Type: application/json',
], 15);
$responseBody = $httpResponse['body'] ?? null;
$httpStatus = (int) ($httpResponse['status'] ?? 0);
$curlError = $httpResponse ? '' : 'Could not reach Paystack from this server.';

$response = $responseBody ? json_decode($responseBody, true) : null;
$authorization = is_array($response['data'] ?? null) ? $response['data'] : null;
$verifiedAmount = $authorization['amount'] ?? null;
$paystackEmail = (string) ($authorization['customer']['email'] ?? '');
$sessionEmail = (string) ($_SESSION['customer_email'] ?? '');

if ($curlError || $httpStatus < 200 || $httpStatus >= 300 || !$authorization
    || ($response['status'] ?? false) !== true
    || ($authorization['status'] ?? '') !== 'success'
    || !is_numeric($verifiedAmount) || (int) $verifiedAmount !== $expectedAmount
    || strtoupper((string) ($authorization['currency'] ?? '')) !== SHOP_CURRENCY
    || strcasecmp($paystackEmail, $sessionEmail) !== 0) {
    error_log($curlError ?: 'Paystack verification failed for reference ' . $reference);
    payment_fail('Payment could not be verified.');
}

// The cart must still match what the customer was shown when the payment page loaded.
// It is re-priced with the exchange rates saved when the payment page loaded, not today's rates.
$currentView = cart_view($cartController->getCartItems($customerId), SHOP_CURRENCY, $rateSnapshot);
if (!$currentView['ok'] || (int) round($currentView['total'] * 100) !== $expectedAmount) {
    error_log('Cart changed during payment for reference ' . $reference);
    payment_fail('Your cart changed while you were paying, so the order was not recorded. Please keep your payment reference (' . $reference . ') and contact support.');
}

$orderId = $cartController->processPayment(
    $customerId,
    get_ip(),
    $reference,
    $expectedAmount / 100
);

if ($orderId === false) {
    payment_fail('Your payment was received, but the order could not be recorded. Please keep your payment reference (' . $reference . ') and contact support.');
}

// Confirmation email. A mail failure must never undo a recorded order.
$customerEmail = filter_var($sessionEmail, FILTER_VALIDATE_EMAIL);
$order = $cartController->getOrderById($orderId);
if ($customerEmail && $order && function_exists('mail')) {
    $lines = [
        'Thank you for your order at Shoppn.',
        '',
        'Order number: ' . (int) $order['order_id'],
        'Invoice number: ' . (int) $order['invoice_no'],
        'Date: ' . $order['order_date'],
        'Payment reference: ' . $order['reference'],
        '',
        'Items:',
    ];
    foreach ($order['items'] as $item) {
        $lines[] = '- ' . $item['product_title'] . ' x ' . (int) $item['qty']
            . ' at ' . number_format((float) $item['product_price'], 2);
    }
    $lines[] = '';
    $lines[] = 'Total paid: ' . SHOP_CURRENCY . ' ' . number_format((float) $order['amount'], 2);

    $host = preg_replace('/[^A-Za-z0-9.\-]/', '', (string) ($_SERVER['SERVER_NAME'] ?? 'localhost')) ?: 'localhost';
    $sent = @mail(
        $customerEmail,
        'Shoppn order confirmation #' . (int) $order['invoice_no'],
        implode("\r\n", $lines),
        'From: Shoppn <no-reply@' . $host . '>'
    );
    if (!$sent) {
        error_log('Unable to send order confirmation email for order ' . $orderId);
    }
}

unset($_SESSION['checkout_total_kobo'], $_SESSION['checkout_rates']);
redirect(BASE_URL . '/views/payment_success.php?order_id=' . (int) $orderId);

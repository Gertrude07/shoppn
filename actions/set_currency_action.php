<?php
require __DIR__ . '/../core/core.php';
require_customer();

// Only allow sending the customer back to a page inside this shop.
function safe_return_url($url) {
    $url = (string) $url;
    // The (?!/) stops "//evil.com" from passing when BASE_URL is empty.
    $pattern = '#^' . preg_quote(BASE_URL, '#') . '/(?!/)[A-Za-z0-9_./\-]*(\?[A-Za-z0-9_=&%.\-]*)?$#';
    return preg_match($pattern, $url) ? $url : BASE_URL . '/views/shop.php';
}

$currency = strtoupper(trim((string) ($_POST['currency'] ?? '')));
$returnTo = safe_return_url($_POST['return'] ?? '');

if (!set_preferred_currency($currency)) {
    $_SESSION['error'] = 'Please choose one of the available currencies.';
    redirect(BASE_URL . '/views/choose_currency.php');
}

redirect($returnTo);

<?php
session_start();
date_default_timezone_set('UTC');

// Set to true only while testing. Payment errors then show their technical detail on the failure page.
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}

// Payments are charged in this currency (the one the Paystack account settles in).
define('SHOP_CURRENCY', 'GHS');
define('BASE_URL', '/~gertrude.akagbo/E_Commerce_Labs/shoppn');

require_once __DIR__ . '/db_class.php';

function get_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function is_logged_in() {
    return isset($_SESSION['customer_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 1;
}

function require_login() {
    if (!is_logged_in()) {
        redirect(BASE_URL . '/views/login.php');
    }
}

function require_admin() {
    if (!is_admin()) {
        $_SESSION['error'] = 'You do not have permission to view that page.';
        redirect(BASE_URL . '/index.php');
    }
}

function require_customer() {
    require_login();
    if (is_admin()) {
        $_SESSION['error'] = 'Admin accounts manage the store and do not have a cart.';
        redirect(BASE_URL . '/views/admin/dashboard.php');
    }
}

function get_product_upload_paths() {
    $projectDir = rtrim(__DIR__ . '/../images/products', '/\\') . DIRECTORY_SEPARATOR;
    $candidates = [$projectDir];

    if (DIRECTORY_SEPARATOR === '\\') {
        $candidates[] = 'C:' . DIRECTORY_SEPARATOR . 'shoppn_uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
    }

    foreach ($candidates as $directory) {
        if (!is_dir($directory)) {
            @mkdir($directory, 0777, true);
        }

        if (is_dir($directory) && is_writable($directory)) {
            return [
                'storage' => $directory,
                'display' => $projectDir,
            ];
        }
    }

    return [
        'storage' => $projectDir,
        'display' => $projectDir,
    ];
}

function get_uploaded_mime_type($path) {
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = finfo_file($finfo, $path);
            finfo_close($finfo);
            return $mimeType ?: false;
        }
    }

    if (function_exists('mime_content_type')) {
        $mimeType = mime_content_type($path);
        if ($mimeType) {
            return $mimeType;
        }
    }

    if (function_exists('getimagesize')) {
        $imageInfo = @getimagesize($path);
        return $imageInfo['mime'] ?? false;
    }

    return false;
}

function mirror_uploaded_product_image($sourcePath, $filename) {
    $projectDir = rtrim(__DIR__ . '/../images/products', '/\\') . DIRECTORY_SEPARATOR;

    if (is_dir($projectDir) && is_writable($projectDir)) {
        $destPath = $projectDir . basename($filename);
        @copy($sourcePath, $destPath);
    }
}

function get_currency_symbol($currency) {
    $currency = strtoupper(trim((string) $currency));
    $symbols = [
        'USD' => '$',
        'GHS' => 'GH₵',
        'NGN' => '₦',
        'EUR' => '€',
    ];

    return $symbols[$currency] ?? $currency;
}

function format_price($amount, $currency = SHOP_CURRENCY) {
    $amount = (float) $amount;
    $currency = strtoupper(trim((string) $currency));
    $symbol = get_currency_symbol($currency);
    $formatted = number_format($amount, 2);

    if ($currency === 'GHS') {
        return $symbol . ' ' . $formatted;
    }

    return $symbol . $formatted;
}

// Paystack keys come from core/paystack_cred.php (gitignored), or from environment variables.
if (is_file(__DIR__ . '/paystack_cred.php')) {
    require_once __DIR__ . '/paystack_cred.php';
}

function get_paystack_key($type) {
    if ($type === 'public') {
        $key = defined('PAYSTACK_PUBLIC_KEY') ? PAYSTACK_PUBLIC_KEY : (getenv('PAYSTACK_PUBLIC_KEY') ?: '');
    } else {
        $key = defined('PAYSTACK_SECRET_KEY') ? PAYSTACK_SECRET_KEY : (getenv('PAYSTACK_SECRET_KEY') ?: '');
    }
    // The example placeholder keys (pk_test_xxxx...) count as not configured.
    return preg_match('/x{6,}/i', (string) $key) ? '' : (string) $key;
}

// Simple GET request. Uses cURL when available and PHP's own web streams otherwise.
// Returns ['status' => int, 'body' => string], or null when the server could not be reached.
function http_get($url, $headers = [], $timeout = 15) {
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
        ]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if ($body === false) {
            error_log('http_get failed for ' . parse_url($url, PHP_URL_HOST) . ': ' . $error);
            return null;
        }
        return ['status' => $status, 'body' => (string) $body];
    }

    if (!filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
        error_log('http_get: neither cURL nor allow_url_fopen is available on this server.');
        return null;
    }
    $context = stream_context_create(['http' => [
        'method' => 'GET',
        'header' => implode("\r\n", $headers),
        'timeout' => $timeout,
        'ignore_errors' => true,
    ]]);
    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        error_log('http_get (streams) failed for ' . parse_url($url, PHP_URL_HOST));
        return null;
    }
    $status = 0;
    foreach (($http_response_header ?? []) as $line) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
            $status = (int) $m[1];
        }
    }
    return ['status' => $status, 'body' => $body];
}

require_once __DIR__ . '/currency.php';

// Every page requires a logged-in user, except the login/register pages and their actions.
$publicPages = ['login.php', 'register.php', 'login_action.php', 'register_action.php', 'logout.php'];
if (PHP_SAPI !== 'cli' && !is_logged_in() && !in_array(basename($_SERVER['SCRIPT_NAME'] ?? ''), $publicPages, true)) {
    $_SESSION['error'] = 'Please log in to continue.';
    redirect(BASE_URL . '/views/login.php');
}
// Every customer must choose one display currency before browsing, shopping or paying.
$currencyPages = ['choose_currency.php', 'set_currency_action.php', 'logout.php'];
if (PHP_SAPI !== 'cli' && is_logged_in() && !is_admin() && preferred_currency() === null
    && !in_array(basename($_SERVER['SCRIPT_NAME'] ?? ''), $currencyPages, true)) {
    $returnTo = ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' ? '?return=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '') : '';
    redirect(BASE_URL . '/views/choose_currency.php' . $returnTo);
}
?>

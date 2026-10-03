<?php
session_start();
date_default_timezone_set('UTC');


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

function get_product_upload_paths() {
    $projectDir = rtrim(__DIR__ . '/../images/products', '/\\') . DIRECTORY_SEPARATOR;
    $externalDir = 'C:' . DIRECTORY_SEPARATOR . 'shoppn_uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
    $candidates = [$projectDir, $externalDir];

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

function format_price($amount, $currency = 'USD') {
    $amount = (float) $amount;
    $currency = strtoupper(trim((string) $currency));
    $symbol = get_currency_symbol($currency);
    $formatted = number_format($amount, 2);

    if ($currency === 'GHS') {
        return $symbol . ' ' . $formatted;
    }

    return $symbol . $formatted;
}
?>

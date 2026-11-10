<?php
// Multi-currency support.
// Vendors price each product in any supported currency. Each customer picks one display currency,
// and every price they see is converted to it. Payment is always charged in SHOP_CURRENCY
// (the currency the Paystack account settles in), converted from the same rates the customer saw.

define('SUPPORTED_CURRENCIES', ['GHS', 'USD', 'NGN', 'EUR']);

// Free, key-less rates feed (ExchangeRate-API open access). Attribution is shown in the footer.
if (!defined('RATES_API_URL')) {
    define('RATES_API_URL', 'https://open.er-api.com/v6/latest/USD');
}
define('RATES_CACHE_SECONDS', 43200);   // refetch at most every 12 hours
define('RATES_RETRY_SECONDS', 600);     // after a failed fetch, wait 10 minutes before trying again

function currency_name($code) {
    $names = ['GHS' => 'Ghana Cedi', 'USD' => 'US Dollar', 'NGN' => 'Nigerian Naira', 'EUR' => 'Euro'];
    return $names[$code] ?? $code;
}

function fetch_exchange_rates() {
    $response = http_get(RATES_API_URL, [], 8);
    if (!$response) {
        return null;
    }
    $status = $response['status'];
    $json = $response['body'] !== '' ? json_decode($response['body'], true) : null;
    if ($status < 200 || $status >= 300 || !is_array($json) || ($json['result'] ?? '') !== 'success' || !is_array($json['rates'] ?? null)) {
        return null;
    }

    $rates = [];
    foreach (SUPPORTED_CURRENCIES as $code) {
        $value = $json['rates'][$code] ?? null;
        if (!is_numeric($value) || (float) $value <= 0) {
            return null;
        }
        $rates[$code] = (float) $value;
    }

    return ['rates' => $rates, 'updated' => (string) ($json['time_last_update_utc'] ?? '')];
}

// Returns ['rates' => [CODE => units per 1 USD], 'updated' => string] or null when no rates are available at all.
function get_exchange_rates() {
    static $result = false;
    if ($result !== false) {
        return $result;
    }

    $file = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'shoppn_rates.json';
    $data = null;
    if (is_file($file)) {
        $decoded = json_decode((string) @file_get_contents($file), true);
        $data = is_array($decoded) ? $decoded : null;
    }
    // If the temp folder is not writable on this host, fall back to a copy kept in the visitor's session.
    if (!is_array($data) && is_array($_SESSION['shoppn_rates'] ?? null)) {
        $data = $_SESSION['shoppn_rates'];
    }

    $now = time();
    $hasRates = is_array($data) && is_array($data['rates'] ?? null);
    $isFresh = $hasRates && ($now - (int) ($data['fetched_at'] ?? 0)) < RATES_CACHE_SECONDS;
    $recentlyTried = is_array($data) && ($now - (int) ($data['last_attempt'] ?? 0)) < RATES_RETRY_SECONDS;

    if (!$isFresh && !$recentlyTried) {
        $fetched = fetch_exchange_rates();
        $data = is_array($data) ? $data : [];
        $data['last_attempt'] = $now;
        if ($fetched) {
            $data['rates'] = $fetched['rates'];
            $data['updated'] = $fetched['updated'];
            $data['fetched_at'] = $now;
        } else {
            error_log('Could not fetch exchange rates; using cached rates if any.');
        }
        @file_put_contents($file, json_encode($data), LOCK_EX);
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['shoppn_rates'] = $data;
        }
    }

    $result = (is_array($data) && is_array($data['rates'] ?? null))
        ? ['rates' => $data['rates'], 'updated' => (string) ($data['updated'] ?? '')]
        : null;
    return $result;
}

function get_rates_array() {
    $data = get_exchange_rates();
    return $data['rates'] ?? null;
}

// Converts $amount from one currency to another using USD-based rates. Returns null if a rate is missing.
function convert_price($amount, $from, $to, $rates) {
    $from = strtoupper(trim((string) $from));
    $to = strtoupper(trim((string) $to));
    $amount = (float) $amount;
    if ($from === $to) {
        return round($amount, 2);
    }
    if (!is_array($rates) || empty($rates[$from]) || empty($rates[$to])) {
        return null;
    }
    return round($amount / (float) $rates[$from] * (float) $rates[$to], 2);
}

function is_supported_currency($code) {
    return in_array(strtoupper(trim((string) $code)), SUPPORTED_CURRENCIES, true);
}

// The customer's chosen display currency, or null if they have not chosen yet (admins never choose).
function preferred_currency() {
    if (!empty($_SESSION['preferred_currency']) && is_supported_currency($_SESSION['preferred_currency'])) {
        return $_SESSION['preferred_currency'];
    }
    $customerId = (int) ($_SESSION['customer_id'] ?? 0);
    if ($customerId > 0) {
        $cookie = strtoupper((string) ($_COOKIE['shoppn_cur_' . $customerId] ?? ''));
        if (is_supported_currency($cookie)) {
            $_SESSION['preferred_currency'] = $cookie;
            return $cookie;
        }
    }
    return null;
}

function set_preferred_currency($code) {
    $code = strtoupper(trim((string) $code));
    if (!is_supported_currency($code)) {
        return false;
    }
    $_SESSION['preferred_currency'] = $code;
    $customerId = (int) ($_SESSION['customer_id'] ?? 0);
    if ($customerId > 0 && !headers_sent()) {
        // One cookie per customer, so people sharing a browser each keep their own choice.
        setcookie('shoppn_cur_' . $customerId, $code, [
            'expires' => time() + 31536000,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    return true;
}

// A price shown in the customer's currency, with the vendor's listed price as a tooltip when converted.
// Admins and visitors without a chosen currency see the vendor's own currency.
function price_html($amount, $from) {
    $from = strtoupper(trim((string) $from));
    $native = format_price($amount, $from);
    $preferred = preferred_currency();
    if ($preferred === null || $preferred === $from) {
        return htmlspecialchars($native);
    }
    $converted = convert_price($amount, $from, $preferred, get_rates_array());
    if ($converted === null) {
        return htmlspecialchars($native);
    }
    return '<span title="Listed at ' . htmlspecialchars($native) . '">' . htmlspecialchars(format_price($converted, $preferred)) . '</span>';
}

// Prices every cart line in $target currency. Each unit price is rounded to 2 decimals first,
// so the totals on screen always match the sum of the lines shown.
function cart_view($items, $target, $rates) {
    $lines = [];
    $total = 0.0;
    $qty = 0;
    $ok = true;
    $converted = false;
    foreach ($items as $item) {
        $from = strtoupper(trim((string) ($item['product_currency'] ?? SHOP_CURRENCY)));
        $q = max(1, (int) ($item['qty'] ?? 1));
        $unit = convert_price($item['product_price'] ?? 0, $from, $target, $rates);
        $line = null;
        if ($unit === null) {
            $ok = false;
        } else {
            $line = round($unit * $q, 2);
            $total += $line;
            if ($from !== strtoupper($target)) {
                $converted = true;
            }
        }
        $lines[] = ['item' => $item, 'qty' => $q, 'native_currency' => $from, 'unit' => $unit, 'line' => $line];
        $qty += $q;
    }
    return ['lines' => $lines, 'total' => $ok ? round($total, 2) : null, 'qty' => $qty, 'ok' => $ok, 'converted' => $converted];
}

function rates_note() {
    $data = get_exchange_rates();
    if (!$data) {
        return '';
    }
    $when = $data['updated'] !== '' ? ' (rates updated ' . $data['updated'] . ')' : '';
    return 'Prices listed in other currencies are converted using daily exchange rates' . $when . '.';
}

<?php
session_start();
date_default_timezone_set('UTC');

// Base path the app is served from. Change this if you move the app to a
// different folder or domain root.
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
?>

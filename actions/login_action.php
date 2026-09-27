<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/login.php');
}

$email = trim(strip_tags($_POST['email'] ?? ''));
$pass  = $_POST['pass'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
    $_SESSION['error'] = 'Please enter a valid email and password.';
    redirect(BASE_URL . '/views/login.php');
}

$controller = new CustomerController();
$result = $controller->login($email, $pass);

if ($result['success']) {
    $customer = $result['customer'];
    $_SESSION['customer_id']    = $customer['customer_id'];
    $_SESSION['customer_name']  = $customer['customer_name'];
    $_SESSION['customer_email'] = $customer['customer_email'];
    $_SESSION['user_role']      = (int) $customer['user_role'];
    redirect(BASE_URL . '/index.php');
} else {
    $_SESSION['error'] = $result['error'];
    redirect(BASE_URL . '/views/login.php');
}

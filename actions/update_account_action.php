<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/CustomerController.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/account/edit_account.php');
}

$name  = trim(strip_tags($_POST['name'] ?? ''));
$email = trim(strip_tags($_POST['email'] ?? ''));

if (strlen($name) < 2 || strlen($name) > 100) {
    $_SESSION['error'] = 'Name must be between 2 and 100 characters.';
    redirect(BASE_URL . '/views/account/edit_account.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    $_SESSION['error'] = 'Please enter a valid email address (100 characters or fewer).';
    redirect(BASE_URL . '/views/account/edit_account.php');
}

$controller = new CustomerController();
$result = $controller->updateProfile((int) $_SESSION['customer_id'], $name, $email);

if ($result['success']) {
    $_SESSION['customer_name']  = $name;
    $_SESSION['customer_email'] = $email;
    $_SESSION['success'] = 'Your details have been updated.';
    redirect(BASE_URL . '/views/account/my_account.php');
}

$_SESSION['error'] = $result['error'];
redirect(BASE_URL . '/views/account/edit_account.php');

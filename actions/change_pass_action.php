<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/CustomerController.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/account/change_pass.php');
}

$current = $_POST['current_pass'] ?? '';
$new     = $_POST['new_pass'] ?? '';
$confirm = $_POST['confirm_pass'] ?? '';

if ($current === '' || $new === '' || $confirm === '') {
    $_SESSION['error'] = 'Please fill in all password fields.';
    redirect(BASE_URL . '/views/account/change_pass.php');
}

if (!preg_match('/^(?=.{8,64}$)(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])\S+$/', $new)) {
    $_SESSION['error'] = 'Password must be 8-64 characters with lowercase, uppercase, a digit, and a special character.';
    redirect(BASE_URL . '/views/account/change_pass.php');
}

if ($new !== $confirm) {
    $_SESSION['error'] = 'The new passwords do not match.';
    redirect(BASE_URL . '/views/account/change_pass.php');
}

if ($new === $current) {
    $_SESSION['error'] = 'Your new password must be different from the current one.';
    redirect(BASE_URL . '/views/account/change_pass.php');
}

$controller = new CustomerController();
$result = $controller->changePassword((int) $_SESSION['customer_id'], $current, $new);

if ($result['success']) {
    $_SESSION['success'] = 'Your password has been changed.';
    redirect(BASE_URL . '/views/account/my_account.php');
}

$_SESSION['error'] = $result['error'];
redirect(BASE_URL . '/views/account/change_pass.php');

<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/register.php');
}

$name    = trim(strip_tags($_POST['name'] ?? ''));
$email   = trim(strip_tags($_POST['email'] ?? ''));
$pass    = $_POST['pass'] ?? '';
$country = trim(strip_tags($_POST['country'] ?? ''));
$city    = trim(strip_tags($_POST['city'] ?? ''));
$contact = trim(strip_tags($_POST['contact'] ?? ''));

$errors = [];

if (strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (strlen($email) > 50) {
    $errors[] = 'Email must be 50 characters or fewer.';
}
if (strlen($pass) < 8 || !preg_match('/\d/', $pass)) {
    $errors[] = 'Password must be at least 8 characters and include a digit.';
}
if ($country === '') {
    $errors[] = 'Country is required.';
}
if ($city === '') {
    $errors[] = 'City is required.';
}
if (!preg_match('/^[0-9+\-\s]{7,15}$/', $contact)) {
    $errors[] = 'Please enter a valid contact number.';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    redirect(BASE_URL . '/views/register.php');
}

$controller = new CustomerController();
$result = $controller->register([
    'name'    => $name,
    'email'   => $email,
    'pass'    => $pass,
    'country' => $country,
    'city'    => $city,
    'contact' => $contact,
]);

if ($result['success']) {
    $_SESSION['success'] = 'Registration successful. Please log in to continue.';
    redirect(BASE_URL . '/views/login.php');
} else {
    $_SESSION['error'] = $result['error'];
    redirect(BASE_URL . '/views/register.php');
}

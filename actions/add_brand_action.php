<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/ProductController.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/admin/brand.php');
}

$name = trim(strip_tags($_POST['brand_name'] ?? ''));

if ($name === '') {
    $_SESSION['error'] = 'Brand name is required.';
    redirect(BASE_URL . '/views/admin/brand.php');
}

$controller = new ProductController();
if ($controller->addBrand($name)) {
    $_SESSION['success'] = 'Brand added.';
} else {
    $_SESSION['error'] = 'Unable to add brand.';
}

redirect(BASE_URL . '/views/admin/brand.php');

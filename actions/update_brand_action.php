<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/ProductController.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/admin/brand.php');
}

$id = filter_var($_POST['brand_id'] ?? null, FILTER_VALIDATE_INT);
$name = trim(strip_tags($_POST['brand_name'] ?? ''));

if ($id === false || $id < 1 || $name === '') {
    $_SESSION['error'] = 'A valid brand ID and name are required.';
    redirect(BASE_URL . '/views/admin/brand.php');
}

$controller = new ProductController();
if ($controller->updateBrand($id, $name)) {
    $_SESSION['success'] = 'Brand updated.';
} else {
    $_SESSION['error'] = 'Unable to update brand.';
}

redirect(BASE_URL . '/views/admin/brand.php');

<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/ProductController.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/admin/brand.php');
}

$id = filter_var($_POST['brand_id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    $_SESSION['error'] = 'A valid brand ID is required.';
    redirect(BASE_URL . '/views/admin/brand.php');
}

$controller = new ProductController();
$brand = $controller->getBrandById($id);
if (!$brand) {
    $_SESSION['error'] = 'Brand not found.';
    redirect(BASE_URL . '/views/admin/brand.php');
}

$products = $controller->getAllProducts();
foreach ($products as $product) {
    if ((int) $product['product_brand'] === $id) {
        $_SESSION['error'] = 'This brand is linked to products and cannot be deleted.';
        redirect(BASE_URL . '/views/admin/brand.php');
    }
}

if ($controller->deleteBrand($id)) {
    $_SESSION['success'] = 'Brand deleted.';
} else {
    $_SESSION['error'] = 'Unable to delete brand.';
}

redirect(BASE_URL . '/views/admin/brand.php');

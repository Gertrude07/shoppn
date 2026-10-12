<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/ProductController.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/admin/product.php');
}

$id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    $_SESSION['error'] = 'A valid product ID is required.';
    redirect(BASE_URL . '/views/admin/product.php');
}

$controller = new ProductController();
$product = $controller->getProductById($id);
if (!$product) {
    $_SESSION['error'] = 'Product not found.';
    redirect(BASE_URL . '/views/admin/product.php');
}

if ($controller->deleteProduct($id)) {
    $imagePath = rtrim(__DIR__ . '/../images/products', '/\\') . DIRECTORY_SEPARATOR . basename((string) ($product['product_image'] ?? ''));
    if (is_file($imagePath)) {
        @unlink($imagePath);
    }
    $_SESSION['success'] = 'Product deleted.';
} else {
    $_SESSION['error'] = 'Unable to delete product.';
}

redirect(BASE_URL . '/views/admin/product.php');

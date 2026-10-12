<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/ProductController.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/admin/category.php');
}

$id = filter_var($_POST['cat_id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    $_SESSION['error'] = 'A valid category ID is required.';
    redirect(BASE_URL . '/views/admin/category.php');
}

$controller = new ProductController();
$category = $controller->getCategoryById($id);
if (!$category) {
    $_SESSION['error'] = 'Category not found.';
    redirect(BASE_URL . '/views/admin/category.php');
}

$products = $controller->getAllProducts();
foreach ($products as $product) {
    if ((int) $product['product_cat'] === $id) {
        $_SESSION['error'] = 'This category is linked to products and cannot be deleted.';
        redirect(BASE_URL . '/views/admin/category.php');
    }
}

if ($controller->deleteCategory($id)) {
    $_SESSION['success'] = 'Category deleted.';
} else {
    $_SESSION['error'] = 'Unable to delete category.';
}

redirect(BASE_URL . '/views/admin/category.php');

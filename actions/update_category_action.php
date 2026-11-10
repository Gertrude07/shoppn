<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/ProductController.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/admin/category.php');
}

$id = filter_var($_POST['cat_id'] ?? null, FILTER_VALIDATE_INT);
$name = trim(strip_tags($_POST['cat_name'] ?? ''));

if ($id === false || $id < 1 || $name === '') {
    $_SESSION['error'] = 'A valid category ID and name are required.';
    redirect(BASE_URL . '/views/admin/category.php');
}

$controller = new ProductController();
if ($controller->updateCategory($id, $name)) {
    $_SESSION['success'] = 'Category updated.';
} else {
    $_SESSION['error'] = 'Unable to update category.';
}

redirect(BASE_URL . '/views/admin/category.php');

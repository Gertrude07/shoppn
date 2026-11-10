<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/ProductController.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/admin/category.php');
}

$name = trim(strip_tags($_POST['cat_name'] ?? ''));

if ($name === '') {
    $_SESSION['error'] = 'Category name is required.';
    redirect(BASE_URL . '/views/admin/category.php');
}

$controller = new ProductController();
if ($controller->addCategory($name)) {
    $_SESSION['success'] = 'Category added.';
} else {
    $_SESSION['error'] = 'Unable to add category.';
}

redirect(BASE_URL . '/views/admin/category.php');

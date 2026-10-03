<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/../controllers/ProductController.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/admin/product.php');
}

$id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
$cat = filter_var($_POST['cat_id'] ?? null, FILTER_VALIDATE_INT);
$brand = filter_var($_POST['brand_id'] ?? null, FILTER_VALIDATE_INT);
$title = trim(strip_tags($_POST['product_title'] ?? ''));
$price = $_POST['product_price'] ?? '';
$currency = strtoupper(trim((string) ($_POST['product_currency'] ?? 'USD')));
$desc = trim(strip_tags($_POST['product_desc'] ?? ''));
$keywords = trim(strip_tags($_POST['product_keywords'] ?? ''));
$currentImage = basename($_POST['current_image'] ?? '');
$file = $_FILES['product_image'] ?? null;
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$allowedCurrencies = ['USD', 'GHS', 'NGN', 'EUR'];
$maxFileSize = 5 * 1024 * 1024;

if ($id === false || $id < 1 || $cat === false || $cat < 1 || $brand === false || $brand < 1 || $title === '' || !is_numeric($price) || $price < 0 || !in_array($currency, $allowedCurrencies, true) || $desc === '' || $keywords === '' || $currentImage === '') {
    $_SESSION['error'] = 'Please complete all product fields with valid values.';
    redirect(BASE_URL . '/views/admin/product.php');
}

$imageFilename = $currentImage;
$newUploadPath = null;

if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > $maxFileSize) {
        $_SESSION['error'] = 'Please upload an image no larger than 5 MB.';
        redirect(BASE_URL . '/views/admin/product.php?edit_id=' . $id);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!in_array($mimeType, $allowedTypes, true)) {
        $_SESSION['error'] = 'Only JPEG, PNG, GIF, and WebP images are allowed.';
        redirect(BASE_URL . '/views/admin/product.php?edit_id=' . $id);
    }

    $uploadPaths = get_product_upload_paths();
    $uploadDir = $uploadPaths['storage'];
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        $_SESSION['error'] = 'The product image folder could not be created by the server.';
        redirect(BASE_URL . '/views/admin/product.php?edit_id=' . $id);
    }
    if (is_dir($uploadDir) && !is_writable($uploadDir)) {
        @chmod($uploadDir, 0777);
    }
    if (!is_writable($uploadDir)) {
        $_SESSION['error'] = 'The product image folder exists but is not writable by the server. Please give the folder write permissions.';
        redirect(BASE_URL . '/views/admin/product.php?edit_id=' . $id);
    }

    $originalName = basename($file['name']);
    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
    $imageFilename = uniqid('', true) . '_' . $safeName;
    $newUploadPath = $uploadDir . $imageFilename;

    if (!move_uploaded_file($file['tmp_name'], $newUploadPath)) {
        $_SESSION['error'] = 'The product image could not be moved into the product image folder.';
        redirect(BASE_URL . '/views/admin/product.php?edit_id=' . $id);
    }

    if ($uploadDir !== $uploadPaths['display']) {
        mirror_uploaded_product_image($newUploadPath, $imageFilename);
    }
}

$controller = new ProductController();
if ($controller->updateProduct($id, $cat, $brand, $title, (float) $price, $currency, $desc, $imageFilename, $keywords)) {
    $_SESSION['success'] = 'Product updated.';
    if ($newUploadPath && $currentImage !== $imageFilename) {
        $oldPath = __DIR__ . '/../images/products/' . $currentImage;
        if (is_file($oldPath)) {
            unlink($oldPath);
        }
    }
} else {
    if ($newUploadPath && is_file($newUploadPath)) {
        unlink($newUploadPath);
    }
    $_SESSION['error'] = 'Unable to update product.';
}

redirect(BASE_URL . '/views/admin/product.php');

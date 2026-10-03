<?php
require __DIR__ . '/../../core/core.php';
require __DIR__ . '/../../controllers/ProductController.php';
require_admin();

$controller = new ProductController();
$editId = filter_var($_GET['edit_id'] ?? null, FILTER_VALIDATE_INT);
$editingProduct = ($editId && $editId > 0) ? $controller->getProductById($editId) : false;
$categories = $controller->getAllCategories();
$brands = $controller->getAllBrands();
$products = $controller->getAllProducts();
$formAction = $editingProduct ? BASE_URL . '/actions/update_product_action.php' : BASE_URL . '/actions/add_product_action.php';
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<section class="admin-page">
    <h1><?= $editingProduct ? 'Edit Product' : 'Add Product' ?></h1>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form id="product-form" class="admin-form" action="<?= $formAction ?>" method="POST" enctype="multipart/form-data" novalidate>
        <?php if ($editingProduct): ?>
            <input type="hidden" name="product_id" value="<?= (int) $editingProduct['product_id'] ?>">
            <input type="hidden" name="current_image" value="<?= htmlspecialchars($editingProduct['product_image']) ?>">
        <?php endif; ?>

        <div class="admin-field">
            <label for="product-title">Product Title</label>
            <input type="text" id="product-title" name="product_title" value="<?= htmlspecialchars($editingProduct['product_title'] ?? '') ?>" required>
        </div>

        <div class="admin-field admin-price-group">
            <label for="product-price">Price</label>
            <div class="inline-price-field">
                <select id="product-currency" name="product_currency" required>
                    <option value="USD" <?= (($editingProduct['product_currency'] ?? 'USD') === 'USD') ? 'selected' : '' ?>>USD ($)</option>
                    <option value="GHS" <?= (($editingProduct['product_currency'] ?? 'USD') === 'GHS') ? 'selected' : '' ?>>GHS (GH₵)</option>
                    <option value="NGN" <?= (($editingProduct['product_currency'] ?? 'USD') === 'NGN') ? 'selected' : '' ?>>NGN (₦)</option>
                    <option value="EUR" <?= (($editingProduct['product_currency'] ?? 'USD') === 'EUR') ? 'selected' : '' ?>>EUR (€)</option>
                </select>
                <input type="number" id="product-price" name="product_price" min="0" step="0.01" value="<?= htmlspecialchars($editingProduct['product_price'] ?? '') ?>" required>
            </div>
        </div>

        <div class="admin-field">
            <label for="product-description">Description</label>
            <textarea id="product-description" name="product_desc" required><?= htmlspecialchars($editingProduct['product_desc'] ?? '') ?></textarea>
        </div>

        <div class="admin-field">
            <label for="product-keywords">Keywords</label>
            <input type="text" id="product-keywords" name="product_keywords" value="<?= htmlspecialchars($editingProduct['product_keywords'] ?? '') ?>" required>
        </div>

        <div class="admin-field">
            <label for="category">Category</label>
            <select id="category" name="cat_id" required>
                <option value="">Select category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['cat_id'] ?>" <?= ((int) ($editingProduct['cat_id'] ?? 0) === (int) $category['cat_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['cat_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="admin-field">
            <label for="brand">Brand</label>
            <select id="brand" name="brand_id" required>
                <option value="">Select brand</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= (int) $brand['brand_id'] ?>" <?= ((int) ($editingProduct['brand_id'] ?? 0) === (int) $brand['brand_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($brand['brand_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="admin-field">
            <label for="product-image">Product Image</label>
            <input type="file" id="product-image" name="product_image" accept="image/jpeg,image/png,image/gif,image/webp" <?= $editingProduct ? '' : 'required' ?>>
            <span class="field-error" id="product-image-error"></span>
            <p class="form-help">JPEG, PNG, GIF, or WebP, maximum 5 MB.</p>
        </div>

        <?php if ($editingProduct && !empty($editingProduct['product_image'])): ?>
            <p>Current image:</p>
            <img class="product-preview" src="<?= BASE_URL ?>/images/products/<?= rawurlencode($editingProduct['product_image']) ?>" alt="Current product image">
        <?php endif; ?>

        <button type="submit"><?= $editingProduct ? 'Update Product' : 'Add Product' ?></button>
        <?php if ($editingProduct): ?>
            <a href="<?= BASE_URL ?>/views/admin/product.php">Cancel edit</a>
        <?php endif; ?>
    </form>

    <h2>Products</h2>
    <?php if ($products): ?>
        <table class="admin-table">
            <thead>
                <tr><th>Product</th><th>Category</th><th>Brand</th><th>Price</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['product_title']) ?></td>
                        <td><?= htmlspecialchars($product['cat_name']) ?></td>
                        <td><?= htmlspecialchars($product['brand_name']) ?></td>
                        <td><?= htmlspecialchars($product['product_currency'] ?? 'USD') ?> <?= number_format((float) $product['product_price'], 2) ?></td>
                        <td><a href="<?= BASE_URL ?>/views/admin/product.php?edit_id=<?= (int) $product['product_id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No products have been added yet.</p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../layout/sidebar.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
<script src="<?= BASE_URL ?>/js/validate.js"></script>

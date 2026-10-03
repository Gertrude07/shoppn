<?php
require __DIR__ . '/../../core/core.php';
require __DIR__ . '/../../controllers/ProductController.php';
require_admin();

$controller = new ProductController();
$editId = filter_var($_GET['edit_id'] ?? null, FILTER_VALIDATE_INT);
$editingBrand = ($editId && $editId > 0) ? $controller->getBrandById($editId) : false;
$brands = $controller->getAllBrands();
$formAction = $editingBrand ? BASE_URL . '/actions/update_brand_action.php' : BASE_URL . '/actions/add_brand_action.php';
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<section class="admin-page">
    <h1>Manage Brands</h1>

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

    <form class="admin-form" action="<?= $formAction ?>" method="POST">
        <?php if ($editingBrand): ?>
            <input type="hidden" name="brand_id" value="<?= (int) $editingBrand['brand_id'] ?>">
        <?php endif; ?>
        <label for="brand-name">Brand name</label>
        <input type="text" id="brand-name" name="brand_name" value="<?= htmlspecialchars($editingBrand['brand_name'] ?? '') ?>" required>
        <button type="submit"><?= $editingBrand ? 'Update Brand' : 'Add Brand' ?></button>
        <?php if ($editingBrand): ?>
            <a href="<?= BASE_URL ?>/views/admin/brand.php">Cancel edit</a>
        <?php endif; ?>
    </form>

    <h2>Brands</h2>
    <?php if ($brands): ?>
        <table class="admin-table">
            <thead>
                <tr><th>Brand</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($brands as $brand): ?>
                    <tr>
                        <td><?= htmlspecialchars($brand['brand_name']) ?></td>
                        <td><a href="<?= BASE_URL ?>/views/admin/brand.php?edit_id=<?= (int) $brand['brand_id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No brands have been added yet.</p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../layout/sidebar.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>

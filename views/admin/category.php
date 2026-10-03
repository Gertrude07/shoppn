<?php
require __DIR__ . '/../../core/core.php';
require __DIR__ . '/../../controllers/ProductController.php';
require_admin();

$controller = new ProductController();
$editId = filter_var($_GET['edit_id'] ?? null, FILTER_VALIDATE_INT);
$editingCategory = ($editId && $editId > 0) ? $controller->getCategoryById($editId) : false;
$categories = $controller->getAllCategories();
$formAction = $editingCategory ? BASE_URL . '/actions/update_category_action.php' : BASE_URL . '/actions/add_category_action.php';
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<section class="admin-page">
    <h1>Manage Categories</h1>

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
        <?php if ($editingCategory): ?>
            <input type="hidden" name="cat_id" value="<?= (int) $editingCategory['cat_id'] ?>">
        <?php endif; ?>
        <label for="category-name">Category name</label>
        <input type="text" id="category-name" name="cat_name" value="<?= htmlspecialchars($editingCategory['cat_name'] ?? '') ?>" required>
        <button type="submit"><?= $editingCategory ? 'Update Category' : 'Add Category' ?></button>
        <?php if ($editingCategory): ?>
            <a href="<?= BASE_URL ?>/views/admin/category.php">Cancel edit</a>
        <?php endif; ?>
    </form>

    <h2>Categories</h2>
    <?php if ($categories): ?>
        <table class="admin-table">
            <thead>
                <tr><th>Category</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= htmlspecialchars($category['cat_name']) ?></td>
                        <td><a href="<?= BASE_URL ?>/views/admin/category.php?edit_id=<?= (int) $category['cat_id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No categories have been added yet.</p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../layout/sidebar.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>

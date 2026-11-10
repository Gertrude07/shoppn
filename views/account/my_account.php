<?php
require __DIR__ . '/../../core/core.php';
require_login();
require __DIR__ . '/../layout/header.php';
?>

<div class="account-page">
    <h1>My Account</h1>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['customer_name'] ?? ''); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['customer_email'] ?? ''); ?></p>
    <p><strong>Role:</strong> <?php echo (isset($_SESSION['user_role']) && (int) $_SESSION['user_role'] === 1) ? 'Admin' : 'Customer'; ?></p>

    <div class="account-links">
        <a class="button secondary" href="<?= BASE_URL ?>/views/account/edit_account.php">Edit Profile</a>
        <a class="button secondary" href="<?= BASE_URL ?>/views/account/change_pass.php">Change Password</a>
    </div>
</div>

<?php require __DIR__ . '/../layout/sidebar.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>

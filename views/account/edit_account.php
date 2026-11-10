<?php
require __DIR__ . '/../../core/core.php';
require_login();
require __DIR__ . '/../layout/header.php';
?>

<div class="auth-page">
    <h1>Edit Profile</h1>
    <p class="page-subtitle">Update your name and email address.</p>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form id="edit-account-form" class="auth-form" action="<?= BASE_URL ?>/actions/update_account_action.php" method="POST" novalidate>
        <label for="name">Name</label>
        <input type="text" id="name" name="name" maxlength="100" value="<?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?>" required>
        <span class="field-error" id="name-error"></span>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" maxlength="100" value="<?= htmlspecialchars($_SESSION['customer_email'] ?? '') ?>" required>
        <span class="field-error" id="email-error"></span>

        <button type="submit" id="edit-account-submit">Save Changes</button>
    </form>

    <p class="form-footer"><a href="<?= BASE_URL ?>/views/account/my_account.php">&larr; Back to My Account</a></p>
</div>

<?php require __DIR__ . '/../layout/sidebar.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
<script src="<?= BASE_URL ?>/js/validate.js"></script>

<?php
require __DIR__ . '/../../core/core.php';
require_login();
require __DIR__ . '/../layout/header.php';
?>

<div class="auth-page">
    <h1>Change Password</h1>
    <p class="page-subtitle">Choose a strong password you have not used before.</p>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form id="change-pass-form" class="auth-form" action="<?= BASE_URL ?>/actions/change_pass_action.php" method="POST" novalidate>
        <label for="current-pass">Current password</label>
        <input type="password" id="current-pass" name="current_pass" required>
        <span class="field-error" id="current-pass-error"></span>

        <label for="new-pass">New password</label>
        <input type="password" id="new-pass" name="new_pass" required>
        <span class="field-error" id="new-pass-error"></span>

        <label for="confirm-pass">Confirm new password</label>
        <input type="password" id="confirm-pass" name="confirm_pass" required>
        <span class="field-error" id="confirm-pass-error"></span>

        <button type="submit" id="change-pass-submit">Change Password</button>
    </form>

    <p class="form-footer"><a href="<?= BASE_URL ?>/views/account/my_account.php">&larr; Back to My Account</a></p>
</div>

<?php require __DIR__ . '/../layout/sidebar.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
<script src="<?= BASE_URL ?>/js/validate.js"></script>

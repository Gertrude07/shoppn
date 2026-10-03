<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/layout/header.php';
?>

<div class="auth-page">
    <h1>Login</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form id="login-form" class="auth-form" action="<?= BASE_URL ?>/actions/login_action.php" method="POST" novalidate>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
        <span class="field-error" id="email-error"></span>

        <label for="pass">Password</label>
        <input type="password" id="pass" name="pass" required>
        <span class="field-error" id="pass-error"></span>

        <button type="submit" id="login-submit">Login</button>
    </form>

    <p>Don't have an account? <a href="<?= BASE_URL ?>/views/register.php">Register</a></p>
</div>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
<script src="<?= BASE_URL ?>/js/validate.js"></script>

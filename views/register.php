<?php
require __DIR__ . '/../core/core.php';
require __DIR__ . '/layout/header.php';

$registerOld = $_SESSION['register_old'] ?? [];
unset($_SESSION['register_old']);
?>

<div class="auth-page">
    <h1>Create an Account</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form id="register-form" class="auth-form" action="<?= BASE_URL ?>/actions/register_action.php" method="POST" novalidate>
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($registerOld['name'] ?? '') ?>" required>
        <span class="field-error" id="name-error"></span>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($registerOld['email'] ?? '') ?>" required>
        <span class="field-error" id="email-error"></span>

        <label for="pass">Password</label>
        <input type="password" id="pass" name="pass" minlength="8" maxlength="64" required>
        <span class="field-error" id="pass-error"></span>

        <label for="country">Country</label>
        <select id="country" name="country" required>
            <option value="">Select country</option>
            <option value="Ghana" <?= ($registerOld['country'] ?? '') === 'Ghana' ? 'selected' : '' ?>>Ghana</option>
            <option value="Nigeria" <?= ($registerOld['country'] ?? '') === 'Nigeria' ? 'selected' : '' ?>>Nigeria</option>
            <option value="Kenya" <?= ($registerOld['country'] ?? '') === 'Kenya' ? 'selected' : '' ?>>Kenya</option>
            <option value="Other" <?= ($registerOld['country'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>
        <span class="field-error" id="country-error"></span>

        <label for="city">City</label>
        <input type="text" id="city" name="city" value="<?= htmlspecialchars($registerOld['city'] ?? '') ?>" required>
        <span class="field-error" id="city-error"></span>

        <label for="contact">Contact Number</label>
        <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($registerOld['contact'] ?? '') ?>" placeholder="e.g. +233 24 000 0000" required>
        <span class="field-error" id="contact-error"></span>

        <button type="submit" id="register-submit">Register</button>
    </form>

    <p>Already have an account? <a href="<?= BASE_URL ?>/views/login.php">Login</a></p>
</div>

<?php require __DIR__ . '/layout/sidebar.php'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
<script src="<?= BASE_URL ?>/js/validate.js"></script>

<?php
use App\Helper;
use App\Session;

$pageTitle = 'Reset Password — Infinity Learning';
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$error = Session::flash('error');
require VIEW_PATH . '/layouts/header.php';
?>
<section class="auth-split">
    <div class="auth-split-side">
        <div class="auth-split-side-inner">
            <a href="/" class="auth-split-brand">
                <span class="brand-infinity">Infinity</span>
                <span class="brand-learning">L E A R N I N G</span>
            </a>
            <h2>Set a new password</h2>
            <p class="auth-split-sub">
                Choose something strong — at least 8 characters, with letters and numbers.
            </p>

            <ul class="auth-split-list">
                <li><span class="feature-check">✓</span> Don't reuse a password from another site</li>
                <li><span class="feature-check">✓</span> Use the eye icon to double-check what you type</li>
                <li><span class="feature-check">✓</span> You'll be signed out of other devices</li>
            </ul>
        </div>
    </div>

    <div class="auth-split-main">
        <div class="auth-card">
            <h2>Set new password</h2>
            <p class="muted" style="margin-bottom:1.25rem">For <strong><?= Helper::e($email) ?></strong></p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= Helper::e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="/reset-handler.php">
                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                <input type="hidden" name="token" value="<?= Helper::e($token) ?>">
                <input type="hidden" name="email" value="<?= Helper::e($email) ?>">

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" required minlength="8" autofocus>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="password_confirm" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Update password</button>
            </form>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
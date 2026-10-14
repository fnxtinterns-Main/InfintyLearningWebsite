<?php
use App\Helper;
use App\Session;

$pageTitle = 'Forgot Password — Infinity Learning';
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
            <h2>Reset your password</h2>
            <p class="auth-split-sub">
                Enter the email you registered with. We'll send you a secure reset link.
            </p>

            <ul class="auth-split-list">
                <li><span class="feature-check">✓</span> The link expires in 1 hour</li>
                <li><span class="feature-check">✓</span> Your old password stays valid until you reset</li>
                <li><span class="feature-check">✓</span> Check your spam folder if you don't see it</li>
            </ul>
        </div>
    </div>

    <div class="auth-split-main">
        <div class="auth-card">
            <h2>Forgot password?</h2>
            <p class="muted" style="margin-bottom:1.25rem">No problem — we'll get you back in.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= Helper::e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="/forgot-handler.php">
                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required autofocus placeholder="you@example.com">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Send reset link</button>
            </form>

            <p class="auth-switch"><a href="/login">← Back to login</a></p>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
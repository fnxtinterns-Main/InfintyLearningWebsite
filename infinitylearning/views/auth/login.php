<?php
use App\Helper;
use App\Session;

$pageTitle = 'Login — Infinity Learning';
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
            <h2>Welcome back 👋</h2>
            <p class="auth-split-sub">
                One login. Every subject, activity, certificate — and everyone in your school.
            </p>

            <ul class="auth-split-list">
                <li><span class="feature-check">✓</span> Track attendance and lessons</li>
                <li><span class="feature-check">✓</span> Compete in school and global events</li>
                <li><span class="feature-check">✓</span> Download achievements as PDFs</li>
            </ul>

            <div class="auth-split-quote">
                "Our teachers save hours every week using Infinity Learning."
                <span class="muted">— Principal, City Public School</span>
            </div>
        </div>
    </div>

    <div class="auth-split-main">
        <div class="auth-card">
            <h2>Login</h2>
            <p class="muted" style="margin-bottom:1.25rem">Sign in to your account</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= Helper::e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="/login-handler.php">
                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required autofocus
                           value="<?= Helper::old('email') ?>" placeholder="you@example.com">
                </div>
                <div class="form-group">
                    <div class="label-row">
                        <label>Password</label>
                        <a href="/forgot" class="label-hint">Forgot password?</a>
                    </div>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
            </form>

            <p class="auth-switch">
                New here? <a href="/register?type=student">Sign up as student</a> or <a href="/register?type=teacher">teacher</a>.
            </p>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
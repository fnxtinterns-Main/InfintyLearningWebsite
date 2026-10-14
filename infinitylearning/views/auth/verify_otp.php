<?php
use App\Helper;
use App\Session;

$phone = $_SESSION['_pending_phone'] ?? '';
$role  = $_SESSION['_pending_role'] ?? 'student';
$data  = $_SESSION['_pending_registration'] ?? [];
$email = $data['email'] ?? '';
$pageTitle = 'Verify OTP — Infinity Learning';

if (!$phone) Helper::redirect('/register?type=' . urlencode($role));

require VIEW_PATH . '/layouts/header.php';
?>
<section class="auth-split">
    <div class="auth-split-side">
        <div class="auth-split-side-inner">
            <a href="/" class="auth-split-brand">
                <span class="brand-infinity">Infinity</span>
                <span class="brand-learning">L E A R N I N G</span>
            </a>
            <h2>One last step 📩</h2>
            <p class="auth-split-sub">
                We've sent a 6-digit code to <strong>your phone</strong> and <strong>your email</strong>.
                Use whichever arrives first.
            </p>

            <ul class="auth-split-list">
                <li><span class="feature-check">✓</span> The code expires in 10 minutes</li>
                <li><span class="feature-check">✓</span> Didn't get it? Tap resend below</li>
                <li><span class="feature-check">✓</span> Check spam if using email</li>
            </ul>
        </div>
    </div>

    <div class="auth-split-main">
        <div class="auth-card">
            <h2>Verify your account</h2>
            <p class="muted" style="margin-bottom:1.25rem">
                Sent to <strong><?= Helper::e($phone) ?></strong>
                <?php if ($email): ?><br>and <strong><?= Helper::e($email) ?></strong><?php endif; ?>
            </p>

            <?php if ($msg = Session::flash('error')): ?>
                <div class="alert alert-error"><?= Helper::e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = Session::flash('success')): ?>
                <div class="alert alert-success"><?= Helper::e($msg) ?></div>
            <?php endif; ?>

            <form method="post" action="/otp-handler.php">
                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                <input type="hidden" name="action" value="verify">
                <div class="form-group">
                    <label>Enter OTP</label>
                    <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" required
                           inputmode="numeric" autocomplete="one-time-code" autofocus
                           style="text-align:center;font-size:1.4rem;letter-spacing:.4em;font-family:var(--font-mono)">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Verify &amp; continue</button>
            </form>

            <form method="post" action="/otp-handler.php" class="resend-form">
                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                <input type="hidden" name="action" value="resend">
                <button type="submit" class="btn-link">Resend to phone &amp; email</button>
            </form>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
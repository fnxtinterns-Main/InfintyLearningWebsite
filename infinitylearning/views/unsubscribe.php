<?php
use App\Helper;
use App\Database;
use App\Audit;

$pageTitle = 'Email Preferences & Unsubscribe — Infinity Learning';

$msg = '';
$error = '';
$emailParam = trim($_GET['email'] ?? $_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $reason = trim($_POST['reason'] ?? 'User opted out via unsubscribe form');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $db = Database::connect();
            
            // Check if user exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $u = $stmt->fetch();
            $userId = $u ? (int)$u['id'] : null;

            // Update user marketing consent to 0 if found
            if ($userId) {
                $up = $db->prepare("UPDATE users SET marketing_consent = 0 WHERE id = ?");
                $up->execute([$userId]);
            }

            // Record into email_unsubscribes
            $ins = $db->prepare("
                INSERT INTO email_unsubscribes (email, user_id, reason, ip_address, unsubscribed_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE reason = VALUES(reason), unsubscribed_at = NOW()
            ");
            $ins->execute([$email, $userId, $reason, $_SERVER['REMOTE_ADDR'] ?? null]);

            Audit::log('email.unsubscribed', 'user', $userId, ['email' => $email, 'reason' => $reason]);

            $msg = "You have been successfully unsubscribed from optional marketing and announcement emails. Essential transactional emails (such as password resets, OTPs, and fee receipts) will still be delivered.";
        } catch (\Throwable $e) {
            $error = 'An error occurred while updating your preferences. Please try again or contact info@infintylearning.net.';
        }
    }
}

require VIEW_PATH . '/layouts/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">✉️ EMAIL PREFERENCES</span>
                <h1>Manage Email Subscriptions</h1>
                <p>
                    We respect your inbox. You can opt out of non-essential promotional, newsletter,
                    and announcement emails at any time.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container-narrow">
        <div class="card" style="max-width:580px; margin:0 auto; padding:32px;">
            <?php if ($msg): ?>
                <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:10px; padding:18px; margin-bottom:20px; color:#065F46;">
                    <h3 style="margin:0 0 8px; font-size:16px; font-weight:800;">✓ Preferences Updated</h3>
                    <p style="margin:0; font-size:13.5px; line-height:1.6;"><?= htmlspecialchars($msg) ?></p>
                </div>
                <div style="text-align:center; margin-top:20px;">
                    <a href="/" class="btn btn-primary" style="padding:10px 24px;">Return to Homepage</a>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div style="background:#FEF2F2; border:1px solid #FCA5A5; border-radius:10px; padding:14px; margin-bottom:20px; color:#991B1B; font-size:13.5px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/unsubscribe">
                    <div class="form-group" style="margin-bottom:18px;">
                        <label style="display:block; font-size:13.5px; font-weight:700; color:#111827; margin-bottom:6px;">Your Registered Email Address *</label>
                        <input type="email" name="email" required value="<?= htmlspecialchars($emailParam) ?>" placeholder="e.g. name@example.com" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;">
                    </div>

                    <div class="form-group" style="margin-bottom:20px;">
                        <label style="display:block; font-size:13.5px; font-weight:700; color:#111827; margin-bottom:6px;">Reason for Unsubscribing (Optional)</label>
                        <select name="reason" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;">
                            <option value="I receive too many emails">I receive too many emails</option>
                            <option value="Content is no longer relevant">Content is no longer relevant to me</option>
                            <option value="Never signed up for marketing">I only want mandatory school transactional emails</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <p style="font-size:12px; color:#64748B; line-height:1.5; margin:0 0 20px;">
                        <em>Note: In accordance with standard compliance guidelines, unsubscribing applies to marketing newsletters and general announcements. Account-critical transactional emails (such as login OTPs, system alerts, and password recoveries) cannot be unsubscribed from while your account is active.</em>
                    </p>

                    <button type="submit" class="btn btn-primary btn-block" style="width:100%; padding:12px; font-size:14px; font-weight:700;">
                        Unsubscribe from Marketing Emails
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

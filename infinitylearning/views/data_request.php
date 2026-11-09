<?php
use App\Helper;
use App\Database;
use App\Audit;
use App\Auth;

$pageTitle = 'Data Access & Deletion Request — Infinity Learning';

$msg = '';
$error = '';
$user = Auth::user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestType = $_POST['request_type'] ?? '';
    $fullName    = trim($_POST['full_name'] ?? '');
    $email       = strtolower(trim($_POST['email'] ?? ''));
    $phone       = trim($_POST['phone'] ?? '');
    $rel         = trim($_POST['relationship'] ?? 'Self');
    $details     = trim($_POST['details'] ?? '');

    $validTypes = ['access_export', 'deletion_erasure', 'correction', 'marketing_optout'];

    if (!in_array($requestType, $validTypes, true)) {
        $error = 'Please select a valid request category.';
    } elseif (empty($fullName) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid full name and email address.';
    } else {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                INSERT INTO privacy_requests 
                    (user_id, request_type, full_name, email, phone, relationship_to_student, details, ip_address, status, created_at)
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $user ? (int)$user['id'] : null,
                $requestType,
                $fullName,
                $email,
                $phone ?: null,
                $rel ?: null,
                $details ?: null,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            $reqId = $db->lastInsertId();
            Audit::log('privacy_request.submitted', 'user', $user ? (int)$user['id'] : null, [
                'request_id'   => $reqId,
                'request_type' => $requestType,
                'email'        => $email,
            ]);

            $msg = "Your privacy request (#DSR-{$reqId}) has been successfully logged. In compliance with the Digital Personal Data Protection Act 2023 and global privacy frameworks, our Data Protection team will verify identity and process your request within 30 days.";
        } catch (\Throwable $e) {
            $error = 'An error occurred while logging your request. Please email info@infintylearning.net directly.';
        }
    }
}

require VIEW_PATH . '/layouts/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">🛡️ PRIVACY &amp; DATA RIGHTS</span>
                <h1>Data Access &amp; Erasure Request</h1>
                <p>
                    Exercise your statutory rights under the <em>Digital Personal Data Protection Act 2023</em>, 
                    GDPR, and applicable data privacy regulations.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container-narrow">
        <div class="card" style="padding:32px;">
            <?php if ($msg): ?>
                <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:10px; padding:20px; color:#065F46; margin-bottom:24px;">
                    <h3 style="margin:0 0 8px; font-size:17px; font-weight:800;">✓ Request Received</h3>
                    <p style="margin:0; font-size:14px; line-height:1.6;"><?= htmlspecialchars($msg) ?></p>
                </div>
                <div style="text-align:center;">
                    <a href="/privacy" class="btn btn-outline" style="margin-right:10px;">View Privacy Policy</a>
                    <a href="/" class="btn btn-primary">Return Home</a>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div style="background:#FEF2F2; border:1px solid #FCA5A5; border-radius:10px; padding:14px; margin-bottom:20px; color:#991B1B; font-size:13.5px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <p style="font-size:14px; color:#475569; line-height:1.6; margin:0 0 24px;">
                    Infinity Learning is committed to full data transparency. Whether you are a student, parent/guardian, or educator, you have the right to request a complete machine-readable copy of your data or permanently delete your account records.
                </p>

                <form method="POST" action="/data-request">
                    <div class="form-group" style="margin-bottom:18px;">
                        <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Select Request Type *</label>
                        <select name="request_type" required class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;">
                            <option value="access_export">📥 Data Access &amp; Export (Download all stored records)</option>
                            <option value="deletion_erasure">🗑️ Data Deletion &amp; Account Erasure (Right to be Forgotten)</option>
                            <option value="correction">✏️ Data Rectification &amp; Profile Correction</option>
                            <option value="marketing_optout">🚫 Marketing &amp; Communications Opt-Out</option>
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
                        <div>
                            <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Full Name *</label>
                            <input type="text" name="full_name" required value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" placeholder="Your legal name" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Registered Email Address *</label>
                            <input type="email" name="email" required value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="name@example.com" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
                        <div>
                            <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Phone Number (Optional)</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+91 98765 43210" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Applicant Relationship *</label>
                            <select name="relationship" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;">
                                <option value="Self">I am the account holder (Student / Teacher / Admin)</option>
                                <option value="Parent/Guardian">I am the Parent / Legal Guardian of a minor student</option>
                                <option value="School Administrator">I am an authorized School Administrator</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:22px;">
                        <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Additional Context or Specific Requests</label>
                        <textarea name="details" rows="3" placeholder="Please specify if this applies to a particular course, school profile, certificate, or academic term..." class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;"></textarea>
                    </div>

                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:14px 16px; margin-bottom:22px; font-size:12.5px; color:#64748B; line-height:1.55;">
                        🛡️ <strong>Verification &amp; Security Note:</strong> To safeguard student privacy, we may require email or OTP verification before dispatching data archives or executing permanent erasure. Educational records mandated by statutory school boards may be subject to institutional retention rules.
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="width:100%; padding:13px; font-size:15px; font-weight:700;">
                        Submit Official Privacy Request →
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

<?php
use App\Helper;
use App\Database;
use App\Audit;
use App\Auth;

$pageTitle = 'Data Principal Rights & Nomination Portal (DPDP Act 2023) — Infinity Learning';

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

    // Nominee details for Section 14 DPDP
    $nomineeName = trim($_POST['nominee_name'] ?? '');
    $nomineeRel  = trim($_POST['nominee_rel'] ?? '');
    $nomineeContact = trim($_POST['nominee_contact'] ?? '');

    $validTypes = ['access_export', 'deletion_erasure', 'correction', 'marketing_optout', 'nomination'];

    if (!in_array($requestType, $validTypes, true)) {
        $error = 'Please select a valid request category under the DPDP Act 2023.';
    } elseif (empty($fullName) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid full name and registered email address.';
    } elseif ($requestType === 'nomination' && (empty($nomineeName) || empty($nomineeContact))) {
        $error = 'Please provide the designated nominee\'s full name and contact information under Section 14.';
    } else {
        if ($requestType === 'nomination') {
            $nominationSummary = "NOMINEE DESIGNATION (Section 14 DPDP Act 2023):\n"
                               . "Nominee Name: {$nomineeName}\n"
                               . "Relationship: {$nomineeRel}\n"
                               . "Nominee Contact: {$nomineeContact}\n"
                               . (!empty($details) ? "Additional Instructions: {$details}\n" : "");
            $details = $nominationSummary;
        }

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

            if ($requestType === 'nomination') {
                $msg = "Your statutory nomination request (#DSR-{$reqId}) under Section 14 of the Digital Personal Data Protection Act, 2023 has been recorded. Our Data Protection Cell will verify and log this representation within 30 days.";
            } else {
                $msg = "Your privacy request (#DSR-{$reqId}) has been successfully logged. In compliance with the Digital Personal Data Protection Act 2023 and global privacy frameworks, our Data Protection team will verify identity and fulfill your request within 30 days.";
            }
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
                <span class="kicker">🛡️ DPDP ACT 2023 · STATUTORY PORTAL</span>
                <h1>Data Principal Rights &amp; Nomination Portal</h1>
                <p>
                    Exercise your statutory rights under the <em>Digital Personal Data Protection Act, 2023</em> 
                    (Sections 6, 11, 12, 13 &amp; 14) and applicable data privacy regulations.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container-narrow">
        <div class="card" style="padding:32px; border-radius:14px; box-shadow:0 4px 20px rgba(0,0,0,0.04);">
            <?php if ($msg): ?>
                <div style="background:#ECFDF5; border:1.5px solid #A7F3D0; border-radius:12px; padding:22px; color:#065F46; margin-bottom:24px;">
                    <h3 style="margin:0 0 8px; font-size:18px; font-weight:800; display:flex; align-items:center; gap:8px;">
                        <span>✓</span> Request Recorded Successfully
                    </h3>
                    <p style="margin:0; font-size:14px; line-height:1.65;"><?= htmlspecialchars($msg) ?></p>
                </div>
                <div style="text-align:center; display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
                    <a href="/privacy" class="btn btn-outline">Read Privacy Policy (DPDP 2023)</a>
                    <a href="/" class="btn btn-primary">Return to Homepage</a>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div style="background:#FEF2F2; border:1.5px solid #FCA5A5; border-radius:10px; padding:16px; margin-bottom:24px; color:#991B1B; font-size:14px; font-weight:600; display:flex; align-items:center; gap:10px;">
                        <span>⚠️</span> <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <p style="font-size:14px; color:#475569; line-height:1.65; margin:0 0 24px;">
                    Infinity Learning is fully committed to user rights under the Indian Digital Personal Data Protection Act, 2023. 
                    Whether you are an independent candidate, a minor student's parent/guardian, or an educator, use this self-service 
                    form to submit verifiable data requests or designate a legal nominee.
                </p>

                <form method="POST" action="/data-request" id="dpdpRequestForm">
                    <div class="form-group" style="margin-bottom:20px;">
                        <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Select Statutory Category *</label>
                        <select name="request_type" id="requestTypeSelect" onchange="handleRequestTypeChange()" required class="form-control" style="width:100%; padding:12px 14px; border:1.5px solid #CBD5E1; border-radius:10px; font-size:14px; background:#fff;">
                            <option value="access_export">📥 Data Access &amp; Summary (Section 11 DPDP Act — Download all records)</option>
                            <option value="deletion_erasure">🗑️ Data Deletion &amp; Account Erasure (Section 12 DPDP Act — Right to be Forgotten)</option>
                            <option value="correction">✏️ Data Rectification &amp; Profile Correction (Section 12 DPDP Act)</option>
                            <option value="nomination">🤝 Nomination of Representative (Section 14 DPDP Act 2023)</option>
                            <option value="marketing_optout">🚫 Marketing &amp; Communications Consent Withdrawal (Section 6(4))</option>
                        </select>
                    </div>

                    <!-- Dynamic Section 14 Nomination Sub-card -->
                    <div id="nominationFields" style="display:none; background:#F5F3FF; border:1.5px solid #DDD6FE; border-radius:10px; padding:18px; margin-bottom:22px;">
                        <h4 style="margin:0 0 6px; font-size:14px; font-weight:800; color:#5B21B6; display:flex; align-items:center; gap:8px;">
                            <span>🤝</span> Section 14 DPDP Act 2023 — Nominee Designation Details
                        </h4>
                        <p style="margin:0 0 14px; font-size:12.5px; color:#6D28D9; line-height:1.55;">
                            Under Section 14 of the DPDP Act 2023, you have the right to nominate an individual who will exercise 
                            your data rights in the event of your death or incapacity.
                        </p>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:12px;">
                            <div>
                                <label style="display:block; font-size:12.5px; font-weight:700; color:#4C1D95; margin-bottom:4px;">Nominee Full Name *</label>
                                <input type="text" name="nominee_name" id="nomineeNameInput" placeholder="Legal name of nominee" class="form-control" style="width:100%; padding:10px 12px; border:1.5px solid #C4B5FD; border-radius:8px; font-size:13.5px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12.5px; font-weight:700; color:#4C1D95; margin-bottom:4px;">Relationship to You *</label>
                                <input type="text" name="nominee_rel" id="nomineeRelInput" placeholder="e.g. Spouse, Parent, Sibling, Legal Heir" class="form-control" style="width:100%; padding:10px 12px; border:1.5px solid #C4B5FD; border-radius:8px; font-size:13.5px;">
                            </div>
                        </div>
                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#4C1D95; margin-bottom:4px;">Nominee Contact Email or Phone *</label>
                            <input type="text" name="nominee_contact" id="nomineeContactInput" placeholder="Nominee's active email or phone number" class="form-control" style="width:100%; padding:10px 12px; border:1.5px solid #C4B5FD; border-radius:8px; font-size:13.5px;">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
                        <div>
                            <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Applicant Full Name *</label>
                            <input type="text" name="full_name" required value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" placeholder="Your legal name" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Registered Email Address *</label>
                            <input type="email" name="email" required value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="name@example.com" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
                        <div>
                            <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Contact Phone Number</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+91 98765 43210" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Applicant Capacity *</label>
                            <select name="relationship" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px; background:#fff;">
                                <option value="Self">I am the primary Data Principal (Student / Candidate / Teacher / Admin)</option>
                                <option value="Parent/Guardian">I am the Parent / Legal Guardian of a minor student (§ 9 DPDP)</option>
                                <option value="Nominee">I am the Designated Nominee of a Data Principal (§ 14 DPDP)</option>
                                <option value="School Administrator">I am an authorized School Administrator</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:22px;">
                        <label style="display:block; font-size:13.5px; font-weight:700; color:#0F172A; margin-bottom:6px;">Specific Context or Instructions</label>
                        <textarea name="details" rows="3" placeholder="Please specify if this applies to a specific course, BPSC score, school record, or certificate..." class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #CBD5E1; border-radius:8px; font-size:14px;"></textarea>
                    </div>

                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:16px; margin-bottom:24px; font-size:12.5px; color:#475569; line-height:1.6;">
                        🛡️ <strong>Statutory Timeline &amp; Identity Verification:</strong> Pursuant to Section 13 of the DPDP Act 2023, your request will receive an acknowledgement within 48 hours and complete resolution within <strong>30 calendar days</strong>. For security, we may send a verification email or OTP before processing irreversible actions. If unresolved within 30 days, you have the statutory right to approach the <strong>Data Protection Board of India (DPBI)</strong>.
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="width:100%; padding:14px; font-size:15px; font-weight:700; border-radius:10px; cursor:pointer;">
                        Submit Official Request Under DPDP Act 2023 →
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function handleRequestTypeChange() {
    const select = document.getElementById('requestTypeSelect');
    const nomBox = document.getElementById('nominationFields');
    const nomName = document.getElementById('nomineeNameInput');
    const nomRel = document.getElementById('nomineeRelInput');
    const nomContact = document.getElementById('nomineeContactInput');

    if (select && select.value === 'nomination') {
        if (nomBox) nomBox.style.display = 'block';
        if (nomName) nomName.required = true;
        if (nomRel) nomRel.required = true;
        if (nomContact) nomContact.required = true;
    } else {
        if (nomBox) nomBox.style.display = 'none';
        if (nomName) nomName.required = false;
        if (nomRel) nomRel.required = false;
        if (nomContact) nomContact.required = false;
    }
}
document.addEventListener('DOMContentLoaded', handleRequestTypeChange);
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

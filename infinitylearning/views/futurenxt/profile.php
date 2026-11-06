<?php
declare(strict_types=1);

use App\FutureNxt\Auth;
use App\FutureNxt\Employee;
use App\Helper;

Auth::requireLogin();
$me = Auth::user();

// Refresh latest employee record directly from DB
$freshMe = Employee::findById((int)$me['id']);
if ($freshMe) {
    $me = $freshMe;
}

$pageTitle = 'My Profile & Account Settings';
$activePage = 'profile';

$error = $_GET['error'] ?? $_SESSION['fnx_error'] ?? null;
$success = $_GET['success'] ?? $_SESSION['fnx_success'] ?? null;
unset($_SESSION['fnx_error'], $_SESSION['fnx_success']);

require __DIR__ . '/layouts/header.php';
?>

<div class="fnx-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:14px;">
        <div>
            <h1 style="margin:0 0 4px; font-size:24px; font-weight:800; color:#111827;">My Profile &amp; Settings</h1>
            <p style="margin:0; font-size:13.5px; color:#4B5563;">
                Manage your personal profile, credentials, and view verified identification &amp; banking records.
            </p>
        </div>
        <?php if (Auth::isAdminOrExec()): ?>
            <button type="button" onclick="fnxOpenModal('editProfileModal')" class="fnx-btn fnx-btn-orange" style="box-shadow:0 3px 10px rgba(255,122,24,0.3); font-size:14px; padding:9px 20px;">
                ✏️ Edit Profile
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($error)): ?>
        <div class="fnx-alert fnx-alert-danger" style="margin-bottom:20px;">
            <span>⚠️</span> <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="fnx-alert fnx-alert-success" style="margin-bottom:20px;">
            <span>✓</span> <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 2fr; gap:24px;">
        <!-- Left: Profile Badge & Security -->
        <div>
            <!-- Badge Card -->
            <div class="fnx-card" style="text-align:center; padding:28px 20px; margin-bottom:24px;">
                <div style="position:relative; display:inline-block; margin-bottom:14px;">
                    <img src="<?= !empty($me['photo_path']) ? htmlspecialchars($me['photo_path']) : '/assets/img/avatar-default.png' ?>" 
                         alt="<?= htmlspecialchars($me['full_name']) ?>" 
                         id="fnxMyAvatarImg"
                         style="width:96px; height:96px; border-radius:22px; object-fit:cover; border:3px solid var(--fnx-orange); box-shadow:0 8px 20px rgba(255,101,0,0.25); display:block;">
                    
                    <?php if (Auth::isAdminOrExec()): ?>
                        <button type="button" 
                                onclick="document.getElementById('profilePhotoFile').click()" 
                                style="position:absolute; bottom:-6px; right:-6px; width:32px; height:32px; border-radius:50%; background:var(--fnx-orange); color:#FFFFFF; border:2px solid #FFFFFF; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.25); font-size:14px;" 
                                title="Upload Profile Picture">
                            📷
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (Auth::isAdminOrExec()): ?>
                    <form method="POST" action="/futurenxt-handler.php?action=upload_profile_photo" enctype="multipart/form-data" id="profilePhotoForm" style="margin-bottom:14px;">
                        <input type="file" id="profilePhotoFile" name="photo" accept="image/png,image/jpeg,image/jpg,image/webp" style="display:none;" onchange="this.form.submit()">
                        <button type="button" onclick="document.getElementById('profilePhotoFile').click()" class="fnx-btn fnx-btn-outline" style="font-size:12px; padding:6px 14px; border-radius:999px;">
                            📷 Upload Profile Picture
                        </button>
                        <div style="font-size:11px; color:#6B7280; margin-top:6px;">JPG, PNG or WEBP (Max 5MB)</div>
                    </form>
                <?php endif; ?>
                
                <h2 style="margin:0 0 4px; font-size:20px; font-weight:800; color:#111827;">
                    <?= htmlspecialchars($me['full_name']) ?>
                </h2>
                <div style="margin-bottom:12px;">
                    <span class="fnx-badge fnx-badge-orange" style="font-family:monospace; font-size:13px;">
                        <?= htmlspecialchars($me['emp_code']) ?>
                    </span>
                </div>
                <div style="font-size:13.5px; font-weight:700; color:#111827;">
                    <?= htmlspecialchars($me['designation'] ?? ucfirst($me['role'])) ?>
                </div>
                <div style="font-size:12.5px; color:#4B5563; margin-top:2px; font-weight:600;">
                    <?= htmlspecialchars($me['department'] ?? 'Corporate Executive') ?>
                </div>

                <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--fnx-border); display:flex; justify-content:space-around; font-size:12px;">
                    <div>
                        <div style="color:#4B5563; font-weight:600;">Status</div>
                        <strong style="color:#10B981;"><?= ucfirst($me['status'] ?? 'active') ?></strong>
                    </div>
                    <div>
                        <div style="color:#4B5563; font-weight:600;">Joined</div>
                        <strong style="color:#111827;"><?= !empty($me['date_of_joining']) ? date('d M Y', strtotime($me['date_of_joining'])) : (!empty($me['doj']) ? date('d M Y', strtotime($me['doj'])) : '—') ?></strong>
                    </div>
                </div>

                <?php if (Auth::isAdminOrExec()): ?>
                    <div style="margin-top:18px; padding-top:14px; border-top:1px dashed var(--fnx-border);">
                        <button type="button" onclick="fnxOpenModal('editProfileModal')" class="fnx-btn fnx-btn-orange" style="width:100%; font-size:13px;">
                            ✏️ Edit Profile Details
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Change Password Form -->
            <div class="fnx-card">
                <h3 style="margin:0 0 14px; font-size:16px; font-weight:800; color:#111827 !important;">Security &amp; Password</h3>
                <form method="POST" action="/futurenxt-handler.php?action=change_password">
                    <div style="margin-bottom:14px;">
                        <label class="fnx-form-label">Current Password *</label>
                        <div class="fnx-pw-wrap">
                            <input type="password" id="current_password" name="current_password" class="fnx-input" required>
                            <button type="button" onclick="fnxTogglePassword('current_password', this)" class="fnx-pw-eye" title="Show password">👁️</button>
                        </div>
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="fnx-form-label">New Password *</label>
                        <div class="fnx-pw-wrap">
                            <input type="password" id="new_password" name="new_password" class="fnx-input" placeholder="Min 8 characters" required>
                            <button type="button" onclick="fnxTogglePassword('new_password', this)" class="fnx-pw-eye" title="Show password">👁️</button>
                        </div>
                    </div>
                    <div style="margin-bottom:16px;">
                        <label class="fnx-form-label">Confirm New Password *</label>
                        <div class="fnx-pw-wrap">
                            <input type="password" id="confirm_password" name="confirm_password" class="fnx-input" required>
                            <button type="button" onclick="fnxTogglePassword('confirm_password', this)" class="fnx-pw-eye" title="Show password">👁️</button>
                        </div>
                    </div>
                    <button type="submit" class="fnx-btn fnx-btn-orange" style="width:100%;">Update Password</button>
                </form>
            </div>
        </div>

        <!-- Right: Profile Info, KYC Documents, Banking -->
        <div>
            <!-- Personal & Contact Details -->
            <div class="fnx-card" style="margin-bottom:24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="margin:0; font-size:16px; font-weight:800; color:#111827 !important;">Personal &amp; Contact Information</h3>
                    <?php if (Auth::isAdminOrExec()): ?>
                        <button type="button" onclick="fnxOpenModal('editProfileModal')" class="fnx-btn fnx-btn-outline" style="font-size:12px; padding:4px 12px;">
                            ✏️ Edit
                        </button>
                    <?php endif; ?>
                </div>

                <div class="fnx-grid-2" style="font-size:13px; row-gap:14px;">
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">Corporate Email:</span>
                        <strong style="color:#111827;"><?= htmlspecialchars($me['email']) ?></strong>
                    </div>
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">Phone Number:</span>
                        <strong style="color:#111827;"><?= htmlspecialchars($me['phone'] ?? '—') ?></strong>
                    </div>
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">Date of Birth:</span>
                        <strong style="color:#111827;"><?= !empty($me['dob']) ? date('d M Y', strtotime($me['dob'])) : '—' ?></strong>
                    </div>
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">Highest Qualification:</span>
                        <strong style="color:#111827;"><?= htmlspecialchars($me['highest_qualification'] ?? $me['qualification'] ?? '—') ?></strong>
                    </div>
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">Current Address:</span>
                        <span style="color:#111827; font-weight:600;"><?= nl2br(htmlspecialchars($me['current_address'] ?? '—')) ?></span>
                    </div>
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">Permanent Address:</span>
                        <span style="color:#111827; font-weight:600;"><?= nl2br(htmlspecialchars($me['permanent_address'] ?? '—')) ?></span>
                    </div>
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">Emergency Contact:</span>
                        <strong style="color:#111827;">
                            <?= htmlspecialchars($me['emergency_contact_name'] ?? '—') ?>
                            <?php if (!empty($me['emergency_contact_relation'])): ?>
                                (<?= htmlspecialchars($me['emergency_contact_relation']) ?>)
                            <?php endif; ?>
                        </strong>
                    </div>
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">Emergency Phone:</span>
                        <strong style="color:#111827;"><?= htmlspecialchars($me['emergency_contact_phone'] ?? '—') ?></strong>
                    </div>
                </div>
            </div>

            <!-- KYC Documents -->
            <div class="fnx-card" style="margin-bottom:24px;">
                <h3 style="margin:0 0 16px; font-size:16px; font-weight:800; color:#111827 !important;">Verified Identification</h3>
                <div style="display:flex; flex-direction:column; gap:12px; font-size:13px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:8px; border-bottom:1px solid var(--fnx-border);">
                        <div>
                            <div style="font-weight:700; color:#111827;">Aadhaar Card</div>
                            <?php 
                            $realAadhar = (string)($me['aadhar_number'] ?? $me['aadhar_no'] ?? '');
                            $maskAadhar = !empty($realAadhar) ? '•••• •••• ' . substr($realAadhar, -4) : 'Not registered';
                            ?>
                            <div class="fnx-mask-wrap" style="margin-top:2px;">
                                <span id="aadharMask" data-mask="<?= htmlspecialchars($maskAadhar) ?>" data-real="<?= htmlspecialchars($realAadhar ?: 'Not registered') ?>" data-masked="1" style="font-family:monospace; font-size:13px; color:#111827; font-weight:700;">
                                    <?= htmlspecialchars($maskAadhar) ?>
                                </span>
                                <?php if (!empty($realAadhar)): ?>
                                    <button type="button" class="fnx-eye-btn" onclick="fnxToggleMask('aadharMask', this)" title="Show Aadhaar number">👁️</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($me['aadhar_doc_path'])): ?>
                            <a href="<?= htmlspecialchars($me['aadhar_doc_path']) ?>" target="_blank" class="fnx-btn fnx-btn-outline" style="font-size:12px; padding:3px 10px;">View Document ↗</a>
                        <?php endif; ?>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:8px; border-bottom:1px solid var(--fnx-border);">
                        <div>
                            <div style="font-weight:700; color:#111827;">PAN Card</div>
                            <?php 
                            $realPan = (string)($me['pan_number'] ?? $me['pan_no'] ?? '');
                            $maskPan = !empty($realPan) ? '••••••' . substr($realPan, -4) : 'Not registered';
                            ?>
                            <div class="fnx-mask-wrap" style="margin-top:2px;">
                                <span id="panMask" data-mask="<?= htmlspecialchars($maskPan) ?>" data-real="<?= htmlspecialchars($realPan ?: 'Not registered') ?>" data-masked="1" style="font-family:monospace; font-size:13px; color:#111827; font-weight:700;">
                                    <?= htmlspecialchars($maskPan) ?>
                                </span>
                                <?php if (!empty($realPan)): ?>
                                    <button type="button" class="fnx-eye-btn" onclick="fnxToggleMask('panMask', this)" title="Show PAN number">👁️</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($me['pan_doc_path'])): ?>
                            <a href="<?= htmlspecialchars($me['pan_doc_path']) ?>" target="_blank" class="fnx-btn fnx-btn-outline" style="font-size:12px; padding:3px 10px;">View Document ↗</a>
                        <?php endif; ?>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-weight:700; color:#111827;">Curriculum Vitae / Resume</div>
                        </div>
                        <?php 
                        $resumeFile = $me['resume_doc_path'] ?? $me['resume_path'] ?? '';
                        if (!empty($resumeFile)): ?>
                            <a href="<?= htmlspecialchars($resumeFile) ?>" target="_blank" class="fnx-btn fnx-btn-outline" style="font-size:12px; padding:3px 10px;">Download PDF 📄</a>
                        <?php else: ?>
                            <span style="color:#6B7280;">Not uploaded</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Banking Records with Eye Toggle -->
            <div class="fnx-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="margin:0; font-size:16px; font-weight:800; color:#111827 !important;">Payroll &amp; Banking Details</h3>
                    <?php if (Auth::isAdminOrExec()): ?>
                        <button type="button" onclick="fnxOpenModal('editProfileModal')" class="fnx-btn fnx-btn-outline" style="font-size:12px; padding:4px 12px;">
                            ✏️ Edit
                        </button>
                    <?php endif; ?>
                </div>

                <div class="fnx-grid-2" style="font-size:13px; row-gap:14px;">
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">Bank Name:</span>
                        <strong style="color:#111827;"><?= htmlspecialchars($me['bank_name'] ?? '—') ?></strong>
                    </div>
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">Account Number:</span>
                        <?php 
                        $realAcc = (string)($me['bank_account_no'] ?? '');
                        $maskAcc = !empty($realAcc) ? '••••••••' . substr($realAcc, -4) : '—';
                        ?>
                        <div class="fnx-mask-wrap">
                            <strong id="bankAccMask" data-mask="<?= htmlspecialchars($maskAcc) ?>" data-real="<?= htmlspecialchars($realAcc ?: '—') ?>" data-masked="1" style="font-family:monospace; color:#111827; font-size:14px;">
                                <?= htmlspecialchars($maskAcc) ?>
                            </strong>
                            <?php if (!empty($realAcc)): ?>
                                <button type="button" class="fnx-eye-btn" onclick="fnxToggleMask('bankAccMask', this)" title="Show account number">👁️</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">IFSC Code:</span>
                        <strong style="font-family:monospace; color:#111827;"><?= htmlspecialchars($me['bank_ifsc'] ?? '—') ?></strong>
                    </div>
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">Account Holder:</span>
                        <strong style="color:#111827;"><?= htmlspecialchars($me['bank_account_name'] ?? $me['full_name'] ?? '—') ?></strong>
                    </div>
                    <div>
                        <span style="color:#4B5563; display:block; margin-bottom:2px; font-weight:600;">UPI ID:</span>
                        <?php 
                        $realUpi = (string)($me['upi_id'] ?? '');
                        $maskUpi = !empty($realUpi) ? '••••' . substr($realUpi, -6) : '—';
                        ?>
                        <div class="fnx-mask-wrap">
                            <strong id="upiMask" data-mask="<?= htmlspecialchars($maskUpi) ?>" data-real="<?= htmlspecialchars($realUpi ?: '—') ?>" data-masked="1" style="font-family:monospace; color:#111827;">
                                <?= htmlspecialchars($maskUpi) ?>
                            </strong>
                            <?php if (!empty($realUpi)): ?>
                                <button type="button" class="fnx-eye-btn" onclick="fnxToggleMask('upiMask', this)" title="Show UPI ID">👁️</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (Auth::isAdminOrExec()): ?>
<!-- Edit Profile Modal (Admin, CEO, CTO Only) -->
<div class="fnx-modal-backdrop" id="editProfileModal" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:850px; width:95%; max-height:90vh; overflow-y:auto;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:#111827;">✏️ Edit Profile Details</h3>
                <p style="margin:4px 0 0; font-size:12.5px; color:#4B5563;">Exclusive executive access for Admin, CEO, and CTO to update personal, contact, and banking information.</p>
            </div>
            <button type="button" onclick="fnxCloseModal('editProfileModal')" class="fnx-modal-close">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=update_employee" enctype="multipart/form-data">
            <input type="hidden" name="employee_id" value="<?= (int)$me['id'] ?>">
            <input type="hidden" name="redirect_url" value="/futurenxt/profile">

            <div class="fnx-modal-body">
                <!-- Section 1: Basic Information -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    1. Personal &amp; Executive Information
                </div>
                <div class="fnx-grid-2" style="margin-bottom:18px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Full Name *</label>
                        <input type="text" name="full_name" class="fnx-input" value="<?= htmlspecialchars($me['full_name'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">Corporate Email *</label>
                        <input type="email" name="email" class="fnx-input" value="<?= htmlspecialchars($me['email'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">Phone Number</label>
                        <input type="text" name="phone" class="fnx-input" value="<?= htmlspecialchars($me['phone'] ?? '') ?>" placeholder="+91 98765 43210">
                    </div>
                    <div>
                        <label class="fnx-form-label">Designation</label>
                        <input type="text" name="designation" class="fnx-input" value="<?= htmlspecialchars($me['designation'] ?? '') ?>" placeholder="e.g. Chief Executive Officer">
                    </div>
                    <div>
                        <label class="fnx-form-label">Department</label>
                        <input type="text" name="department" class="fnx-input" value="<?= htmlspecialchars($me['department'] ?? 'Corporate Leadership') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Date of Birth</label>
                        <input type="date" name="dob" class="fnx-input" value="<?= htmlspecialchars($me['dob'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Highest Qualification</label>
                        <input type="text" name="highest_qualification" class="fnx-input" value="<?= htmlspecialchars($me['highest_qualification'] ?? $me['qualification'] ?? '') ?>" placeholder="e.g. B.Tech / MBA / Ph.D">
                    </div>
                    <div>
                        <label class="fnx-form-label">Date of Joining</label>
                        <input type="date" name="date_of_joining" class="fnx-input" value="<?= htmlspecialchars($me['date_of_joining'] ?? $me['doj'] ?? '') ?>">
                    </div>
                </div>

                <!-- Section 2: Addresses -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    2. Residential &amp; Permanent Addresses
                </div>
                <div class="fnx-grid-2" style="margin-bottom:18px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Current Residential Address</label>
                        <textarea name="current_address" class="fnx-input" rows="2" style="resize:vertical;"><?= htmlspecialchars($me['current_address'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="fnx-form-label">Permanent Address</label>
                        <textarea name="permanent_address" class="fnx-input" rows="2" style="resize:vertical;"><?= htmlspecialchars($me['permanent_address'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Section 3: Emergency Contacts -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    3. Emergency Contact
                </div>
                <div class="fnx-grid-3" style="margin-bottom:18px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Contact Person Name</label>
                        <input type="text" name="emergency_contact_name" class="fnx-input" value="<?= htmlspecialchars($me['emergency_contact_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Emergency Phone</label>
                        <input type="text" name="emergency_contact_phone" class="fnx-input" value="<?= htmlspecialchars($me['emergency_contact_phone'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Relationship</label>
                        <input type="text" name="emergency_contact_relation" class="fnx-input" value="<?= htmlspecialchars($me['emergency_contact_relation'] ?? '') ?>" placeholder="e.g. Spouse / Parent">
                    </div>
                </div>

                <!-- Section 4: Banking & Payroll -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    4. Payroll &amp; Banking Records
                </div>
                <div class="fnx-grid-2" style="margin-bottom:18px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="fnx-input" value="<?= htmlspecialchars($me['bank_name'] ?? '') ?>" placeholder="e.g. HDFC Bank">
                    </div>
                    <div>
                        <label class="fnx-form-label">Bank Account Number</label>
                        <input type="text" name="bank_account_no" class="fnx-input" value="<?= htmlspecialchars($me['bank_account_no'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">IFSC Code</label>
                        <input type="text" name="bank_ifsc" class="fnx-input" value="<?= htmlspecialchars($me['bank_ifsc'] ?? '') ?>" placeholder="e.g. HDFC0001234" style="text-transform:uppercase;">
                    </div>
                    <div>
                        <label class="fnx-form-label">Account Holder Name</label>
                        <input type="text" name="bank_account_name" class="fnx-input" value="<?= htmlspecialchars($me['bank_account_name'] ?? $me['full_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">UPI ID</label>
                        <input type="text" name="upi_id" class="fnx-input" value="<?= htmlspecialchars($me['upi_id'] ?? '') ?>" placeholder="username@bank">
                    </div>
                </div>

                <!-- Section 5: Identification (KYC) -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    5. Identification (Aadhaar &amp; PAN)
                </div>
                <div class="fnx-grid-2" style="margin-bottom:14px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Aadhaar Card Number (12 digits)</label>
                        <input type="text" name="aadhar_number" class="fnx-input" value="<?= htmlspecialchars($me['aadhar_number'] ?? $me['aadhar_no'] ?? '') ?>" placeholder="1234 5678 9012">
                    </div>
                    <div>
                        <label class="fnx-form-label">PAN Card Number (10 chars)</label>
                        <input type="text" name="pan_number" class="fnx-input" value="<?= htmlspecialchars($me['pan_number'] ?? $me['pan_no'] ?? '') ?>" placeholder="ABCDE1234F" style="text-transform:uppercase;">
                    </div>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" onclick="fnxCloseModal('editProfileModal')" class="fnx-btn fnx-btn-outline">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange" style="padding:10px 24px;">✓ Save Profile Changes</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/layouts/footer.php'; ?>

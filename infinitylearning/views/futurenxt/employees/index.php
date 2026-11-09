<?php
declare(strict_types=1);

use App\FutureNxt\Auth;
use App\FutureNxt\Employee;
use App\Helper;

Auth::requireLogin();
$me = Auth::user();
$pageTitle = 'Personnel & Partners Directory';
$activePage = 'employees';

$filters = [
    'role' => $_GET['role'] ?? '',
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? '',
];
$employees = Employee::list($filters);
$managersList = Employee::getManagers();

$error = $_SESSION['fnx_error'] ?? null;
$success = $_SESSION['fnx_success'] ?? null;
unset($_SESSION['fnx_error'], $_SESSION['fnx_success']);

require __DIR__ . '/../layouts/header.php';
?>

<div class="fnx-container">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="margin:0 0 4px; font-size:24px; font-weight:800; color:var(--fnx-navy);">Partners &amp; Team</h1>
            <p style="margin:0; font-size:13.5px; color:var(--fnx-muted);">
                Directory of executives, officers, managers, and partners across FutureNxt.
            </p>
        </div>
        <div class="fnx-page-header-actions" style="display:flex; gap:10px;">
            <?php if (Auth::isAdminOrExec() || Auth::role() === 'manager' || Auth::role() === 'office_manager'): ?>
                <button type="button" class="fnx-btn fnx-btn-orange" onclick="fnxOpenModal('onboardModal'); fnxUpdateCodePreview();">
                    <span>+</span> Onboard New Partner
                </button>
            <?php endif; ?>
        </div>
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

    <!-- Filter Bar -->
    <div class="fnx-card" style="padding:16px 20px; margin-bottom:24px;">
        <form method="GET" action="/futurenxt/employees" class="fnx-filter-form" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <div style="flex:1; min-width:200px;" class="fnx-filter-search">
                <input type="text" name="search" class="fnx-input" placeholder="Search by name, email, phone, or code (e.g. FNX-I-001)..." value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
            <div style="min-width:140px;" class="fnx-filter-item">
                <select name="role" class="fnx-select">
                    <option value="">All Roles</option>
                    <option value="ceo" <?= $filters['role'] === 'ceo' ? 'selected' : '' ?>>CEO</option>
                    <option value="cto" <?= $filters['role'] === 'cto' ? 'selected' : '' ?>>CTO</option>
                    <option value="admin" <?= $filters['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="office_incharge" <?= $filters['role'] === 'office_incharge' ? 'selected' : '' ?>>Office In-Charge</option>
                    <option value="manager" <?= $filters['role'] === 'manager' ? 'selected' : '' ?>>Manager</option>
                    <option value="employee" <?= $filters['role'] === 'employee' ? 'selected' : '' ?>>Employee</option>
                    <option value="intern" <?= $filters['role'] === 'intern' ? 'selected' : '' ?>>Intern</option>
                </select>
            </div>
            <div style="min-width:130px;" class="fnx-filter-item">
                <select name="status" class="fnx-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="pip" <?= $filters['status'] === 'pip' ? 'selected' : '' ?>>PIP</option>
                    <option value="suspended" <?= $filters['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    <option value="resigned" <?= $filters['status'] === 'resigned' ? 'selected' : '' ?>>Resigned</option>
                    <option value="terminated" <?= $filters['status'] === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                </select>
            </div>
            <div class="fnx-filter-actions" style="display:flex; gap:8px;">
                <button type="submit" class="fnx-btn fnx-btn-dark">Filter</button>
                <?php if (!empty($filters['search']) || !empty($filters['role']) || !empty($filters['status'])): ?>
                    <a href="/futurenxt/employees" class="fnx-btn fnx-btn-outline">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Personnel Directory Table -->
    <div class="fnx-card">
        <div class="fnx-table-scroll-hint">
            <span>↔️ Swipe horizontally to view all partner records &amp; actions</span>
            <span>👉</span>
        </div>
        <div class="fnx-table-wrap">
            <table class="fnx-table">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>ID Code</th>
                        <th>Role &amp; Department</th>
                        <th>Contact</th>
                        <th>Active Tasks</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:36px; color:var(--fnx-muted);">
                                No personnel found matching criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $emp): 
                            $photo = !empty($emp['photo_path']) ? $emp['photo_path'] : '/assets/img/avatar-default.png';
                            $activeTaskCount = (int)($emp['active_tasks_count'] ?? 0);
                        ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($emp['full_name']) ?>" 
                                             onerror="this.onerror=null; this.src='/assets/img/avatar-default.png';"
                                             style="width:40px; height:40px; border-radius:10px; object-fit:cover; border:1px solid var(--fnx-border);">
                                        <div>
                                            <a href="/futurenxt/employees/view?id=<?= $emp['id'] ?>" style="font-weight:700; color:var(--fnx-navy); text-decoration:none;">
                                                <?= htmlspecialchars($emp['full_name']) ?>
                                            </a>
                                            <div style="font-size:12px; color:var(--fnx-muted);"><?= htmlspecialchars($emp['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fnx-badge fnx-badge-orange" style="font-family:monospace; font-size:11.5px;">
                                        <?= htmlspecialchars($emp['emp_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:600; font-size:13px; color:var(--fnx-text);">
                                        <?= htmlspecialchars($emp['designation'] ?? ucfirst($emp['role'])) ?>
                                    </div>
                                    <div style="font-size:11.5px; color:var(--fnx-muted);"><?= htmlspecialchars($emp['department'] ?? 'Operations') ?></div>
                                    <?php if (!empty($emp['manager_name'])): ?>
                                        <div style="font-size:11px; color:#2563EB; margin-top:3px; font-weight:600;">
                                            ↳ Reports to: <?= htmlspecialchars($emp['manager_name']) ?> (<?= htmlspecialchars($emp['manager_code']) ?>)
                                        </div>
                                    <?php elseif (in_array($emp['role'], ['ceo', 'cto', 'admin'])): ?>
                                        <div style="font-size:11px; color:#D97706; margin-top:3px; font-weight:700;">
                                            ★ Executive Leadership
                                        </div>
                                    <?php else: ?>
                                        <div style="font-size:11px; color:#6B7280; margin-top:3px;">
                                            ↳ Direct to Leadership
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($emp['reportees_count']) && (int)$emp['reportees_count'] > 0): ?>
                                        <span class="fnx-badge fnx-badge-info" style="font-size:10px; padding:2px 7px; margin-top:4px; display:inline-block;">
                                            👥 <?= (int)$emp['reportees_count'] ?> Direct Report(s)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12.5px;">
                                    <?= htmlspecialchars($emp['phone'] ?? '—') ?>
                                </td>
                                <td>
                                    <span class="fnx-badge <?= $activeTaskCount >= 3 ? 'fnx-badge-danger' : ($activeTaskCount > 0 ? 'fnx-badge-info' : '') ?>">
                                        <?= $activeTaskCount ?> / 3 Active
                                    </span>
                                </td>
                                <td>
                                    <?php if ($emp['status'] === 'active'): ?>
                                        <span class="fnx-badge fnx-badge-success">Active</span>
                                    <?php elseif ($emp['status'] === 'pip'): ?>
                                        <span class="fnx-badge fnx-badge-danger" title="<?= htmlspecialchars($emp['pip_reason'] ?? 'PIP') ?>">PIP</span>
                                    <?php elseif ($emp['status'] === 'suspended'): ?>
                                        <span class="fnx-badge" style="background:#EF4444; color:#FFFFFF; font-weight:800;">Suspended</span>
                                    <?php elseif ($emp['status'] === 'terminated'): ?>
                                        <span class="fnx-badge" style="background:#111827; color:#FFFFFF; font-weight:800;">Terminated</span>
                                    <?php elseif ($emp['status'] === 'resigned'): ?>
                                        <?php 
                                            $remDays = !empty($emp['notice_end_date']) ? max(0, (int)ceil((strtotime($emp['notice_end_date']) - time()) / 86400)) : 15;
                                        ?>
                                        <span class="fnx-badge fnx-badge-warning" style="font-weight:700;" title="15-day notice period ends: <?= !empty($emp['notice_end_date']) ? date('d M Y', strtotime($emp['notice_end_date'])) : '—' ?>">
                                            15d Notice (<?= $remDays ?>d left)
                                        </span>
                                    <?php else: ?>
                                        <span class="fnx-badge"><?= ucfirst($emp['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex; gap:6px; align-items:center;">
                                        <a href="/futurenxt/employees/view?id=<?= $emp['id'] ?>" class="fnx-btn fnx-btn-outline" style="padding:4px 8px; font-size:11.5px;">
                                            View Profile
                                        </a>
                                        <?php if (Auth::isAdminOrExec()): ?>
                                            <button type="button" class="fnx-btn fnx-btn-outline" style="padding:4px 8px; font-size:11.5px; color:#2563EB; border-color:#93C5FD;" 
                                                    title="Send Onboarding Credentials to Partner Email"
                                                    onclick="resendOnboardingEmail(<?= (int)$emp['id'] ?>, '<?= htmlspecialchars(addslashes($emp['full_name'])) ?>', '<?= htmlspecialchars(addslashes($emp['email'])) ?>')">
                                                📧 Onboarding Mail
                                            </button>
                                            <button type="button" class="fnx-btn fnx-btn-orange" style="padding:4px 9px; font-size:11.5px;" onclick='openEditEmployeeModal(<?= json_encode($emp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                ✏️ Edit
                                            </button>
                                        <?php endif; ?>
                                        <?php if (Auth::isAdminOrExec() && (int)$emp['id'] !== (int)$me['id']): ?>
                                            <button type="button" class="fnx-btn fnx-btn-outline" style="padding:4px 8px; font-size:11.5px; color:#D97706; border-color:#FCD34D;" 
                                                    onclick="openAdminResetPasswordModal(<?= (int)$emp['id'] ?>, '<?= htmlspecialchars(addslashes($emp['full_name'])) ?>', '<?= htmlspecialchars(addslashes($emp['emp_code'])) ?>', '<?= htmlspecialchars(addslashes($emp['email'])) ?>')">
                                                🔑 Reset PW
                                            </button>
                                        <?php endif; ?>
                                        <?php if (Auth::isAdminOrExec() && $emp['role'] !== 'admin' && (int)$emp['id'] !== (int)$me['id']): ?>
                                            <button type="button" class="fnx-btn fnx-btn-outline" style="padding:4px 8px; font-size:11.5px; color:#EF4444; border-color:#FCA5A5;" 
                                                    onclick="openStatusModal(<?= (int)$emp['id'] ?>, '<?= htmlspecialchars(addslashes($emp['full_name'])) ?>', '<?= htmlspecialchars($emp['status']) ?>', '<?= htmlspecialchars(addslashes($emp['notice_end_date'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($emp['status_remarks'] ?? '')) ?>')">
                                                Status / Action
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL: ONBOARD NEW PERSONNEL
     ========================================================================= -->
<?php if (Auth::isAdminOrExec() || Auth::role() === 'manager' || Auth::role() === 'office_manager'): ?>
<div id="onboardModal" class="fnx-modal-backdrop" style="display:<?= (isset($_GET['action']) && $_GET['action'] === 'new') ? 'flex' : 'none' ?>;">
    <div class="fnx-modal-dialog" style="max-width:760px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:var(--fnx-navy);">Onboard New Employee / Partner</h3>
                <p style="margin:2px 0 0; font-size:12.5px; color:var(--fnx-muted);">Generates unique sequential ID (FNX-I-001, FNX-M-001-E-001, etc.) &amp; credentials</p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('onboardModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=create_employee" enctype="multipart/form-data">
            <input type="hidden" name="redirect_url" value="/futurenxt/employees">
            <div class="fnx-modal-body" style="max-height:75vh; overflow-y:auto;">
                <!-- Role & Core Account -->
                <h4 style="margin:0 0 12px; font-size:14px; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em;">1. Designation, Hierarchy &amp; Access</h4>
                
                <div class="fnx-grid-2" style="margin-bottom:14px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Role Classification *</label>
                        <select name="role" id="onboardRole" class="fnx-select" onchange="fnxUpdateCodePreview()" required>
                            <option value="employee" selected>Employee</option>
                            <option value="intern">Intern</option>
                            <?php if (Auth::isAdminOrExec()): ?>
                                <option value="manager">Manager</option>
                                <option value="office_manager">Office Manager</option>
                                <?php if ($me['role'] === 'admin'): ?>
                                    <option value="cto">CTO</option>
                                    <option value="ceo">CEO</option>
                                    <option value="admin">Admin</option>
                                <?php endif; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="fnx-form-label">Assign Reporting Manager *</label>
                        <?php if (Auth::isAdminOrExec()): ?>
                            <select name="manager_id" id="onboardManagerId" class="fnx-select" onchange="fnxUpdateCodePreview()">
                                <option value="">-- Direct under CEO / CTO / Executive --</option>
                                <?php foreach ($managersList as $mgr): ?>
                                    <option value="<?= $mgr['id'] ?>" data-code="<?= htmlspecialchars($mgr['emp_code']) ?>" data-role="<?= htmlspecialchars($mgr['role']) ?>">
                                        <?= htmlspecialchars($mgr['full_name']) ?> (<?= htmlspecialchars($mgr['emp_code']) ?> · <?= ucfirst($mgr['role']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color:#6B7280; font-size:11px; margin-top:2px; display:block;">
                                Under a manager (e.g. FNX-M-001) code becomes <strong>FNX-M-001-E-001</strong>
                            </small>
                        <?php else: ?>
                            <input type="hidden" name="manager_id" id="onboardManagerId" value="<?= (int)$me['id'] ?>">
                            <div class="fnx-input" style="background:#F3F4F6; color:#111827; font-weight:700;">
                                Assigned Under You: <?= htmlspecialchars($me['full_name']) ?> (<?= htmlspecialchars($me['emp_code']) ?>)
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Live Auto-Generated ID Badge -->
                <div style="margin-bottom:16px; padding:12px 16px; background:#FFF9F4; border:1.5px solid var(--fnx-border); border-radius:10px; display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <strong style="font-size:13px; color:#111827; display:block;">System Generated Employee ID:</strong>
                        <span style="font-size:11.5px; color:#6B7280;">Hierarchical code automatically formatted</span>
                    </div>
                    <span id="onboardCodePreview" class="fnx-badge fnx-badge-orange" style="font-family:'JetBrains Mono',monospace; font-size:14px; font-weight:800; padding:6px 12px;">
                        Loading...
                    </span>
                </div>

                <div class="fnx-grid-2" style="margin-bottom:16px;">
                    <div>
                        <label class="fnx-form-label">Department *</label>
                        <input type="text" name="department" class="fnx-input" placeholder="e.g. Engineering, AI Research, Marketing" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">Official Job Title</label>
                        <input type="text" name="designation" class="fnx-input" placeholder="e.g. Full Stack Intern, Tech Lead">
                    </div>
                </div>

                <div class="fnx-grid-2" style="margin-bottom:20px;">
                    <div>
                        <label class="fnx-form-label">Corporate Email Address *</label>
                        <input type="email" name="email" class="fnx-input" placeholder="username@futurenxt.com" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">Temporary Access Password *</label>
                        <input type="password" name="password" class="fnx-input" placeholder="Minimum 8 characters" value="Futurenxt@2026" required>
                    </div>
                </div>

                <!-- Personal Information -->
                <h4 style="margin:0 0 12px; font-size:14px; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em;">2. Personal Details</h4>
                <div class="fnx-grid-3" style="margin-bottom:16px;">
                    <div>
                        <label class="fnx-form-label">Full Name *</label>
                        <input type="text" name="full_name" class="fnx-input" placeholder="e.g. Rahul Verma" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">Phone Number *</label>
                        <input type="text" name="phone" class="fnx-input" placeholder="e.g. 9876543210" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">Date of Birth</label>
                        <input type="date" name="dob" class="fnx-input">
                    </div>
                </div>

                <div class="fnx-grid-3" style="margin-bottom:16px;">
                    <div>
                        <label class="fnx-form-label">Date of Joining</label>
                        <input type="date" name="doj" class="fnx-input" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Highest Qualification</label>
                        <input type="text" name="qualification" class="fnx-input" placeholder="e.g. B.Tech / MCA / BCA">
                    </div>
                    <div>
                        <label class="fnx-form-label">Passport Photo</label>
                        <input type="file" name="photo" class="fnx-input" accept="image/*">
                    </div>
                </div>

                <!-- Identity Verification & Documents -->
                <h4 style="margin:0 0 12px; font-size:14px; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em;">3. Identification &amp; KYC Verification</h4>
                <div class="fnx-grid-2" style="margin-bottom:16px;">
                    <div>
                        <label class="fnx-form-label">Aadhaar Card Number</label>
                        <input type="text" name="aadhar_no" class="fnx-input" placeholder="12-digit number">
                    </div>
                    <div>
                        <label class="fnx-form-label">Aadhaar Document Upload (PDF/Image)</label>
                        <input type="file" name="aadhar_doc" class="fnx-input" accept=".pdf,image/*">
                    </div>
                </div>

                <div class="fnx-grid-2" style="margin-bottom:16px;">
                    <div>
                        <label class="fnx-form-label">PAN Card Number</label>
                        <input type="text" name="pan_no" class="fnx-input" placeholder="10-digit alphanumeric">
                    </div>
                    <div>
                        <label class="fnx-form-label">PAN Document Upload (PDF/Image)</label>
                        <input type="file" name="pan_doc" class="fnx-input" accept=".pdf,image/*">
                    </div>
                </div>

                <div class="fnx-grid-2" style="margin-bottom:20px;">
                    <div>
                        <label class="fnx-form-label">Candidate Resume (PDF)</label>
                        <input type="file" name="resume_doc" class="fnx-input" accept=".pdf">
                    </div>
                    <div>
                        <label class="fnx-form-label">Offer / Appointment Letter (PDF)</label>
                        <input type="file" name="offer_letter_doc" class="fnx-input" accept=".pdf">
                    </div>
                </div>

                <!-- Banking Information -->
                <h4 style="margin:0 0 12px; font-size:14px; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em;">4. Payroll &amp; Banking Details</h4>
                <div class="fnx-grid-3" style="margin-bottom:16px;">
                    <div>
                        <label class="fnx-form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="fnx-input" placeholder="e.g. HDFC Bank, SBI">
                    </div>
                    <div>
                        <label class="fnx-form-label">Account Number</label>
                        <input type="text" name="bank_account_no" class="fnx-input" placeholder="Account Number">
                    </div>
                    <div>
                        <label class="fnx-form-label">IFSC Code</label>
                        <input type="text" name="bank_ifsc" class="fnx-input" placeholder="e.g. HDFC0001234">
                    </div>
                </div>
                <div class="fnx-grid-2" style="margin-bottom:10px;">
                    <div>
                        <label class="fnx-form-label">Account Holder Name</label>
                        <input type="text" name="bank_account_holder" class="fnx-input" placeholder="As per bank passbook">
                    </div>
                    <div>
                        <label class="fnx-form-label">UPI ID (Optional)</label>
                        <input type="text" name="upi_id" class="fnx-input" placeholder="username@upi">
                    </div>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('onboardModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">Complete Onboarding &amp; Create Profile →</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: CHANGE STATUS / SUSPEND / TERMINATE / 15-DAY NOTICE PERIOD
     ========================================================================= -->
<div id="statusModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:520px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:var(--fnx-navy);">Update Employment Status</h3>
                <p id="statusModalEmpName" style="margin:2px 0 0; font-size:12.5px; color:var(--fnx-muted);"></p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('statusModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=update_status">
            <input type="hidden" id="statusEmpId" name="employee_id" value="">
            <div class="fnx-modal-body">
                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Employment Status *</label>
                    <select id="statusSelect" name="status" class="fnx-select" onchange="toggleStatusNotices(this.value)" required>
                        <option value="active">Active (Full Access)</option>
                        <option value="resigned">Notice Period / Resignation (Strict 15 Days)</option>
                        <option value="suspended">Suspended (Immediate Login Freeze)</option>
                        <option value="terminated">Terminated (Permanent Access Revocation)</option>
                        <option value="pip">Performance Improvement Plan (PIP)</option>
                    </select>
                </div>

                <!-- 15-Day Notice Period Notice -->
                <div id="noticePeriodBox" style="display:none; padding:12px 14px; background:#FEF3C7; border:1.5px solid #FDE68A; border-radius:8px; margin-bottom:16px;">
                    <strong style="color:#92400E; font-size:13px; display:block; margin-bottom:4px;">📜 Company Notice Period Policy:</strong>
                    <p style="margin:0; font-size:12.5px; color:#78350F; line-height:1.5;">
                        Notice periods at FutureNxt are strictly <strong>15 calendar days</strong>. Setting status to Resigned automatically activates a 15-day countdown effective from today until <strong id="noticeCalcEndDate"><?= date('d M Y', strtotime('+15 days')) ?></strong>.
                    </p>
                </div>

                <!-- Suspended Warning -->
                <div id="suspendedNoticeBox" style="display:none; padding:12px 14px; background:#FEF2F2; border:1.5px solid #FCA5A5; border-radius:8px; margin-bottom:16px;">
                    <strong style="color:#991B1B; font-size:13px; display:block; margin-bottom:4px;">⚠️ Immediate Account Suspension:</strong>
                    <p style="margin:0; font-size:12.5px; color:#7F1D1D; line-height:1.5;">
                        The employee will be immediately locked out of the portal, active login sessions will be terminated, and they will be blocked from submitting daily updates.
                    </p>
                </div>

                <!-- Terminated Warning -->
                <div id="terminatedNoticeBox" style="display:none; padding:12px 14px; background:#111827; border:1.5px solid #374151; border-radius:8px; margin-bottom:16px;">
                    <strong style="color:#F97316; font-size:13px; display:block; margin-bottom:4px;">🛑 Employment Termination:</strong>
                    <p style="margin:0; font-size:12.5px; color:#F3F4F6; line-height:1.5;">
                        Employment is terminated permanently. All portal access is revoked and active task assignments are archived.
                    </p>
                </div>

                <!-- PIP Fields -->
                <div id="pipFields" style="display:none; padding:14px; background:#FEF2F2; border:1px solid #FCA5A5; border-radius:10px; margin-bottom:16px;">
                    <div style="margin-bottom:12px;">
                        <label class="fnx-form-label" style="color:#991B1B;">Reason for PIP *</label>
                        <textarea name="pip_reason" class="fnx-textarea" rows="2" placeholder="Describe the performance areas requiring improvement..."></textarea>
                    </div>
                    <div>
                        <label class="fnx-form-label" style="color:#991B1B;">PIP Evaluation End Date</label>
                        <input type="date" name="pip_end_date" class="fnx-input" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                </div>

                <!-- Status Remarks / Justification -->
                <div style="margin-bottom:14px;">
                    <label class="fnx-form-label">Status Remarks &amp; Notes (Optional)</label>
                    <textarea id="statusRemarksText" name="status_remarks" class="fnx-textarea" rows="2" placeholder="Reason or administrative remarks for this status change..."></textarea>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('statusModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">Confirm Status Change</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: ADMIN RESET PASSWORD VIA EMAIL OTP (NO CURRENT PASSWORD NEEDED)
     ========================================================================= -->
<div id="adminResetPasswordModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:500px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:#111827;">🔑 Reset Partner Password</h3>
                <p id="resetModalSubtitle" style="margin:2px 0 0; font-size:12.5px; color:#4B5563;"></p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('adminResetPasswordModal')">&times;</button>
        </div>

        <div class="fnx-modal-body">
            <!-- STEP 1: Request OTP -->
            <div id="otpStep1">
                <div style="background:#FFF9F4; border:1px solid #F3DECB; border-radius:10px; padding:16px; margin-bottom:16px;">
                    <p style="margin:0 0 10px; font-size:13.5px; color:#374151; line-height:1.5;">
                        As an Administrator, you can update this partner's password without needing their current password.
                    </p>
                    <p style="margin:0; font-size:12.5px; color:#6B7280; line-height:1.5;">
                        A secure 6-digit OTP will be dispatched to their registered email (<strong id="resetTargetEmailText"></strong>) from <strong>fnxt.admin@gmail.com</strong>.
                    </p>
                </div>

                <div id="otpRequestAlert" style="display:none; margin-bottom:14px; padding:10px 14px; border-radius:8px; font-size:13px;"></div>

                <button type="button" id="btnSendResetOtp" class="fnx-btn fnx-btn-orange" style="width:100%; justify-content:center; padding:11px 16px; font-size:14px; font-weight:700;" onclick="fnxSendPasswordOtp()">
                    📧 Send 6-Digit OTP to Partner's Email →
                </button>
            </div>

            <!-- STEP 2: Verify OTP & Enter New Password -->
            <div id="otpStep2" style="display:none;">
                <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:10px; padding:12px 14px; margin-bottom:16px; font-size:13px; color:#065F46;">
                    ✓ 6-Digit OTP sent to <strong id="resetEmailConfirmed"></strong>. Valid for 10 minutes.
                </div>

                <div id="otpConfirmAlert" style="display:none; margin-bottom:14px; padding:10px 14px; border-radius:8px; font-size:13px;"></div>

                <div style="margin-bottom:14px;">
                    <label class="fnx-form-label">Enter 6-Digit Verification OTP *</label>
                    <input type="text" id="resetOtpCode" class="fnx-input" maxlength="6" placeholder="• • • • • •" style="font-family:'JetBrains Mono',monospace; font-size:22px; font-weight:900; letter-spacing:8px; text-align:center; padding:8px;" required>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="fnx-form-label">New Password *</label>
                    <input type="password" id="resetNewPassword" class="fnx-input" placeholder="Enter new password (min. 6 characters)" required>
                </div>

                <div style="margin-bottom:18px;">
                    <label class="fnx-form-label">Confirm New Password *</label>
                    <input type="password" id="resetConfirmPassword" class="fnx-input" placeholder="Re-enter new password" required>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                    <button type="button" class="fnx-btn fnx-btn-outline" style="font-size:12px;" onclick="fnxSendPasswordOtp()">
                        ↻ Resend OTP
                    </button>
                    <button type="button" id="btnConfirmReset" class="fnx-btn fnx-btn-orange" style="font-size:13.5px; font-weight:700;" onclick="fnxConfirmPasswordReset()">
                        ✓ Verify OTP &amp; Update Password
                    </button>
                </div>
            </div>
        </div>

        <div class="fnx-modal-footer">
            <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('adminResetPasswordModal')">Close Window</button>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL: EDIT EMPLOYEE PROFILE (ADMIN / CEO / CTO)
     ========================================================================= -->
<div id="editEmployeeListModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:850px; width:95%; max-height:90vh; overflow-y:auto;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:#111827;">✏️ Edit Employee Profile</h3>
                <p id="editListModalSubtitle" style="margin:4px 0 0; font-size:12.5px; color:#4B5563;"></p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('editEmployeeListModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=update_employee" enctype="multipart/form-data">
            <input type="hidden" id="editListEmpId" name="employee_id" value="">
            <input type="hidden" name="redirect_url" value="/futurenxt/employees">

            <div class="fnx-modal-body">
                <!-- Section 1: Employment & Core Info -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    1. Employment &amp; Personal Information
                </div>
                <div class="fnx-grid-2" style="margin-bottom:18px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Full Name *</label>
                        <input type="text" id="editListFullName" name="full_name" class="fnx-input" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">Email Address *</label>
                        <input type="email" id="editListEmail" name="email" class="fnx-input" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">Role</label>
                        <select id="editListRole" name="role" class="fnx-input">
                            <option value="ceo">CEO</option>
                            <option value="cto">CTO</option>
                            <option value="admin">Admin</option>
                            <option value="office_manager">Office Manager</option>
                            <option value="manager">Manager</option>
                            <option value="employee">Employee</option>
                            <option value="intern">Intern</option>
                        </select>
                    </div>
                    <div>
                        <label class="fnx-form-label">Reporting Manager / Reports To</label>
                        <select id="editListManagerId" name="manager_id" class="fnx-input">
                            <option value="">-- Direct under CEO / CTO / Executive --</option>
                            <?php foreach ($managersList as $mgr): ?>
                                <option value="<?= $mgr['id'] ?>">
                                    <?= htmlspecialchars($mgr['full_name']) ?> (<?= htmlspecialchars($mgr['emp_code']) ?> · <?= ucfirst($mgr['role']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="fnx-form-label">Account Status</label>
                        <select id="editListStatus" name="status" class="fnx-input">
                            <option value="active">Active</option>
                            <option value="pip">PIP (Performance Improvement Plan)</option>
                            <option value="suspended">Suspended</option>
                            <option value="resigned">Resigned</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                    <div>
                        <label class="fnx-form-label">Phone Number</label>
                        <input type="text" id="editListPhone" name="phone" class="fnx-input">
                    </div>
                    <div>
                        <label class="fnx-form-label">Designation</label>
                        <input type="text" id="editListDesignation" name="designation" class="fnx-input">
                    </div>
                    <div>
                        <label class="fnx-form-label">Department</label>
                        <input type="text" id="editListDepartment" name="department" class="fnx-input">
                    </div>
                    <div>
                        <label class="fnx-form-label">Highest Qualification</label>
                        <input type="text" id="editListQualification" name="highest_qualification" class="fnx-input">
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label class="fnx-form-label">Profile / Passport Photo (Upload to Change)</label>
                        <input type="file" name="photo" class="fnx-input" accept="image/png,image/jpeg,image/jpg,image/webp">
                        <small style="color:#6B7280; font-size:11.5px; margin-top:2px; display:block;">Select an image (JPG, PNG, or WEBP up to 5MB) to update this employee's avatar.</small>
                    </div>
                </div>

                <!-- Section 2: Addresses & Emergency -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    2. Addresses &amp; Emergency Contact
                </div>
                <div class="fnx-grid-2" style="margin-bottom:18px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Current Address</label>
                        <textarea id="editListCurrentAddress" name="current_address" class="fnx-input" rows="2"></textarea>
                    </div>
                    <div>
                        <label class="fnx-form-label">Permanent Address</label>
                        <textarea id="editListPermanentAddress" name="permanent_address" class="fnx-input" rows="2"></textarea>
                    </div>
                    <div>
                        <label class="fnx-form-label">Emergency Contact Name</label>
                        <input type="text" id="editListEmergencyName" name="emergency_contact_name" class="fnx-input">
                    </div>
                    <div>
                        <label class="fnx-form-label">Emergency Phone</label>
                        <input type="text" id="editListEmergencyPhone" name="emergency_contact_phone" class="fnx-input">
                    </div>
                </div>

                <!-- Section 3: Banking & Payroll -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    3. Payroll &amp; Banking Details
                </div>
                <div class="fnx-grid-2" style="margin-bottom:18px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Bank Name</label>
                        <input type="text" id="editListBankName" name="bank_name" class="fnx-input">
                    </div>
                    <div>
                        <label class="fnx-form-label">Account Number</label>
                        <input type="text" id="editListBankAcc" name="bank_account_no" class="fnx-input">
                    </div>
                    <div>
                        <label class="fnx-form-label">IFSC Code</label>
                        <input type="text" id="editListBankIfsc" name="bank_ifsc" class="fnx-input">
                    </div>
                    <div>
                        <label class="fnx-form-label">Account Holder</label>
                        <input type="text" id="editListBankHolder" name="bank_account_name" class="fnx-input">
                    </div>
                    <div>
                        <label class="fnx-form-label">UPI ID</label>
                        <input type="text" id="editListUpi" name="upi_id" class="fnx-input">
                    </div>
                    <div>
                        <label class="fnx-form-label">Reset Password (Optional)</label>
                        <input type="password" name="new_password" class="fnx-input" placeholder="Leave blank to keep unchanged">
                    </div>
                </div>

                <!-- Section 4: Identification (Aadhaar & PAN) -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    4. Identification (Aadhaar &amp; PAN)
                </div>
                <div class="fnx-grid-2" style="margin-bottom:14px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Aadhaar Card Number</label>
                        <input type="text" id="editListAadhar" name="aadhar_number" class="fnx-input" placeholder="12 digits">
                    </div>
                    <div>
                        <label class="fnx-form-label">PAN Card Number</label>
                        <input type="text" id="editListPan" name="pan_number" class="fnx-input" style="text-transform:uppercase;" placeholder="10 characters">
                    </div>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('editEmployeeListModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">✓ Save Employee Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditEmployeeModal(emp) {
    if (!emp) return;
    document.getElementById('editListEmpId').value = emp.id || '';
    document.getElementById('editListModalSubtitle').innerText = (emp.full_name || '') + ' (' + (emp.emp_code || '') + ')';
    document.getElementById('editListFullName').value = emp.full_name || '';
    document.getElementById('editListEmail').value = emp.email || '';
    document.getElementById('editListRole').value = emp.role || 'employee';
    const mgrSel = document.getElementById('editListManagerId');
    if (mgrSel) {
        mgrSel.value = emp.manager_id || '';
    }
    document.getElementById('editListStatus').value = emp.status || 'active';
    document.getElementById('editListPhone').value = emp.phone || '';
    document.getElementById('editListDesignation').value = emp.designation || '';
    document.getElementById('editListDepartment').value = emp.department || 'Operations';
    document.getElementById('editListQualification').value = emp.highest_qualification || emp.qualification || '';
    document.getElementById('editListCurrentAddress').value = emp.current_address || '';
    document.getElementById('editListPermanentAddress').value = emp.permanent_address || '';
    document.getElementById('editListEmergencyName').value = emp.emergency_contact_name || '';
    document.getElementById('editListEmergencyPhone').value = emp.emergency_contact_phone || '';
    document.getElementById('editListBankName').value = emp.bank_name || '';
    document.getElementById('editListBankAcc').value = emp.bank_account_no || '';
    document.getElementById('editListBankIfsc').value = emp.bank_ifsc || '';
    document.getElementById('editListBankHolder').value = emp.bank_account_name || '';
    document.getElementById('editListUpi').value = emp.upi_id || '';
    document.getElementById('editListAadhar').value = emp.aadhar_number || emp.aadhar_no || '';
    document.getElementById('editListPan').value = emp.pan_number || emp.pan_no || '';
    fnxOpenModal('editEmployeeListModal');
}

let currentResetEmpId = null;
let currentResetEmpEmail = '';

function openStatusModal(id, name, currentStatus, noticeEndDate, remarks) {
    document.getElementById('statusEmpId').value = id;
    document.getElementById('statusModalEmpName').innerText = name;
    const sel = document.getElementById('statusSelect');
    sel.value = currentStatus;
    const remEl = document.getElementById('statusRemarksText');
    if (remEl) remEl.value = remarks || '';
    toggleStatusNotices(currentStatus);
    fnxOpenModal('statusModal');
}

function toggleStatusNotices(val) {
    const pf = document.getElementById('pipFields');
    const nb = document.getElementById('noticePeriodBox');
    const sb = document.getElementById('suspendedNoticeBox');
    const tb = document.getElementById('terminatedNoticeBox');

    if (pf) pf.style.display = (val === 'pip') ? 'block' : 'none';
    if (nb) nb.style.display = (val === 'resigned') ? 'block' : 'none';
    if (sb) sb.style.display = (val === 'suspended') ? 'block' : 'none';
    if (tb) tb.style.display = (val === 'terminated') ? 'block' : 'none';
}

function togglePipFields(val) {
    toggleStatusNotices(val);
}

function openAdminResetPasswordModal(id, name, code, email) {
    currentResetEmpId = id;
    currentResetEmpEmail = email || '';

    document.getElementById('resetModalSubtitle').innerText = name + ' (' + code + ')';
    document.getElementById('resetTargetEmailText').innerText = email || 'No email on record';

    // Reset step views
    document.getElementById('otpStep1').style.display = 'block';
    document.getElementById('otpStep2').style.display = 'none';

    const a1 = document.getElementById('otpRequestAlert');
    if (a1) { a1.style.display = 'none'; a1.innerText = ''; }
    const a2 = document.getElementById('otpConfirmAlert');
    if (a2) { a2.style.display = 'none'; a2.innerText = ''; }

    document.getElementById('resetOtpCode').value = '';
    document.getElementById('resetNewPassword').value = '';
    document.getElementById('resetConfirmPassword').value = '';

    fnxOpenModal('adminResetPasswordModal');
}

function fnxSendPasswordOtp() {
    if (!currentResetEmpId) return;

    const btn = document.getElementById('btnSendResetOtp');
    const alertBox = document.getElementById('otpRequestAlert');
    const originalText = btn.innerText;

    btn.disabled = true;
    btn.innerText = '⏳ Dispatching OTP from fnxt.admin@gmail.com...';
    if (alertBox) alertBox.style.display = 'none';

    const formData = new FormData();
    formData.append('employee_id', currentResetEmpId);
    formData.append('ajax', '1');

    fetch('/futurenxt-handler.php?action=admin_request_password_otp', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = originalText;

        if (data.success) {
            document.getElementById('resetEmailConfirmed').innerText = data.email || currentResetEmpEmail;
            document.getElementById('otpStep1').style.display = 'none';
            document.getElementById('otpStep2').style.display = 'block';
            document.getElementById('resetOtpCode').focus();
        } else {
            if (alertBox) {
                alertBox.className = 'fnx-alert fnx-alert-danger';
                alertBox.style.display = 'block';
                alertBox.innerText = data.error || 'Failed to dispatch OTP. Please check server logs.';
            } else {
                alert(data.error || 'Failed to dispatch OTP.');
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = originalText;
        if (alertBox) {
            alertBox.className = 'fnx-alert fnx-alert-danger';
            alertBox.style.display = 'block';
            alertBox.innerText = 'Network error while dispatching OTP.';
        }
    });
}

function fnxConfirmPasswordReset() {
    if (!currentResetEmpId) return;

    const otp = document.getElementById('resetOtpCode').value.trim();
    const newPass = document.getElementById('resetNewPassword').value.trim();
    const confPass = document.getElementById('resetConfirmPassword').value.trim();
    const alertBox = document.getElementById('otpConfirmAlert');

    if (!otp || otp.length !== 6) {
        if (alertBox) {
            alertBox.className = 'fnx-alert fnx-alert-danger';
            alertBox.style.display = 'block';
            alertBox.innerText = 'Please enter the complete 6-digit OTP received via email.';
        }
        return;
    }

    if (!newPass || newPass.length < 6) {
        if (alertBox) {
            alertBox.className = 'fnx-alert fnx-alert-danger';
            alertBox.style.display = 'block';
            alertBox.innerText = 'New password must be at least 6 characters in length.';
        }
        return;
    }

    if (newPass !== confPass) {
        if (alertBox) {
            alertBox.className = 'fnx-alert fnx-alert-danger';
            alertBox.style.display = 'block';
            alertBox.innerText = 'Passwords do not match. Please verify both fields.';
        }
        return;
    }

    const btn = document.getElementById('btnConfirmReset');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerText = '⏳ Verifying & Updating...';
    if (alertBox) alertBox.style.display = 'none';

    const formData = new FormData();
    formData.append('employee_id', currentResetEmpId);
    formData.append('otp_code', otp);
    formData.append('new_password', newPass);
    formData.append('ajax', '1');

    fetch('/futurenxt-handler.php?action=admin_confirm_password_reset', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = originalText;

        if (data.success) {
            fnxCloseModal('adminResetPasswordModal');
            window.location.href = '/futurenxt/employees?success=' + encodeURIComponent(data.message || 'Password updated successfully! A security confirmation email was dispatched to the partner.');
        } else {
            if (alertBox) {
                alertBox.className = 'fnx-alert fnx-alert-danger';
                alertBox.style.display = 'block';
                alertBox.innerText = data.error || 'Failed to update password. Please check the OTP.';
            } else {
                alert(data.error || 'Failed to update password.');
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = originalText;
        if (alertBox) {
            alertBox.className = 'fnx-alert fnx-alert-danger';
            alertBox.style.display = 'block';
            alertBox.innerText = 'Network error while confirming password reset.';
        }
    });
}

function resendOnboardingEmail(id, name, email) {
    if (!confirm('Dispatch official onboarding credentials email to ' + name + ' (' + email + ')?')) {
        return;
    }
    const btn = event.target;
    const oldText = btn.innerText;
    btn.disabled = true;
    btn.innerText = '⏳ Sending...';
    
    const formData = new FormData();
    formData.append('partner_id', id);
    formData.append('ajax', '1');
    
    fetch('/futurenxt-handler.php?action=resend_onboarding_email', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        btn.disabled = false;
        btn.innerText = oldText;
        if (d.success) {
            alert('✓ ' + d.message);
        } else {
            alert('❌ ' + (d.message || d.error || 'Failed to dispatch email.'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = oldText;
        alert('Network or server error while sending onboarding email.');
    });
}

function fnxUpdateCodePreview() {
    const roleEl = document.getElementById('onboardRole');
    const mgrEl = document.getElementById('onboardManagerId');
    const badge = document.getElementById('onboardCodePreview');
    if (!roleEl || !badge) return;
    const role = roleEl.value;
    const mgrId = mgrEl ? mgrEl.value : '';
    badge.innerText = 'Calculating...';
    fetch('/futurenxt-handler.php?action=get_next_emp_code&role=' + encodeURIComponent(role) + '&manager_id=' + encodeURIComponent(mgrId))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.code) {
                badge.innerText = data.code;
            }
        })
        .catch(function() {
            badge.innerText = 'Auto-Assigned';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    fnxUpdateCodePreview();
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<?php
declare(strict_types=1);

use App\FutureNxt\Auth;
use App\FutureNxt\Employee;
use App\FutureNxt\Task;
use App\Helper;

Auth::requireLogin();
$me = Auth::user();

$empId = (int)($_GET['id'] ?? 0);
if (!$empId) {
    Helper::redirect('/futurenxt/employees');
}

$employee = Employee::findById($empId);
if (!$employee) {
    Helper::redirect('/futurenxt/employees');
}

// Access check: only Admin, Execs, or the employee themselves can view full details
if (!Auth::isAdminOrExec() && (int)$me['id'] !== $empId) {
    Helper::redirect('/futurenxt/dashboard');
}

$tasks = Task::listForEmployee($empId);
$activeTaskCount = count(array_filter($tasks, fn($t) => $t['status'] === 'in_progress'));
$managersList = Employee::getManagers();

$pageTitle = $employee['full_name'] . ' (' . $employee['emp_code'] . ')';
$activePage = 'employees';

require __DIR__ . '/../layouts/header.php';
?>

<div class="fnx-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <a href="/futurenxt/employees" class="fnx-btn fnx-btn-outline">← Back to Personnel Directory</a>
        <?php if (Auth::isAdminOrExec()): ?>
            <div style="display:flex; gap:10px; align-items:center;">
                <span class="fnx-badge <?= $employee['status'] === 'active' ? 'fnx-badge-success' : 'fnx-badge-danger' ?>">
                    Status: <?= ucfirst($employee['status']) ?>
                </span>
                <button type="button" onclick="fnxOpenModal('editEmpModal')" class="fnx-btn fnx-btn-orange" style="font-size:13px; padding:7px 16px;">
                    ✏️ Edit Profile
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Profile Overview Card -->
    <div class="fnx-card" style="margin-bottom:24px;">
        <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
            <div style="position:relative; display:inline-block;">
                <img src="<?= !empty($employee['photo_path']) ? htmlspecialchars($employee['photo_path']) : '/assets/img/avatar-default.png' ?>" 
                     alt="<?= htmlspecialchars($employee['full_name']) ?>" 
                     style="width:104px; height:104px; border-radius:20px; object-fit:cover; border:2.5px solid var(--fnx-orange); box-shadow:0 6px 16px rgba(0,0,0,0.08); display:block;">
                <?php if (Auth::isAdminOrExec()): ?>
                    <button type="button" 
                            onclick="document.getElementById('empDetailPhotoFile').click()" 
                            style="position:absolute; bottom:-6px; right:-6px; width:32px; height:32px; border-radius:50%; background:var(--fnx-orange); color:#FFFFFF; border:2px solid #FFFFFF; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.25); font-size:14px;" 
                            title="Upload / Change Photo">
                        📷
                    </button>
                    <form method="POST" action="/futurenxt-handler.php?action=upload_profile_photo" enctype="multipart/form-data" id="empDetailPhotoForm" style="display:none;">
                        <input type="hidden" name="employee_id" value="<?= (int)$employee['id'] ?>">
                        <input type="file" id="empDetailPhotoFile" name="photo" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="document.getElementById('empDetailPhotoForm').submit()">
                    </form>
                <?php endif; ?>
            </div>
            
            <div style="flex:1; min-width:260px;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:4px; flex-wrap:wrap;">
                    <h1 style="margin:0; font-size:24px; font-weight:800; color:var(--fnx-navy);">
                        <?= htmlspecialchars($employee['full_name']) ?>
                    </h1>
                    <span class="fnx-badge fnx-badge-orange" style="font-family:monospace; font-size:12.5px;">
                        <?= htmlspecialchars($employee['emp_code']) ?>
                    </span>
                    <span class="fnx-badge fnx-badge-info">
                        <?= htmlspecialchars($employee['designation'] ?? ucfirst($employee['role'])) ?>
                    </span>
                </div>
                <div style="font-size:13.5px; color:var(--fnx-muted); margin-bottom:8px;">
                    Department: <strong style="color:var(--fnx-text);"><?= htmlspecialchars($employee['department'] ?? 'Operations') ?></strong> · 
                    Joined: <strong><?= !empty($employee['date_of_joining'] ?? $employee['doj'] ?? null) ? date('d M Y', strtotime($employee['date_of_joining'] ?? $employee['doj'])) : '—' ?></strong> · 
                    Reports To: 
                    <?php if (!empty($employee['manager_name'])): ?>
                        <a href="/futurenxt/employees/detail?id=<?= (int)$employee['manager_id'] ?>" style="color:var(--fnx-orange); font-weight:700; text-decoration:none;">
                            👤 <?= htmlspecialchars($employee['manager_name']) ?> (<?= htmlspecialchars($employee['manager_code']) ?>)
                        </a>
                    <?php else: ?>
                        <strong style="color:#111827;">Direct Executive Leadership</strong>
                    <?php endif; ?>
                </div>
                <div style="display:flex; gap:16px; flex-wrap:wrap; font-size:13px; color:var(--fnx-muted);">
                    <span>📧 <?= htmlspecialchars($employee['email']) ?></span>
                    <span>📞 <?= htmlspecialchars($employee['phone'] ?? '—') ?></span>
                    <span>🎓 <?= htmlspecialchars($employee['qualification'] ?? '—') ?></span>
                </div>
            </div>

            <div style="text-align:right;">
                <div style="font-size:12px; color:var(--fnx-muted); margin-bottom:4px;">Workload Capacity</div>
                <span class="fnx-badge <?= $activeTaskCount >= 3 ? 'fnx-badge-danger' : 'fnx-badge-success' ?>" style="font-size:14px; padding:6px 12px;">
                    <?= $activeTaskCount ?> / 3 Active Tasks
                </span>
            </div>
        </div>

        <?php if ($employee['status'] === 'pip'): ?>
            <div style="margin-top:20px; padding:14px 18px; background:#FEF2F2; border-left:4px solid #EF4444; border-radius:8px; font-size:13px;">
                <strong style="color:#991B1B;">⚠️ Performance Improvement Plan (PIP) Active:</strong>
                <p style="margin:4px 0 0; color:#7F1D1D;"><?= htmlspecialchars($employee['pip_reason'] ?? 'Under performance evaluation.') ?></p>
                <?php if (!empty($employee['pip_end_date'])): ?>
                    <div style="font-size:12px; color:#B91C1C; margin-top:4px;">Evaluation Review Date: <strong><?= date('d M Y', strtotime($employee['pip_end_date'])) ?></strong></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 2 Columns: Identity & Banking / Task Assignments -->
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
        <!-- Left Column: KYC, Identity & Banking -->
        <div>
            <!-- KYC & Identity -->
            <div class="fnx-card" style="margin-bottom:24px;">
                <h3 style="margin:0 0 16px; font-size:16px; font-weight:800; color:#111827 !important; display:flex; align-items:center; gap:8px;">
                    <span>🆔</span> Identity &amp; Verification Documents
                </h3>

                <div style="display:flex; flex-direction:column; gap:12px; font-size:13px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:8px; border-bottom:1px solid var(--fnx-border);">
                        <span style="color:#4B5563; font-weight:600;">Aadhaar Card:</span>
                        <div style="text-align:right;">
                            <?php 
                            $realAadhar = (string)($employee['aadhar_number'] ?? $employee['aadhar_no'] ?? '');
                            $maskAadhar = !empty($realAadhar) ? '•••• •••• ' . substr($realAadhar, -4) : 'Not provided';
                            ?>
                            <div class="fnx-mask-wrap">
                                <strong id="empAadharMask" data-mask="<?= htmlspecialchars($maskAadhar) ?>" data-real="<?= htmlspecialchars($realAadhar ?: 'Not provided') ?>" data-masked="1" style="color:#111827; font-family:monospace;">
                                    <?= htmlspecialchars($maskAadhar) ?>
                                </strong>
                                <?php if (!empty($realAadhar)): ?>
                                    <button type="button" class="fnx-eye-btn" onclick="fnxToggleMask('empAadharMask', this)" title="Show Aadhaar">👁️</button>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($employee['aadhar_doc_path'])): ?>
                                <div style="margin-top:2px;"><a href="<?= htmlspecialchars($employee['aadhar_doc_path']) ?>" target="_blank" style="color:var(--fnx-orange); font-size:12px; font-weight:700;">View Document ↗</a></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:8px; border-bottom:1px solid var(--fnx-border);">
                        <span style="color:#4B5563; font-weight:600;">PAN Card:</span>
                        <div style="text-align:right;">
                            <?php 
                            $realPan = (string)($employee['pan_number'] ?? $employee['pan_no'] ?? '');
                            $maskPan = !empty($realPan) ? '••••••' . substr($realPan, -4) : 'Not provided';
                            ?>
                            <div class="fnx-mask-wrap">
                                <strong id="empPanMask" data-mask="<?= htmlspecialchars($maskPan) ?>" data-real="<?= htmlspecialchars($realPan ?: 'Not provided') ?>" data-masked="1" style="color:#111827; font-family:monospace;">
                                    <?= htmlspecialchars($maskPan) ?>
                                </strong>
                                <?php if (!empty($realPan)): ?>
                                    <button type="button" class="fnx-eye-btn" onclick="fnxToggleMask('empPanMask', this)" title="Show PAN">👁️</button>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($employee['pan_doc_path'])): ?>
                                <div style="margin-top:2px;"><a href="<?= htmlspecialchars($employee['pan_doc_path']) ?>" target="_blank" style="color:var(--fnx-orange); font-size:12px; font-weight:700;">View Document ↗</a></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:8px; border-bottom:1px solid var(--fnx-border);">
                        <span style="color:#4B5563; font-weight:600;">Resume / CV:</span>
                        <div>
                            <?php 
                            $resumeFile = $employee['resume_doc_path'] ?? $employee['resume_path'] ?? '';
                            if (!empty($resumeFile)): ?>
                                <a href="<?= htmlspecialchars($resumeFile) ?>" target="_blank" class="fnx-btn fnx-btn-outline" style="padding:3px 10px; font-size:12px; font-weight:700;">Download Resume PDF 📄</a>
                            <?php else: ?>
                                <span style="color:#6B7280;">Not uploaded</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:#4B5563; font-weight:600;">Appointment Letter:</span>
                        <div>
                            <?php if (!empty($employee['offer_letter_path'])): ?>
                                <a href="<?= htmlspecialchars($employee['offer_letter_path']) ?>" target="_blank" class="fnx-btn fnx-btn-outline" style="padding:3px 10px; font-size:12px; font-weight:700;">View Letter 📄</a>
                            <?php else: ?>
                                <span style="color:#6B7280;">Not uploaded</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banking & Payroll -->
            <div class="fnx-card">
                <h3 style="margin:0 0 16px; font-size:16px; font-weight:800; color:#111827 !important; display:flex; align-items:center; gap:8px;">
                    <span>🏦</span> Banking &amp; Payroll Information
                </h3>

                <div style="display:flex; flex-direction:column; gap:12px; font-size:13px;">
                    <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid var(--fnx-border);">
                        <span style="color:#4B5563; font-weight:600;">Bank Name:</span>
                        <strong style="color:#111827;"><?= htmlspecialchars($employee['bank_name'] ?? '—') ?></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:8px; border-bottom:1px solid var(--fnx-border);">
                        <span style="color:#4B5563; font-weight:600;">Account Number:</span>
                        <?php 
                        $realEmpAcc = (string)($employee['bank_account_no'] ?? '');
                        $maskEmpAcc = !empty($realEmpAcc) ? '••••••••' . substr($realEmpAcc, -4) : '—';
                        ?>
                        <div class="fnx-mask-wrap">
                            <strong id="empAccMask" data-mask="<?= htmlspecialchars($maskEmpAcc) ?>" data-real="<?= htmlspecialchars($realEmpAcc ?: '—') ?>" data-masked="1" style="font-family:monospace; color:#111827; font-size:13.5px;">
                                <?= htmlspecialchars($maskEmpAcc) ?>
                            </strong>
                            <?php if (!empty($realEmpAcc)): ?>
                                <button type="button" class="fnx-eye-btn" onclick="fnxToggleMask('empAccMask', this)" title="Show account number">👁️</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid var(--fnx-border);">
                        <span style="color:#4B5563; font-weight:600;">IFSC Code:</span>
                        <strong style="font-family:monospace; color:#111827;"><?= htmlspecialchars($employee['bank_ifsc'] ?? '—') ?></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid var(--fnx-border);">
                        <span style="color:#4B5563; font-weight:600;">Account Holder:</span>
                        <strong style="color:#111827;"><?= htmlspecialchars($employee['bank_account_holder'] ?? '—') ?></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:#4B5563; font-weight:600;">UPI ID:</span>
                        <?php 
                        $realEmpUpi = (string)($employee['upi_id'] ?? '');
                        $maskEmpUpi = !empty($realEmpUpi) ? '••••' . substr($realEmpUpi, -6) : '—';
                        ?>
                        <div class="fnx-mask-wrap">
                            <strong id="empUpiMask" data-mask="<?= htmlspecialchars($maskEmpUpi) ?>" data-real="<?= htmlspecialchars($realEmpUpi ?: '—') ?>" data-masked="1" style="font-family:monospace; color:#111827;">
                                <?= htmlspecialchars($maskEmpUpi) ?>
                            </strong>
                            <?php if (!empty($realEmpUpi)): ?>
                                <button type="button" class="fnx-eye-btn" onclick="fnxToggleMask('empUpiMask', this)" title="Show UPI ID">👁️</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Assigned Tasks -->
        <div>
            <div class="fnx-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="margin:0; font-size:16px; font-weight:800; color:#111827 !important; display:flex; align-items:center; gap:8px;">
                        <span>📋</span> Task Portfolio
                    </h3>
                    <span class="fnx-badge <?= $activeTaskCount >= 3 ? 'fnx-badge-danger' : 'fnx-badge-info' ?>">
                        <?= $activeTaskCount ?> / 3 Active Tasks
                    </span>
                </div>

                <?php if (empty($tasks)): ?>
                    <p style="font-size:13px; color:var(--fnx-muted); margin:0;">No tasks currently assigned.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php foreach ($tasks as $t): ?>
                            <div style="padding:12px 14px; background:var(--fnx-bg); border-radius:10px; border:1px solid var(--fnx-border); font-size:13px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                    <div>
                                        <strong style="color:var(--fnx-navy);"><?= htmlspecialchars($t['title']) ?></strong>
                                        <div style="font-size:11.5px; color:var(--fnx-muted); font-family:monospace;">
                                            <?= htmlspecialchars($t['task_code']) ?> · Due: <?= !empty($t['due_date']) ? date('d M Y', strtotime($t['due_date'])) : '—' ?>
                                        </div>
                                    </div>
                                    <span class="fnx-badge <?= $t['status'] === 'approved' ? 'fnx-badge-success' : ($t['status'] === 'in_progress' ? 'fnx-badge-info' : 'fnx-badge-warning') ?>">
                                        <?= ucfirst($t['status']) ?>
                                    </span>
                                </div>

                                <?php if (!empty($t['documentation_pdf'])): ?>
                                    <div style="margin-top:8px;">
                                        <a href="<?= htmlspecialchars($t['documentation_pdf']) ?>" target="_blank" style="font-size:12px; color:var(--fnx-orange); text-decoration:none;">
                                            📄 View Approved Documentation PDF ↗
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($employee['reportees'])): ?>
                <div class="fnx-card" style="margin-top:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                        <h3 style="margin:0; font-size:16px; font-weight:800; color:#111827 !important; display:flex; align-items:center; gap:8px;">
                            <span>👥</span> Team Members Reporting to <?= htmlspecialchars($employee['full_name']) ?>
                        </h3>
                        <span class="fnx-badge fnx-badge-orange"><?= count($employee['reportees']) ?> Personnel</span>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <?php foreach ($employee['reportees'] as $rep): ?>
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:var(--fnx-bg); border-radius:10px; border:1px solid var(--fnx-border); flex-wrap:wrap; gap:8px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span class="fnx-badge fnx-badge-orange" style="font-family:monospace; font-size:12px;">
                                        <?= htmlspecialchars($rep['emp_code']) ?>
                                    </span>
                                    <div>
                                        <a href="/futurenxt/employees/detail?id=<?= (int)$rep['id'] ?>" style="font-size:13.5px; font-weight:700; color:#111827; text-decoration:none;">
                                            <?= htmlspecialchars($rep['full_name']) ?>
                                        </a>
                                        <div style="font-size:11.5px; color:#4B5563;">
                                            <?= htmlspecialchars($rep['designation'] ?? ucfirst($rep['role'])) ?> · <?= htmlspecialchars($rep['department'] ?? 'Operations') ?>
                                        </div>
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span class="fnx-badge fnx-badge-info" style="font-size:11px;">
                                        <?= (int)($rep['active_tasks_count'] ?? 0) ?>/3 Active
                                    </span>
                                    <a href="/futurenxt/employees/detail?id=<?= (int)$rep['id'] ?>" class="fnx-btn fnx-btn-outline" style="padding:4px 8px; font-size:11.5px;">View →</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (Auth::isAdminOrExec()): ?>
<!-- Edit Employee Profile Modal (Admin, CEO, CTO Only) -->
<div class="fnx-modal-backdrop" id="editEmpModal" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:850px; width:95%; max-height:90vh; overflow-y:auto;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:#111827;">✏️ Edit Profile: <?= htmlspecialchars($employee['full_name']) ?> (<?= htmlspecialchars($employee['emp_code']) ?>)</h3>
                <p style="margin:4px 0 0; font-size:12.5px; color:#4B5563;">Administrative modifications to employee record and credentials.</p>
            </div>
            <button type="button" onclick="fnxCloseModal('editEmpModal')" class="fnx-modal-close">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=update_employee" enctype="multipart/form-data">
            <input type="hidden" name="employee_id" value="<?= (int)$employee['id'] ?>">
            <input type="hidden" name="redirect_url" value="/futurenxt/employees/detail?id=<?= (int)$employee['id'] ?>">

            <div class="fnx-modal-body">
                <!-- Section 1: Basic Information -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    1. Employment &amp; Personal Information
                </div>
                <div class="fnx-grid-2" style="margin-bottom:18px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Full Name *</label>
                        <input type="text" name="full_name" class="fnx-input" value="<?= htmlspecialchars($employee['full_name'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">Email Address *</label>
                        <input type="email" name="email" class="fnx-input" value="<?= htmlspecialchars($employee['email'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">Role</label>
                        <select name="role" class="fnx-input">
                            <?php foreach (['ceo' => 'CEO', 'cto' => 'CTO', 'admin' => 'Admin', 'office_manager' => 'Office Manager', 'manager' => 'Manager', 'employee' => 'Employee', 'intern' => 'Intern'] as $rVal => $rLabel): ?>
                                <option value="<?= $rVal ?>" <?= ($employee['role'] ?? '') === $rVal ? 'selected' : '' ?>><?= $rLabel ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="fnx-form-label">Reporting Manager / Reports To</label>
                        <select name="manager_id" class="fnx-input">
                            <option value="">-- Direct under CEO / CTO / Executive --</option>
                            <?php foreach ($managersList as $mgr): ?>
                                <?php if ((int)$mgr['id'] !== (int)$employee['id']): ?>
                                    <option value="<?= $mgr['id'] ?>" <?= (int)($employee['manager_id'] ?? 0) === (int)$mgr['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mgr['full_name']) ?> (<?= htmlspecialchars($mgr['emp_code']) ?> · <?= ucfirst($mgr['role']) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="fnx-form-label">Account Status</label>
                        <select name="status" class="fnx-input">
                            <option value="active" <?= ($employee['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="pip" <?= ($employee['status'] ?? '') === 'pip' ? 'selected' : '' ?>>PIP (Performance Improvement Plan)</option>
                            <option value="suspended" <?= ($employee['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            <option value="terminated" <?= ($employee['status'] ?? '') === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                        </select>
                    </div>
                    <div>
                        <label class="fnx-form-label">Phone Number</label>
                        <input type="text" name="phone" class="fnx-input" value="<?= htmlspecialchars($employee['phone'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Designation</label>
                        <input type="text" name="designation" class="fnx-input" value="<?= htmlspecialchars($employee['designation'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Department</label>
                        <input type="text" name="department" class="fnx-input" value="<?= htmlspecialchars($employee['department'] ?? 'Operations') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Highest Qualification</label>
                        <input type="text" name="highest_qualification" class="fnx-input" value="<?= htmlspecialchars($employee['highest_qualification'] ?? $employee['qualification'] ?? '') ?>">
                    </div>
                </div>

                <!-- Section 2: Addresses -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    2. Addresses &amp; Emergency
                </div>
                <div class="fnx-grid-2" style="margin-bottom:18px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Current Address</label>
                        <textarea name="current_address" class="fnx-input" rows="2"><?= htmlspecialchars($employee['current_address'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="fnx-form-label">Permanent Address</label>
                        <textarea name="permanent_address" class="fnx-input" rows="2"><?= htmlspecialchars($employee['permanent_address'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="fnx-form-label">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" class="fnx-input" value="<?= htmlspecialchars($employee['emergency_contact_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Emergency Phone</label>
                        <input type="text" name="emergency_contact_phone" class="fnx-input" value="<?= htmlspecialchars($employee['emergency_contact_phone'] ?? '') ?>">
                    </div>
                </div>

                <!-- Section 3: Banking & Payroll -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    3. Payroll &amp; Banking Details
                </div>
                <div class="fnx-grid-2" style="margin-bottom:18px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="fnx-input" value="<?= htmlspecialchars($employee['bank_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Account Number</label>
                        <input type="text" name="bank_account_no" class="fnx-input" value="<?= htmlspecialchars($employee['bank_account_no'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">IFSC Code</label>
                        <input type="text" name="bank_ifsc" class="fnx-input" value="<?= htmlspecialchars($employee['bank_ifsc'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Account Holder</label>
                        <input type="text" name="bank_account_name" class="fnx-input" value="<?= htmlspecialchars($employee['bank_account_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">UPI ID</label>
                        <input type="text" name="upi_id" class="fnx-input" value="<?= htmlspecialchars($employee['upi_id'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">Reset Password (Optional)</label>
                        <input type="password" name="new_password" class="fnx-input" placeholder="Leave blank to keep current">
                    </div>
                </div>

                <!-- Section 4: KYC -->
                <div style="font-size:13px; font-weight:800; color:var(--fnx-orange); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                    4. Aadhaar &amp; PAN Numbers
                </div>
                <div class="fnx-grid-2" style="margin-bottom:14px; row-gap:12px;">
                    <div>
                        <label class="fnx-form-label">Aadhaar Card Number</label>
                        <input type="text" name="aadhar_number" class="fnx-input" value="<?= htmlspecialchars($employee['aadhar_number'] ?? $employee['aadhar_no'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="fnx-form-label">PAN Card Number</label>
                        <input type="text" name="pan_number" class="fnx-input" value="<?= htmlspecialchars($employee['pan_number'] ?? $employee['pan_no'] ?? '') ?>" style="text-transform:uppercase;">
                    </div>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" onclick="fnxCloseModal('editEmpModal')" class="fnx-btn fnx-btn-outline">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange" style="padding:10px 24px;">✓ Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

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
        <a href="/futurenxt/employees" class="fnx-btn fnx-btn-outline">← Back to Partners Directory</a>
        <?php if (Auth::isAdminOrExec()): ?>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <?php if ($employee['status'] === 'active'): ?>
                    <span class="fnx-badge fnx-badge-success">Status: Active</span>
                <?php elseif ($employee['status'] === 'suspended'): ?>
                    <span class="fnx-badge" style="background:#EF4444; color:#FFFFFF; font-weight:800;">Status: Suspended</span>
                <?php elseif ($employee['status'] === 'terminated'): ?>
                    <span class="fnx-badge" style="background:#111827; color:#FFFFFF; font-weight:800;">Status: Terminated</span>
                <?php elseif ($employee['status'] === 'resigned'): ?>
                    <?php 
                        $remDays = !empty($employee['notice_end_date']) ? max(0, (int)ceil((strtotime($employee['notice_end_date']) - time()) / 86400)) : 15;
                    ?>
                    <span class="fnx-badge fnx-badge-warning" style="font-weight:700;">
                        15d Notice Period (<?= $remDays ?>d left · Ends: <?= !empty($employee['notice_end_date']) ? date('d M Y', strtotime($employee['notice_end_date'])) : '' ?>)
                    </span>
                <?php elseif ($employee['status'] === 'pip'): ?>
                    <span class="fnx-badge fnx-badge-danger">Status: PIP</span>
                <?php endif; ?>

                <?php if ((int)$employee['id'] !== (int)$me['id']): ?>
                    <button type="button" onclick="sendDetailOnboardingEmail(<?= (int)$employee['id'] ?>, '<?= htmlspecialchars(addslashes($employee['full_name'])) ?>', '<?= htmlspecialchars(addslashes($employee['email'])) ?>')" class="fnx-btn fnx-btn-outline" style="font-size:13px; padding:7px 14px; color:#2563EB; border-color:#93C5FD;">
                        📧 Send Onboarding Email
                    </button>
                    <button type="button" onclick="openAdminResetPasswordModal(<?= (int)$employee['id'] ?>, '<?= htmlspecialchars(addslashes($employee['full_name'])) ?>', '<?= htmlspecialchars(addslashes($employee['emp_code'])) ?>', '<?= htmlspecialchars(addslashes($employee['email'])) ?>')" class="fnx-btn fnx-btn-outline" style="font-size:13px; padding:7px 14px; color:#D97706; border-color:#FCD34D;">
                        🔑 Reset Password (OTP)
                    </button>
                    <button type="button" onclick="openStatusModal(<?= (int)$employee['id'] ?>, '<?= htmlspecialchars(addslashes($employee['full_name'])) ?>', '<?= htmlspecialchars($employee['status']) ?>', '<?= htmlspecialchars(addslashes($employee['notice_end_date'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($employee['status_remarks'] ?? '')) ?>')" class="fnx-btn fnx-btn-outline" style="font-size:13px; padding:7px 14px; color:#EF4444; border-color:#FCA5A5;">
                        Status / Action
                    </button>
                <?php endif; ?>

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

        <?php if ($employee['status'] === 'resigned'): ?>
            <?php 
                $remDays = !empty($employee['notice_end_date']) ? max(0, (int)ceil((strtotime($employee['notice_end_date']) - time()) / 86400)) : 15;
            ?>
            <div style="margin-top:20px; padding:14px 18px; background:#FEF3C7; border-left:4px solid #F59E0B; border-radius:8px; font-size:13px;">
                <strong style="color:#92400E;">📜 15-Day Notice Period Active:</strong>
                <p style="margin:4px 0 0; color:#78350F; font-size:13px;">
                    Notice period duration: <strong><?= !empty($employee['notice_start_date']) ? date('d M Y', strtotime($employee['notice_start_date'])) : 'Today' ?></strong> to <strong><?= !empty($employee['notice_end_date']) ? date('d M Y', strtotime($employee['notice_end_date'])) : 'In 15 days' ?></strong> (<strong><?= $remDays ?> days remaining</strong>).
                </p>
                <?php if (!empty($employee['status_remarks'])): ?>
                    <p style="margin:4px 0 0; color:#92400E; font-size:12.5px;">Remarks: <?= htmlspecialchars($employee['status_remarks']) ?></p>
                <?php endif; ?>
            </div>
        <?php elseif ($employee['status'] === 'suspended'): ?>
            <div style="margin-top:20px; padding:14px 18px; background:#FEF2F2; border-left:4px solid #EF4444; border-radius:8px; font-size:13px;">
                <strong style="color:#991B1B;">⚠️ Account Suspended:</strong>
                <p style="margin:4px 0 0; color:#7F1D1D;">This employee account has been suspended by administration. Portal login and daily updates are disabled.</p>
                <?php if (!empty($employee['status_remarks'])): ?>
                    <p style="margin:4px 0 0; color:#991B1B; font-size:12.5px;">Remarks: <?= htmlspecialchars($employee['status_remarks']) ?></p>
                <?php endif; ?>
            </div>
        <?php elseif ($employee['status'] === 'terminated'): ?>
            <div style="margin-top:20px; padding:14px 18px; background:#111827; border-left:4px solid #F97316; border-radius:8px; font-size:13px; color:#F3F4F6;">
                <strong style="color:#F97316;">🛑 Employment Terminated:</strong>
                <p style="margin:4px 0 0; color:#E5E7EB;">Employment record terminated. Portal credentials and permissions have been revoked.</p>
                <?php if (!empty($employee['status_remarks'])): ?>
                    <p style="margin:4px 0 0; color:#D1D5DB; font-size:12.5px;">Remarks: <?= htmlspecialchars($employee['status_remarks']) ?></p>
                <?php endif; ?>
            </div>
        <?php elseif ($employee['status'] === 'pip'): ?>
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
                        <span class="fnx-badge fnx-badge-orange"><?= count($employee['reportees']) ?> Partner(s)</span>
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
                        Notice periods at FutureNxt are strictly <strong>15 calendar days</strong>. Setting status to Resigned automatically activates a 15-day countdown effective from today until <strong><?= date('d M Y', strtotime('+15 days')) ?></strong>.
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
                <h3 style="margin:0; font-size:18px; font-weight:800; color:#111827;">🔑 Reset Employee Password</h3>
                <p id="resetModalSubtitle" style="margin:2px 0 0; font-size:12.5px; color:#4B5563;"></p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('adminResetPasswordModal')">&times;</button>
        </div>

        <div class="fnx-modal-body">
            <!-- STEP 1: Request OTP -->
            <div id="otpStep1">
                <div style="background:#FFF9F4; border:1px solid #F3DECB; border-radius:10px; padding:16px; margin-bottom:16px;">
                    <p style="margin:0 0 10px; font-size:13.5px; color:#374151; line-height:1.5;">
                        As an Administrator, you can update this employee's password without needing their current password.
                    </p>
                    <p style="margin:0; font-size:12.5px; color:#6B7280; line-height:1.5;">
                        A secure 6-digit OTP will be dispatched to their registered email (<strong id="resetTargetEmailText"></strong>) from <strong>fnxt.admin@gmail.com</strong>.
                    </p>
                </div>

                <div id="otpRequestAlert" style="display:none; margin-bottom:14px; padding:10px 14px; border-radius:8px; font-size:13px;"></div>

                <button type="button" id="btnSendResetOtp" class="fnx-btn fnx-btn-orange" style="width:100%; justify-content:center; padding:11px 16px; font-size:14px; font-weight:700;" onclick="fnxSendPasswordOtp()">
                    📧 Send 6-Digit OTP to Employee's Email →
                </button>
            </div>

            <!-- STEP 2: Verify OTP & Enter New Password -->
            <div id="otpStep2" style="display:none;">
                <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:10px; padding:12px 14px; margin-bottom:16px; font-size:13px; color:#065F46;">
                    ✓ 6-Digit OTP sent to <strong id="resetEmailConfirmed"></strong>. Valid for 15 minutes.
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

<script>
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
            window.location.href = '/futurenxt/employees/detail?id=' + currentResetEmpId + '&success=' + encodeURIComponent(data.message || 'Password updated successfully! A security confirmation email was dispatched to the partner.');
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

function sendDetailOnboardingEmail(id, name, email) {
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
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

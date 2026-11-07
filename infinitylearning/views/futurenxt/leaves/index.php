<?php
declare(strict_types=1);

use App\FutureNxt\Auth;
use App\FutureNxt\Employee;
use App\FutureNxt\Leave;
use App\FutureNxt\Task;
use App\Helper;

Auth::requireLogin();
$me = Auth::user();
$pageTitle = 'Leave Operations & Task Conflict Check';
$activePage = 'leaves';

$statusFilter = $_GET['status'] ?? '';
$myLeavesOnly = isset($_GET['my']) && $_GET['my'] === '1';

$filters = [];
if (!empty($statusFilter)) {
    $filters['status'] = $statusFilter;
}
if ($myLeavesOnly || !Auth::isAdminOrExec()) {
    $filters['employee_id'] = (int)$me['id'];
}

$leaves = Leave::list($filters);
$allEmployees = Employee::list(['status' => 'active']);

$error = $_GET['error'] ?? $_SESSION['fnx_error'] ?? null;
$success = $_GET['success'] ?? $_SESSION['fnx_success'] ?? null;
unset($_SESSION['fnx_error'], $_SESSION['fnx_success']);

require __DIR__ . '/../layouts/header.php';
?>

<div class="fnx-container">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="margin:0 0 4px; font-size:24px; font-weight:800; color:var(--fnx-navy);">Leave Management</h1>
            <p style="margin:0; font-size:13.5px; color:var(--fnx-muted);">
                Leave applications with intelligent task-conflict detection and automated handover workflows.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" class="fnx-btn fnx-btn-orange" onclick="fnxOpenModal('applyLeaveModal')">
                <span>+</span> Apply for Leave
            </button>
            <a href="/futurenxt/wfh" class="fnx-btn fnx-btn-outline">
                💻 Work From Anywhere (WFA) Portal →
            </a>
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

    <!-- Filter Tabs -->
    <div class="fnx-card" style="padding:16px 20px; margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="/futurenxt/leaves" class="fnx-btn <?= empty($statusFilter) && !$myLeavesOnly ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 12px; font-size:12.5px;">All Requests</a>
                <a href="/futurenxt/leaves?my=1" class="fnx-btn <?= $myLeavesOnly ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 12px; font-size:12.5px;">My Leaves</a>
                <a href="/futurenxt/leaves?status=pending" class="fnx-btn <?= $statusFilter === 'pending' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 12px; font-size:12.5px;">Pending Approval</a>
                <a href="/futurenxt/leaves?status=approved" class="fnx-btn <?= $statusFilter === 'approved' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 12px; font-size:12.5px;">Approved</a>
            </div>
            <div style="font-size:12.5px; color:var(--fnx-muted);">
                Showing <strong><?= count($leaves) ?></strong> leave application(s)
            </div>
        </div>
    </div>

    <!-- Leave Requests List with Task Conflict Indicator -->
    <div class="fnx-card">
        <div class="fnx-table-wrap">
            <table class="fnx-table">
                <thead>
                    <tr>
                        <th>Personnel</th>
                        <th>Leave Type</th>
                        <th>Duration &amp; Dates</th>
                        <th>Task Conflict Detection</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leaves)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:36px; color:var(--fnx-muted);">
                                No leave requests recorded.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leaves as $l): 
                            // Run task conflict detection
                            $conflicts = Leave::getTaskConflicts((int)$l['employee_id'], $l['start_date'], $l['end_date']);
                            $hasConflict = !empty($conflicts);
                        ?>
                            <tr>
                                <td>
                                    <div style="font-weight:700; color:var(--fnx-navy);"><?= htmlspecialchars($l['employee_name']) ?></div>
                                    <div style="font-size:11.5px; color:var(--fnx-muted); font-family:monospace;"><?= htmlspecialchars($l['emp_code']) ?></div>
                                </td>
                                <td>
                                    <span class="fnx-badge fnx-badge-info" style="text-transform:capitalize;">
                                        <?= htmlspecialchars($l['leave_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size:13px; font-weight:600; color:var(--fnx-text);">
                                        <?= date('d M Y', strtotime($l['start_date'])) ?> to <?= date('d M Y', strtotime($l['end_date'])) ?>
                                    </div>
                                    <div style="font-size:11.5px; color:var(--fnx-muted);"><?= (int)$l['total_days'] ?> Day(s) Total</div>
                                </td>
                                <td>
                                    <?php if ($hasConflict && $l['status'] === 'pending'): ?>
                                        <div style="padding:6px 10px; background:#FEF2F2; border:1px solid #FCA5A5; border-radius:8px; font-size:12px; color:#991B1B;">
                                            <strong>⚠️ <?= count($conflicts) ?> Conflicting Task(s)!</strong>
                                            <div style="font-size:11px; margin-top:2px;">
                                                Active tasks due during leave period.
                                            </div>
                                        </div>
                                    <?php elseif ($l['status'] === 'approved'): ?>
                                        <span class="fnx-badge fnx-badge-success">Cleared ✓</span>
                                    <?php else: ?>
                                        <span class="fnx-badge fnx-badge-success">No Conflicts ✓</span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width:200px; font-size:12.5px; color:var(--fnx-muted);">
                                    <?= htmlspecialchars($l['reason']) ?>
                                </td>
                                <td>
                                    <?php if ($l['status'] === 'approved'): ?>
                                        <span class="fnx-badge fnx-badge-success">Approved</span>
                                    <?php elseif ($l['status'] === 'rejected'): ?>
                                        <span class="fnx-badge fnx-badge-danger">Rejected</span>
                                    <?php else: ?>
                                        <span class="fnx-badge fnx-badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <?php if (Auth::isAdminOrExec() && $l['status'] === 'pending'): ?>
                                        <button type="button" class="fnx-btn fnx-btn-orange" style="padding:4px 8px; font-size:11.5px;"
                                                onclick="openLeaveReviewModal(<?= htmlspecialchars(json_encode($l)) ?>, <?= htmlspecialchars(json_encode($conflicts)) ?>)">
                                            Review &amp; Decide
                                        </button>
                                    <?php else: ?>
                                        <span style="font-size:12px; color:var(--fnx-muted);">
                                            <?= !empty($l['reviewed_at']) ? 'Reviewed on ' . date('d M', strtotime($l['reviewed_at'])) : '—' ?>
                                        </span>
                                    <?php endif; ?>
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
     MODAL: APPLY LEAVE
     ========================================================================= -->
<div id="applyLeaveModal" class="fnx-modal-backdrop" style="display:<?= (isset($_GET['action']) && $_GET['action'] === 'new') ? 'flex' : 'none' ?>;">
    <div class="fnx-modal-dialog" style="max-width:540px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:var(--fnx-navy);">Apply for Leave</h3>
                <p style="margin:2px 0 0; font-size:12.5px; color:var(--fnx-muted);">The system automatically screens for task delivery conflicts.</p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('applyLeaveModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=apply_leave">
            <input type="hidden" name="redirect_url" value="/futurenxt/leaves">
            <div class="fnx-modal-body">
                <?php if (Auth::isAdminOrExec()): ?>
                    <div style="margin-bottom:14px;">
                        <label class="fnx-form-label">Partner *</label>
                        <select name="employee_id" class="fnx-select" required>
                            <?php foreach ($allEmployees as $emp): ?>
                                <option value="<?= $emp['id'] ?>" <?= ((int)$emp['id'] === (int)$me['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['emp_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Leave Classification *</label>
                    <select name="leave_type" class="fnx-select" required>
                        <option value="casual">Casual Leave</option>
                        <option value="sick">Sick / Medical Leave</option>
                        <option value="emergency">Emergency Leave</option>
                        <option value="paid">Paid Leave</option>
                        <option value="unpaid">Unpaid Leave</option>
                    </select>
                </div>

                <div class="fnx-grid-2" style="margin-bottom:16px;">
                    <div>
                        <label class="fnx-form-label">Start Date *</label>
                        <input type="date" name="start_date" class="fnx-input" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">End Date *</label>
                        <input type="date" name="end_date" class="fnx-input" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Reason for Absence *</label>
                    <textarea name="reason" class="fnx-textarea" rows="3" placeholder="Provide context for the leave request..." required></textarea>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('applyLeaveModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">Submit Leave Application →</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: REVIEW LEAVE & CONFLICT REASSIGNMENT
     ========================================================================= -->
<div id="leaveReviewModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:620px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:var(--fnx-navy);">Review Leave Application</h3>
                <p id="leaveApplicantName" style="margin:2px 0 0; font-size:12.5px; color:var(--fnx-muted);"></p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('leaveReviewModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=review_leave">
            <input type="hidden" id="reviewLeaveId" name="leave_id" value="">
            <div class="fnx-modal-body">
                <!-- Task Conflicts Box -->
                <div id="conflictsContainer" style="display:none; margin-bottom:18px; padding:14px; background:#FEF2F2; border:1px solid #FCA5A5; border-radius:10px;">
                    <strong style="color:#991B1B; font-size:13.5px; display:flex; align-items:center; gap:6px;">
                        <span>⚠️</span> Active Task Delivery Conflicts Detected!
                    </strong>
                    <p style="margin:4px 0 10px; font-size:12px; color:#7F1D1D;">
                        The following active tasks are assigned to this partner and are due during the requested leave period. Reassign these tasks to ensure project deadlines are not hindered:
                    </p>
                    <div id="conflictsList" style="display:flex; flex-direction:column; gap:8px;"></div>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Application Decision *</label>
                    <select name="decision" class="fnx-select" required>
                        <option value="approve">Approve Leave Application</option>
                        <option value="reject">Reject Leave Application</option>
                    </select>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Review Remarks / Conditions</label>
                    <textarea name="remarks" class="fnx-textarea" rows="2" placeholder="e.g. Approved with tasks handed over to team..."></textarea>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('leaveReviewModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">Confirm Decision</button>
            </div>
        </form>
    </div>
</div>

<script>
function openLeaveReviewModal(leaveData, conflicts) {
    document.getElementById('reviewLeaveId').value = leaveData.id;
    document.getElementById('leaveApplicantName').innerText = 
        leaveData.employee_name + ' (' + leaveData.emp_code + ') · ' + leaveData.start_date + ' to ' + leaveData.end_date + ' (' + leaveData.total_days + ' days)';
    
    const container = document.getElementById('conflictsContainer');
    const list = document.getElementById('conflictsList');
    list.innerHTML = '';

    if (conflicts && conflicts.length > 0) {
        container.style.display = 'block';
        conflicts.forEach(function(c) {
            const item = document.createElement('div');
            item.style.padding = '8px 10px';
            item.style.background = '#fff';
            item.style.borderRadius = '6px';
            item.style.border = '1px solid #FECACA';
            item.style.fontSize = '12px';
            item.style.display = 'flex';
            item.style.justifyContent = 'space-between';
            item.style.alignItems = 'center';
            item.innerHTML = '<div><strong>' + c.title + '</strong> (' + c.task_code + ')<br><span style="color:#64748B;">Due: ' + c.due_date + '</span></div>' +
                             '<a href="/futurenxt/tasks" target="_blank" class="fnx-btn fnx-btn-outline" style="padding:2px 8px; font-size:11px; color:#EF4444; border-color:#FCA5A5;">Reassign Task ↗</a>';
            list.appendChild(item);
        });
    } else {
        container.style.display = 'none';
    }

    fnxOpenModal('leaveReviewModal');
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

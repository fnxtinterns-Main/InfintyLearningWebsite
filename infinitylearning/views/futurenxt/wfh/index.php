<?php
declare(strict_types=1);

use App\FutureNxt\Auth;
use App\FutureNxt\Employee;
use App\FutureNxt\WFH;
use App\Helper;

Auth::requireLogin();
$me = Auth::user();
$pageTitle = 'Work From Anywhere (WFA) Operations';
$activePage = 'wfh';

$statusFilter = $_GET['status'] ?? '';
$myWfhOnly = isset($_GET['my']) && $_GET['my'] === '1';

$filters = [];
if (!empty($statusFilter)) {
    $filters['status'] = $statusFilter;
}
if ($myWfhOnly || !Auth::isAdminOrExec()) {
    $filters['employee_id'] = (int)$me['id'];
}

$wfhs = WFH::list($filters);
$allEmployees = Employee::list(['status' => 'active']);

$error = $_GET['error'] ?? $_SESSION['fnx_error'] ?? null;
$success = $_GET['success'] ?? $_SESSION['fnx_success'] ?? null;
unset($_SESSION['fnx_error'], $_SESSION['fnx_success']);

require __DIR__ . '/../layouts/header.php';
?>

<div class="fnx-container">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="margin:0 0 4px; font-size:24px; font-weight:800; color:var(--fnx-navy);">Work From Anywhere (WFA)</h1>
            <p style="margin:0; font-size:13.5px; color:var(--fnx-muted);">
                Submit remote work requests and manage manager/executive approvals.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" class="fnx-btn fnx-btn-orange" onclick="fnxOpenModal('requestWfhModal')">
                <span>+</span> Request WFA
            </button>
            <a href="/futurenxt/leaves" class="fnx-btn fnx-btn-outline">
                🏖️ Leave Portal →
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

    <!-- Table of WFA requests -->
    <div class="fnx-card">
        <div class="fnx-table-wrap">
            <table class="fnx-table">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Remote Dates</th>
                        <th>Days</th>
                        <th>Deliverables &amp; Reason</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($wfhs)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:36px; color:var(--fnx-muted);">
                                No WFA requests recorded.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($wfhs as $w): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:700; color:var(--fnx-navy);"><?= htmlspecialchars($w['employee_name']) ?></div>
                                    <div style="font-size:11.5px; color:var(--fnx-muted); font-family:monospace;"><?= htmlspecialchars($w['emp_code']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight:600; font-size:13px; color:var(--fnx-text);">
                                        <?= date('d M Y', strtotime($w['start_date'])) ?> to <?= date('d M Y', strtotime($w['end_date'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="fnx-badge fnx-badge-info"><?= (int)$w['total_days'] ?>d</span>
                                </td>
                                <td style="max-width:280px; font-size:12.5px; color:var(--fnx-muted);">
                                    <?= htmlspecialchars($w['reason']) ?>
                                </td>
                                <td>
                                    <?php if ($w['status'] === 'approved'): ?>
                                        <span class="fnx-badge fnx-badge-success">Approved ✓</span>
                                    <?php elseif ($w['status'] === 'rejected'): ?>
                                        <span class="fnx-badge fnx-badge-danger">Rejected</span>
                                    <?php else: ?>
                                        <span class="fnx-badge fnx-badge-warning">Pending Review</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <?php if (Auth::isAdminOrExec() && $w['status'] === 'pending'): ?>
                                        <button type="button" class="fnx-btn fnx-btn-orange" style="padding:4px 8px; font-size:11.5px;"
                                                onclick="openWfhReviewModal(<?= (int)$w['id'] ?>, '<?= htmlspecialchars(addslashes($w['employee_name'])) ?>', '<?= date('d M', strtotime($w['start_date'])) ?> to <?= date('d M Y', strtotime($w['end_date'])) ?>')">
                                            Review
                                        </button>
                                    <?php else: ?>
                                        <span style="font-size:12px; color:var(--fnx-muted);">
                                            <?= !empty($w['reviewed_at']) ? 'Reviewed on ' . date('d M', strtotime($w['reviewed_at'])) : '—' ?>
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
     MODAL: REQUEST WFA
     ========================================================================= -->
<div id="requestWfhModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:520px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:var(--fnx-navy);">Request Work From Anywhere (WFA)</h3>
                <p style="margin:2px 0 0; font-size:12.5px; color:var(--fnx-muted);">Ensure active internet connection and availability during working hours.</p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('requestWfhModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=request_wfa">
            <input type="hidden" name="redirect_url" value="/futurenxt/wfh">
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
                    <label class="fnx-form-label">Reason &amp; Deliverables *</label>
                    <textarea name="reason" class="fnx-textarea" rows="3" placeholder="Outline your deliverables and reasons for WFA..." required></textarea>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('requestWfhModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">Submit WFA Request →</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: REVIEW WFA
     ========================================================================= -->
<div id="wfhReviewModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:480px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:17px; font-weight:800; color:var(--fnx-navy);">Review WFA Request</h3>
                <p id="wfhApplicantName" style="margin:2px 0 0; font-size:12.5px; color:var(--fnx-muted);"></p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('wfhReviewModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=review_wfa">
            <input type="hidden" id="reviewWfhId" name="wfh_id" value="">
            <div class="fnx-modal-body">
                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Decision *</label>
                    <select name="decision" class="fnx-select" required>
                        <option value="approve">Approve WFA Request</option>
                        <option value="reject">Reject WFA Request</option>
                    </select>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Remarks</label>
                    <textarea name="remarks" class="fnx-textarea" rows="2" placeholder="e.g. Approved. Please join daily standup on Google Meet..."></textarea>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('wfhReviewModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">Confirm Decision</button>
            </div>
        </form>
    </div>
</div>

<script>
function openWfhReviewModal(id, name, dates) {
    document.getElementById('reviewWfhId').value = id;
    document.getElementById('wfhApplicantName').innerText = name + ' (' + dates + ')';
    fnxOpenModal('wfhReviewModal');
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
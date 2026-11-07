<?php
declare(strict_types=1);

use App\FutureNxt\Auth;
use App\FutureNxt\Employee;
use App\FutureNxt\DailyUpdate;
use App\FutureNxt\Task;
use App\FutureNxt\Audit;
use App\Helper;

Auth::requireLogin();
$me = Auth::user();
$pageTitle = 'Daily Work Updates & Standup';
$activePage = 'daily_updates';

$selectedDate = trim((string)($_GET['date'] ?? date('Y-m-d')));
$tab = $_GET['tab'] ?? 'all';
if (!Auth::isManagerOrAbove() && $tab !== 'my') {
    $tab = 'my';
}

$filters = [];
if (!empty($selectedDate)) {
    $filters['date'] = $selectedDate;
}

if ($tab === 'my') {
    $filters['employee_id'] = (int)$me['id'];
} elseif ($tab === 'team' && (Auth::role() === 'manager' || Auth::role() === 'office_manager')) {
    $filters['manager_id'] = (int)$me['id'];
}

if (!empty($_GET['q'])) {
    $filters['q'] = trim($_GET['q']);
}
if (!empty($_GET['employee_id']) && Auth::isManagerOrAbove()) {
    $filters['employee_id'] = (int)$_GET['employee_id'];
}

$updates = DailyUpdate::list($filters);

// Today's update for logged-in user
$myTodayUpdate = DailyUpdate::getToday((int)$me['id'], date('Y-m-d'));

// My active tasks for auto-filling
$myActiveTasks = Task::listForEmployee((int)$me['id'], ['status' => 'in_progress']);

// Company / Team Stats for selected date
$managerIdForStats = (Auth::role() === 'manager' || Auth::role() === 'office_manager') ? (int)$me['id'] : null;
$stats = DailyUpdate::getStats($selectedDate, $managerIdForStats);
$pendingPersonnel = DailyUpdate::getPendingEmployees($selectedDate, $managerIdForStats);
$allEmployees = Employee::list(['status' => 'active']);

$error = $_GET['error'] ?? $_SESSION['fnx_error'] ?? null;
$success = $_GET['success'] ?? $_SESSION['fnx_success'] ?? null;
unset($_SESSION['fnx_error'], $_SESSION['fnx_success']);

require __DIR__ . '/../layouts/header.php';
?>

<div class="fnx-container">
    <!-- Header Banner -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="margin:0 0 4px; font-size:24px; font-weight:800; color:var(--fnx-navy);">Daily Work Updates</h1>
            <p style="margin:0; font-size:13.5px; color:var(--fnx-muted);">
                Company-wide daily standup. Everyone at FutureNxt records daily deliverables, progress, and blockers.
            </p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button type="button" class="fnx-btn fnx-btn-orange" onclick="fnxOpenModal('writeUpdateModal')">
                <span><?= $myTodayUpdate ? '✏️' : '+' ?></span> <?= $myTodayUpdate ? 'Edit Today\'s Update' : 'Write Today\'s Update' ?>
            </button>
            <a href="/futurenxt/tasks" class="fnx-btn fnx-btn-outline">
                📋 Task Hub →
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

    <?php $isExempt = in_array($me['role'], ['ceo', 'admin'], true); ?>
    <!-- User's Personal Status Card for Today -->
    <div class="fnx-card" style="margin-bottom:24px; background:<?= $myTodayUpdate ? '#ECFDF5' : ($isExempt ? '#EFF6FF' : '#FFFBEB') ?>; border:1.5px solid <?= $myTodayUpdate ? '#A7F3D0' : ($isExempt ? '#BFDBFE' : '#FDE68A') ?>;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:44px; height:44px; border-radius:12px; background:<?= $myTodayUpdate ? '#10B981' : ($isExempt ? '#3B82F6' : '#F59E0B') ?>; color:#FFF; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">
                    <?= $myTodayUpdate ? '✓' : ($isExempt ? '👑' : '📝') ?>
                </div>
                <div>
                    <strong style="font-size:15px; color:#111827; display:block;">
                        <?php if ($isExempt): ?>
                            <?= $myTodayUpdate ? 'Executive Log Submitted for Today (' . date('d M Y') . ')' : 'Executive Leadership (Admin & CEO) · Daily Logging Optional' ?>
                        <?php else: ?>
                            <?= $myTodayUpdate ? 'You have submitted your daily update for today (' . date('d M Y') . ')' : 'Daily Update Required for Today (' . date('d M Y') . ')' ?>
                        <?php endif; ?>
                    </strong>
                    <p style="margin:2px 0 0; font-size:13px; color:#4B5563;">
                        <?php if ($isExempt): ?>
                            <?= $myTodayUpdate ? ('Logged at ' . Audit::formatIst($myTodayUpdate['updated_at'] ?? $myTodayUpdate['created_at'], 'h:i A') . '.') : 'As Administrator/CEO, you are excluded from mandatory daily logs. You can review all organizational standups below.' ?>
                        <?php else: ?>
                            <?= $myTodayUpdate 
                                ? ('Submitted at ' . Audit::formatIst($myTodayUpdate['updated_at'] ?? $myTodayUpdate['created_at'], 'h:i A') . '. You can revise or add further deliverables anytime before midnight.') 
                                : 'Every team member must submit their accomplishments and deliverables daily before end of day.' ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div>
                <button type="button" class="fnx-btn <?= $myTodayUpdate ? 'fnx-btn-outline' : 'fnx-btn-orange' ?>" onclick="fnxOpenModal('writeUpdateModal')" style="font-size:13px;">
                    <?= $myTodayUpdate ? '✏️ Revise Today\'s Update' : ($isExempt ? '+ Write Optional Update' : 'Submit Today\'s Update Now →') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Management / Leadership Standup Metrics -->
    <?php if (Auth::isManagerOrAbove()): ?>
        <div class="fnx-grid-4" style="margin-bottom:24px;">
            <div class="fnx-card">
                <div style="font-size:12px; font-weight:800; color:#4B5563; text-transform:uppercase; letter-spacing:0.04em;">Selected Date</div>
                <div style="font-size:22px; font-weight:800; color:#111827; margin-top:4px;">
                    <?= date('d M Y', strtotime($selectedDate)) ?>
                </div>
                <div style="font-size:12px; color:#6B7280; margin-top:4px;">
                    <?= $selectedDate === date('Y-m-d') ? '● Today' : 'Historical Date' ?>
                </div>
            </div>
            <div class="fnx-card">
                <div style="font-size:12px; font-weight:800; color:#4B5563; text-transform:uppercase; letter-spacing:0.04em;">Submitted Updates</div>
                <div style="font-size:28px; font-weight:800; color:#16A34A; margin-top:4px;">
                    <?= $stats['submitted'] ?> / <?= $stats['total_active'] ?>
                </div>
                <div style="font-size:12px; color:#4B5563; margin-top:4px;">
                    <strong><?= $stats['percentage'] ?>%</strong> of team logged
                </div>
            </div>
            <div class="fnx-card">
                <div style="font-size:12px; font-weight:800; color:#4B5563; text-transform:uppercase; letter-spacing:0.04em;">Pending Submissions</div>
                <div style="font-size:28px; font-weight:800; color:<?= $stats['pending'] > 0 ? '#DC2626' : '#16A34A' ?>; margin-top:4px;">
                    <?= $stats['pending'] ?>
                </div>
                <div style="font-size:12px; color:#4B5563; margin-top:4px;">
                    <?= $stats['pending'] > 0 ? 'Awaiting submission' : 'All personnel completed ✓' ?>
                </div>
            </div>
            <div class="fnx-card">
                <div style="font-size:12px; font-weight:800; color:#4B5563; text-transform:uppercase; letter-spacing:0.04em;">Filter Date</div>
                <form method="GET" action="/futurenxt/daily-updates" style="margin-top:6px; display:flex; gap:6px;">
                    <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
                    <input type="date" name="date" class="fnx-input" style="padding:6px 10px; font-size:12.5px;" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()">
                </form>
            </div>
        </div>

        <!-- Pending Employees Drawer if any -->
        <?php if (!empty($pendingPersonnel)): ?>
            <div class="fnx-card" style="margin-bottom:24px; padding:16px 20px; background:#FFF; border-left:4px solid #F59E0B;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <strong style="font-size:13.5px; color:#B45309; display:flex; align-items:center; gap:6px;">
                        <span>⚠️</span> <?= count($pendingPersonnel) ?> Personnel Pending Update for <?= date('d M Y', strtotime($selectedDate)) ?>:
                    </strong>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    <?php foreach ($pendingPersonnel as $p): ?>
                        <span class="fnx-badge" style="background:#FEF3C7; color:#92400E; border:1px solid #FDE68A; font-size:11.5px; padding:4px 10px;">
                            <?= htmlspecialchars($p['full_name']) ?> (<?= htmlspecialchars($p['emp_code']) ?>)
                            <?php if (!empty($p['manager_name'])): ?>
                                · <em style="font-size:10.5px; opacity:0.85;">Under <?= htmlspecialchars($p['manager_name']) ?></em>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Navigation Filter Bar -->
    <div class="fnx-card" style="padding:16px 20px; margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <?php if (Auth::isManagerOrAbove()): ?>
                    <a href="/futurenxt/daily-updates?tab=all&date=<?= urlencode($selectedDate) ?>" class="fnx-btn <?= $tab === 'all' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 14px; font-size:12.5px;">
                        All Organization Updates (<?= count($updates) ?>)
                    </a>
                    <?php if (Auth::role() === 'manager' || Auth::role() === 'office_manager'): ?>
                        <a href="/futurenxt/daily-updates?tab=team&date=<?= urlencode($selectedDate) ?>" class="fnx-btn <?= $tab === 'team' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 14px; font-size:12.5px;">
                            My Direct Reports
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="/futurenxt/daily-updates?tab=my" class="fnx-btn <?= $tab === 'my' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 14px; font-size:12.5px;">
                    My Update History
                </a>
            </div>

            <form method="GET" action="/futurenxt/daily-updates" style="display:flex; gap:8px; align-items:center;">
                <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
                <input type="date" name="date" class="fnx-input" style="padding:6px 10px; font-size:12.5px; width:auto;" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()">
                <?php if ($selectedDate !== date('Y-m-d')): ?>
                    <a href="/futurenxt/daily-updates?tab=<?= htmlspecialchars($tab) ?>&date=<?= date('Y-m-d') ?>" class="fnx-btn fnx-btn-outline" style="padding:6px 10px; font-size:12px;">
                        Today
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Daily Updates List -->
    <div style="display:flex; flex-direction:column; gap:18px;">
        <?php if (empty($updates)): ?>
            <div class="fnx-card" style="text-align:center; padding:48px 20px;">
                <span style="font-size:36px; display:block; margin-bottom:8px;">📝</span>
                <h3 style="margin:0 0 4px; font-size:17px; font-weight:800; color:var(--fnx-navy);">No updates recorded for <?= date('d M Y', strtotime($selectedDate)) ?></h3>
                <p style="margin:0 0 16px; font-size:13.5px; color:var(--fnx-muted);">Be the first to record what you achieved today!</p>
                <button type="button" class="fnx-btn fnx-btn-orange" onclick="fnxOpenModal('writeUpdateModal')">
                    + Write Today's Update
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($updates as $up): 
                $photo = !empty($up['photo_path']) ? $up['photo_path'] : '/assets/img/avatar-default.png';
            ?>
                <div class="fnx-card" style="padding:22px 26px;">
                    <!-- User Header & Timestamp -->
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($up['employee_name']) ?>"
                                 onerror="this.onerror=null; this.src='/assets/img/avatar-default.png';"
                                 style="width:46px; height:46px; border-radius:12px; object-fit:cover; border:1px solid var(--fnx-border);">
                            <div>
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                    <strong style="font-size:15px; color:#111827;"><?= htmlspecialchars($up['employee_name']) ?></strong>
                                    <span class="fnx-badge fnx-badge-orange" style="font-family:'JetBrains Mono',monospace; font-size:11px;">
                                        <?= htmlspecialchars($up['emp_code']) ?>
                                    </span>
                                    <span class="fnx-badge" style="font-size:11px;">
                                        <?= htmlspecialchars($up['designation'] ?? ucfirst($up['employee_role'])) ?>
                                    </span>
                                </div>
                                <div style="font-size:12px; color:#6B7280; margin-top:2px;">
                                    <span><?= htmlspecialchars($up['department'] ?? 'Operations') ?></span>
                                    <?php if (!empty($up['manager_name'])): ?>
                                        · <span style="color:#2563EB; font-weight:600;">Reports to: <?= htmlspecialchars($up['manager_name']) ?> (<?= htmlspecialchars($up['manager_code']) ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div style="text-align:right;">
                            <span class="fnx-badge fnx-badge-info" style="font-size:12px; font-weight:700;">
                                📅 <?= date('d M Y', strtotime($up['update_date'])) ?>
                            </span>
                            <div style="font-size:11.5px; color:#6B7280; margin-top:4px;">
                                Logged at <?= date('h:i A', strtotime($up['updated_at'] ?? $up['created_at'])) ?>
                                <?php if (!empty($up['hours_spent'])): ?>
                                    · <strong><?= (float)$up['hours_spent'] ?> hrs</strong>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks Associated -->
                    <?php if (!empty($up['tasks_summary'])): ?>
                        <div style="margin-bottom:12px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span style="font-size:11.5px; font-weight:700; color:#4B5563; text-transform:uppercase; letter-spacing:0.04em;">Tasks Worked On:</span>
                            <span class="fnx-badge" style="background:#FFF0E5; color:var(--fnx-orange); border:1px solid #FFD8BE; font-size:12px;">
                                <?= htmlspecialchars($up['tasks_summary']) ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <!-- Work Done Deliverables / Accomplishments -->
                    <div style="margin-bottom:14px;">
                        <div style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.04em;">
                            Accomplishments &amp; Deliverables:
                        </div>
                        <div style="white-space:pre-wrap; word-break:break-word; font-size:14px; line-height:1.65; color:#111827; background:#FFF9F4; border:1.5px solid var(--fnx-border); border-radius:10px; padding:14px 18px; font-family:inherit;">
                            <?= htmlspecialchars($up['work_done']) ?>
                        </div>
                    </div>

                    <!-- Blockers / Challenges if any -->
                    <?php if (!empty($up['blockers'])): ?>
                        <div style="margin-bottom:10px; padding:10px 14px; background:#FEF2F2; border:1px solid #FCA5A5; border-radius:8px;">
                            <strong style="color:#991B1B; font-size:12.5px; display:flex; align-items:center; gap:6px;">
                                <span>⚠️</span> Blockers / Obstacles:
                            </strong>
                            <div style="white-space:pre-wrap; color:#7F1D1D; font-size:13px; line-height:1.5; margin-top:2px;">
                                <?= htmlspecialchars($up['blockers']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Tomorrow's Plan if any -->
                    <?php if (!empty($up['plan_tomorrow'])): ?>
                        <div style="padding:10px 14px; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px;">
                            <strong style="color:#166534; font-size:12.5px; display:flex; align-items:center; gap:6px;">
                                <span>🎯</span> Plan for Next Working Day:
                            </strong>
                            <div style="white-space:pre-wrap; color:#14532D; font-size:13px; line-height:1.5; margin-top:2px;">
                                <?= htmlspecialchars($up['plan_tomorrow']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- =========================================================================
     MODAL: WRITE / EDIT DAILY UPDATE
     ========================================================================= -->
<div id="writeUpdateModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:680px; width:95%;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:#111827;">📝 Submit Daily Work Update</h3>
                <p style="margin:2px 0 0; font-size:12px; color:#4B5563;">
                    Record your deliverables, points accomplished, and any blockers for <?= date('l, d F Y') ?>.
                </p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('writeUpdateModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=submit_daily_update">
            <input type="hidden" name="redirect_url" value="/futurenxt/daily-updates">
            <div class="fnx-modal-body">
                <div class="fnx-grid-2" style="margin-bottom:14px;">
                    <div>
                        <label class="fnx-form-label">Update Date *</label>
                        <input type="date" name="update_date" class="fnx-input" value="<?= htmlspecialchars($myTodayUpdate['update_date'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div>
                        <label class="fnx-form-label">Hours Spent (Optional)</label>
                        <input type="number" step="0.5" min="0.5" max="24" name="hours_spent" class="fnx-input" placeholder="e.g. 8.0" value="<?= htmlspecialchars((string)($myTodayUpdate['hours_spent'] ?? '8.0')) ?>">
                    </div>
                </div>

                <!-- Active Tasks Selection -->
                <?php if (!empty($myActiveTasks)): ?>
                    <div style="margin-bottom:14px;">
                        <label class="fnx-form-label">Tasks Worked On Today</label>
                        <div style="display:flex; flex-wrap:wrap; gap:8px;">
                            <?php foreach ($myActiveTasks as $at): ?>
                                <label style="display:inline-flex; align-items:center; gap:6px; background:#FFF9F4; border:1px solid var(--fnx-border); padding:5px 10px; border-radius:6px; cursor:pointer; font-size:12px;">
                                    <input type="checkbox" onchange="fnxAppendTaskSummary(this, '<?= htmlspecialchars(addslashes($at['task_code'] . ': ' . $at['title'])) ?>')" style="accent-color:var(--fnx-orange);">
                                    <strong style="color:var(--fnx-orange);"><?= htmlspecialchars($at['task_code']) ?></strong>
                                    <span><?= htmlspecialchars(mb_substr($at['title'], 0, 30)) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom:14px;">
                    <label class="fnx-form-label">Task(s) Summary / Subject Line</label>
                    <input type="text" id="dailyTasksSummary" name="tasks_summary" class="fnx-input" placeholder="e.g. TSK-0001 (BSPC Dashboard) + API Integration" value="<?= htmlspecialchars($myTodayUpdate['tasks_summary'] ?? '') ?>">
                </div>

                <div style="margin-bottom:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; flex-wrap:wrap; gap:6px;">
                        <label class="fnx-form-label" style="margin:0;">What did you accomplish today? (List all points &amp; deliverables) *</label>
                        <div style="display:inline-flex; align-items:center; gap:6px;">
                            <button type="button" class="fnx-btn fnx-btn-outline" style="padding:2px 8px; font-size:11px; background:#FFF9F4;" onclick="fnxInsertPointer('number')">
                                + 1. 2. 3. Numbered
                            </button>
                            <button type="button" class="fnx-btn fnx-btn-outline" style="padding:2px 8px; font-size:11px; background:#FFF9F4;" onclick="fnxInsertPointer('bullet')">
                                + • Bullets
                            </button>
                        </div>
                    </div>
                    <textarea name="work_done" id="fnxWorkDoneTextarea" class="fnx-textarea" rows="6" placeholder="1. Completed payment gateway callback verification&#10;2. Fixed UI responsiveness on mobile&#10;3. Prepared test case documentation..." required style="resize:vertical; font-family:inherit; line-height:1.6;"><?= htmlspecialchars($myTodayUpdate['work_done'] ?? '') ?></textarea>
                    <small style="color:#6B7280; font-size:11.5px; margin-top:3px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">
                        <span>* Point-by-point breakdown is recommended for clarity.</span>
                        <span style="color:var(--fnx-orange); font-weight:700;">💡 Auto-numbering active (Press Enter for next point)</span>
                    </small>
                </div>

                <div class="fnx-grid-2" style="margin-bottom:14px;">
                    <div>
                        <label class="fnx-form-label">Blockers / Obstacles (Optional)</label>
                        <textarea name="blockers" class="fnx-textarea" rows="2" placeholder="Any pending approvals, API keys, or blockers..."><?= htmlspecialchars($myTodayUpdate['blockers'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="fnx-form-label">Plan for Tomorrow (Optional)</label>
                        <textarea name="plan_tomorrow" class="fnx-textarea" rows="2" placeholder="Tasks to be picked up next..."><?= htmlspecialchars($myTodayUpdate['plan_tomorrow'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('writeUpdateModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">✓ Submit Daily Update →</button>
            </div>
        </form>
    </div>
</div>

<script>
function fnxAppendTaskSummary(chk, text) {
    const input = document.getElementById('dailyTasksSummary');
    if (!input) return;
    let parts = input.value.split(',').map(s => s.trim()).filter(Boolean);
    if (chk.checked) {
        if (!parts.includes(text)) parts.push(text);
    } else {
        parts = parts.filter(p => p !== text);
    }
    input.value = parts.join(', ');
}

// Auto-numbering pointers functionality
document.addEventListener('DOMContentLoaded', function() {
    const workTextarea = document.getElementById('fnxWorkDoneTextarea');
    if (!workTextarea) return;

    // When focusing an empty textarea, auto insert "1. "
    workTextarea.addEventListener('focus', function() {
        if (!this.value.trim()) {
            this.value = '1. ';
            this.setSelectionRange(3, 3);
        }
    });

    // Auto numbering on Enter and clean deletion on Backspace
    workTextarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const val = this.value;
            const cursorPos = this.selectionStart;
            const lineStart = val.lastIndexOf('\n', cursorPos - 1) + 1;
            const currentLine = val.substring(lineStart, cursorPos);
            
            // Check numbered list: e.g. "1. " or "2. "
            const matchNumber = currentLine.match(/^(\s*)(\d+)\.\s*(.*)$/);
            if (matchNumber) {
                e.preventDefault();
                const indent = matchNumber[1];
                const num = parseInt(matchNumber[2], 10);
                const content = matchNumber[3].trim();
                
                // If user pressed Enter on an empty pointer (just "X. "), clear it
                if (!content) {
                    const before = val.substring(0, lineStart);
                    const after = val.substring(cursorPos);
                    this.value = before + after;
                    this.setSelectionRange(lineStart, lineStart);
                    return;
                }
                
                const nextPoint = '\n' + indent + (num + 1) + '. ';
                const before = val.substring(0, cursorPos);
                const after = val.substring(cursorPos);
                this.value = before + nextPoint + after;
                const newPos = cursorPos + nextPoint.length;
                this.setSelectionRange(newPos, newPos);
                return;
            }

            // Check bullet list: e.g. "• " or "- "
            const matchBullet = currentLine.match(/^(\s*)([-•*])\s*(.*)$/);
            if (matchBullet) {
                e.preventDefault();
                const indent = matchBullet[1];
                const bullet = matchBullet[2];
                const content = matchBullet[3].trim();

                if (!content) {
                    const before = val.substring(0, lineStart);
                    const after = val.substring(cursorPos);
                    this.value = before + after;
                    this.setSelectionRange(lineStart, lineStart);
                    return;
                }

                const nextPoint = '\n' + indent + bullet + ' ';
                const before = val.substring(0, cursorPos);
                const after = val.substring(cursorPos);
                this.value = before + nextPoint + after;
                const newPos = cursorPos + nextPoint.length;
                this.setSelectionRange(newPos, newPos);
                return;
            }
        }
    });
});

function fnxInsertPointer(type) {
    const ta = document.getElementById('fnxWorkDoneTextarea');
    if (!ta) return;
    const val = ta.value;
    if (type === 'number') {
        const lines = val.split('\n').filter(Boolean);
        const nextNum = lines.length + 1;
        ta.value = val ? (val.trimEnd() + '\n' + nextNum + '. ') : '1. ';
    } else {
        ta.value = val ? (val.trimEnd() + '\n• ') : '• ';
    }
    ta.focus();
    ta.setSelectionRange(ta.value.length, ta.value.length);
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<?php
declare(strict_types=1);

use App\FutureNxt\Auth;
use App\FutureNxt\Employee;
use App\FutureNxt\Task;
use App\FutureNxt\Leave;
use App\FutureNxt\WFH;
use App\FutureNxt\Audit;
use App\FutureNxt\DailyUpdate;
use App\FutureNxt\Notice;

Auth::requireLogin();
$me = Auth::user();
$pageTitle = 'Executive Dashboard';
$activePage = 'dashboard';

// Fetch metrics
$empStats = Employee::getStats();
$taskStats = Task::getStats();
$leaveStats = Leave::getStats();
$wfhStats = WFH::getStats();
$dailyUpdateStats = DailyUpdate::getStats();
$myTodayUpdate = DailyUpdate::getToday((int)$me['id']);
$recentNotices = Notice::getRecent(3);

$myActiveTasks = Task::listForEmployee((int)$me['id'], ['status' => 'in_progress']);
$myActiveCount = count($myActiveTasks);
$recentTasks = Task::list(['exclude_status' => 'approved', 'limit' => 6]);
$pendingLeaves = Leave::list(['status' => 'pending', 'limit' => 5]);
$pendingWfhs = WFH::list(['status' => 'pending', 'limit' => 5]);
$recentAudits = Audit::getRecent(6);

require __DIR__ . '/layouts/header.php';
?>

<div class="fnx-container">
    <!-- Welcome Banner matching site styling -->
    <div class="fnx-welcome-banner" style="background: linear-gradient(135deg, #FFF0E5 0%, #FFE2CC 100%) !important; border: 1.5px solid var(--fnx-border) !important; border-radius:18px; padding:28px 32px; margin-bottom:28px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; box-shadow:0 4px 16px rgba(43,27,18,0.04);">
        <div>
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                <span class="fnx-badge fnx-badge-orange"><?= htmlspecialchars($me['emp_code']) ?></span>
                <span style="font-size:13px; color:#4B5563 !important; font-weight:700;"><?= htmlspecialchars($me['department'] ?? 'Corporate Executive') ?></span>
            </div>
            <h1 style="margin:0 0 6px; font-size:26px; font-weight:800; letter-spacing:-0.02em; color:#111827 !important;">
                Welcome back, <?= htmlspecialchars($me['full_name']) ?>!
            </h1>
            <p style="margin:0; font-size:14px; color:#4B5563 !important; font-weight:500;">
                FutureNxt Operations Command Center · <?= date('l, d F Y') ?>
            </p>
        </div>
        <div class="fnx-welcome-actions" style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="/futurenxt/daily-updates" class="fnx-btn" style="background:<?= $myTodayUpdate ? '#10B981' : '#FF7A18' ?> !important; color:#FFFFFF !important; border:none !important; font-weight:700;">
                📝 <?= $myTodayUpdate ? '✓ Today\'s Update Logged' : '+ Submit Daily Update' ?>
            </a>
            <?php if (Auth::isManagerOrAbove()): ?>
                <a href="/futurenxt/tasks?action=new" class="fnx-btn fnx-btn-orange">
                    <span>+</span> Assign New Task
                </a>
            <?php endif; ?>
            <?php if (Auth::isAdminOrExec()): ?>
                <a href="/futurenxt/employees?action=new" class="fnx-btn fnx-btn-outline" style="color:#111827 !important; border-color:#111827 !important; background:#FFFFFF !important;">
                    <span>+</span> Onboard Employee
                </a>
            <?php endif; ?>
            <a href="/futurenxt/leaves?action=new" class="fnx-btn fnx-btn-outline" style="color:#111827 !important; border-color:#111827 !important; background:#FFFFFF !important;">
                Apply Leave / WFH
            </a>
        </div>
    </div>

    <?php $isExempt = in_array($me['role'], ['ceo', 'admin'], true); ?>
    <!-- Daily Work Update Mandatory Tracker Banner -->
    <div class="fnx-card" style="margin-bottom:24px; background:#fff !important; border-left: 4px solid <?= $myTodayUpdate ? '#10B981' : ($isExempt ? '#3B82F6' : '#FF7A18') ?> !important; box-shadow:0 2px 10px rgba(0,0,0,0.03);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div style="display:flex; align-items:flex-start; gap:14px;">
                <div style="width:40px; height:40px; border-radius:10px; background:<?= $myTodayUpdate ? '#ECFDF5' : ($isExempt ? '#EFF6FF' : '#FFF7ED') ?>; color:<?= $myTodayUpdate ? '#059669' : ($isExempt ? '#2563EB' : '#EA580C') ?>; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;">
                    <?= $myTodayUpdate ? '✅' : ($isExempt ? '👑' : '📝') ?>
                </div>
                <div>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <strong style="font-size:15px; color:#111827 !important;">
                            <?php if ($isExempt): ?>
                                <?= $myTodayUpdate ? "Today's Work Log Submitted" : "Company Daily Updates Overview (Admin & CEO Exempt)" ?>
                            <?php else: ?>
                                <?= $myTodayUpdate ? "Today's Work Update Submitted" : "Mandatory Daily Work Update Pending" ?>
                            <?php endif; ?>
                        </strong>
                        <span class="fnx-badge <?= $myTodayUpdate ? 'fnx-badge-success' : ($isExempt ? 'fnx-badge-info' : 'fnx-badge-warning') ?>">
                            <?php if ($myTodayUpdate): ?>
                                Logged at <?= Audit::formatIst($myTodayUpdate['created_at'], 'h:i A') ?>
                            <?php elseif ($isExempt): ?>
                                Admin/CEO: Optional
                            <?php else: ?>
                                Submission Required
                            <?php endif; ?>
                        </span>
                        <span style="font-size:12px; color:#6B7280;">· Company Total: <strong><?= (int)$dailyUpdateStats['submitted'] ?> / <?= (int)$dailyUpdateStats['total_active'] ?> submitted (<?= (int)$dailyUpdateStats['percentage'] ?>%)</strong></span>
                    </div>
                    <p style="margin:4px 0 0; font-size:13px; color:#4B5563 !important;">
                        <?php if ($myTodayUpdate): ?>
                            Tasks Summary: "<strong><?= htmlspecialchars(mb_strimwidth($myTodayUpdate['tasks_summary'] ?? '', 0, 90, '...')) ?></strong>"
                        <?php elseif ($isExempt): ?>
                            Daily work logs are mandatory for all team members, interns, and managers. Track full organizational standup below.
                        <?php else: ?>
                            Every team member, manager, and executive must write a daily log of work accomplished today.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div>
                <a href="/futurenxt/daily-updates" class="fnx-btn <?= $myTodayUpdate ? 'fnx-btn-outline' : 'fnx-btn-orange' ?>" style="font-size:12.5px; padding:6px 14px;">
                    <?= $myTodayUpdate ? 'View / Edit Update →' : ($isExempt ? 'Review Daily Updates 📋' : 'Write Today\'s Update 📝') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Personal Workload Limit Notice -->
    <div class="fnx-card" style="margin-bottom:28px; background:#fff !important; border-left: 4px solid <?= $myActiveCount >= 3 ? '#EF4444' : ($myActiveCount === 2 ? '#F59E0B' : '#10B981') ?> !important;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <strong style="font-size:15px; color:#111827 !important;">Personal Active Task Load</strong>
                    <span class="fnx-badge <?= $myActiveCount >= 3 ? 'fnx-badge-danger' : 'fnx-badge-info' ?>">
                        <?= $myActiveCount ?> / 3 Active Tasks Limit
                    </span>
                </div>
                <p style="margin:4px 0 0; font-size:13px; color:#4B5563 !important;">
                    Company policy restricts active tasks to a maximum of 3 concurrent assignments per individual to guarantee quality and deliverability.
                </p>
            </div>
            <div style="min-width:220px; flex:1; max-width:300px;">
                <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:800; color:#111827 !important; margin-bottom:6px;">
                    <span>Current Capacity</span>
                    <span><?= round(($myActiveCount / 3) * 100) ?>%</span>
                </div>
                <div style="height:10px; background:#E5E7EB; border-radius:999px; overflow:hidden;">
                    <div style="height:100%; width:<?= ($myActiveCount / 3) * 100 ?>%; background:<?= $myActiveCount >= 3 ? '#EF4444' : ($myActiveCount === 2 ? '#F59E0B' : '#10B981') ?>; transition:width 0.4s ease;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 High-Level Metric Tiles -->
    <div class="fnx-grid-4" style="margin-bottom:28px;">
        <div class="fnx-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:800; color:#4B5563 !important; text-transform:uppercase; letter-spacing:0.05em;">Total Personnel</div>
                    <div style="font-size:32px; font-weight:800; color:#111827 !important; margin-top:4px;">
                        <?= (int)($empStats['total'] ?? 0) ?>
                    </div>
                </div>
                <div style="width:44px; height:44px; border-radius:12px; background:rgba(255,122,24,0.1); color:var(--fnx-orange); display:flex; align-items:center; justify-content:center; font-size:20px;">
                    👥
                </div>
            </div>
            <div style="margin-top:14px; font-size:12.5px; color:#4B5563 !important; display:flex; gap:12px;">
                <span>Active: <strong style="color:#16A34A !important;"><?= (int)($empStats['active'] ?? 0) ?></strong></span>
                <span>Interns: <strong style="color:#111827 !important;"><?= (int)($empStats['interns'] ?? 0) ?></strong></span>
                <span>PIP: <strong style="color:#DC2626 !important;"><?= (int)($empStats['pip'] ?? 0) ?></strong></span>
            </div>
        </div>

        <div class="fnx-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:800; color:#4B5563 !important; text-transform:uppercase; letter-spacing:0.05em;">Active Tasks</div>
                    <div style="font-size:32px; font-weight:800; color:var(--fnx-orange) !important; margin-top:4px;">
                        <?= (int)($taskStats['active'] ?? (($taskStats['in_progress'] ?? 0) + ($taskStats['pending'] ?? 0))) ?>
                    </div>
                </div>
                <div style="width:44px; height:44px; border-radius:12px; background:rgba(255,122,24,0.1); color:var(--fnx-orange); display:flex; align-items:center; justify-content:center; font-size:20px;">
                    📋
                </div>
            </div>
            <div style="margin-top:14px; font-size:12.5px; color:#4B5563 !important; display:flex; gap:12px;">
                <span>Pending Approval: <strong style="color:var(--fnx-orange) !important;"><?= (int)($taskStats['submitted'] ?? 0) ?></strong></span>
                <span>Completed: <strong style="color:#111827 !important;"><?= (int)($taskStats['approved'] ?? 0) ?></strong></span>
            </div>
        </div>

        <div class="fnx-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:800; color:#4B5563 !important; text-transform:uppercase; letter-spacing:0.05em;">Pending Leaves</div>
                    <div style="font-size:32px; font-weight:800; color:#2563EB !important; margin-top:4px;">
                        <?= (int)($leaveStats['pending'] ?? 0) ?>
                    </div>
                </div>
                <div style="width:44px; height:44px; border-radius:12px; background:rgba(37,99,235,0.1); color:#2563EB; display:flex; align-items:center; justify-content:center; font-size:20px;">
                    🏖️
                </div>
            </div>
            <div style="margin-top:14px; font-size:12.5px; color:#4B5563 !important;">
                <span>Approved this month: <strong style="color:#111827 !important;"><?= (int)($leaveStats['approved'] ?? 0) ?></strong></span>
            </div>
        </div>

        <div class="fnx-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:800; color:#4B5563 !important; text-transform:uppercase; letter-spacing:0.05em;">WFH Requests</div>
                    <div style="font-size:32px; font-weight:800; color:#7C3AED !important; margin-top:4px;">
                        <?= (int)($wfhStats['pending'] ?? 0) ?>
                    </div>
                </div>
                <div style="width:44px; height:44px; border-radius:12px; background:rgba(124,58,237,0.1); color:#7C3AED; display:flex; align-items:center; justify-content:center; font-size:20px;">
                    💻
                </div>
            </div>
            <div style="margin-top:14px; font-size:12.5px; color:#4B5563 !important;">
                <span>Active WFH approvals: <strong style="color:#111827 !important;"><?= (int)($wfhStats['approved'] ?? 0) ?></strong></span>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Content Section -->
    <div class="fnx-main-columns" style="display:grid; grid-template-columns: 2fr 1fr; gap:24px;">
        <!-- Left Column: Tasks Overview & Actions -->
        <div>
            <div class="fnx-card" style="margin-bottom:24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                    <div>
                        <h2 style="margin:0; font-size:18px; font-weight:800; color:#111827 !important;">Operational Tasks</h2>
                        <p style="margin:2px 0 0; font-size:13px; color:#4B5563 !important;">Recent assignments, multi-assignees, and PDF documentation status</p>
                    </div>
                    <a href="/futurenxt/tasks" class="fnx-btn fnx-btn-outline" style="padding:6px 12px; font-size:12.5px;">View All Tasks →</a>
                </div>

                <?php if (empty($recentTasks)): ?>
                    <div style="text-align:center; padding:36px 20px; color:var(--fnx-muted); background:var(--fnx-bg); border-radius:12px; border:1px dashed var(--fnx-border);">
                        <span style="font-size:32px;">📋</span>
                        <p style="margin:8px 0 0; font-size:14px;">No active tasks found. Start by assigning a task!</p>
                    </div>
                <?php else: ?>
                    <div class="fnx-table-wrap">
                        <table class="fnx-table">
                            <thead>
                                <tr>
                                    <th>Task Code &amp; Title</th>
                                    <th>Assignees</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Documentation</th>
                                    <th style="text-align:right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTasks as $t): 
                                    $assignees = $t['assignees'] ?? [];
                                    $isAssignedToMe = false;
                                    foreach ($assignees as $asgn) {
                                        $aid = (int)($asgn['id'] ?? $asgn['employee_id'] ?? 0);
                                        if ($aid === (int)$me['id']) {
                                            $isAssignedToMe = true;
                                            break;
                                        }
                                    }
                                    $taskPayload = [
                                        'id'                      => (int)$t['id'],
                                        'task_code'               => $t['task_code'],
                                        'title'                   => $t['title'],
                                        'description'             => $t['description'] ?? '',
                                        'priority'                => $t['priority'] ?? 'medium',
                                        'status'                  => $t['status'] ?? 'in_progress',
                                        'due_date'                => !empty($t['due_date']) ? date('d M Y', strtotime($t['due_date'])) : '—',
                                        'created_at'              => !empty($t['created_at']) ? date('d M Y, h:i A', strtotime($t['created_at'])) : '—',
                                        'created_by_name'         => $t['created_by_name'] ?? 'Management',
                                        'approved_by_name'        => $t['approved_by_name'] ?? null,
                                        'approved_by_code'        => $t['approved_by_code'] ?? null,
                                        'approved_by_designation' => $t['approved_by_designation'] ?? (!empty($t['approved_by_role']) ? ucfirst($t['approved_by_role']) : null),
                                        'approved_at'             => !empty($t['approved_at']) ? Audit::formatIst($t['approved_at'], 'd M Y, h:i A') : null,
                                        'documentation_pdf'       => !empty($t['documentation_pdf']) ? $t['documentation_pdf'] : (!empty($t['doc_path']) ? $t['doc_path'] : null),
                                        'review_feedback'         => $t['review_feedback'] ?? null,
                                        'assignees'               => $assignees,
                                        'daily_updates'           => DailyUpdate::getByTask((int)$t['id'], $t['task_code']),
                                        'can_submit_pdf'          => ($isAssignedToMe || Auth::isManagerOrAbove()) && in_array($t['status'], ['in_progress', 'reopened', 'submitted'], true),
                                        'can_reassign'            => Auth::isManagerOrAbove() && in_array($t['status'], ['in_progress', 'reopened', 'submitted'], true),
                                    ];
                                    $taskJson = htmlspecialchars(json_encode($taskPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
                                    $docUrl = !empty($t['documentation_pdf']) ? $t['documentation_pdf'] : (!empty($t['doc_path']) ? $t['doc_path'] : null);
                                ?>
                                    <tr>
                                        <td>
                                            <a href="javascript:void(0)" data-task="<?= $taskJson ?>" onclick="openViewTaskModal(this)"
                                               style="font-weight:700; color:var(--fnx-navy); font-size:13.5px; text-decoration:none;"
                                               onmouseover="this.style.color='var(--fnx-orange)'" onmouseout="this.style.color='var(--fnx-navy)'"
                                               title="Click to view full scope &amp; point-by-point details">
                                                <?= htmlspecialchars($t['title']) ?>
                                            </a>
                                            <div style="font-size:11.5px; color:var(--fnx-muted); font-family:monospace; margin-top:2px;"><?= htmlspecialchars($t['task_code']) ?> · <?= ucfirst($t['priority'] ?? 'medium') ?> Priority</div>
                                        </td>
                                        <td>
                                            <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                                <?php foreach ($assignees as $a): ?>
                                                    <span class="fnx-badge fnx-badge-info" style="font-size:10.5px;" title="<?= htmlspecialchars($a['full_name']) ?>">
                                                        <?= htmlspecialchars($a['emp_code']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                                <?php if (empty($assignees)): ?>
                                                    <span class="fnx-badge">Unassigned</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td style="font-size:13px;">
                                            <?= !empty($t['due_date']) ? date('d M Y', strtotime($t['due_date'])) : '—' ?>
                                        </td>
                                        <td>
                                            <?php if ($t['status'] === 'approved'): ?>
                                                <span class="fnx-badge fnx-badge-success">Approved</span>
                                            <?php elseif ($t['status'] === 'submitted'): ?>
                                                <span class="fnx-badge fnx-badge-warning">Under Review</span>
                                            <?php elseif ($t['status'] === 'in_progress'): ?>
                                                <span class="fnx-badge fnx-badge-info">In Progress</span>
                                            <?php else: ?>
                                                <span class="fnx-badge"><?= ucfirst($t['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($docUrl)): ?>
                                                <a href="<?= htmlspecialchars($docUrl) ?>" target="_blank" class="fnx-badge fnx-badge-success" style="text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                                    📄 PDF Doc
                                                </a>
                                            <?php else: ?>
                                                <span style="font-size:12px; color:var(--fnx-muted);">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:right;">
                                            <button type="button" class="fnx-btn fnx-btn-outline" style="padding:4px 9px; font-size:11px; font-weight:700; color:#111827 !important; border-color:#D1D5DB; background:#FFFFFF;"
                                                    data-task="<?= $taskJson ?>" onclick="openViewTaskModal(this)">
                                                👁️ View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Approvals, Notices & Audit Trail -->
        <div>
            <!-- Company Notices & Holiday Announcements -->
            <div class="fnx-card" style="margin-bottom:24px; border-left:4px solid var(--fnx-orange);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:18px;">📢</span>
                        <h2 style="margin:0; font-size:16px; font-weight:800; color:#111827 !important;">Notice Board &amp; Holidays</h2>
                    </div>
                    <a href="/futurenxt/notices" style="font-size:12px; font-weight:700; color:var(--fnx-orange); text-decoration:none;">View All →</a>
                </div>

                <?php if (empty($recentNotices)): ?>
                    <p style="font-size:13px; color:var(--fnx-muted); margin:0;">No active notices posted.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <?php foreach ($recentNotices as $rn): 
                            $rnIcon = match($rn['category']) {
                                'holiday' => '🏖️',
                                'event'   => '🎉',
                                'urgent'  => '🚨',
                                default   => '📌'
                            };
                        ?>
                            <div style="padding:10px 12px; background:var(--fnx-bg); border-radius:10px; border:1px solid var(--fnx-border); font-size:12.5px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                                    <strong style="color:#111827;">
                                        <?= $rnIcon ?> <?= htmlspecialchars($rn['title']) ?>
                                    </strong>
                                    <?php if (!empty($rn['is_pinned'])): ?>
                                        <span class="fnx-badge fnx-badge-orange" style="font-size:10px; padding:2px 6px;">Pinned</span>
                                    <?php endif; ?>
                                </div>
                                <div style="color:#4B5563; font-size:12px; margin:4px 0 6px; line-height:1.4;">
                                    <?= htmlspecialchars(mb_substr($rn['content'], 0, 95)) ?><?= mb_strlen($rn['content']) > 95 ? '...' : '' ?>
                                </div>
                                <div style="font-size:11px; color:#6B7280; display:flex; justify-content:space-between;">
                                    <span>Date: <?= date('d M Y', strtotime($rn['notice_date'])) ?></span>
                                    <a href="/futurenxt/notices" style="color:var(--fnx-orange); font-weight:700; text-decoration:none;">Read full →</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pending Leave Requests -->
            <?php if (Auth::isAdminOrExec()): ?>
                <div class="fnx-card" style="margin-bottom:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                        <h2 style="margin:0; font-size:16px; font-weight:800; color:#111827 !important;">Pending Leaves</h2>
                        <a href="/futurenxt/leaves" style="font-size:12px; font-weight:700; color:var(--fnx-orange); text-decoration:none;">Review All →</a>
                    </div>
                    <?php if (empty($pendingLeaves)): ?>
                        <p style="font-size:13px; color:var(--fnx-muted); margin:0;">No leave approvals pending.</p>
                    <?php else: ?>
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <?php foreach ($pendingLeaves as $l): ?>
                                <div style="padding:10px 12px; background:var(--fnx-bg); border-radius:10px; border:1px solid var(--fnx-border); font-size:12.5px;">
                                    <div style="display:flex; justify-content:space-between; font-weight:700; color:var(--fnx-navy);">
                                        <span><?= htmlspecialchars($l['employee_name']) ?> (<?= htmlspecialchars($l['emp_code']) ?>)</span>
                                        <span class="fnx-badge fnx-badge-warning"><?= ucfirst($l['leave_type']) ?></span>
                                    </div>
                                    <div style="color:var(--fnx-muted); margin:3px 0;">
                                        📅 <?= date('d M', strtotime($l['start_date'])) ?> to <?= date('d M Y', strtotime($l['end_date'])) ?> (<?= $l['total_days'] ?>d)
                                    </div>
                                    <div style="margin-top:6px; display:flex; gap:6px;">
                                        <a href="/futurenxt/leaves?review=<?= $l['id'] ?>" class="fnx-btn fnx-btn-orange" style="padding:3px 8px; font-size:11px;">Review &amp; Conflicts</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Recent Corporate Audit Logs -->
            <div class="fnx-card">
                <h2 style="margin:0 0 14px; font-size:16px; font-weight:800; color:#111827 !important;">Activity &amp; Audit Trail</h2>
                <?php if (empty($recentAudits)): ?>
                    <p style="font-size:13px; color:var(--fnx-muted); margin:0;">No logged activity yet.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <?php foreach ($recentAudits as $log): ?>
                            <div style="font-size:12px; border-left:2px solid var(--fnx-orange); padding-left:10px;">
                                <div style="color:var(--fnx-navy); font-weight:600;"><?= htmlspecialchars($log['details'] ?? $log['action']) ?></div>
                                <div style="color:var(--fnx-muted); font-size:11px; margin-top:2px;">
                                    <?= htmlspecialchars($log['emp_code'] ?? 'SYSTEM') ?> · <?= Audit::formatIst($log['created_at']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL: VIEW TASK DETAILS (Full Points, Scope & Deliverables)
     ========================================================================= -->
<div id="viewTaskModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:700px; width:95%;">
        <div class="fnx-modal-header" style="align-items:flex-start;">
            <div style="flex:1; padding-right:16px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
                    <span id="vtCode" class="fnx-badge fnx-badge-orange" style="font-family:'JetBrains Mono',monospace; font-size:12px;"></span>
                    <span id="vtPriority" class="fnx-badge"></span>
                    <span id="vtStatus" class="fnx-badge"></span>
                </div>
                <h3 id="vtTitle" style="margin:0; font-size:20px; font-weight:800; color:#111827; line-height:1.35;"></h3>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('viewTaskModal')">&times;</button>
        </div>

        <div class="fnx-modal-body" style="display:flex; flex-direction:column; gap:16px;">
            <!-- Meta Grid -->
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px; background:#FFFFFF; padding:14px; border:1px solid var(--fnx-border); border-radius:10px;">
                <div>
                    <div style="font-size:11.5px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.04em;">Due Date</div>
                    <div id="vtDueDate" style="font-size:14px; font-weight:700; color:#111827; margin-top:2px;"></div>
                </div>
                <div>
                    <div style="font-size:11.5px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.04em;">Assigned By</div>
                    <div id="vtCreatedBy" style="font-size:13.5px; font-weight:600; color:#111827; margin-top:2px;"></div>
                </div>
                <div id="vtApprovedByWrap" style="display:none;">
                    <div style="font-size:11.5px; font-weight:800; color:#065F46; text-transform:uppercase; letter-spacing:0.04em;">Approved By</div>
                    <div id="vtApprovedBy" style="font-size:13px; font-weight:800; color:#065F46; margin-top:2px; line-height:1.35;"></div>
                </div>
                <div>
                    <div style="font-size:11.5px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.04em;">PDF Documentation</div>
                    <div id="vtDocStatus" style="font-size:13px; margin-top:2px;"></div>
                </div>
            </div>

            <!-- Assigned Personnel -->
            <div>
                <label class="fnx-form-label" style="margin-bottom:6px;">Assigned Personnel:</label>
                <div id="vtAssigneesList" style="display:flex; flex-wrap:wrap; gap:8px;"></div>
            </div>

            <!-- Full Requirements / Scope Points -->
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label class="fnx-form-label" style="margin:0;">Task Scope, Requirements &amp; Points *</label>
                    <span style="font-size:11.5px; color:#6B7280;">Full Deliverables</span>
                </div>
                <div id="vtDescription" style="white-space:pre-wrap; word-break:break-word; font-size:13.5px; line-height:1.65; color:#111827; background:#FFF9F4; border:1.5px solid var(--fnx-border); border-radius:10px; padding:16px 18px; max-height:360px; overflow-y:auto; font-family:inherit;">
                </div>
            </div>

            <!-- Review Feedback Box -->
            <div id="vtFeedbackBox" style="display:none; padding:12px 14px; background:#FEF3C7; border:1px solid #FDE68A; border-radius:10px;">
                <strong style="font-size:12.5px; color:#92400E; display:block; margin-bottom:2px;">Management Review Feedback:</strong>
                <p id="vtFeedbackText" style="margin:0; font-size:13px; color:#78350F; line-height:1.5;"></p>
            </div>

            <!-- Task Progress & Daily Work Updates -->
            <div style="border-top:1.5px solid var(--fnx-border); padding-top:14px; margin-top:4px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:16px;">⏱️</span>
                        <label class="fnx-form-label" style="margin:0; font-size:13.5px; font-weight:800; color:var(--fnx-navy);">
                            Task Progress &amp; Daily Work Updates
                        </label>
                    </div>
                    <span id="vtUpdatesCount" class="fnx-badge fnx-badge-orange" style="font-size:11px;">0 updates logged</span>
                </div>

                <div id="vtUpdatesContainer" style="max-height:280px; overflow-y:auto; display:flex; flex-direction:column; gap:10px; padding-right:2px;">
                    <!-- Dynamically injected -->
                </div>
            </div>
        </div>

        <div class="fnx-modal-footer" style="justify-content:flex-end; gap:10px;">
            <a href="/futurenxt/tasks" class="fnx-btn fnx-btn-orange">Open in Task Hub →</a>
            <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('viewTaskModal')">Close Window</button>
        </div>
    </div>
</div>

<script>
function openViewTaskModal(elem) {
    const raw = elem.getAttribute('data-task');
    if (!raw) return;
    try {
        const t = JSON.parse(raw);
        document.getElementById('vtCode').innerText = t.task_code || 'TSK';
        document.getElementById('vtTitle').innerText = t.title || 'Untitled Task';
        
        // Priority
        const pEl = document.getElementById('vtPriority');
        pEl.innerText = (t.priority || 'medium').toUpperCase();
        pEl.className = 'fnx-badge ' + (t.priority === 'urgent' ? 'fnx-badge-danger' : (t.priority === 'high' ? 'fnx-badge-warning' : ''));
        
        // Status
        const sEl = document.getElementById('vtStatus');
        const st = (t.status || 'in_progress');
        sEl.innerText = st === 'in_progress' ? 'In Progress' : (st === 'submitted' ? 'Under Review' : (st === 'approved' ? 'Approved' : (st === 'reopened' ? 'Revision Needed' : st)));
        sEl.className = 'fnx-badge ' + (st === 'approved' ? 'fnx-badge-success' : (st === 'submitted' ? 'fnx-badge-warning' : (st === 'reopened' ? 'fnx-badge-danger' : 'fnx-badge-info')));
        
        document.getElementById('vtDueDate').innerText = t.due_date || '—';
        document.getElementById('vtCreatedBy').innerText = (t.created_by_name || 'Management') + ' · ' + (t.created_at || '');

        // Approved By Information
        const appWrap = document.getElementById('vtApprovedByWrap');
        const appEl = document.getElementById('vtApprovedBy');
        if (appWrap && appEl) {
            if (st === 'approved') {
                appWrap.style.display = 'block';
                let txt = t.approved_by_name || 'Management';
                if (t.approved_by_code) txt += ' (' + t.approved_by_code + ')';
                if (t.approved_by_designation) txt += ' · ' + t.approved_by_designation;
                if (t.approved_at) txt += ' on ' + t.approved_at;
                appEl.innerText = txt;
            } else {
                appWrap.style.display = 'none';
                appEl.innerText = '';
            }
        }
        
        // PDF Documentation
        const docEl = document.getElementById('vtDocStatus');
        if (t.documentation_pdf) {
            docEl.innerHTML = '<a href="' + t.documentation_pdf + '" target="_blank" class="fnx-badge fnx-badge-success" style="text-decoration:none;">📄 View PDF Document ↗</a>';
        } else {
            docEl.innerHTML = '<span style="color:#6B7280; font-size:12.5px;">Not uploaded yet</span>';
        }

        // Assignees
        const asgBox = document.getElementById('vtAssigneesList');
        asgBox.innerHTML = '';
        if (t.assignees && t.assignees.length > 0) {
            t.assignees.forEach(function(a) {
                const sp = document.createElement('span');
                sp.className = 'fnx-badge fnx-badge-info';
                sp.style.fontSize = '12px';
                sp.innerText = a.full_name + ' (' + a.emp_code + ')';
                asgBox.appendChild(sp);
            });
        } else {
            asgBox.innerHTML = '<span class="fnx-badge">Unassigned</span>';
        }

        // Points & Description (preserve line breaks, bullets, numbers)
        document.getElementById('vtDescription').innerText = t.description || 'No description provided.';

        // Feedback
        const fbBox = document.getElementById('vtFeedbackBox');
        if (t.review_feedback && t.review_feedback.trim() !== '') {
            fbBox.style.display = 'block';
            document.getElementById('vtFeedbackText').innerText = t.review_feedback;
        } else {
            fbBox.style.display = 'none';
        }

        // Daily Work Updates Timeline
        const upCountEl = document.getElementById('vtUpdatesCount');
        const upBox = document.getElementById('vtUpdatesContainer');
        upBox.innerHTML = '';
        
        const updates = t.daily_updates || [];
        if (updates.length > 0) {
            let totalHours = 0;
            updates.forEach(u => {
                if (u.hours_spent) totalHours += parseFloat(u.hours_spent);
            });
            upCountEl.innerText = updates.length + (updates.length === 1 ? ' update' : ' updates') + (totalHours > 0 ? ' · ' + totalHours.toFixed(1) + ' hrs total' : '');
            
            updates.forEach(function(u) {
                const card = document.createElement('div');
                card.style.cssText = 'background:#FFFFFF; border:1px solid var(--fnx-border); border-left:4px solid var(--fnx-orange); border-radius:8px; padding:12px 14px;';
                
                let html = '<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; flex-wrap:wrap; gap:6px;">';
                html += '<div style="display:flex; align-items:center; gap:8px;">';
                html += '<strong style="font-size:13px; color:var(--fnx-navy);">' + (u.employee_name || 'Assignee') + '</strong>';
                if (u.emp_code) html += '<span class="fnx-badge fnx-badge-info" style="font-size:10.5px;">' + u.emp_code + '</span>';
                if (u.hours_spent) {
                    html += '<span class="fnx-badge fnx-badge-orange" style="font-size:10.5px;">⏱️ ' + parseFloat(u.hours_spent).toFixed(1) + ' hrs</span>';
                }
                html += '</div>';
                html += '<span style="font-size:11.5px; font-weight:700; color:#6B7280;">📅 ' + (u.formatted_date || u.update_date) + (u.formatted_ist_time ? ' · ' + u.formatted_ist_time : '') + '</span>';
                html += '</div>';

                // Work Accomplished
                html += '<div style="white-space:pre-wrap; word-break:break-word; font-size:12.5px; line-height:1.6; color:#1F2937; background:#FFF9F4; padding:9px 12px; border-radius:6px; border:1px solid #F3DECB;">' + (u.work_done || 'No details provided.') + '</div>';

                // Blockers
                if (u.blockers && u.blockers.trim() !== '') {
                    html += '<div style="margin-top:6px; font-size:12px; color:#DC2626; background:#FEF2F2; padding:6px 10px; border-radius:6px; border:1px solid #FCA5A5;"><strong>⚠️ Blocker:</strong> ' + u.blockers + '</div>';
                }

                // Plan tomorrow
                if (u.plan_tomorrow && u.plan_tomorrow.trim() !== '') {
                    html += '<div style="margin-top:6px; font-size:12px; color:#4B5563;"><strong>🎯 Next Steps:</strong> ' + u.plan_tomorrow + '</div>';
                }

                card.innerHTML = html;
                upBox.appendChild(card);
            });
        } else {
            upCountEl.innerText = '0 updates logged';
            upBox.innerHTML = '<div style="padding:16px; text-align:center; background:#FFF9F4; border:1px dashed var(--fnx-border); border-radius:8px; color:#6B7280; font-size:12.5px;">No daily work updates logged for this task yet. Daily progress updates logged by assignees will appear here automatically.</div>';
        }

        fnxOpenModal('viewTaskModal');
    } catch(err) {
        console.error(err);
    }
}
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>

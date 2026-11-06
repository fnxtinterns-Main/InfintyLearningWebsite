<?php
declare(strict_types=1);

use App\FutureNxt\Auth;
use App\FutureNxt\Employee;
use App\FutureNxt\Task;
use App\Helper;

Auth::requireLogin();
$me = Auth::user();
$pageTitle = 'Task Operations & Management';
$activePage = 'tasks';

$statusFilter = $_GET['status'] ?? '';
$myTasksOnly = isset($_GET['my']) && $_GET['my'] === '1';

$filters = [];
if (!empty($statusFilter)) {
    $filters['status'] = $statusFilter;
}
if ($myTasksOnly || !Auth::isManagerOrAbove()) {
    $filters['employee_id'] = (int)$me['id'];
}

$tasks = Task::list($filters);
$allEmployees = Employee::list(['status' => 'active']);

$error = $_GET['error'] ?? $_SESSION['fnx_error'] ?? null;
$success = $_GET['success'] ?? $_SESSION['fnx_success'] ?? null;
unset($_SESSION['fnx_error'], $_SESSION['fnx_success']);

require __DIR__ . '/../layouts/header.php';
?>

<div class="fnx-container">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="margin:0 0 4px; font-size:24px; font-weight:800; color:var(--fnx-navy);">Task Management</h1>
            <p style="margin:0; font-size:13.5px; color:var(--fnx-muted);">
                Multi-assignee tasks, strict 3-task capacity limits, task reassignment, and mandatory PDF documentation.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <?php if (Auth::isManagerOrAbove()): ?>
                <button type="button" class="fnx-btn fnx-btn-orange" onclick="fnxOpenModal('createTaskModal')">
                    <span>+</span> Create &amp; Assign Task
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
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="/futurenxt/tasks" class="fnx-btn <?= empty($statusFilter) && !$myTasksOnly ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 12px; font-size:12.5px;">All Tasks</a>
                <a href="/futurenxt/tasks?my=1" class="fnx-btn <?= $myTasksOnly ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 12px; font-size:12.5px;">My Assignments</a>
                <a href="/futurenxt/tasks?status=in_progress" class="fnx-btn <?= $statusFilter === 'in_progress' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 12px; font-size:12.5px;">In Progress</a>
                <a href="/futurenxt/tasks?status=submitted" class="fnx-btn <?= $statusFilter === 'submitted' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 12px; font-size:12.5px;">Pending Approval</a>
                <a href="/futurenxt/tasks?status=approved" class="fnx-btn <?= $statusFilter === 'approved' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 12px; font-size:12.5px;">Approved</a>
            </div>
            <div style="font-size:12.5px; color:var(--fnx-muted);">
                Showing <strong><?= count($tasks) ?></strong> task(s)
            </div>
        </div>
    </div>

    <!-- Task List Table -->
    <div class="fnx-card">
        <div class="fnx-table-wrap">
            <table class="fnx-table">
                <thead>
                    <tr>
                        <th>Task Information</th>
                        <th>Assignees (Capacity)</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>PDF Documentation</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:36px; color:var(--fnx-muted);">
                                No tasks found matching this criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tasks as $t): 
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
                                'id'                => (int)$t['id'],
                                'task_code'         => $t['task_code'],
                                'title'             => $t['title'],
                                'description'       => $t['description'] ?? '',
                                'priority'          => $t['priority'] ?? 'medium',
                                'status'            => $t['status'] ?? 'in_progress',
                                'due_date'          => !empty($t['due_date']) ? date('d M Y', strtotime($t['due_date'])) : '—',
                                'created_at'        => !empty($t['created_at']) ? date('d M Y, h:i A', strtotime($t['created_at'])) : '—',
                                'created_by_name'   => $t['created_by_name'] ?? 'Management',
                                'documentation_pdf' => !empty($t['documentation_pdf']) ? $t['documentation_pdf'] : (!empty($t['doc_path']) ? $t['doc_path'] : null),
                                'review_feedback'   => $t['review_feedback'] ?? null,
                                'assignees'         => $assignees,
                                'can_submit_pdf'    => ($isAssignedToMe || Auth::isManagerOrAbove()) && in_array($t['status'], ['in_progress', 'reopened', 'submitted'], true),
                                'can_reassign'      => Auth::isManagerOrAbove() && in_array($t['status'], ['in_progress', 'reopened', 'submitted'], true),
                            ];
                            $taskJson = htmlspecialchars(json_encode($taskPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
                            $docUrl = !empty($t['documentation_pdf']) ? $t['documentation_pdf'] : (!empty($t['doc_path']) ? $t['doc_path'] : null);
                        ?>
                            <tr>
                                <td style="max-width:280px;">
                                    <a href="javascript:void(0)" data-task="<?= $taskJson ?>" onclick="openViewTaskModal(this)"
                                       style="font-weight:700; color:var(--fnx-navy); font-size:14px; text-decoration:none; display:inline-block; transition:color 0.15s;"
                                       onmouseover="this.style.color='var(--fnx-orange)'" onmouseout="this.style.color='var(--fnx-navy)'"
                                       title="Click to view full scope &amp; point-by-point details">
                                        <?= htmlspecialchars($t['title']) ?>
                                    </a>
                                    <div style="font-size:11.5px; color:var(--fnx-muted); font-family:monospace; margin-top:2px;">
                                        <?= htmlspecialchars($t['task_code']) ?>
                                    </div>
                                    <?php if (!empty($t['description'])): ?>
                                        <div style="font-size:12px; color:var(--fnx-muted); margin-top:4px; line-height:1.4;">
                                            <?= htmlspecialchars(mb_substr($t['description'], 0, 100)) ?><?= mb_strlen($t['description']) > 100 ? '...' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex; flex-direction:column; gap:4px;">
                                        <?php foreach ($assignees as $a): ?>
                                            <span class="fnx-badge fnx-badge-info" style="font-size:11px; display:inline-flex; align-items:center; gap:6px;">
                                                <?= htmlspecialchars($a['full_name']) ?> (<?= htmlspecialchars($a['emp_code']) ?>)
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (empty($assignees)): ?>
                                            <span class="fnx-badge">Unassigned</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($t['priority'] === 'urgent'): ?>
                                        <span class="fnx-badge fnx-badge-danger">Urgent</span>
                                    <?php elseif ($t['priority'] === 'high'): ?>
                                        <span class="fnx-badge fnx-badge-warning">High</span>
                                    <?php else: ?>
                                        <span class="fnx-badge"><?= ucfirst($t['priority']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:13px;">
                                    <?= !empty($t['due_date']) ? date('d M Y', strtotime($t['due_date'])) : '—' ?>
                                </td>
                                <td>
                                    <?php if ($t['status'] === 'approved'): ?>
                                        <span class="fnx-badge fnx-badge-success">Approved ✓</span>
                                    <?php elseif ($t['status'] === 'submitted'): ?>
                                        <span class="fnx-badge fnx-badge-warning">Under Review</span>
                                    <?php elseif ($t['status'] === 'in_progress'): ?>
                                        <span class="fnx-badge fnx-badge-info">In Progress</span>
                                    <?php elseif ($t['status'] === 'reopened'): ?>
                                        <span class="fnx-badge fnx-badge-danger">Revision Needed</span>
                                    <?php else: ?>
                                        <span class="fnx-badge"><?= ucfirst($t['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($docUrl)): ?>
                                        <a href="<?= htmlspecialchars($docUrl) ?>" target="_blank" class="fnx-badge fnx-badge-success" style="text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                            📄 View PDF ↗
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size:12px; color:var(--fnx-muted);">Not uploaded</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex; gap:6px; flex-wrap:wrap; justify-content:flex-end;">
                                        <!-- View Task Scope & Points Button (Always Visible to Everyone) -->
                                        <button type="button" class="fnx-btn fnx-btn-outline" style="padding:4px 10px; font-size:11.5px; font-weight:700; color:#111827 !important; border-color:#D1D5DB; background:#FFFFFF;"
                                                data-task="<?= $taskJson ?>"
                                                onclick="openViewTaskModal(this)">
                                            👁️ View Task
                                        </button>

                                        <!-- Employee action: Upload Documentation PDF -->
                                        <?php if (($isAssignedToMe || Auth::isManagerOrAbove()) && in_array($t['status'], ['in_progress', 'reopened', 'submitted'], true)): ?>
                                            <button type="button" class="fnx-btn fnx-btn-outline" style="padding:4px 8px; font-size:11.5px;"
                                                    onclick="openDocModal(<?= $t['id'] ?>, '<?= htmlspecialchars(addslashes($t['title'])) ?>')">
                                                📄 Submit PDF
                                            </button>
                                        <?php endif; ?>

                                        <!-- Admin / Exec action: Pass / Reassign Task -->
                                        <?php if (Auth::isManagerOrAbove() && in_array($t['status'], ['in_progress', 'reopened', 'submitted'], true)): ?>
                                            <button type="button" class="fnx-btn fnx-btn-outline" style="padding:4px 8px; font-size:11.5px; color:#3B82F6; border-color:#93C5FD;"
                                                    onclick="openReassignModal(<?= $t['id'] ?>, '<?= htmlspecialchars(addslashes($t['title'])) ?>')">
                                                🔁 Reassign
                                            </button>
                                        <?php endif; ?>

                                        <!-- Admin / Exec action: Review & Approve -->
                                        <?php if (Auth::isAdminOrExec() && $t['status'] === 'submitted'): ?>
                                            <button type="button" class="fnx-btn fnx-btn-orange" style="padding:4px 8px; font-size:11.5px;"
                                                    onclick="openReviewModal(<?= $t['id'] ?>, '<?= htmlspecialchars(addslashes($t['title'])) ?>', '<?= htmlspecialchars($t['documentation_pdf'] ?? '') ?>')">
                                                Review / Approve
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
     MODAL: CREATE TASK (Multi-Assignee, 3-task limit check)
     ========================================================================= -->
<?php if (Auth::isManagerOrAbove()): ?>
<div id="createTaskModal" class="fnx-modal-backdrop" style="display:<?= (isset($_GET['action']) && $_GET['action'] === 'new') ? 'flex' : 'none' ?>;">
    <div class="fnx-modal-dialog" style="max-width:640px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:#111827;">Create &amp; Assign Task</h3>
                <p style="margin:2px 0 0; font-size:12px; color:#4B5563;">Assign to one or multiple team members (Max 3 active tasks allowed per individual).</p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('createTaskModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=create_task">
            <input type="hidden" name="redirect_url" value="/futurenxt/tasks">
            <div class="fnx-modal-body">
                <div style="margin-bottom:12px;">
                    <label class="fnx-form-label">Task Title *</label>
                    <input type="text" name="title" class="fnx-input" placeholder="e.g. Build Payment Gateway Integration" required>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="fnx-form-label">Task Description &amp; Deliverables *</label>
                    <textarea name="description" class="fnx-textarea" rows="2" placeholder="Provide clear requirements and deliverables..." required style="resize:vertical;"></textarea>
                </div>

                <div class="fnx-grid-2" style="margin-bottom:12px; row-gap:10px;">
                    <div>
                        <label class="fnx-form-label">Priority</label>
                        <select name="priority" class="fnx-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="fnx-form-label">Due Date *</label>
                        <input type="date" name="due_date" class="fnx-input" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                </div>

                <div style="margin-bottom:8px;">
                    <label class="fnx-form-label">Assign To (Select One or More) *</label>
                    <div style="max-height:150px; overflow-y:auto; border:1.5px solid var(--fnx-border); border-radius:10px; padding:8px 10px; background:#FFF9F4;">
                        <?php foreach ($allEmployees as $emp): 
                            $activeCount = (int)($emp['active_tasks_count'] ?? 0);
                            $isMaxed = $activeCount >= 3;
                        ?>
                            <label style="display:flex; justify-content:space-between; align-items:center; padding:6px 8px; border-radius:6px; cursor:<?= $isMaxed ? 'not-allowed' : 'pointer' ?>; opacity:<?= $isMaxed ? '0.5' : '1' ?>; user-select:none;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <input type="checkbox" name="assignees[]" value="<?= $emp['id'] ?>" <?= $isMaxed ? 'disabled' : '' ?> style="accent-color:var(--fnx-orange); width:16px; height:16px;">
                                    <span style="font-weight:700; font-size:13px; color:#111827;">
                                        <?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['emp_code']) ?>)
                                    </span>
                                </div>
                                <span class="fnx-badge <?= $isMaxed ? 'fnx-badge-danger' : 'fnx-badge-info' ?>" style="font-size:11px;">
                                    <?= $activeCount ?> / 3 Active <?= $isMaxed ? '(Full)' : '' ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small style="color:#6B7280; font-size:11px; margin-top:4px; display:block;">
                        * Personnel with 3 active tasks are disabled as per corporate capacity limits.
                    </small>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('createTaskModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange" style="padding:10px 22px;">✓ Assign Task</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- =========================================================================
     MODAL: SUBMIT TASK DOCUMENTATION (MANDATORY PDF)
     ========================================================================= -->
<div id="docModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:520px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:17px; font-weight:800; color:var(--fnx-navy);">Submit Task Documentation</h3>
                <p id="docTaskTitle" style="margin:2px 0 0; font-size:12.5px; color:var(--fnx-muted);"></p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('docModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=submit_task_docs" enctype="multipart/form-data" onsubmit="const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerHTML = '⏳ Uploading PDF...';">
            <input type="hidden" id="docTaskId" name="task_id" value="">
            <input type="hidden" name="redirect_url" value="/futurenxt/tasks">
            <div class="fnx-modal-body">
                <div style="padding:12px; background:rgba(255,101,0,0.08); border:1px solid rgba(255,101,0,0.2); border-radius:10px; margin-bottom:16px; font-size:12.5px; color:var(--fnx-navy);">
                    📌 <strong>Requirement:</strong> Documentation must be uploaded in <strong>PDF format (.pdf)</strong> before manager or executive approval.
                </div>

                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Upload Documentation PDF *</label>
                    <input type="file" name="documentation_pdf" class="fnx-input" accept=".pdf" required>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Submission Remarks / Notes</label>
                    <textarea name="submission_notes" class="fnx-textarea" rows="3" placeholder="Summarize completed deliverables..."></textarea>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('docModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">Upload PDF &amp; Submit for Approval →</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: REASSIGN TASK (Pass task to another employee)
     ========================================================================= -->
<div id="reassignModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:520px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:17px; font-weight:800; color:var(--fnx-navy);">Pass / Reassign Task</h3>
                <p id="reassignTaskTitle" style="margin:2px 0 0; font-size:12.5px; color:var(--fnx-muted);"></p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('reassignModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=reassign_task">
            <input type="hidden" id="reassignTaskId" name="task_id" value="">
            <div class="fnx-modal-body">
                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Assign To New Personnel *</label>
                    <select name="new_employee_id" class="fnx-select" required>
                        <option value="">Select Personnel (Check 3-task limit)</option>
                        <?php foreach ($allEmployees as $emp): 
                            $activeCount = (int)($emp['active_tasks_count'] ?? 0);
                            $isMaxed = $activeCount >= 3;
                        ?>
                            <option value="<?= $emp['id'] ?>" <?= $isMaxed ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['emp_code']) ?>) — <?= $activeCount ?>/3 Tasks <?= $isMaxed ? '[Capacity Full]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Reason for Handover / Reassignment *</label>
                    <textarea name="reassign_reason" class="fnx-textarea" rows="2" placeholder="e.g. Employee on medical leave, workload rebalancing..." required></textarea>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('reassignModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">Confirm Reassignment →</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: REVIEW / APPROVE TASK
     ========================================================================= -->
<div id="reviewModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:520px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:17px; font-weight:800; color:var(--fnx-navy);">Review &amp; Approve Task</h3>
                <p id="reviewTaskTitle" style="margin:2px 0 0; font-size:12.5px; color:var(--fnx-muted);"></p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('reviewModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=review_task">
            <input type="hidden" id="reviewTaskId" name="task_id" value="">
            <div class="fnx-modal-body">
                <div style="margin-bottom:16px; padding:12px; background:var(--fnx-bg); border-radius:10px; border:1px solid var(--fnx-border);">
                    <div style="font-size:13px; font-weight:700; color:var(--fnx-navy); margin-bottom:4px;">Submitted Documentation:</div>
                    <a id="reviewDocLink" href="#" target="_blank" class="fnx-btn fnx-btn-outline" style="font-size:12px;">
                        📄 Inspect Uploaded PDF ↗
                    </a>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Decision *</label>
                    <select name="decision" class="fnx-select" required>
                        <option value="approve">Approve Task as Completed</option>
                        <option value="reopen">Request Revision / Reopen</option>
                    </select>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="fnx-form-label">Reviewer Feedback</label>
                    <textarea name="remarks" class="fnx-textarea" rows="2" placeholder="Feedback or next steps for the assignee(s)..."></textarea>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('reviewModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">Submit Review</button>
            </div>
        </form>
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
        </div>

        <div class="fnx-modal-footer" style="justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div id="vtActionButtons" style="display:flex; gap:8px; flex-wrap:wrap;"></div>
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

        // Action buttons
        const actBox = document.getElementById('vtActionButtons');
        actBox.innerHTML = '';
        if (t.can_submit_pdf) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'fnx-btn fnx-btn-orange';
            b.style.fontSize = '12px';
            b.innerHTML = '📄 Submit PDF';
            b.onclick = function() {
                fnxCloseModal('viewTaskModal');
                openDocModal(t.id, t.title);
            };
            actBox.appendChild(b);
        }
        if (t.can_reassign) {
            const b2 = document.createElement('button');
            b2.type = 'button';
            b2.className = 'fnx-btn fnx-btn-outline';
            b2.style.fontSize = '12px';
            b2.innerHTML = '🔁 Reassign';
            b2.onclick = function() {
                fnxCloseModal('viewTaskModal');
                openReassignModal(t.id, t.title);
            };
            actBox.appendChild(b2);
        }

        fnxOpenModal('viewTaskModal');
    } catch(err) {
        console.error(err);
    }
}

function openDocModal(taskId, title) {
    document.getElementById('docTaskId').value = taskId;
    document.getElementById('docTaskTitle').innerText = title;
    fnxOpenModal('docModal');
}

function openReassignModal(taskId, title) {
    document.getElementById('reassignTaskId').value = taskId;
    document.getElementById('reassignTaskTitle').innerText = title;
    fnxOpenModal('reassignModal');
}

function openReviewModal(taskId, title, docPdf) {
    document.getElementById('reviewTaskId').value = taskId;
    document.getElementById('reviewTaskTitle').innerText = title;
    const link = document.getElementById('reviewDocLink');
    if (docPdf) {
        link.href = docPdf;
        link.style.display = 'inline-block';
    } else {
        link.style.display = 'none';
    }
    fnxOpenModal('reviewModal');
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<?php
use App\Auth;
use App\School;
use App\Helper;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);
if (!$school) { Auth::logout(); Helper::redirect('/login'); }

$status = $_GET['status'] ?? 'pending';
$students = School::users($schoolId, 'student', $status ?: null);
$activeTeachers = School::users($schoolId, 'teacher', 'active');

$pageTitle = 'Students — ' . $school['name'];
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>
        <div class="dashboard-header">
            <div class="dashboard-header-text">
                <span class="kicker">👩‍🎓 STUDENTS</span>
                <h1>Manage your students</h1>
                <p class="muted">Supervise student registrations, academic identifiers, mentors, and account status under <strong><?= Helper::e($school['name']) ?></strong>.</p>
            </div>
            <span class="pill"><?= count($students) ?> shown</span>
        </div>

        <div class="filters">
            <a href="?status=pending"   class="<?= $status === 'pending'   ? 'active' : '' ?>">Pending</a>
            <a href="?status=active"    class="<?= $status === 'active'    ? 'active' : '' ?>">Approved</a>
            <a href="?status=rejected"  class="<?= $status === 'rejected'  ? 'active' : '' ?>">Rejected</a>
            <a href="?status=suspended" class="<?= $status === 'suspended' ? 'active' : '' ?>">Suspended</a>
            <a href="?status="          class="<?= $status === ''          ? 'active' : '' ?>">All</a>
        </div>

        <?php if (!$students): ?>
            <div class="empty-state">
                <div class="empty-state-icon">👩‍🎓</div>
                <h3>No students in this list</h3>
                <p>When students register under your school, they'll appear here for approval and administration.</p>
            </div>
        <?php else: ?>
            <!-- Mentor Assignment Action Bar (For batches of 15-20 students) -->
            <form id="bulkStudentForm" method="post" action="/school/user-action-handler.php">
                <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                <input type="hidden" name="action" value="assign_mentor">

                <div class="card mb-3" style="padding:14px 20px; border-radius:12px; background:var(--surface-1); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:1.2rem;">👨‍🏫</span>
                        <div>
                            <strong style="font-size:13.5px;">Mentor Assignment</strong>
                            <div class="muted" style="font-size:12px;">Select 15–20 students via checkboxes below and assign a dedicated teacher mentor.</div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <select name="mentor_user_id" id="bulkMentorSelect" class="form-control" style="padding:6px 12px; border-radius:8px; border:1px solid var(--il-border); font-size:13px; max-width:240px;" required>
                            <option value="">— Select Teacher Mentor —</option>
                            <?php foreach ($activeTeachers as $t): ?>
                                <option value="<?= (int)$t['id'] ?>">
                                    <?= Helper::e($t['full_name'] ?: $t['email']) ?>
                                    <?php if (!empty($t['expert_field'])): ?> (<?= Helper::e($t['expert_field']) ?>)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm" onclick="return validateBulkSelection()">
                            Assign Mentor to Selected
                        </button>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th style="width:36px; text-align:center;">
                                    <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)" style="cursor:pointer;">
                                </th>
                                <th>#</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Reg. No</th>
                                <th>Roll No</th>
                                <th>Mentor</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $sIdx = 1; foreach ($students as $s): ?>
                            <tr>
                                <td style="text-align:center;">
                                    <input type="checkbox" name="student_ids[]" value="<?= (int)$s['id'] ?>" class="student-chk" style="cursor:pointer;">
                                </td>
                                <td><?= $sIdx++ ?></td>
                                <td>
                                    <div class="flex" style="gap:.6rem; align-items:center;">
                                        <?php if (!empty($s['avatar'])): ?>
                                            <img class="avatar" src="<?= Helper::e($s['avatar']) ?>" alt="">
                                        <?php else: ?>
                                            <span class="avatar avatar-initial" data-role="student">
                                                <?= Helper::e(mb_strtoupper(mb_substr($s['full_name'] ?: $s['email'], 0, 1))) ?>
                                            </span>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= Helper::e($s['full_name'] ?: '—') ?></strong><br>
                                            <span class="muted" style="font-size:.82rem"><?= Helper::e($s['email']) ?></span>
                                            <span class="muted" style="font-size:.75rem; margin-left:4px;">(ID: #<?= (int)$s['id'] ?>)</span>
                                        </div>
                                    </div>
                                </td>
                                <td><strong><?= Helper::e($s['current_class'] ?: '—') ?></strong></td>
                                <td>
                                    <?php if (!empty($s['registration_number'])): ?>
                                        <span class="pill" style="font-size:11px; font-weight:600; background:rgba(37,99,235,0.08); color:var(--il-primary);">
                                            <?= Helper::e($s['registration_number']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($s['roll_number'])): ?>
                                        <span style="font-weight:700;"><?= Helper::e($s['roll_number']) ?></span>
                                    <?php else: ?>
                                        <span class="muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($s['mentor_name'])): ?>
                                        <span class="badge" style="background:#f3e8ff; color:#6b21a8; font-size:11.5px; font-weight:600;">
                                            👨‍🏫 <?= Helper::e($s['mentor_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="muted" style="font-size:12px;">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= Helper::e($s['city'] ?: '—') ?><?= $s['state'] ? ', ' . Helper::e($s['state']) : '' ?></td>
                                <td>
                                    <span class="badge badge-<?= $s['status'] === 'active' ? 'success' : ($s['status'] === 'pending' ? 'primary' : ($s['status'] === 'suspended' ? 'muted' : 'danger')) ?>">
                                        <?= Helper::e($s['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                                <td>
                                    <div class="flex" style="gap:.3rem;flex-wrap:wrap">
                                        <?php if ($s['status'] === 'pending'): ?>
                                            <button type="submit" form="actionForm_<?= (int)$s['id'] ?>" class="btn btn-success btn-sm">Approve</button>
                                            <button type="submit" form="actionFormReject_<?= (int)$s['id'] ?>" class="btn btn-danger btn-sm">Reject</button>
                                        <?php endif; ?>

                                        <?php if ($s['status'] === 'suspended'): ?>
                                            <button type="submit" form="actionFormReactivate_<?= (int)$s['id'] ?>" class="btn btn-success btn-sm">Reactivate</button>
                                        <?php endif; ?>

                                        <?php if ($s['status'] === 'active'): ?>
                                            <a class="btn btn-ghost btn-sm" href="/student?id=<?= (int)$s['id'] ?>">View</a>
                                            <a class="btn btn-outline-primary btn-sm" href="/profile?user_id=<?= (int)$s['id'] ?>" title="Edit Student Profile">✏️ Edit</a>
                                            <a class="btn btn-secondary btn-sm" href="/school/report-card?student_id=<?= (int)$s['id'] ?>" target="_blank" title="Generate Academic Report Card">📄 Report Card</a>
                                            <button type="submit" form="actionFormSuspend_<?= (int)$s['id'] ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Suspend this student account?')">Suspend</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>

            <!-- Independent per-row action forms -->
            <?php foreach ($students as $s): ?>
                <?php if ($s['status'] === 'pending'): ?>
                    <form id="actionForm_<?= (int)$s['id'] ?>" method="post" action="/school/user-action-handler.php" style="display:none;">
                        <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                        <input type="hidden" name="user_id" value="<?= (int)$s['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                    </form>
                    <form id="actionFormReject_<?= (int)$s['id'] ?>" method="post" action="/school/user-action-handler.php" style="display:none;">
                        <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                        <input type="hidden" name="user_id" value="<?= (int)$s['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                    </form>
                <?php elseif ($s['status'] === 'suspended'): ?>
                    <form id="actionFormReactivate_<?= (int)$s['id'] ?>" method="post" action="/school/user-action-handler.php" style="display:none;">
                        <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                        <input type="hidden" name="user_id" value="<?= (int)$s['id'] ?>">
                        <input type="hidden" name="action" value="reactivate">
                    </form>
                <?php elseif ($s['status'] === 'active'): ?>
                    <form id="actionFormSuspend_<?= (int)$s['id'] ?>" method="post" action="/school/user-action-handler.php" style="display:none;">
                        <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                        <input type="hidden" name="user_id" value="<?= (int)$s['id'] ?>">
                        <input type="hidden" name="action" value="suspend">
                    </form>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleSelectAll(source) {
    document.querySelectorAll('.student-chk').forEach(c => c.checked = source.checked);
}
function validateBulkSelection() {
    const checked = document.querySelectorAll('.student-chk:checked');
    if (checked.length === 0) {
        alert('Please select at least one student using the checkboxes.');
        return false;
    }
    const mentor = document.getElementById('bulkMentorSelect').value;
    if (!mentor) {
        alert('Please select a teacher mentor from the dropdown.');
        return false;
    }
    return confirm('Assign the selected mentor to ' + checked.length + ' student(s)?');
}
</script>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
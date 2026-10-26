<?php
use App\Auth;
use App\School;
use App\Helper;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);
if (!$school) { Auth::logout(); Helper::redirect('/login'); }

$status = $_GET['status'] ?? 'pending';
$teachers = School::users($schoolId, 'teacher', $status ?: null);

$pageTitle = 'Teachers — ' . $school['name'];
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>
        <div class="dashboard-header">
            <div class="dashboard-header-text">
                <span class="kicker">👩‍🏫 TEACHERS</span>
                <h1>Manage your teachers</h1>
                <p class="muted">Approve teachers who registered under <strong><?= Helper::e($school['name']) ?></strong>.</p>
            </div>
            <span class="pill"><?= count($teachers) ?> shown</span>
        </div>

        <div class="filters">
            <a href="?status=pending"   class="<?= $status === 'pending'   ? 'active' : '' ?>">Pending</a>
            <a href="?status=active"    class="<?= $status === 'active'    ? 'active' : '' ?>">Approved</a>
            <a href="?status=rejected"  class="<?= $status === 'rejected'  ? 'active' : '' ?>">Rejected</a>
            <a href="?status=suspended" class="<?= $status === 'suspended' ? 'active' : '' ?>">Suspended</a>
            <a href="?status="          class="<?= $status === ''          ? 'active' : '' ?>">All</a>
        </div>

        <?php if (!$teachers): ?>
            <div class="empty-state">
                <div class="empty-state-icon">👩‍🏫</div>
                <h3>No teachers in this list</h3>
                <p>When teachers register under your school, they'll appear here for approval.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Teacher</th>
                            <th>Teacher ID</th>
                            <th>Reg. No</th>
                            <th>Class Teacher</th>
                            <th>Expertise</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($teachers as $t): ?>
                        <tr>
                            <td><?= (int)$t['id'] ?></td>
                            <td>
                                <div class="flex" style="gap:.6rem; align-items:center;">
                                    <?php if (!empty($t['avatar'])): ?>
                                        <img class="avatar" src="<?= Helper::e($t['avatar']) ?>" alt="">
                                    <?php else: ?>
                                        <span class="avatar avatar-initial" data-role="teacher">
                                            <?= Helper::e(mb_strtoupper(mb_substr($t['full_name'] ?: $t['email'], 0, 1))) ?>
                                        </span>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= Helper::e($t['full_name'] ?: '—') ?></strong><br>
                                        <span class="muted" style="font-size:.82rem"><?= Helper::e($t['email']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($t['teacher_code'])): ?>
                                    <span class="pill" style="font-weight:700; background:rgba(37,99,235,0.08); color:var(--il-primary);">
                                        <?= Helper::e($t['teacher_code']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= Helper::e($t['teacher_registration_number'] ?: '—') ?>
                            </td>
                            <td>
                                <?php if (!empty($t['class_teacher_grade'])): ?>
                                    <span class="badge badge-primary" style="font-size:11px;">
                                        ⭐ <?= Helper::e($t['class_teacher_grade']) ?><?= !empty($t['class_teacher_section']) ? ' - ' . Helper::e($t['class_teacher_section']) : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="muted" style="font-size:12px;">Not Assigned</span>
                                <?php endif; ?>
                                <?php if ($t['status'] === 'active'): ?>
                                    <br>
                                    <button type="button" class="btn btn-ghost btn-sm" style="font-size:11px; padding:2px 6px; margin-top:4px;"
                                            onclick="openClassTeacherModal(<?= (int)$t['id'] ?>, '<?= Helper::e($t['full_name'] ?: $t['email']) ?>', '<?= Helper::e($t['class_teacher_grade'] ?? '') ?>', '<?= Helper::e($t['class_teacher_section'] ?? '') ?>')">
                                        ✏️ <?= !empty($t['class_teacher_grade']) ? 'Edit' : 'Assign' ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td><?= Helper::e($t['expert_field'] ?: '—') ?></td>
                            <td><?= Helper::e($t['city'] ?: '—') ?><?= $t['state'] ? ', ' . Helper::e($t['state']) : '' ?></td>
                            <td>
                                <span class="badge badge-<?= $t['status'] === 'active' ? 'success' : ($t['status'] === 'pending' ? 'primary' : ($t['status'] === 'suspended' ? 'muted' : 'danger')) ?>">
                                    <?= Helper::e($t['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                            <td>
                                <div class="flex" style="gap:.3rem;flex-wrap:wrap">
                                    <?php if ($t['status'] !== 'active'): ?>
                                        <form method="post" action="/school/user-action-handler.php" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$t['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn-success btn-sm"><?= $t['status'] === 'suspended' ? 'Reactivate' : 'Approve' ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($t['status'] !== 'rejected' && $t['status'] !== 'suspended'): ?>
                                        <form method="post" action="/school/user-action-handler.php" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$t['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($t['status'] === 'active'): ?>
                                        <form method="post" action="/school/user-action-handler.php" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$t['id'] ?>">
                                            <input type="hidden" name="action" value="suspend">
                                            <button class="btn btn-ghost btn-sm" onclick="return confirm('Suspend this teacher account?')">Suspend</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================
     CLASS TEACHER ASSIGNMENT MODAL
     ============================================================ -->
<div id="classTeacherModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="max-width:440px; width:100%; border-radius:16px; padding:24px; box-shadow:0 20px 40px rgba(0,0,0,0.25);">
        <div class="flex between mb-2">
            <h3 style="margin:0;">Assign Class Teacher</h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeClassTeacherModal()">✕</button>
        </div>
        <p class="muted" style="font-size:13px; margin-bottom:16px;" id="modalTeacherNameDesc">
            Select the grade and section this teacher will supervise and record class attendance for.
        </p>

        <form method="post" action="/school/user-action-handler.php">
            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
            <input type="hidden" name="action" value="assign_class_teacher">
            <input type="hidden" name="user_id" id="modalTeacherUserId" value="">

            <div class="form-group mb-2">
                <label style="font-weight:600; font-size:13px; display:block; margin-bottom:4px;">Grade / Class *</label>
                <select name="class_teacher_grade" id="modalGradeSelect" class="form-control" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--il-border);">
                    <option value="">— Remove Assignment —</option>
                    <?php for ($g = 1; $g <= 12; $g++): ?>
                        <option value="Grade <?= $g ?>">Grade <?= $g ?></option>
                    <?php endfor; ?>
                    <option value="Kindergarten">Kindergarten</option>
                    <option value="Pre-Primary">Pre-Primary</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label style="font-weight:600; font-size:13px; display:block; margin-bottom:4px;">Section (optional)</label>
                <input type="text" name="class_teacher_section" id="modalSectionInput" placeholder="e.g. A, B, Rose, Daisy" maxlength="15"
                       class="form-control" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--il-border);">
            </div>

            <div class="flex" style="justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeClassTeacherModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Assignment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openClassTeacherModal(userId, name, currentGrade, currentSection) {
    document.getElementById('modalTeacherUserId').value = userId;
    document.getElementById('modalTeacherNameDesc').innerText = 'Assigning Class Teacher responsibilities for ' + name + '.';
    document.getElementById('modalGradeSelect').value = currentGrade || '';
    document.getElementById('modalSectionInput').value = currentSection || '';
    const modal = document.getElementById('classTeacherModal');
    modal.style.display = 'flex';
}
function closeClassTeacherModal() {
    document.getElementById('classTeacherModal').style.display = 'none';
}
</script>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
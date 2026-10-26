<?php
use App\Auth;
use App\School;
use App\Helper;
use App\Database;
use App\Session;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);
if (!$school) { Auth::logout(); Helper::redirect('/login'); }
School::ensureSchema();

$db = Database::connect();
$currentUser = Auth::user();

// Handle Create / Edit POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
        Session::flash('error', 'Invalid request session. Please try again.');
        Helper::redirect('/school/batches');
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $classLevel = trim($_POST['class_level'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $teacherId = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;
        $capacity = !empty($_POST['capacity']) ? (int)$_POST['capacity'] : 50;
        $desc = trim($_POST['description'] ?? '') ?: null;

        if ($name === '') {
            Session::flash('error', 'Class / Batch name is required.');
        } else {
            try {
                $stmt = $db->prepare("
                    INSERT INTO batches (uuid, school_id, name, description, subject, class_level, teacher_id, capacity, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    Auth::uuid(),
                    $schoolId,
                    $name,
                    $desc,
                    $subject ?: null,
                    $classLevel ?: null,
                    $teacherId,
                    $capacity,
                    (int)$currentUser['id']
                ]);
                Session::flash('success', "Class / Batch '{$name}' created successfully.");
            } catch (\Throwable $e) {
                Session::flash('error', 'Failed to create class: ' . $e->getMessage());
            }
        }
        Helper::redirect('/school/batches');
    } elseif ($action === 'edit') {
        $batchId = (int)($_POST['batch_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $classLevel = trim($_POST['class_level'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $teacherId = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;
        $capacity = !empty($_POST['capacity']) ? (int)$_POST['capacity'] : 50;
        $status = in_array($_POST['status'] ?? '', ['active', 'archived'], true) ? $_POST['status'] : 'active';
        $desc = trim($_POST['description'] ?? '') ?: null;

        if ($batchId > 0 && $name !== '') {
            try {
                $stmt = $db->prepare("
                    UPDATE batches 
                    SET name = ?, class_level = ?, subject = ?, teacher_id = ?, capacity = ?, status = ?, description = ?
                    WHERE id = ? AND (school_id = ? OR teacher_id IN (SELECT id FROM users WHERE school_id = ?))
                ");
                $stmt->execute([$name, $classLevel ?: null, $subject ?: null, $teacherId, $capacity, $status, $desc, $batchId, $schoolId, $schoolId]);
                Session::flash('success', "Class / Batch '{$name}' updated successfully.");
            } catch (\Throwable $e) {
                Session::flash('error', 'Failed to update class: ' . $e->getMessage());
            }
        } else {
            Session::flash('error', 'Class / Batch name and ID are required.');
        }
        Helper::redirect('/school/batches');
    }
}

// Fetch active teachers in school for assignment
$teachers = $db->query("
    SELECT u.id, t.full_name, t.expert_field
    FROM users u
    JOIN teachers t ON t.user_id = u.id
    WHERE u.school_id = {$schoolId} AND u.status = 'active'
    ORDER BY t.full_name ASC
")->fetchAll();

// Fetch all batches belonging to this school or created by school teachers
$stmt = $db->prepare("
    SELECT b.*, u.email AS teacher_email, t.full_name AS teacher_name,
           (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id = b.id) AS student_count
    FROM batches b
    LEFT JOIN users u ON u.id = b.teacher_id
    LEFT JOIN teachers t ON t.user_id = u.id
    WHERE (b.school_id = ? OR b.teacher_id IN (SELECT id FROM users WHERE school_id = ?) OR b.created_by IN (SELECT id FROM users WHERE school_id = ?))
    ORDER BY b.created_at DESC
");
$stmt->execute([$schoolId, $schoolId, $schoolId]);
$batches = $stmt->fetchAll();

$gradeOptions = [
    'Kindergarten',
    'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
    'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
];

$pageTitle = 'Class / Batches — ' . $school['name'];
$error = Session::flash('error');
$success = Session::flash('success');
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <main class="admin-main">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">🏫 ACADEMIC GROUPS</span>
                <h1 style="margin:2px 0 0; font-size:24px;">Class / Batch Management</h1>
                <p class="muted" style="font-size:14px; margin-top:4px;">Create, organize, and assign teachers to all classes and batches for <strong><?= Helper::e($school['name']) ?></strong>.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" onclick="openCreateModal()">+ Create Class / Batch</button>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error mt-2"><?= Helper::e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success mt-2"><?= Helper::e($success) ?></div><?php endif; ?>

        <?php if (!$batches): ?>
            <div class="card text-center mt-3" style="padding:40px 20px;">
                <p class="muted" style="font-size:16px;">No classes or batches created yet.</p>
                <button type="button" class="btn btn-primary mt-2" onclick="openCreateModal()">+ Create First Class / Batch</button>
            </div>
        <?php else: ?>
            <div class="table-wrap mt-3">
                <table class="data">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Class / Batch</th>
                            <th>Assigned Teacher</th>
                            <th>Subject</th>
                            <th>Grade Level</th>
                            <th>Students</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $bIdx = 1;
                    foreach ($batches as $b): 
                    ?>
                        <tr>
                            <td class="muted" style="font-weight:700;"><?= $bIdx++ ?></td>
                            <td>
                                <strong><?= Helper::e($b['name']) ?></strong>
                                <?php if (!empty($b['description'])): ?>
                                    <div class="muted" style="font-size:.76rem; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= Helper::e($b['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($b['teacher_name']): ?>
                                    <span style="font-weight:600;"><?= Helper::e($b['teacher_name']) ?></span>
                                    <div class="muted" style="font-size:.78rem"><?= Helper::e($b['teacher_email']) ?></div>
                                <?php else: ?>
                                    <span class="muted" style="font-style:italic;">— unassigned —</span>
                                <?php endif; ?>
                            </td>
                            <td><?= Helper::e($b['subject'] ?: '—') ?></td>
                            <td><span class="badge badge-brand"><?= Helper::e($b['class_level'] ?: '—') ?></span></td>
                            <td><strong><?= (int)$b['student_count'] ?></strong> <span class="muted">/ <?= (int)$b['capacity'] ?></span></td>
                            <td>
                                <span class="badge badge-<?= $b['status'] === 'active' ? 'success' : 'muted' ?>">
                                    <?= Helper::e($b['status']) ?>
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick='openEditModal(<?= json_encode($b) ?>)'>
                                    ✏️ Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- ================= CREATE CLASS MODAL ================= -->
<div id="createClassModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:99999; align-items:flex-start; justify-content:center; padding:50px 16px; overflow-y:auto;">
    <div class="card" style="max-width:540px; width:100%; border-radius:16px; background:var(--surface-0); box-shadow:0 20px 40px rgba(0,0,0,0.3); border:1px solid var(--il-border); margin:0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h3 style="margin:0; font-size:18px;">+ Create New Class / Batch</h3>
            <button type="button" onclick="closeCreateModal()" style="background:none; border:none; font-size:22px; cursor:pointer; color:var(--il-muted);">&times;</button>
        </div>
        <form method="post" action="/school/batches">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label>Class / Batch Name *</label>
                <input type="text" name="name" required placeholder="e.g. Grade 10 - Section A or Physics Batch 1">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Grade Level</label>
                    <select name="class_level">
                        <option value="">— select grade —</option>
                        <?php foreach ($gradeOptions as $go): ?>
                            <option value="<?= $go ?>"><?= $go ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" placeholder="e.g. Mathematics, Science, General">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Assigned Teacher</label>
                    <select name="teacher_id">
                        <option value="">— assign a teacher —</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= Helper::e($t['full_name']) ?><?php if ($t['expert_field']): ?> (<?= Helper::e($t['expert_field']) ?>)<?php endif; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Student Capacity</label>
                    <input type="number" name="capacity" min="1" max="500" value="50">
                </div>
            </div>

            <div class="form-group">
                <label>Description / Notes</label>
                <textarea name="description" rows="2" placeholder="Optional notes for this class..."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Class / Batch</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= EDIT CLASS MODAL ================= -->
<div id="editClassModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:99999; align-items:flex-start; justify-content:center; padding:50px 16px; overflow-y:auto;">
    <div class="card" style="max-width:540px; width:100%; border-radius:16px; background:var(--surface-0); box-shadow:0 20px 40px rgba(0,0,0,0.3); border:1px solid var(--il-border); margin:0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h3 style="margin:0; font-size:18px;">✏️ Edit Class / Batch</h3>
            <button type="button" onclick="closeEditModal()" style="background:none; border:none; font-size:22px; cursor:pointer; color:var(--il-muted);">&times;</button>
        </div>
        <form method="post" action="/school/batches">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="batch_id" id="edit_batch_id">

            <div class="form-group">
                <label>Class / Batch Name *</label>
                <input type="text" name="name" id="edit_name" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Grade Level</label>
                    <select name="class_level" id="edit_class_level">
                        <option value="">— select grade —</option>
                        <?php foreach ($gradeOptions as $go): ?>
                            <option value="<?= $go ?>"><?= $go ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" id="edit_subject">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Assigned Teacher</label>
                    <select name="teacher_id" id="edit_teacher_id">
                        <option value="">— unassigned —</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= Helper::e($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Student Capacity</label>
                    <input type="number" name="capacity" id="edit_capacity" min="1" max="500">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status">
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Description / Notes</label>
                <textarea name="description" id="edit_description" rows="2"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    const modal = document.getElementById('createClassModal');
    if (modal) modal.style.display = 'flex';
}
function closeCreateModal() {
    const modal = document.getElementById('createClassModal');
    if (modal) modal.style.display = 'none';
}
function openEditModal(batch) {
    document.getElementById('edit_batch_id').value = batch.id || '';
    document.getElementById('edit_name').value = batch.name || '';
    document.getElementById('edit_class_level').value = batch.class_level || '';
    document.getElementById('edit_subject').value = batch.subject || '';
    document.getElementById('edit_teacher_id').value = batch.teacher_id || '';
    document.getElementById('edit_capacity').value = batch.capacity || 50;
    document.getElementById('edit_status').value = batch.status || 'active';
    document.getElementById('edit_description').value = batch.description || '';

    const modal = document.getElementById('editClassModal');
    if (modal) modal.style.display = 'flex';
}
function closeEditModal() {
    const modal = document.getElementById('editClassModal');
    if (modal) modal.style.display = 'none';
}
window.addEventListener('click', function(e) {
    const cModal = document.getElementById('createClassModal');
    const eModal = document.getElementById('editClassModal');
    if (e.target === cModal) closeCreateModal();
    if (e.target === eModal) closeEditModal();
});
</script>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
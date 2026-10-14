<?php
use App\Auth;
use App\Batch;
use App\Helper;
use App\Session;

Auth::requireRole('admin');

// ---- Handle inline actions (create / update / archive / restore / delete) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Session::verifyCsrf($_POST['csrf'] ?? null)) {
    $action = $_POST['action'] ?? '';
    try {
        switch ($action) {
            case 'create':
                $newId = Batch::create([
                    'name'        => $_POST['name']        ?? '',
                    'description' => $_POST['description'] ?? '',
                    'subject'     => $_POST['subject']     ?? '',
                    'class_level' => $_POST['class_level'] ?? '',
                    'teacher_id'  => $_POST['teacher_id']  ?? null,
                    'capacity'    => $_POST['capacity']    ?? 50,
                ], (int)Auth::user()['id']);
                Session::flash('success', 'Batch created.');
                Helper::redirect('/admin/batches');
                break;

            case 'update':
                Batch::update((int)$_POST['id'], [
                    'name'        => $_POST['name']        ?? '',
                    'description' => $_POST['description'] ?? '',
                    'subject'     => $_POST['subject']     ?? '',
                    'class_level' => $_POST['class_level'] ?? '',
                    'teacher_id'  => $_POST['teacher_id']  ?? null,
                    'capacity'    => $_POST['capacity']    ?? 50,
                ]);
                Session::flash('success', 'Batch updated.');
                Helper::redirect('/admin/batches');
                break;

            case 'archive':
                Batch::archive((int)$_POST['id']);
                Session::flash('success', 'Batch archived.');
                Helper::redirect('/admin/batches');
                break;

            case 'restore':
                Batch::restore((int)$_POST['id']);
                Session::flash('success', 'Batch restored.');
                Helper::redirect('/admin/batches');
                break;

            case 'delete':
                Batch::delete((int)$_POST['id']);
                Session::flash('success', 'Batch deleted.');
                Helper::redirect('/admin/batches');
                break;
        }
    } catch (\Throwable $e) {
        Session::flash('error', 'Action failed: ' . $e->getMessage());
        Helper::redirect('/admin/batches');
    }
}

$editing = isset($_GET['edit']) ? Batch::find((int)$_GET['edit']) : null;
$batches = Batch::allWithArchived();
$teachers = Batch::availableTeachers();

$pageTitle = 'Batches — Admin';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/admin/_sidebar.php'; ?>
    <div>
        <div class="flex between" style="align-items:flex-start">
            <div>
                <h1>Batches</h1>
                <p class="muted">Group students with a teacher. Admins can create, assign and manage any batch.</p>
            </div>
        </div>

        <!-- ============ Create / Edit form ============ -->
        <form method="post" class="card mt-2">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
            <?php endif; ?>

            <h3 style="margin-bottom:.75rem"><?= $editing ? 'Edit batch' : 'Create a new batch' ?></h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Batch name *</label>
                    <input type="text" name="name" required value="<?= Helper::e($editing['name'] ?? '') ?>"
                           placeholder="e.g. Grade 7 Maths — Morning">
                </div>
                <div class="form-group">
                    <label>Teacher</label>
                    <select name="teacher_id">
                        <option value="">— unassigned —</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"
                                <?= $editing && (int)$editing['teacher_id'] === (int)$t['id'] ? 'selected' : '' ?>>
                                <?= Helper::e($t['full_name'] ?: $t['email']) ?>
                                <?php if ($t['expert_field']): ?> · <?= Helper::e($t['expert_field']) ?><?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" value="<?= Helper::e($editing['subject'] ?? '') ?>"
                           placeholder="e.g. Mathematics">
                </div>
                <div class="form-group">
                    <label>Class level</label>
                    <input type="text" name="class_level" value="<?= Helper::e($editing['class_level'] ?? '') ?>"
                           placeholder="e.g. Grade 7">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" value="<?= Helper::e($editing['description'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" min="1" max="500"
                           value="<?= Helper::e((string)($editing['capacity'] ?? 50)) ?>">
                </div>
            </div>

            <div class="flex mt-2">
                <button class="btn btn-primary"><?= $editing ? 'Save changes' : 'Create batch' ?></button>
                <?php if ($editing): ?>
                    <a href="/admin/batches" class="btn btn-ghost">Cancel</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- ============ Batch list ============ -->
        <?php if (!$batches): ?>
            <div class="card text-center mt-3">
                <p class="muted">No batches yet — create the first one above.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap mt-3">
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Batch</th>
                            <th>Teacher</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Students</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($batches as $b): ?>
                        <tr>
                            <td><?= (int)$b['id'] ?></td>
                            <td><strong><?= Helper::e($b['name']) ?></strong></td>
                            <td>
                                <?php if ($b['teacher_name']): ?>
                                    <?= Helper::e($b['teacher_name']) ?>
                                    <div class="muted" style="font-size:.8rem"><?= Helper::e($b['teacher_email']) ?></div>
                                <?php else: ?>
                                    <span class="muted">— unassigned —</span>
                                <?php endif; ?>
                            </td>
                            <td><?= Helper::e($b['subject'] ?: '—') ?></td>
                            <td><?= Helper::e($b['class_level'] ?: '—') ?></td>
                            <td><?= (int)$b['student_count'] ?></td>
                            <td>
                                <span class="badge badge-<?= $b['status'] === 'active' ? 'success' : 'muted' ?>">
                                    <?= Helper::e($b['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="flex" style="gap:.3rem;flex-wrap:wrap">
                                    <a class="btn btn-ghost btn-sm" href="/teacher/batch?id=<?= (int)$b['id'] ?>">Open</a>
                                    <a class="btn btn-ghost btn-sm" href="/admin/batches?edit=<?= (int)$b['id'] ?>">Edit</a>

                                    <?php if ($b['status'] === 'active'): ?>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Archive this batch?')">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                            <input type="hidden" name="action" value="archive">
                                            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                            <button class="btn btn-ghost btn-sm">Archive</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                            <input type="hidden" name="action" value="restore">
                                            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                            <button class="btn btn-success btn-sm">Restore</button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="post" style="display:inline"
                                          onsubmit="return confirm('Delete this batch permanently? This cannot be undone.')">
                                        <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                        <button class="btn btn-danger btn-sm">Delete</button>
                                    </form>
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
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
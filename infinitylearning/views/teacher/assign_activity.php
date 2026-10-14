<?php
use App\Auth;
use App\Batch;
use App\Activity;
use App\ActivityAssignment;
use App\Helper;
use App\Session;

Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

$editing = isset($_GET['id']);
$assignment = $editing ? ActivityAssignment::find((int)$_GET['id']) : null;
if ($editing && !$assignment) { Helper::redirect('/teacher/assignments'); }

// Ownership check
if ($editing && $role === 'teacher' && (int)$assignment['assigned_by'] !== (int)Auth::user()['id']) {
    http_response_code(403); exit('You cannot edit this assignment.');
}

$teacherUserId = $role === 'teacher' ? (int)Auth::user()['id'] : null;
$myBatches = Batch::all($teacherUserId);

// Load all published activities (teacher can pick any)
$activities = Activity::list(['status' => 'published']);
$preselectedBatchId = (int)($_GET['batch_id'] ?? 0);

$pageTitle = ($editing ? 'Edit Assignment' : 'Assign Activity') . ' — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section" style="max-width:760px">
    <a href="/teacher/assignments" class="muted">← All assignments</a>
    <div class="card mt-2">
        <h1><?= $editing ? 'Edit assignment' : 'Assign an activity' ?></h1>
        <p class="muted">
            Pick an activity, choose a batch, set a schedule — students in that batch will see it on their dashboard.
        </p>

        <?php if (!$myBatches): ?>
            <div class="alert alert-warning">
                You have no batches yet. <a href="/teacher/batch/new">Create one first →</a>
            </div>
        <?php endif; ?>

        <?php if (!$activities): ?>
            <div class="alert alert-warning">
                No published activities exist yet. Ask an admin to publish some first.
            </div>
        <?php endif; ?>

        <form method="post" action="/teacher/assign-activity-save-handler.php" class="mt-2">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int)$assignment['id'] ?>"><?php endif; ?>

            <?php if (!$editing): ?>
                <div class="form-group">
                    <label>Batch *</label>
                    <select name="batch_id" required>
                        <option value="">— choose a batch —</option>
                        <?php foreach ($myBatches as $b): ?>
                            <option value="<?= (int)$b['id'] ?>"
                                <?= $preselectedBatchId === (int)$b['id'] ? 'selected' : '' ?>>
                                <?= Helper::e($b['name']) ?>
                                <?php if ($b['subject']): ?> · <?= Helper::e($b['subject']) ?><?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Activity *</label>
                    <select name="activity_id" required>
                        <option value="">— choose an activity —</option>
                        <?php foreach ($activities as $act): ?>
                            <option value="<?= (int)$act['id'] ?>">
                                <?= Helper::e($act['title']) ?> · <?= Helper::e(ucfirst($act['category'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Editing <strong><?= Helper::e($assignment['activity_title']) ?></strong>
                    for batch <strong><?= Helper::e($assignment['batch_name']) ?></strong>.
                </div>
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Scheduled date</label>
                    <input type="date" name="scheduled_date"
                           value="<?= Helper::e($assignment['scheduled_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Scheduled time</label>
                    <input type="time" name="scheduled_time"
                           value="<?= Helper::e(substr($assignment['scheduled_time'] ?? '', 0, 5)) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Duration (minutes)</label>
                    <input type="number" name="duration_minutes" min="1" max="600"
                           value="<?= Helper::e((string)($assignment['duration_minutes'] ?? '')) ?>"
                           placeholder="e.g. 60">
                </div>
                <div class="form-group">
                    <label>Due date</label>
                    <input type="date" name="due_date"
                           value="<?= Helper::e($assignment['due_date'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Instructions for students</label>
                <textarea name="instructions" rows="4"
                          placeholder="e.g. Bring your own drawing book and colour pencils. Meet at the art room at 10 AM."><?= Helper::e($assignment['instructions'] ?? '') ?></textarea>
            </div>

            <?php if ($editing): ?>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['scheduled'=>'Scheduled','in_progress'=>'In progress','completed'=>'Completed','cancelled'=>'Cancelled'] as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= ($assignment['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="flex mt-2">
                <button class="btn btn-primary"<?= (!$myBatches || !$activities) && !$editing ? ' disabled' : '' ?>>
                    <?= $editing ? 'Save changes' : 'Assign to batch' ?>
                </button>
                <a href="/teacher/assignments" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
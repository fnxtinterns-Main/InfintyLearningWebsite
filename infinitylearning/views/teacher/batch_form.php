<?php
use App\Auth;
use App\Batch;
use App\Helper;
use App\Session;

Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

$editing = isset($_GET['id']);
$batch = $editing ? Batch::find((int)$_GET['id']) : null;
if ($editing && !$batch) { Helper::redirect('/teacher/batches'); }

// Teachers can only edit their own batches
if ($editing && $role === 'teacher' && (int)$batch['teacher_id'] !== (int)Auth::user()['id']) {
    http_response_code(403);
    exit('You do not own this batch.');
}

$pageTitle = ($editing ? 'Edit Batch' : 'New Batch') . ' — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section" style="max-width:720px">
    <a href="/teacher/batches" class="muted">← Back to batches</a>
    <div class="card mt-2">
        <h1><?= $editing ? 'Edit batch' : 'Create a batch' ?></h1>
        <p class="muted">Batches let you group students and take attendance.</p>

        <form method="post" action="/teacher/batch-save-handler.php" class="mt-2">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= (int)$batch['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Batch name *</label>
                <input type="text" name="name" required
                       value="<?= Helper::e($batch['name'] ?? '') ?>"
                       placeholder="e.g. Grade 7 Maths — Morning Batch">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject"
                           value="<?= Helper::e($batch['subject'] ?? '') ?>"
                           placeholder="e.g. Mathematics">
                </div>
                <div class="form-group">
                    <label>Class level</label>
                    <input type="text" name="class_level"
                           value="<?= Helper::e($batch['class_level'] ?? '') ?>"
                           placeholder="e.g. Grade 7">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"><?= Helper::e($batch['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Capacity</label>
                <input type="number" name="capacity" min="1" max="500"
                       value="<?= Helper::e((string)($batch['capacity'] ?? 50)) ?>">
            </div>

            <div class="flex mt-2">
                <button class="btn btn-primary"><?= $editing ? 'Save changes' : 'Create batch' ?></button>
                <a href="/teacher/batches" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
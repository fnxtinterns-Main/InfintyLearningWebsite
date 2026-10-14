<?php
use App\Auth;
use App\Batch;
use App\Helper;

Auth::requireLogin();
if (!in_array(Auth::role(), ['teacher','admin'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

$teacherUserId = Auth::role() === 'teacher' ? (int)Auth::user()['id'] : null;
$batches = Batch::all($teacherUserId);

$pageTitle = 'My Batches — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section">
    <div class="flex between" style="align-items:flex-start">
        <div>
            <h1>My Batches</h1>
            <p class="muted">Create and manage batches of students you teach.</p>
        </div>
        <a href="/teacher/batch/new" class="btn btn-primary">+ New batch</a>
    </div>

    <?php if (!$batches): ?>
        <div class="card text-center mt-3">
            <p class="muted">No batches yet. Click "New batch" to create your first one.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-3 mt-3">
            <?php foreach ($batches as $b): ?>
                <div class="card card-hover">
                    <div class="flex between">
                        <span class="pill"><?= Helper::e($b['subject'] ?: 'General') ?></span>
                        <span class="badge badge-primary"><?= (int)$b['student_count'] ?> students</span>
                    </div>
                    <h3 class="mt-1"><?= Helper::e($b['name']) ?></h3>
                    <p class="muted" style="font-size:.88rem"><?= Helper::e($b['description'] ?: '—') ?></p>
                    <div class="kv mt-2">
                        <span>Class</span><span><?= Helper::e($b['class_level'] ?: '—') ?></span>
                    </div>
                    <div class="flex mt-2">
                        <a href="/teacher/batch?id=<?= (int)$b['id'] ?>" class="btn btn-secondary btn-sm">Open</a>
                        <a href="/teacher/batch/attendance?id=<?= (int)$b['id'] ?>" class="btn btn-ghost btn-sm">Attendance</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
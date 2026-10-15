<?php
use App\Auth;
use App\Lesson;
use App\Helper;

$schoolId = Auth::requireSchoolAdmin();

$all = Lesson::list(['limit' => 200], $schoolId);
$own = array_filter($all, fn($l) => !empty($l['school_id']) && (int)$l['school_id'] === $schoolId);
$global = array_filter($all, fn($l) => empty($l['school_id']));

$pageTitle = 'Lessons — Admin';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>
        <div class="flex between" style="align-items:flex-start">
            <div>
                <h1>Lessons</h1>
                <p class="muted">Publish lessons only for your school's students.</p>
            </div>
            <a href="/school/lessons/new" class="btn btn-primary">+ New lesson</a>
        </div>

        <div class="card mt-3">
            <div class="flex between">
                <h3>🏫 Your school's lessons</h3>
                <span class="pill"><?= count($own) ?></span>
            </div>
            <div class="divider"></div>
            <?php if (!$own): ?>
                <p class="muted">No lessons yet. Click <strong>+ New lesson</strong> above.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr><th>#</th><th>Title</th><th>Subject</th><th>Difficulty</th><th>Minutes</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($own as $l): ?>
                            <tr>
                                <td><?= (int)$l['id'] ?></td>
                                <td><a href="/lesson?id=<?= (int)$l['id'] ?>" style="color:inherit"><strong><?= Helper::e($l['title']) ?></strong></a></td>
                                <td><?= Helper::e($l['subject_name']) ?></td>
                                <td><span class="pill"><?= Helper::e($l['difficulty']) ?></span></td>
                                <td><?= (int)$l['estimated_minutes'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card mt-3">
            <div class="flex between">
                <h3>🌐 Global lessons (visible to your school)</h3>
                <span class="pill"><?= count($global) ?></span>
            </div>
            <div class="divider"></div>
            <?php if (!$global): ?>
                <p class="muted">No global lessons yet.</p>
            <?php else: ?>
                <?php foreach ($global as $l): ?>
                    <div class="kv">
                        <div>
                            <strong><?= Helper::e($l['title']) ?></strong>
                            <div class="muted" style="font-size:.82rem"><?= Helper::e($l['subject_name']) ?> · <?= Helper::e($l['difficulty']) ?></div>
                        </div>
                        <span class="badge badge-info">Global</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
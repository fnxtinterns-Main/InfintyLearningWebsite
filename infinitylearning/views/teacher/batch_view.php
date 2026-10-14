<?php
use App\Auth;
use App\Batch;
use App\Helper;
use App\Session;

Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

$id = (int)($_GET['id'] ?? 0);
$batch = Batch::find($id);
if (!$batch) { http_response_code(404); require VIEW_PATH . '/404.php'; return; }

// Ownership check for teachers
if ($role === 'teacher' && (int)$batch['teacher_id'] !== (int)Auth::user()['id']) {
    http_response_code(403); exit('You do not own this batch.');
}

$members = Batch::members($id);
$stats = Batch::stats($id);

$pageTitle = $batch['name'] . ' — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section">
    <a href="/teacher/batches" class="muted">← All batches</a>

    <div class="card mt-2">
        <div class="flex between" style="align-items:flex-start;flex-wrap:wrap;gap:.75rem">
            <div>
                <span class="pill"><?= Helper::e($batch['subject'] ?: 'General') ?></span>
                <h1 class="mt-1"><?= Helper::e($batch['name']) ?></h1>
                <p class="muted"><?= Helper::e($batch['description'] ?: '—') ?></p>
            </div>
            <div class="flex" style="flex-wrap:wrap">
                <a href="/teacher/batch/edit?id=<?= (int)$batch['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                <a href="/teacher/batch/import?batch_id=<?= (int)$batch['id'] ?>" class="btn btn-secondary btn-sm">Import students</a>
                <a href="/teacher/batch/attendance?id=<?= (int)$batch['id'] ?>" class="btn btn-primary btn-sm">Take attendance</a>
            </div>
        </div>

        <div class="grid grid-3 mt-3">
            <div class="stat"><span class="ico">👩‍🎓</span><span class="n"><?= $stats['students'] ?></span><span class="l">Students</span></div>
            <div class="stat"><span class="ico">📚</span><span class="n"><?= $stats['lessons'] ?></span><span class="l">Lessons</span></div>
            <div class="stat"><span class="ico">🎯</span><span class="n"><?= (int)$batch['capacity'] ?></span><span class="l">Capacity</span></div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="flex between">
            <h3>👩‍🎓 Enrolled students</h3>
            <span class="pill"><?= count($members) ?></span>
        </div>
        <div class="divider"></div>

        <?php if (!$members): ?>
            <p class="muted">No students enrolled yet — use the search below to add some.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Class</th><th>Joined</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($members as $i => $m): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div class="flex">
                                    <?php if (!empty($m['profile_image'])): ?>
                                        <img class="avatar" src="<?= Helper::e($m['profile_image']) ?>" alt="">
                                    <?php else: ?>
                                        <span class="avatar avatar-initial" data-role="student">
                                            <?= Helper::e(mb_strtoupper(mb_substr($m['full_name'] ?: $m['email'], 0, 1))) ?>
                                        </span>
                                    <?php endif; ?>
                                    <a href="/student?id=<?= (int)$m['user_id'] ?>" style="color:inherit;text-decoration:none">
                                        <strong><?= Helper::e($m['full_name'] ?: '—') ?></strong>
                                    </a>
                                </div>
                            </td>
                            <td><?= Helper::e($m['email']) ?></td>
                            <td><?= Helper::e($m['current_class'] ?: '—') ?></td>
                            <td><?= date('d M Y', strtotime($m['joined_at'])) ?></td>
                            <td>
                                <div class="flex">
                                    <a class="btn btn-ghost btn-sm" href="/student?id=<?= (int)$m['user_id'] ?>">Profile</a>
                                    <form method="post" action="/teacher/batch-remove-student-handler.php" style="display:inline"
                                          onsubmit="return confirm('Remove this student from the batch?')">
                                        <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                        <input type="hidden" name="batch_id" value="<?= (int)$batch['id'] ?>">
                                        <input type="hidden" name="student_user_id" value="<?= (int)$m['user_id'] ?>">
                                        <button class="btn btn-ghost btn-sm">Remove</button>
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

    <div class="card mt-3">
        <h3>➕ Add students</h3>
        <div class="divider"></div>

        <form method="get" action="/teacher/batch" class="flex" style="max-width:480px">
            <input type="hidden" name="id" value="<?= (int)$batch['id'] ?>">
            <input type="text" name="q" value="<?= Helper::e($_GET['q'] ?? '') ?>"
                   placeholder="Search by name, email or phone…"
                   style="flex:1;padding:.65rem .9rem;border:1.5px solid var(--il-border);border-radius:10px">
            <button class="btn btn-primary">Search</button>
        </form>

        <?php
        $q = trim($_GET['q'] ?? '');
        $results = Batch::searchStudents((int)$batch['id'], $q);
        ?>

        <?php if ($q === ''): ?>
            <p class="muted mt-2" style="font-size:.88rem">Type a name, email or phone to search registered students.</p>
        <?php elseif (!$results): ?>
            <p class="muted mt-2">No matching students found (or all are already in this batch).</p>
        <?php else: ?>
            <div class="table-wrap mt-2">
                <table class="data">
                    <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Class</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td><strong><?= Helper::e($r['full_name'] ?: '—') ?></strong></td>
                            <td><?= Helper::e($r['email']) ?></td>
                            <td><?= Helper::e($r['phone']) ?></td>
                            <td><?= Helper::e($r['current_class'] ?: '—') ?></td>
                            <td>
                                <form method="post" action="/teacher/batch-add-student-handler.php" style="display:inline">
                                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                    <input type="hidden" name="batch_id" value="<?= (int)$batch['id'] ?>">
                                    <input type="hidden" name="student_user_id" value="<?= (int)$r['user_id'] ?>">
                                    <input type="hidden" name="q" value="<?= Helper::e($q) ?>">
                                    <button class="btn btn-success btn-sm">+ Add</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
<?php
use App\Auth;
use App\Batch;
use App\Attendance;
use App\Helper;
use App\Session;

Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

$batchId = (int)($_GET['id'] ?? 0);
$batch = Batch::find($batchId);
if (!$batch) { http_response_code(404); require VIEW_PATH . '/404.php'; return; }

if ($role === 'teacher' && (int)$batch['teacher_id'] !== (int)Auth::user()['id']) {
    http_response_code(403); exit('You do not own this batch.');
}

$date = $_GET['date'] ?? date('Y-m-d');
$members = Batch::members($batchId);
$existing = Attendance::forBatchDate($batchId, $date);

$pageTitle = 'Attendance — ' . $batch['name'];
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section">
    <a href="/teacher/batch?id=<?= (int)$batch['id'] ?>" class="muted">← Back to batch</a>

    <div class="card mt-2">
        <div class="flex between" style="align-items:flex-start">
            <div>
                <h1>Mark Attendance</h1>
                <p class="muted"><?= Helper::e($batch['name']) ?> · <?= Helper::e($batch['subject'] ?: 'General') ?></p>
            </div>
            <form method="get" action="/teacher/batch/attendance" class="flex">
                <input type="hidden" name="id" value="<?= (int)$batch['id'] ?>">
                <input type="date" name="date" value="<?= Helper::e($date) ?>"
                       style="padding:.5rem .75rem;border:1.5px solid var(--il-border);border-radius:10px">
                <button class="btn btn-secondary btn-sm">Load</button>
            </form>
        </div>
    </div>

    <?php if (!$members): ?>
        <div class="card mt-3 text-center">
            <p class="muted">No students enrolled. Add students from the batch page first.</p>
        </div>
    <?php else: ?>
        <form method="post" action="/teacher/attendance-save-handler.php" class="card mt-3">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <input type="hidden" name="batch_id" value="<?= (int)$batch['id'] ?>">
            <input type="hidden" name="date" value="<?= Helper::e($date) ?>">

            <div class="flex between mb-2">
                <h3><?= date('d M Y', strtotime($date)) ?></h3>
                <div class="flex">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="markAll('present')">All Present</button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="markAll('absent')">All Absent</button>
                </div>
            </div>
            <div class="divider"></div>

            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>#</th><th>Student</th><th>Present</th><th>Absent</th><th>Late</th><th>Excused</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($members as $i => $m):
                        $sid = (int)$m['user_id'];
                        $current = $existing[$sid]['status'] ?? '';
                    ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <strong><?= Helper::e($m['full_name'] ?: '—') ?></strong>
                                <div class="muted" style="font-size:.8rem"><?= Helper::e($m['email']) ?></div>
                            </td>
                            <?php foreach (['present','absent','late','excused'] as $s): ?>
                                <td>
                                    <input type="radio" name="status[<?= $sid ?>]" value="<?= $s ?>"
                                           <?= $current === $s ? 'checked' : '' ?> required>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button class="btn btn-primary mt-3">Save attendance</button>
        </form>
    <?php endif; ?>
</section>

<script>
function markAll(status) {
    document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(r => r.checked = true);
}
</script>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
<?php
use App\Auth;
use App\Batch;
use App\Activity;
use App\Helper;
use App\Session;
use App\Database;

Auth::requireLogin();
if (!in_array(Auth::role(), ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

$activityId = (int)($_GET['activity_id'] ?? 0);
$activity = $activityId ? Activity::find($activityId) : null;

$teacherUserId = Auth::role() === 'teacher' ? (int)Auth::user()['id'] : null;
$myBatches = Batch::all($teacherUserId);

// If activity is chosen, determine which batches can see it
$eligibleBatches = [];
if ($activity) {
    foreach ($myBatches as $b) {
        $eligibleBatches[] = $b;
    }
}

$batchId = (int)($_GET['batch_id'] ?? 0);
$students = [];
$existing = [];
if ($activity && $batchId) {
    $students = Batch::members($batchId);
    $rows = Database::connect()->prepare("
        SELECT student_user_id, score, position, remarks
        FROM activity_results
        WHERE activity_id = ?
    ");
    $rows->execute([$activityId]);
    foreach ($rows->fetchAll() as $r) {
        $existing[(int)$r['student_user_id']] = $r;
    }
}

$pageTitle = 'Record Activity Results — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section" style="max-width:960px">
    <a href="/teacher/assignments" class="muted">← Back</a>
    <div class="card mt-2">
        <h1>🏅 Record Activity Results</h1>
        <p class="muted">
            Record scores / ranks. Winners, runner-ups, and participants get the right certificate automatically.
        </p>

        <form method="get" class="flex mt-2" style="gap:.5rem;flex-wrap:wrap">
            <select name="activity_id" required style="flex:1;min-width:220px;padding:.65rem;border:1.5px solid var(--il-border);border-radius:10px">
                <option value="">— choose an activity —</option>
                <?php
                $acts = Activity::list(['status' => 'published'], Auth::isSuperAdmin() ? -1 : Auth::schoolId());
                foreach ($acts as $a): ?>
                    <option value="<?= (int)$a['id'] ?>" <?= $activityId === (int)$a['id'] ? 'selected' : '' ?>>
                        <?= Helper::e($a['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($activity): ?>
                <select name="batch_id" required style="flex:1;min-width:220px;padding:.65rem;border:1.5px solid var(--il-border);border-radius:10px">
                    <option value="">— choose a batch —</option>
                    <?php foreach ($eligibleBatches as $b): ?>
                        <option value="<?= (int)$b['id'] ?>" <?= $batchId === (int)$b['id'] ? 'selected' : '' ?>>
                            <?= Helper::e($b['name']) ?> (<?= (int)$b['student_count'] ?> students)
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <button class="btn btn-primary">Load</button>
        </form>
    </div>

    <?php if ($activity && $batchId && $students): ?>
        <form method="post" action="/teacher/activity-result-save-handler.php" class="card mt-3">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <input type="hidden" name="activity_id" value="<?= (int)$activityId ?>">
            <input type="hidden" name="batch_id" value="<?= (int)$batchId ?>">

            <div class="flex between" style="align-items:flex-start">
                <div>
                    <h3 style="margin:0"><?= Helper::e($activity['title']) ?></h3>
                    <p class="muted" style="font-size:.85rem">Record each student's outcome. Leave blank if they didn't participate.</p>
                </div>
            </div>
            <div class="divider"></div>

            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th><th>Student</th><th>Result</th>
                            <th>Position</th><th>Score</th><th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $i => $s):
                        $sid = (int)$s['user_id'];
                        $r = $existing[$sid] ?? null;
                    ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <strong><?= Helper::e($s['full_name'] ?: '—') ?></strong>
                                <div class="muted" style="font-size:.78rem"><?= Helper::e($s['email']) ?></div>
                            </td>
                            <td>
                                <select name="result[<?= $sid ?>][type]"
                                        style="padding:.4rem .6rem;border:1.5px solid var(--il-border);border-radius:8px">
                                    <option value="">— not participated —</option>
                                    <option value="winner">🏆 Winner</option>
                                    <option value="runner_up">🥈 Runner-up</option>
                                    <option value="participant">🎓 Participant</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="result[<?= $sid ?>][position]" min="1" max="100"
                                       placeholder="1-3" style="width:80px;padding:.4rem;border:1.5px solid var(--il-border);border-radius:8px">
                            </td>
                            <td>
                                <input type="number" name="result[<?= $sid ?>][score]" step="0.01"
                                       placeholder="Score" style="width:110px;padding:.4rem;border:1.5px solid var(--il-border);border-radius:8px">
                            </td>
                            <td>
                                <input type="text" name="result[<?= $sid ?>][remarks]"
                                       placeholder="Optional" style="width:100%;padding:.4rem;border:1.5px solid var(--il-border);border-radius:8px">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex mt-3">
                <button class="btn btn-primary">🏅 Save results & generate certificates</button>
                <a href="/teacher/assignments" class="btn btn-ghost">Cancel</a>
            </div>

            <p class="muted mt-2" style="font-size:.82rem">
                💡 Tip: students left as "not participated" will not receive a certificate. All others get one immediately.
            </p>
        </form>
    <?php elseif ($activity && $batchId): ?>
        <div class="card mt-3 text-center">
            <p class="muted">No students enrolled in this batch.</p>
        </div>
    <?php endif; ?>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
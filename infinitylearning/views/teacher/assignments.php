<?php
use App\Auth;
use App\Batch;
use App\ActivityAssignment;
use App\Helper;
use App\Database;

Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

$teacherUserId = $role === 'teacher' ? (int)Auth::user()['id'] : null;
$myBatches = Batch::all($teacherUserId);

// Load all assignments for each batch
$db = Database::connect();
$assignments = [];
foreach ($myBatches as $b) {
    foreach (ActivityAssignment::forBatch((int)$b['id']) as $a) {
        $a['batch_name_display'] = $b['name'];
        $assignments[] = $a;
    }
}
usort($assignments, function ($x, $y) {
    return strcmp((string)($y['scheduled_date'] ?? $y['created_at']), (string)($x['scheduled_date'] ?? $x['created_at']));
});

$pageTitle = 'Activity Assignments — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section">
    <div class="flex between" style="align-items:flex-start">
        <div>
            <h1>Activity Assignments</h1>
            <p class="muted">Schedule activities for your batches. They appear on each student's dashboard.</p>
        </div>
        <a href="/teacher/assign-activity" class="btn btn-primary">+ Assign activity</a>
    </div>

    <?php if (!$assignments): ?>
        <div class="card text-center mt-3">
            <p class="muted">No assignments yet. Click "Assign activity" to send your first one.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap mt-3">
            <table class="data">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Activity</th>
                        <th>Batch</th>
                        <th>Scheduled</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Ack.</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($assignments as $a): ?>
                    <tr>
                        <td><?= (int)$a['id'] ?></td>
                        <td>
                            <strong><?= Helper::e($a['activity_title']) ?></strong>
                            <div class="muted" style="font-size:.8rem"><?= Helper::e(ucfirst($a['activity_category'])) ?></div>
                        </td>
                        <td><?= Helper::e($a['batch_name_display']) ?></td>
                        <td>
                            <?php if ($a['scheduled_date']): ?>
                                <?= date('d M Y', strtotime($a['scheduled_date'])) ?>
                                <?php if ($a['scheduled_time']): ?>
                                    <div class="muted" style="font-size:.8rem"><?= substr($a['scheduled_time'], 0, 5) ?><?= $a['duration_minutes'] ? ' · '.$a['duration_minutes'].' min' : '' ?></div>
                                <?php endif; ?>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?= $a['due_date'] ? date('d M Y', strtotime($a['due_date'])) : '—' ?></td>
                        <td>
                            <span class="badge badge-<?= $a['status']==='scheduled'?'primary':($a['status']==='completed'?'success':($a['status']==='cancelled'?'muted':'info')) ?>">
                                <?= Helper::e($a['status']) ?>
                            </span>
                        </td>
                        <td><?= (int)$a['acknowledged_count'] ?>/<?= (int)$a['student_count'] ?></td>
                        <td>
                            <div class="flex">
                                <a class="btn btn-ghost btn-sm" href="/teacher/assign-activity/edit?id=<?= (int)$a['id'] ?>">Edit</a>
                                <form method="post" action="/teacher/assignment-cancel-handler.php" style="display:inline"
                                      onsubmit="return confirm('Cancel this assignment?')">
                                    <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                    <button class="btn btn-ghost btn-sm">Cancel</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
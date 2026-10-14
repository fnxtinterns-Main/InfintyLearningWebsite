<?php
use App\Auth;
use App\School;
use App\Helper;
use App\Database;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);
if (!$school) { Auth::logout(); Helper::redirect('/login'); }

$db = Database::connect();
$stmt = $db->prepare("
    SELECT b.*, u.email AS teacher_email, t.full_name AS teacher_name,
           (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id = b.id) AS student_count
    FROM batches b
    LEFT JOIN users u ON u.id = b.teacher_id
    LEFT JOIN teachers t ON t.user_id = u.id
    WHERE b.school_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$schoolId]);
$batches = $stmt->fetchAll();

$pageTitle = 'Batches — ' . $school['name'];
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>
        <h1>Batches</h1>
        <p class="muted">All batches created by teachers of <strong><?= Helper::e($school['name']) ?></strong>.</p>

        <?php if (!$batches): ?>
            <div class="card text-center mt-3">
                <p class="muted">No batches yet. Teachers will create batches from their own panel.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap mt-3">
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th><th>Batch</th><th>Teacher</th><th>Subject</th>
                            <th>Class</th><th>Students</th><th>Status</th>
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
                                    <div class="muted" style="font-size:.78rem"><?= Helper::e($b['teacher_email']) ?></div>
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
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
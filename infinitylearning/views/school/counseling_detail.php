<?php
use App\Auth;
use App\School;
use App\Counseling;
use App\Helper;
use App\Session;
use App\Database;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);
if (!$school) { Auth::logout(); Helper::redirect('/login'); }

$id = (int)($_GET['id'] ?? 0);
$request = Counseling::find($id);
if (!$request) { http_response_code(404); require VIEW_PATH . '/404.php'; return; }

// Ownership check
if ((int)$request['school_id'] !== (int)$schoolId) {
    http_response_code(403);
    exit('This request is not from your school.');
}

// Load active teachers of this school
$db = Database::connect();
$teachersStmt = $db->prepare("
    SELECT u.id, t.full_name, u.email, t.expert_field
    FROM users u
    LEFT JOIN teachers t ON t.user_id = u.id
    WHERE u.role = 'teacher'
      AND u.school_id = ?
      AND u.status = 'active'
    ORDER BY t.full_name, u.email
");
$teachersStmt->execute([$schoolId]);
$teachers = $teachersStmt->fetchAll();

// Load history
$logsStmt = $db->prepare("
    SELECT cl.*, u.email AS actor_email
    FROM counseling_logs cl
    LEFT JOIN users u ON u.id = cl.user_id
    WHERE cl.request_id = ?
    ORDER BY cl.created_at DESC
");
$logsStmt->execute([$id]);
$logs = $logsStmt->fetchAll();

$typeMeta = Counseling::types()[$request['counseling_type']] ?? ['label'=>$request['counseling_type'],'icon'=>'📋','color'=>'#999'];

$pageTitle = 'Counseling #' . $id . ' — ' . $school['name'];
require VIEW_PATH . '/layouts/header.php';

$ratings = [
    'rating_concentration' => 'Concentration',
    'rating_cognitive'     => 'Cognitive Skills',
    'rating_comprehension' => 'Comprehension',
    'rating_creativity'    => 'Creativity',
    'rating_confidence'    => 'Confidence',
    'rating_communication' => 'Communication',
];
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>
        <a href="/school/counseling" class="muted">← Back to counseling</a>

        <div class="card mt-2">
            <div class="flex between" style="align-items:flex-start;flex-wrap:wrap;gap:1rem">
                <div>
                    <span class="badge" style="background:<?= Helper::e($typeMeta['color']) ?>22;color:<?= Helper::e($typeMeta['color']) ?>">
                        <?= $typeMeta['icon'] ?> <?= Helper::e($typeMeta['label']) ?> Counseling
                    </span>
                    <h1 style="margin:.5rem 0 .25rem"><?= Helper::e($request['full_name']) ?></h1>
                    <p class="muted">
                        <?= Helper::e($request['email'] ?: '—') ?> · <?= Helper::e($request['phone'] ?: '—') ?>
                        <?php if (!empty($request['current_class'])): ?>
                            · <?= Helper::e($request['current_class']) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <span class="badge badge-<?=
                        $request['status'] === 'pending'     ? 'primary' :
                        ($request['status'] === 'assigned'    ? 'info' :
                        ($request['status'] === 'in_progress' ? 'primary' :
                        ($request['status'] === 'completed'   ? 'success' : 'muted')))
                    ?>">
                        <?= Helper::e(str_replace('_', ' ', $request['status'])) ?>
                    </span>
                </div>
            </div>

            <div class="divider"></div>

            <!-- ============ RATINGS ============ -->
            <h3>Self-rated profile</h3>
            <p class="muted" style="font-size:.85rem">1 = low · 6 = high</p>

            <div style="margin-top:1rem">
                <?php foreach ($ratings as $key => $label):
                    $val = (int)$request[$key];
                    $pct = (int)round($val / 6 * 100);
                ?>
                    <div style="margin-bottom:.85rem">
                        <div class="flex between" style="margin-bottom:.25rem">
                            <span style="font-weight:700;font-size:.9rem"><?= $label ?></span>
                            <span class="muted" style="font-size:.85rem"><strong><?= $val ?></strong>/6</span>
                        </div>
                        <div style="height:10px;border-radius:99px;background:var(--surface-2);overflow:hidden">
                            <div style="height:100%;width:<?= $pct ?>%;background:var(--il-grad)"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ============ REASON ============ -->
            <?php if (!empty($request['reason'])): ?>
                <div class="divider"></div>
                <h3>What the student wants to discuss</h3>
                <p style="line-height:1.7;margin-top:.5rem"><?= nl2br(Helper::e($request['reason'])) ?></p>
            <?php endif; ?>

            <!-- ============ PREFERRED TIME ============ -->
            <?php if (!empty($request['preferred_time'])): ?>
                <div class="kv"><span>Preferred time</span><span><?= Helper::e($request['preferred_time']) ?></span></div>
            <?php endif; ?>
            <?php if (!empty($request['city']) || !empty($request['state'])): ?>
                <div class="kv"><span>Location</span><span><?= Helper::e(trim(($request['city'] ?? '') . ', ' . ($request['state'] ?? ''), ', ')) ?></span></div>
            <?php endif; ?>
            <div class="kv"><span>Submitted</span><span><?= date('d M Y H:i', strtotime($request['created_at'])) ?></span></div>
        </div>

        <!-- ============ ASSIGN TEACHER ============ -->
        <?php if (in_array($request['status'], ['pending','assigned'], true)): ?>
            <div class="card mt-3">
                <h3>👩‍🏫 Assign a counselor</h3>
                <p class="muted" style="font-size:.88rem">Pick a teacher to talk with this student.</p>
                <div class="divider"></div>

                <form method="post" action="/school/counseling-assign-handler.php">
                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                    <input type="hidden" name="request_id" value="<?= (int)$id ?>">

                    <div class="form-group">
                        <label>Assign to teacher *</label>
                        <select name="teacher_user_id" required>
                            <option value="">— choose a teacher —</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= (int)$t['id'] ?>"
                                    <?= (int)($request['assigned_teacher_id'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>>
                                    <?= Helper::e($t['full_name'] ?: $t['email']) ?>
                                    <?php if ($t['expert_field']): ?> · <?= Helper::e($t['expert_field']) ?><?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!$teachers): ?>
                            <small style="color:var(--il-danger)">No approved teachers in your school yet. Approve teachers first.</small>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($request['teacher_name'])): ?>
                        <div class="alert alert-info" style="margin-bottom:1rem">
                            Currently assigned to <strong><?= Helper::e($request['teacher_name']) ?></strong>.
                            Choose another teacher below to reassign.
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-primary"<?= !$teachers ? ' disabled' : '' ?>>
                        <?= !empty($request['assigned_teacher_id']) ? '↻ Reassign' : '✓ Assign teacher' ?>
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="card mt-3">
                <h3>👩‍🏫 Assigned to</h3>
                <div class="divider"></div>
                <?php if (!empty($request['teacher_name'])): ?>
                    <div class="kv"><span>Teacher</span><span><strong><?= Helper::e($request['teacher_name']) ?></strong></span></div>
                    <?php if (!empty($request['assigned_at'])): ?>
                        <div class="kv"><span>Assigned on</span><span><?= date('d M Y H:i', strtotime($request['assigned_at'])) ?></span></div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="muted">No teacher assigned.</p>
                <?php endif; ?>
                <?php if (!empty($request['teacher_notes'])): ?>
                    <h3 style="margin-top:1.5rem;font-size:1rem">Teacher notes</h3>
                    <p style="line-height:1.7;margin-top:.5rem"><?= nl2br(Helper::e($request['teacher_notes'])) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ============ HISTORY ============ -->
        <?php if ($logs): ?>
            <div class="card mt-3">
                <h3>📋 Activity history</h3>
                <div class="divider"></div>
                <?php foreach ($logs as $log): ?>
                    <div class="kv">
                        <div>
                            <strong><?= Helper::e(ucfirst(str_replace('_', ' ', $log['action']))) ?></strong>
                            <?php if (!empty($log['note'])): ?>
                                <div class="muted" style="font-size:.82rem"><?= Helper::e($log['note']) ?></div>
                            <?php endif; ?>
                        </div>
                        <span class="muted" style="font-size:.78rem"><?= date('d M H:i', strtotime($log['created_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
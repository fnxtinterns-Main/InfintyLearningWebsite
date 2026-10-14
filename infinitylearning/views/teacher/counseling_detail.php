<?php
use App\Auth;
use App\Counseling;
use App\Helper;
use App\Session;
use App\Database;

// Only teachers can access
Auth::requireLogin();
if (Auth::role() !== 'teacher') {
    http_response_code(403);
    exit('Forbidden');
}

$me = Auth::user();
$id = (int)($_GET['id'] ?? 0);

// Fetch the request
$request = Counseling::find($id);
if (!$request) { http_response_code(404); require VIEW_PATH . '/404.php'; return; }

// Security: teacher can only view requests assigned to them
if ((int)$request['assigned_teacher_id'] !== (int)$me['id']) {
    http_response_code(403);
    exit('This request is not assigned to you.');
}

// Load activity history
$db = Database::connect();
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

$pageTitle = 'Counseling #' . $id . ' — Teacher';
require VIEW_PATH . '/layouts/header.php';

$ratings = [
    'rating_concentration' => ['Concentration', '📊'],
    'rating_cognitive'     => ['Cognitive Skills', '🧠'],
    'rating_comprehension' => ['Comprehension', '📖'],
    'rating_creativity'    => ['Creativity', '🎨'],
    'rating_confidence'    => ['Confidence', '💪'],
    'rating_communication' => ['Communication', '💬'],
];
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <div>

        <a href="/teacher/counseling" class="muted">← Back to counseling</a>

        <!-- ============ HEADER ============ -->
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
                        <?php if (!empty($request['school_name'])): ?>
                            · <?= Helper::e($request['school_name']) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <span class="badge badge-<?=
                        $request['status'] === 'assigned'    ? 'info' :
                        ($request['status'] === 'in_progress' ? 'primary' :
                        ($request['status'] === 'completed'   ? 'success' : 'muted'))
                    ?>">
                        <?= Helper::e(str_replace('_', ' ', $request['status'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- ============ SELF-RATED PROFILE ============ -->
        <div class="card mt-3">
            <h3>📊 Student's self-rated profile</h3>
            <p class="muted" style="font-size:.85rem">1 = low · 6 = high — use these to guide the conversation.</p>
            <div class="divider"></div>

            <?php foreach ($ratings as $key => [$label, $icon]):
                $val = (int)$request[$key];
                $pct = (int)round($val / 6 * 100);
            ?>
                <div style="margin-bottom:.85rem">
                    <div class="flex between" style="margin-bottom:.25rem">
                        <span style="font-weight:700;font-size:.9rem"><?= $icon ?> <?= $label ?></span>
                        <span class="muted" style="font-size:.85rem"><strong><?= $val ?></strong>/6</span>
                    </div>
                    <div style="height:10px;border-radius:99px;background:var(--surface-2);overflow:hidden">
                        <div style="height:100%;width:<?= $pct ?>%;background:var(--il-grad)"></div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($request['reason'])): ?>
                <div class="divider"></div>
                <h3 style="font-size:1rem">💭 What the student wants to discuss</h3>
                <p style="line-height:1.7;margin-top:.5rem"><?= nl2br(Helper::e($request['reason'])) ?></p>
            <?php endif; ?>

            <?php if (!empty($request['preferred_time'])): ?>
                <div class="kv"><span>Preferred time</span><span><?= Helper::e($request['preferred_time']) ?></span></div>
            <?php endif; ?>
            <?php if (!empty($request['city']) || !empty($request['state'])): ?>
                <div class="kv"><span>Location</span><span><?= Helper::e(trim(($request['city'] ?? '') . ', ' . ($request['state'] ?? ''), ', ')) ?></span></div>
            <?php endif; ?>
            <div class="kv"><span>Requested</span><span><?= date('d M Y H:i', strtotime($request['created_at'])) ?></span></div>
        </div>

        <!-- ============ ACTIONS ============ -->
        <?php if (in_array($request['status'], ['assigned','in_progress'], true)): ?>
            <div class="card mt-3">
                <h3>⚡ Session actions</h3>
                <div class="divider"></div>

                <?php if ($request['status'] === 'assigned'): ?>
                    <form method="post" action="/teacher/counseling-action-handler.php" style="margin-bottom:1rem">
                        <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                        <input type="hidden" name="request_id" value="<?= (int)$id ?>">
                        <input type="hidden" name="action" value="start">
                        <button class="btn btn-primary btn-lg">▶ Mark as in progress</button>
                    </form>
                <?php endif; ?>

                <form method="post" action="/teacher/counseling-action-handler.php">
                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                    <input type="hidden" name="request_id" value="<?= (int)$id ?>">
                    <input type="hidden" name="action" value="complete">

                    <div class="form-group">
                        <label>Session notes / outcome *</label>
                        <textarea name="notes" rows="6" required
                                  placeholder="Summarise your conversation, what the student needed, and what you advised..."><?= Helper::e($request['teacher_notes'] ?? '') ?></textarea>
                        <small>These notes are visible to the school admin and the student.</small>
                    </div>
                    <button class="btn btn-success btn-lg">✓ Mark as completed</button>
                </form>

                <div class="divider"></div>

                <form method="post" action="/teacher/counseling-action-handler.php"
                      onsubmit="return confirm('Cancel this counseling request? The school admin will be notified.')">
                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                    <input type="hidden" name="request_id" value="<?= (int)$id ?>">
                    <input type="hidden" name="action" value="cancel">
                    <button class="btn btn-ghost btn-sm">🚫 Cancel request</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Already completed or cancelled — read-only view -->
            <div class="card mt-3">
                <h3><?= $request['status'] === 'completed' ? '✅ Session completed' : '🚫 Request cancelled' ?></h3>
                <div class="divider"></div>
                <?php if (!empty($request['completed_at'])): ?>
                    <div class="kv"><span>Completed on</span><span><?= date('d M Y H:i', strtotime($request['completed_at'])) ?></span></div>
                <?php endif; ?>
                <?php if (!empty($request['teacher_notes'])): ?>
                    <h3 style="margin-top:1rem;font-size:1rem">Your notes</h3>
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
                                <div class="muted" style="font-size:.82rem"><?= Helper::e(mb_substr($log['note'], 0, 160)) ?></div>
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
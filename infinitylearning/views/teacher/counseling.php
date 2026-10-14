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
$status = $_GET['status'] ?? '';

// Fetch requests assigned to this teacher (optionally filtered by status)
$requests = Counseling::forTeacher((int)$me['id'], $status ?: null);

// Build stats for the top tiles
$allRequests = Counseling::forTeacher((int)$me['id']);
$counts = ['assigned'=>0, 'in_progress'=>0, 'completed'=>0, 'cancelled'=>0];
foreach ($allRequests as $r) {
    if (isset($counts[$r['status']])) $counts[$r['status']]++;
}

$types = Counseling::types();

$pageTitle = 'Counseling — Teacher';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <div>

        <div class="dashboard-header">
            <div class="dashboard-header-text">
                <span class="kicker">🧭 COUNSELING</span>
                <h1>Assigned Counseling</h1>
                <p class="muted">Students your school admin has assigned to you for counseling.</p>
            </div>
        </div>

        <!-- ============ STAT TILES ============ -->
        <div class="grid grid-4">
            <div class="stat" style="border-left-color:#2563EB">
                <span class="ico">📥</span>
                <span class="n"><?= (int)$counts['assigned'] ?></span>
                <span class="l">Assigned</span>
            </div>
            <div class="stat" style="border-left-color:#F59E0B">
                <span class="ico">🔄</span>
                <span class="n"><?= (int)$counts['in_progress'] ?></span>
                <span class="l">In progress</span>
            </div>
            <div class="stat" style="border-left-color:#16A34A">
                <span class="ico">✅</span>
                <span class="n"><?= (int)$counts['completed'] ?></span>
                <span class="l">Completed</span>
            </div>
            <div class="stat">
                <span class="ico">🚫</span>
                <span class="n"><?= (int)$counts['cancelled'] ?></span>
                <span class="l">Cancelled</span>
            </div>
        </div>

        <!-- ============ FILTERS ============ -->
        <div class="filters mt-3">
            <a href="?status="             class="<?= $status === ''            ? 'active' : '' ?>">All</a>
            <a href="?status=assigned"     class="<?= $status === 'assigned'    ? 'active' : '' ?>">Assigned</a>
            <a href="?status=in_progress"  class="<?= $status === 'in_progress' ? 'active' : '' ?>">In progress</a>
            <a href="?status=completed"    class="<?= $status === 'completed'   ? 'active' : '' ?>">Completed</a>
            <a href="?status=cancelled"    class="<?= $status === 'cancelled'   ? 'active' : '' ?>">Cancelled</a>
        </div>

        <!-- ============ TABLE ============ -->
        <?php if (!$requests): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🧭</div>
                <h3>No counseling requests assigned yet</h3>
                <p>When your school admin assigns students to you, they'll appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Avg rating</th>
                            <th>Status</th>
                            <th>Assigned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($requests as $r):
                        // Compute average of the 6 ratings
                        $avg = round((
                            $r['rating_concentration'] + $r['rating_cognitive'] +
                            $r['rating_comprehension'] + $r['rating_creativity'] +
                            $r['rating_confidence'] + $r['rating_communication']
                        ) / 6, 1);

                        $t = $types[$r['counseling_type']] ?? ['label'=>$r['counseling_type'],'icon'=>'📋','color'=>'#999'];

                        $statusClass =
                            $r['status'] === 'assigned'    ? 'info' :
                            ($r['status'] === 'in_progress' ? 'primary' :
                            ($r['status'] === 'completed'   ? 'success' : 'muted'));
                    ?>
                        <tr>
                            <td><?= (int)$r['id'] ?></td>
                            <td>
                                <div class="flex">
                                    <?php if (!empty($r['student_avatar'])): ?>
                                        <img class="avatar" src="<?= Helper::e($r['student_avatar']) ?>" alt="">
                                    <?php else: ?>
                                        <span class="avatar avatar-initial" data-role="student">
                                            <?= Helper::e(mb_strtoupper(mb_substr($r['full_name'], 0, 1))) ?>
                                        </span>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= Helper::e($r['full_name']) ?></strong>
                                        <div class="muted" style="font-size:.78rem">
                                            <?= Helper::e($r['current_class'] ?: '—') ?>
                                            <?php if (!empty($r['school_name'])): ?> · <?= Helper::e($r['school_name']) ?><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background:<?= Helper::e($t['color']) ?>22;color:<?= Helper::e($t['color']) ?>">
                                    <?= $t['icon'] ?> <?= Helper::e($t['label']) ?>
                                </span>
                            </td>
                            <td><strong><?= $avg ?>/6</strong></td>
                            <td>
                                <span class="badge badge-<?= $statusClass ?>">
                                    <?= Helper::e(str_replace('_', ' ', $r['status'])) ?>
                                </span>
                            </td>
                            <td><?= $r['assigned_at'] ? date('d M Y', strtotime($r['assigned_at'])) : '—' ?></td>
                            <td>
                                <a class="btn btn-primary btn-sm" href="/teacher/counseling-detail?id=<?= (int)$r['id'] ?>">Open</a>
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
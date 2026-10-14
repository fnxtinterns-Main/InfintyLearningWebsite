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

$type   = $_GET['type']   ?? '';
$status = $_GET['status'] ?? '';

$requests = Counseling::forSchool($schoolId, $status ?: null);

// Filter by type manually if provided
if ($type) {
    $requests = array_values(array_filter($requests, fn($r) => $r['counseling_type'] === $type));
}

// Count per type for the filter pills
$allRequests = Counseling::forSchool($schoolId);
$countsByType = ['academics'=>0, 'career'=>0, 'admission'=>0, 'psychometric'=>0];
$countsByStatus = ['pending'=>0, 'assigned'=>0, 'in_progress'=>0, 'completed'=>0, 'cancelled'=>0];
foreach ($allRequests as $r) {
    if (isset($countsByType[$r['counseling_type']])) $countsByType[$r['counseling_type']]++;
    if (isset($countsByStatus[$r['status']])) $countsByStatus[$r['status']]++;
}

// Load active teachers of this school for the assign dropdown
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

$types = Counseling::types();

$pageTitle = 'Counseling — ' . $school['name'];
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>

        <div class="dashboard-header">
            <div class="dashboard-header-text">
                <span class="kicker">🧭 COUNSELING</span>
                <h1>Counseling Requests</h1>
                <p class="muted">
                    Students of <strong><?= Helper::e($school['name']) ?></strong> have requested counseling.
                    Assign the right teacher to each request.
                </p>
            </div>
        </div>

        <!-- ============ STAT TILES ============ -->
        <div class="grid grid-4">
            <div class="stat"><span class="ico">⏳</span><span class="n"><?= (int)$countsByStatus['pending'] ?></span><span class="l">Pending</span></div>
            <div class="stat" style="border-left-color:#2563EB"><span class="ico">👩‍🏫</span><span class="n"><?= (int)$countsByStatus['assigned'] ?></span><span class="l">Assigned</span></div>
            <div class="stat" style="border-left-color:#F59E0B"><span class="ico">🔄</span><span class="n"><?= (int)$countsByStatus['in_progress'] ?></span><span class="l">In progress</span></div>
            <div class="stat" style="border-left-color:#16A34A"><span class="ico">✅</span><span class="n"><?= (int)$countsByStatus['completed'] ?></span><span class="l">Completed</span></div>
        </div>

        <!-- ============ FILTERS ============ -->
        <div class="filters mt-3">
            <a href="?status=" class="<?= $status === '' ? 'active' : '' ?>">All statuses</a>
            <a href="?status=pending"     class="<?= $status === 'pending'     ? 'active' : '' ?>">Pending</a>
            <a href="?status=assigned"    class="<?= $status === 'assigned'    ? 'active' : '' ?>">Assigned</a>
            <a href="?status=in_progress" class="<?= $status === 'in_progress' ? 'active' : '' ?>">In progress</a>
            <a href="?status=completed"   class="<?= $status === 'completed'   ? 'active' : '' ?>">Completed</a>
        </div>

        <div class="filters">
            <a href="?<?= $status ? 'status=' . urlencode($status) : '' ?>" class="<?= $type === '' ? 'active' : '' ?>">All types</a>
            <?php foreach ($types as $slug => $t): ?>
                <a href="?type=<?= urlencode($slug) ?><?= $status ? '&status=' . urlencode($status) : '' ?>"
                   class="<?= $type === $slug ? 'active' : '' ?>">
                    <?= $t['icon'] ?> <?= Helper::e($t['label']) ?>
                    <?php if (!empty($countsByType[$slug])): ?>
                        <span class="filter-count"><?= (int)$countsByType[$slug] ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- ============ TABLE ============ -->
        <?php if (!$requests): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🧭</div>
                <h3>No counseling requests match these filters</h3>
                <p>When students submit requests from <code>/counseling</code>, they'll appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Ratings (avg)</th>
                            <th>Status</th>
                            <th>Assigned to</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($requests as $r):
                        $avg = round((
                            $r['rating_concentration'] +
                            $r['rating_cognitive'] +
                            $r['rating_comprehension'] +
                            $r['rating_creativity'] +
                            $r['rating_confidence'] +
                            $r['rating_communication']
                        ) / 6, 1);

                        $t = $types[$r['counseling_type']] ?? ['label'=>$r['counseling_type'],'icon'=>'📋','color'=>'#999'];
                        $statusClass =
                            $r['status'] === 'pending'     ? 'primary' :
                            ($r['status'] === 'assigned'    ? 'info' :
                            ($r['status'] === 'in_progress' ? 'primary' :
                            ($r['status'] === 'completed'   ? 'success' : 'muted')));
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
                                        <div class="muted" style="font-size:.78rem"><?= Helper::e($r['current_class'] ?: '—') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background:<?= Helper::e($t['color']) ?>22;color:<?= Helper::e($t['color']) ?>">
                                    <?= $t['icon'] ?> <?= Helper::e($t['label']) ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-weight:700"><?= $avg ?>/6</span>
                                <div class="muted" style="font-size:.7rem">
                                    C:<?= (int)$r['rating_concentration'] ?>
                                    · G:<?= (int)$r['rating_cognitive'] ?>
                                    · U:<?= (int)$r['rating_comprehension'] ?>
                                    · R:<?= (int)$r['rating_creativity'] ?>
                                    · F:<?= (int)$r['rating_confidence'] ?>
                                    · M:<?= (int)$r['rating_communication'] ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?= $statusClass ?>">
                                    <?= Helper::e(str_replace('_', ' ', $r['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($r['teacher_name'])): ?>
                                    <strong><?= Helper::e($r['teacher_name']) ?></strong>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <a class="btn btn-primary btn-sm" href="/school/counseling-detail?id=<?= (int)$r['id'] ?>">Open</a>
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
<?php
use App\Auth;
use App\Helper;
use App\Session;
use App\Database;

Auth::requireSuperAdmin();

$status   = $_GET['status'] ?? 'pending';
$schoolId = (int)($_GET['school'] ?? 0);

$schools = Database::connect()
    ->query("SELECT id, name, code FROM schools ORDER BY name")
    ->fetchAll();

$db = Database::connect();
$sql = "
    SELECT u.id, u.email, u.phone, u.status, u.created_at,
           u.school_id, sc.name AS school_name, sc.code AS school_code,
           t.full_name, t.gender, t.city, t.state,
           t.expert_field, t.years_experience, t.profile_image
    FROM users u
    LEFT JOIN teachers t ON t.user_id = u.id
    LEFT JOIN schools  sc ON sc.id = u.school_id
    WHERE u.role = 'teacher'
";
$params = [];
if ($status !== '') { $sql .= " AND u.status = ?";    $params[] = $status; }
if ($schoolId)      { $sql .= " AND u.school_id = ?"; $params[] = $schoolId; }
$sql .= " ORDER BY u.created_at DESC LIMIT 300";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$teachers = $stmt->fetchAll();

$pageTitle = 'All Teachers — Admin';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/admin/_sidebar.php'; ?>
    <div>
        <div class="dashboard-header">
            <div class="dashboard-header-text">
                <span class="kicker">👩‍🏫 TEACHERS</span>
                <h1>All Teachers</h1>
                <p class="muted">Every teacher across every school. School admins handle their own approvals.</p>
            </div>
            <span class="pill"><?= count($teachers) ?> shown</span>
        </div>

        <div class="filters">
            <a href="?status=pending"   class="<?= $status === 'pending'   ? 'active' : '' ?>">Pending</a>
            <a href="?status=active"    class="<?= $status === 'active'    ? 'active' : '' ?>">Approved</a>
            <a href="?status=rejected"  class="<?= $status === 'rejected'  ? 'active' : '' ?>">Rejected</a>
            <a href="?status=suspended" class="<?= $status === 'suspended' ? 'active' : '' ?>">Suspended</a>
            <a href="?status="          class="<?= $status === ''          ? 'active' : '' ?>">All</a>
        </div>

        <form method="get" class="search-box" style="margin-bottom:1rem;max-width:420px">
            <?php if ($status): ?><input type="hidden" name="status" value="<?= Helper::e($status) ?>"><?php endif; ?>
            <span class="search-icon">🏫</span>
            <select name="school" onchange="this.form.submit()" style="flex:1;border:none;outline:none;background:transparent;font-family:inherit;font-size:.9rem">
                <option value="0">All schools</option>
                <?php foreach ($schools as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= $schoolId === (int)$s['id'] ? 'selected' : '' ?>>
                        <?= Helper::e($s['name']) ?><?= $s['code'] ? ' (' . Helper::e($s['code']) . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if (!$teachers): ?>
            <div class="empty-state">
                <div class="empty-state-icon">👩‍🏫</div>
                <h3>No teachers match the filters</h3>
                <p>Try changing the status or school filter.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>#</th><th>Teacher</th><th>School</th><th>Expertise</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($teachers as $t): ?>
                        <tr>
                            <td><?= (int)$t['id'] ?></td>
                            <td>
                                <div class="flex">
                                    <?php if (!empty($t['profile_image'])): ?>
                                        <img class="avatar" src="<?= Helper::e($t['profile_image']) ?>" alt="">
                                    <?php else: ?>
                                        <span class="avatar avatar-initial" data-role="teacher">
                                            <?= Helper::e(mb_strtoupper(mb_substr($t['full_name'] ?: $t['email'], 0, 1))) ?>
                                        </span>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= Helper::e($t['full_name'] ?: '—') ?></strong><br>
                                        <span class="muted" style="font-size:.82rem"><?= Helper::e($t['email']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($t['school_name'])): ?>
                                    <a href="/admin/teachers?school=<?= (int)$t['school_id'] ?>" style="color:inherit">
                                        <strong><?= Helper::e($t['school_name']) ?></strong>
                                    </a>
                                    <?php if (!empty($t['school_code'])): ?>
                                        <div class="muted" style="font-size:.78rem"><?= Helper::e($t['school_code']) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= Helper::e($t['expert_field'] ?: '—') ?>
                                <?php if ($t['years_experience']): ?>
                                    <div class="muted" style="font-size:.78rem"><?= (int)$t['years_experience'] ?> yrs</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $t['status'] === 'active' ? 'success' : ($t['status'] === 'pending' ? 'primary' : ($t['status'] === 'suspended' ? 'muted' : 'danger')) ?>">
                                    <?= Helper::e($t['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                            <td>
                                <div class="flex" style="gap:.3rem;flex-wrap:wrap">
                                    <a class="btn btn-ghost btn-sm" href="/admin/teacher-view?id=<?= (int)$t['id'] ?>">View</a>
                                    <?php if ($t['status'] !== 'active'): ?>
                                        <form method="post" action="/admin/teacher-action-handler.php" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$t['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($t['status'] !== 'rejected'): ?>
                                        <form method="post" action="/admin/teacher-action-handler.php" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$t['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
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
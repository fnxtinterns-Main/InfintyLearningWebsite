<?php
use App\Auth;
use App\Helper;
use App\Session;
use App\Database;

Auth::requireSuperAdmin();

$role     = $_GET['role']   ?? '';
$status   = $_GET['status'] ?? '';
$schoolId = (int)($_GET['school'] ?? 0);

$schools = Database::connect()
    ->query("SELECT id, name, code FROM schools ORDER BY name")
    ->fetchAll();

$db = Database::connect();
$sql = "
    SELECT u.id, u.email, u.phone, u.role, u.status, u.created_at, u.last_login_at,
           u.school_id, sc.name AS school_name, sc.code AS school_code,
           COALESCE(s.full_name, t.full_name, 'Super Admin') AS display_name,
           COALESCE(s.city, t.city) AS city,
           COALESCE(s.state, t.state) AS state,
           COALESCE(s.profile_image, t.profile_image) AS avatar
    FROM users u
    LEFT JOIN students s ON s.user_id = u.id
    LEFT JOIN teachers t ON t.user_id = u.id
    LEFT JOIN schools  sc ON sc.id = u.school_id
    WHERE 1=1
";
$params = [];
if ($role)     { $sql .= " AND u.role = ?";      $params[] = $role; }
if ($status)   { $sql .= " AND u.status = ?";    $params[] = $status; }
if ($schoolId) { $sql .= " AND u.school_id = ?"; $params[] = $schoolId; }
$sql .= " ORDER BY u.created_at DESC LIMIT 300";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'All Users — Admin';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/admin/_sidebar.php'; ?>
    <div>
        <div class="dashboard-header">
            <div class="dashboard-header-text">
                <span class="kicker">👥 ALL USERS</span>
                <h1>All Users</h1>
                <p class="muted">Every account across every school. Filter by role, status or school.</p>
            </div>
            <span class="pill"><?= count($users) ?> shown</span>
        </div>

        <div class="filters">
            <a href="?role=" class="<?= $role === '' ? 'active' : '' ?>">All roles</a>
            <a href="?role=student" class="<?= $role === 'student' ? 'active' : '' ?>">Students</a>
            <a href="?role=teacher" class="<?= $role === 'teacher' ? 'active' : '' ?>">Teachers</a>
            <a href="?role=admin" class="<?= $role === 'admin' ? 'active' : '' ?>">Admins</a>
            <a href="?status=active" class="<?= $status === 'active' ? 'active' : '' ?>">Active</a>
            <a href="?status=pending" class="<?= $status === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="?status=suspended" class="<?= $status === 'suspended' ? 'active' : '' ?>">Suspended</a>
        </div>

        <form method="get" class="search-box" style="margin-bottom:1rem;max-width:420px">
            <?php if ($role):   ?><input type="hidden" name="role" value="<?= Helper::e($role) ?>"><?php endif; ?>
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

        <?php if (!$users): ?>
            <div class="empty-state">
                <div class="empty-state-icon">👥</div>
                <h3>No users match the filters</h3>
                <p>Try changing the filters above.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>#</th><th>User</th><th>School</th><th>Role</th><th>Status</th><th>Last login</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td>
                                <div class="flex">
                                    <?php if (!empty($u['avatar'])): ?>
                                        <img class="avatar" src="<?= Helper::e($u['avatar']) ?>" alt="">
                                    <?php else: ?>
                                        <span class="avatar avatar-initial" data-role="<?= Helper::e($u['role']) ?>">
                                            <?= Helper::e(mb_strtoupper(mb_substr($u['display_name'], 0, 1))) ?>
                                        </span>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= Helper::e($u['display_name']) ?></strong><br>
                                        <span class="muted" style="font-size:.82rem"><?= Helper::e($u['email']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($u['school_name'])): ?>
                                    <a href="/admin/users?school=<?= (int)$u['school_id'] ?>" style="color:inherit">
                                        <strong><?= Helper::e($u['school_name']) ?></strong>
                                    </a>
                                    <?php if (!empty($u['school_code'])): ?>
                                        <div class="muted" style="font-size:.78rem"><?= Helper::e($u['school_code']) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="muted" style="font-size:.85rem">—</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="pill"><?= Helper::e($u['role']) ?></span></td>
                            <td>
                                <span class="badge badge-<?= $u['status'] === 'active' ? 'success' : ($u['status'] === 'pending' ? 'primary' : ($u['status'] === 'suspended' ? 'muted' : 'danger')) ?>">
                                    <?= Helper::e($u['status']) ?>
                                </span>
                            </td>
                            <td><?= $u['last_login_at'] ? date('d M Y', strtotime($u['last_login_at'])) : '—' ?></td>
                            <td>
                                <div class="flex" style="gap:.3rem;flex-wrap:wrap">
                                    <?php if ($u['role'] === 'student'): ?>
                                        <a class="btn btn-ghost btn-sm" href="/admin/student-view?id=<?= (int)$u['id'] ?>">View</a>
                                    <?php elseif ($u['role'] === 'teacher'): ?>
                                        <a class="btn btn-ghost btn-sm" href="/admin/teacher-view?id=<?= (int)$u['id'] ?>">View</a>
                                    <?php endif; ?>
                                    <?php if ($u['role'] !== 'admin'): ?>
                                        <form method="post" action="/admin/teacher-action-handler.php" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <input type="hidden" name="action" value="<?= $u['status'] === 'active' ? 'suspend' : 'reactivate' ?>">
                                            <button class="btn <?= $u['status'] === 'active' ? 'btn-ghost' : 'btn-success' ?> btn-sm">
                                                <?= $u['status'] === 'active' ? 'Suspend' : 'Reactivate' ?>
                                            </button>
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
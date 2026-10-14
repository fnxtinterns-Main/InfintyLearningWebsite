<?php
use App\Auth;
use App\Helper;
use App\School;
use App\Database;

Auth::requireSuperAdmin();

$id = (int)($_GET['id'] ?? 0);
$db = Database::connect();

$stmt = $db->prepare("
    SELECT u.*, s.*
    FROM users u
    LEFT JOIN students s ON s.user_id = u.id
    WHERE u.id = ? AND u.role = 'student'
    LIMIT 1
");
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { http_response_code(404); require VIEW_PATH . '/404.php'; return; }

$school = !empty($student['school_id']) ? School::find((int)$student['school_id']) : null;

$parents = $db->prepare("
    SELECT p.* FROM parents p
    JOIN student_parents sp ON sp.parent_id = p.id
    WHERE sp.student_user_id = ?
");
$parents->execute([$id]);
$parentRows = $parents->fetchAll();

$pageTitle = 'Student — ' . ($student['full_name'] ?: $student['email']);
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/admin/_sidebar.php'; ?>
    <div>
        <a href="/admin/users" class="muted">← Back to users</a>
        <div class="card mt-2">
            <div class="flex" style="gap:1.25rem;flex-wrap:wrap">
                <?php if ($student['profile_image']): ?>
                    <img class="avatar avatar-lg" src="<?= Helper::e($student['profile_image']) ?>" alt="">
                <?php else: ?>
                    <span class="avatar avatar-lg avatar-initial" data-role="student">
                        <?= Helper::e(mb_strtoupper(mb_substr($student['full_name'] ?: $student['email'], 0, 1))) ?>
                    </span>
                <?php endif; ?>
                <div>
                    <h1 style="margin:0"><?= Helper::e($student['full_name'] ?: '—') ?></h1>
                    <p class="muted"><?= Helper::e($student['email']) ?> · <?= Helper::e($student['phone']) ?></p>
                    <div class="flex" style="gap:.4rem;flex-wrap:wrap">
                        <span class="badge badge-<?= $student['status'] === 'active' ? 'success' : 'primary' ?>">
                            <?= Helper::e($student['status']) ?>
                        </span>
                        <?php if ($school): ?>
                            <a href="/admin/users?school=<?= (int)$school['id'] ?>" class="badge badge-brand" style="text-decoration:none">
                                🏫 <?= Helper::e($school['name']) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="flex mt-2">
                <a href="/student?id=<?= (int)$student['id'] ?>" class="btn btn-primary btn-sm">
                    Open development profile →
                </a>
            </div>

            <div class="divider"></div>

            <h3>Profile</h3>
            <?php
            $rows = [
                'School'        => $school ? $school['name'] : '—',
                'Gender'        => $student['gender'] ? ucfirst(str_replace('_',' ',$student['gender'])) : '—',
                'Age'           => $student['age'] ?: '—',
                'Board'         => $student['school_board'] ?: '—',
                'Class'         => $student['current_class'] ?: '—',
                'Academic year' => $student['academic_year'] ?: '—',
                'City'          => $student['city'] ?: '—',
                'State'         => $student['state'] ?: '—',
                'Interests'     => $student['interests'] ?: '—',
                'Registered'    => date('d M Y', strtotime($student['created_at'])),
                'Last login'    => $student['last_login_at'] ? date('d M Y H:i', strtotime($student['last_login_at'])) : '—',
            ];
            foreach ($rows as $k => $v): ?>
                <div class="kv"><span><?= $k ?></span><span><?= Helper::e((string)$v) ?></span></div>
            <?php endforeach; ?>

            <?php if ($parentRows): ?>
                <h3 class="mt-3">Parent / Guardian</h3>
                <?php foreach ($parentRows as $p): ?>
                    <div class="kv"><span>Name</span><span><?= Helper::e($p['full_name']) ?></span></div>
                    <div class="kv"><span>Relationship</span><span><?= Helper::e(ucfirst($p['relationship'])) ?></span></div>
                    <div class="kv"><span>Phone</span><span><?= Helper::e($p['phone']) ?></span></div>
                    <div class="kv"><span>Email</span><span><?= Helper::e($p['email'] ?: '—') ?></span></div>
                    <div class="kv"><span>Occupation</span><span><?= Helper::e($p['occupation'] ?: '—') ?></span></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="flex mt-3">
                <?php if ($student['status'] === 'active'): ?>
                    <form method="post" action="/admin/teacher-action-handler.php">
                        <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                        <input type="hidden" name="user_id" value="<?= (int)$student['id'] ?>">
                        <input type="hidden" name="action" value="suspend">
                        <button class="btn btn-ghost">Suspend</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="/admin/teacher-action-handler.php">
                        <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                        <input type="hidden" name="user_id" value="<?= (int)$student['id'] ?>">
                        <input type="hidden" name="action" value="reactivate">
                        <button class="btn btn-success">Reactivate</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
<?php
use App\Auth;
use App\Helper;
use App\School;
use App\Database;

Auth::requireSuperAdmin();

$id = (int)($_GET['id'] ?? 0);
$db = Database::connect();
$stmt = $db->prepare("
    SELECT u.*, t.full_name, t.gender, t.school_name, t.city, t.state,
           t.expert_field, t.years_experience, t.additional_expertise,
           t.bio, t.profile_image, t.approved_at
    FROM users u
    LEFT JOIN teachers t ON t.user_id = u.id
    WHERE u.id = ? AND u.role = 'teacher'
    LIMIT 1
");
$stmt->execute([$id]);
$teacher = $stmt->fetch();
if (!$teacher) { http_response_code(404); require VIEW_PATH . '/404.php'; return; }

$school = !empty($teacher['school_id']) ? School::find((int)$teacher['school_id']) : null;

$pageTitle = 'Teacher — ' . ($teacher['full_name'] ?: $teacher['email']);
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/admin/_sidebar.php'; ?>
    <div>
        <a href="/admin/teachers" class="muted">← Back to teachers</a>
        <div class="card mt-2">
            <div class="flex" style="gap:1.25rem;flex-wrap:wrap">
                <?php if ($teacher['profile_image']): ?>
                    <img class="avatar avatar-lg" src="<?= Helper::e($teacher['profile_image']) ?>" alt="">
                <?php else: ?>
                    <span class="avatar avatar-lg avatar-initial" data-role="teacher">
                        <?= Helper::e(mb_strtoupper(mb_substr($teacher['full_name'] ?: $teacher['email'], 0, 1))) ?>
                    </span>
                <?php endif; ?>
                <div>
                    <h1 style="margin:0"><?= Helper::e($teacher['full_name'] ?: '—') ?></h1>
                    <p class="muted"><?= Helper::e($teacher['email']) ?> · <?= Helper::e($teacher['phone']) ?></p>
                    <div class="flex" style="gap:.4rem;flex-wrap:wrap">
                        <span class="badge badge-<?= $teacher['status'] === 'active' ? 'success' : ($teacher['status'] === 'pending' ? 'primary' : 'danger') ?>">
                            <?= Helper::e($teacher['status']) ?>
                        </span>
                        <?php if ($school): ?>
                            <a href="/admin/teachers?school=<?= (int)$school['id'] ?>" class="badge badge-brand" style="text-decoration:none">
                                🏫 <?= Helper::e($school['name']) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <h3>Profile</h3>
            <?php
            $rows = [
                'School'                => $school ? $school['name'] : '—',
                'Gender'                => $teacher['gender'] ? ucfirst(str_replace('_',' ',$teacher['gender'])) : '—',
                'City'                  => $teacher['city'] ?: '—',
                'State'                 => $teacher['state'] ?: '—',
                'Expert field'          => $teacher['expert_field'] ?: '—',
                'Experience'            => $teacher['years_experience'] ? $teacher['years_experience'].' years' : '—',
                'Additional expertise'  => $teacher['additional_expertise'] ?: '—',
                'Registered'            => date('d M Y', strtotime($teacher['created_at'])),
                'Approved at'           => $teacher['approved_at'] ? date('d M Y', strtotime($teacher['approved_at'])) : '—',
                'Last login'            => $teacher['last_login_at'] ? date('d M Y H:i', strtotime($teacher['last_login_at'])) : '—',
            ];
            foreach ($rows as $k => $v): ?>
                <div class="kv"><span><?= $k ?></span><span><?= Helper::e((string)$v) ?></span></div>
            <?php endforeach; ?>

            <?php if (!empty($teacher['bio'])): ?>
                <h3 class="mt-3">Bio</h3>
                <p><?= nl2br(Helper::e($teacher['bio'])) ?></p>
            <?php endif; ?>

            <div class="flex mt-3">
                <form method="post" action="/admin/teacher-action-handler.php">
                    <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                    <input type="hidden" name="user_id" value="<?= (int)$teacher['id'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <button class="btn btn-success"<?= $teacher['status'] === 'active' ? ' disabled' : '' ?>>Approve</button>
                </form>
                <form method="post" action="/admin/teacher-action-handler.php">
                    <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                    <input type="hidden" name="user_id" value="<?= (int)$teacher['id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <button class="btn btn-danger"<?= $teacher['status'] === 'rejected' ? ' disabled' : '' ?>>Reject</button>
                </form>
                <?php if ($teacher['status'] === 'active'): ?>
                    <form method="post" action="/admin/teacher-action-handler.php">
                        <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                        <input type="hidden" name="user_id" value="<?= (int)$teacher['id'] ?>">
                        <input type="hidden" name="action" value="suspend">
                        <button class="btn btn-ghost">Suspend</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
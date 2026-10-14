<?php
use App\Auth;
use App\School;
use App\Helper;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);
if (!$school) { Auth::logout(); Helper::redirect('/login'); }

$status = $_GET['status'] ?? 'pending';
$students = School::users($schoolId, 'student', $status ?: null);

$pageTitle = 'Students — ' . $school['name'];
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>
        <div class="dashboard-header">
            <div class="dashboard-header-text">
                <span class="kicker">👩‍🎓 STUDENTS</span>
                <h1>Manage your students</h1>
                <p class="muted">Approve students who registered under <strong><?= Helper::e($school['name']) ?></strong>.</p>
            </div>
            <span class="pill"><?= count($students) ?> shown</span>
        </div>

        <div class="filters">
            <a href="?status=pending"   class="<?= $status === 'pending'   ? 'active' : '' ?>">Pending</a>
            <a href="?status=active"    class="<?= $status === 'active'    ? 'active' : '' ?>">Approved</a>
            <a href="?status=rejected"  class="<?= $status === 'rejected'  ? 'active' : '' ?>">Rejected</a>
            <a href="?status=suspended" class="<?= $status === 'suspended' ? 'active' : '' ?>">Suspended</a>
            <a href="?status="          class="<?= $status === ''          ? 'active' : '' ?>">All</a>
        </div>

        <?php if (!$students): ?>
            <div class="empty-state">
                <div class="empty-state-icon">👩‍🎓</div>
                <h3>No students in this list</h3>
                <p>When students register under your school, they'll appear here for approval.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>#</th><th>Student</th><th>Class</th><th>Location</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td><?= (int)$s['id'] ?></td>
                            <td>
                                <div class="flex">
                                    <?php if (!empty($s['avatar'])): ?>
                                        <img class="avatar" src="<?= Helper::e($s['avatar']) ?>" alt="">
                                    <?php else: ?>
                                        <span class="avatar avatar-initial" data-role="student">
                                            <?= Helper::e(mb_strtoupper(mb_substr($s['full_name'] ?: $s['email'], 0, 1))) ?>
                                        </span>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= Helper::e($s['full_name'] ?: '—') ?></strong><br>
                                        <span class="muted" style="font-size:.82rem"><?= Helper::e($s['email']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= Helper::e($s['current_class'] ?: '—') ?></td>
                            <td><?= Helper::e($s['city'] ?: '—') ?><?= $s['state'] ? ', ' . Helper::e($s['state']) : '' ?></td>
                            <td>
                                <span class="badge badge-<?= $s['status'] === 'active' ? 'success' : ($s['status'] === 'pending' ? 'primary' : ($s['status'] === 'suspended' ? 'muted' : 'danger')) ?>">
                                    <?= Helper::e($s['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                            <td>
                                <div class="flex" style="gap:.3rem;flex-wrap:wrap">
                                    <?php if ($s['status'] !== 'active'): ?>
                                        <form method="post" action="/school/user-action-handler.php" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$s['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($s['status'] !== 'rejected'): ?>
                                        <form method="post" action="/school/user-action-handler.php" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$s['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($s['status'] === 'active'): ?>
                                        <a class="btn btn-ghost btn-sm" href="/student?id=<?= (int)$s['id'] ?>">View</a>
                                        <a class="btn btn-secondary btn-sm" href="/school/report-card?student_id=<?= (int)$s['id'] ?>" target="_blank" title="Generate Academic Report Card">📄 Report Card</a>
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
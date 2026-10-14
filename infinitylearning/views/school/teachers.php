<?php
use App\Auth;
use App\School;
use App\Helper;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);
if (!$school) { Auth::logout(); Helper::redirect('/login'); }

$status = $_GET['status'] ?? 'pending';
$teachers = School::users($schoolId, 'teacher', $status ?: null);

$pageTitle = 'Teachers — ' . $school['name'];
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>
        <div class="dashboard-header">
            <div class="dashboard-header-text">
                <span class="kicker">👩‍🏫 TEACHERS</span>
                <h1>Manage your teachers</h1>
                <p class="muted">Approve teachers who registered under <strong><?= Helper::e($school['name']) ?></strong>.</p>
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

        <?php if (!$teachers): ?>
            <div class="empty-state">
                <div class="empty-state-icon">👩‍🏫</div>
                <h3>No teachers in this list</h3>
                <p>When teachers register under your school, they'll appear here for approval.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>#</th><th>Teacher</th><th>Expertise</th><th>Location</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($teachers as $t): ?>
                        <tr>
                            <td><?= (int)$t['id'] ?></td>
                            <td>
                                <div class="flex">
                                    <?php if (!empty($t['avatar'])): ?>
                                        <img class="avatar" src="<?= Helper::e($t['avatar']) ?>" alt="">
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
                            <td><?= Helper::e($t['expert_field'] ?: '—') ?></td>
                            <td><?= Helper::e($t['city'] ?: '—') ?><?= $t['state'] ? ', ' . Helper::e($t['state']) : '' ?></td>
                            <td>
                                <span class="badge badge-<?= $t['status'] === 'active' ? 'success' : ($t['status'] === 'pending' ? 'primary' : ($t['status'] === 'suspended' ? 'muted' : 'danger')) ?>">
                                    <?= Helper::e($t['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                            <td>
                                <div class="flex" style="gap:.3rem;flex-wrap:wrap">
                                    <?php if ($t['status'] !== 'active'): ?>
                                        <form method="post" action="/school/user-action-handler.php" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$t['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($t['status'] !== 'rejected'): ?>
                                        <form method="post" action="/school/user-action-handler.php" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$t['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($t['status'] === 'active'): ?>
                                        <form method="post" action="/school/user-action-handler.php" style="display:inline">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$t['id'] ?>">
                                            <input type="hidden" name="action" value="suspend">
                                            <button class="btn btn-ghost btn-sm">Suspend</button>
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
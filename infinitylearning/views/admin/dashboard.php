<?php
use App\Admin;
use App\Auth;
use App\Helper;
use App\Announcement;
use App\ParentSummary;

Auth::requireSuperAdmin();

$stats         = Admin::stats();
$pending       = Admin::pendingTeachers();
$announcements = Announcement::listForAdmin(-1, 4);

$pageTitle = 'Admin Dashboard — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/admin/_sidebar.php'; ?>

    <div>
        <div class="dashboard-header">
            <div class="dashboard-header-text">
                <span class="kicker">⚙️ PLATFORM ADMIN</span>
                <h1>Admin Dashboard</h1>
                <p class="muted">Monitor users, approve teachers, and publish activities without touching code.</p>
            </div>
            <a href="/admin/analytics" class="btn btn-secondary btn-sm">View analytics →</a>
        </div>

        <!-- ============ STAT TILES ============ -->
        <div class="grid grid-4">
            <?php
            $tiles = [
                ['👩‍🎓', $stats['students'],         'Students',       '#16A34A'],
                ['👩‍🏫', $stats['teachers'],         'Teachers',       '#2563EB'],
                ['⏳',  $stats['pending_teachers'], 'Pending',        '#F59E0B'],
                ['🏫',  $stats['batches'],          'Batches',        '#7C2D5C'],
                ['🏆',  $stats['activities'],       'Activities',     '#FF7A18'],
                ['📚',  $stats['lessons'],          'Lessons',        '#7C2D5C'],
                ['✍️',  $stats['registrations'],    'Registrations',  '#2563EB'],
            ];
            foreach ($tiles as $t): ?>
                <div class="stat" style="border-left-color:<?= $t[3] ?>">
                    <span class="ico"><?= $t[0] ?></span>
                    <span class="n"><?= (int)$t[1] ?></span>
                    <span class="l"><?= $t[2] ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ============ TWO COLUMNS ============ -->
        <div class="grid grid-2 mt-3">
            <div class="card">
                <div class="flex between">
                    <h3>Pending Teacher Approvals</h3>
                    <a href="/admin/teachers" class="btn btn-ghost btn-sm">View all</a>
                </div>
                <div class="divider"></div>
                <?php if (!$pending): ?>
                    <p class="muted">No pending teacher applications 🎉</p>
                <?php else: ?>
                    <?php foreach (array_slice($pending, 0, 5) as $p): ?>
                        <div class="kv">
                            <div>
                                <strong><?= Helper::e($p['full_name']) ?></strong>
                                <div class="muted" style="font-size:.82rem">
                                    <?= Helper::e($p['expert_field']) ?> · <?= Helper::e($p['school_name']) ?>
                                </div>
                            </div>
                            <div class="flex">
                                <form method="post" action="/admin/teacher-action-handler.php">
                                    <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                    <input type="hidden" name="user_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form method="post" action="/admin/teacher-action-handler.php">
                                    <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                    <input type="hidden" name="user_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Quick actions</h3>
                <div class="divider"></div>
                <div class="grid grid-2">
                    <a class="btn btn-primary" href="/admin/activities/new">+ New activity</a>
                    <a class="btn btn-secondary" href="/admin/lessons/new">+ New lesson</a>
                    <a class="btn btn-secondary" href="/admin/schools">+ Onboard school</a>
                    <a class="btn btn-secondary" href="/admin/announcements">+ Announcement</a>
                </div>
            </div>
        </div>

        <!-- ============ WEEKLY PARENT SUMMARIES ============ -->
        <div class="card mt-3">
            <div class="flex between" style="align-items:flex-start;flex-wrap:wrap;gap:.75rem">
                <div>
                    <h3>📧 Weekly parent summaries</h3>
                    <p class="muted" style="font-size:.88rem;margin-top:.25rem">
                        Send each parent a summary of their child's week — attendance, new
                        certificates, activity results, and upcoming events. Parents already
                        emailed for the chosen week are skipped automatically.
                    </p>
                </div>
            </div>
            <div class="divider"></div>

            <form method="post" action="/admin/send-parent-summaries-handler.php"
                  onsubmit="return confirm('Send weekly summaries to all parents now?\n\nParents already emailed this week will be skipped.')">
                <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Week starting</label>
                        <input type="date" name="week_start"
                               value="<?= Helper::e(ParentSummary::defaultWeekStart()) ?>">
                        <small>The 7 days from this date will be summarised.</small>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end">
                        <button class="btn btn-primary">📤 Send parent summaries now</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ============ RECENT ANNOUNCEMENTS ============ -->
        <div class="card mt-3">
            <div class="flex between">
                <h3>Recent Announcements</h3>
                <a href="/admin/announcements" class="btn btn-ghost btn-sm">View all</a>
            </div>
            <div class="divider"></div>
            <?php if (!$announcements): ?>
                <p class="muted">No announcements yet.</p>
            <?php else: foreach ($announcements as $a): ?>
                <div class="kv">
                    <div>
                        <div class="flex" style="gap:.4rem;align-items:center;flex-wrap:wrap">
                            <strong><?= Helper::e($a['title']) ?></strong>
                            <?php if (!empty($a['school_name'])): ?>
                                <span class="badge badge-brand"><?= Helper::e($a['school_name']) ?></span>
                            <?php else: ?>
                                <span class="badge badge-info">Global</span>
                            <?php endif; ?>
                            <span class="badge badge-muted"><?= Helper::e(ucfirst($a['audience'])) ?></span>
                            <?php if (!empty($a['pinned'])): ?>
                                <span class="badge badge-primary">Pinned</span>
                            <?php endif; ?>
                        </div>
                        <div class="muted" style="font-size:.85rem;margin-top:.15rem">
                            <?= Helper::e(mb_substr($a['body'], 0, 120)) ?>
                        </div>
                    </div>
                    <span class="pill"><?= date('d M', strtotime($a['created_at'])) ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
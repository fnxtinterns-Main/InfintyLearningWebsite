<?php
use App\Auth;
use App\Activity;
use App\Helper;

$schoolId = Auth::requireSchoolAdmin();

// Show only this school's activities + global ones
$activities = Activity::list(['status' => ''], $schoolId);

// Split for display
$own    = array_filter($activities, fn($a) => !empty($a['school_id']) && (int)$a['school_id'] === $schoolId);
$global = array_filter($activities, fn($a) => empty($a['school_id']));

$pageTitle = 'Activities — Admin';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>
        <div class="flex between" style="align-items:flex-start;flex-wrap:wrap;gap:.5rem">
            <div>
                <h1>Activities</h1>
                <p class="muted">Create activities for your school. Students and teachers of your school will see them.</p>
            </div>
            <a href="/school/activities/new" class="btn btn-primary">+ New activity</a>
        </div>

        <!-- ============ Your school's activities ============ -->
        <div class="card mt-3">
            <div class="flex between">
                <h3>🏫 Your school's activities</h3>
                <span class="pill"><?= count($own) ?></span>
            </div>
            <div class="divider"></div>

            <?php if (!$own): ?>
                <p class="muted">You haven't created any activities yet. Click <strong>+ New activity</strong> above.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($own as $a): ?>
                            <tr>
                                <td><?= (int)$a['id'] ?></td>
                                <td><strong><?= Helper::e($a['title']) ?></strong></td>
                                <td><span class="badge badge-primary"><?= Helper::e($a['category']) ?></span></td>
                                <td><?= $a['event_date'] ? date('d M Y', strtotime($a['event_date'])) : '—' ?></td>
                                <td>
                                    <span class="badge badge-<?= $a['status'] === 'published' ? 'success' : 'muted' ?>">
                                        <?= Helper::e($a['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex" style="gap:.3rem;flex-wrap:wrap">
                                        <a class="btn btn-ghost btn-sm" href="/admin/activities/edit?id=<?= (int)$a['id'] ?>">Edit</a>
                                        <a class="btn btn-ghost btn-sm" href="/activity?slug=<?= urlencode($a['slug']) ?>" target="_blank">Preview</a>
                                        <a class="btn btn-primary btn-sm" href="/teacher/activity_results?activity_id=<?= (int)$a['id'] ?>">Results</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============ Global activities (read-only view for this school) ============ -->
        <div class="card mt-3">
            <div class="flex between">
                <h3>🌐 Global activities (visible to your school)</h3>
                <span class="pill"><?= count($global) ?></span>
            </div>
            <p class="muted" style="font-size:.85rem">
                These are published by the Infinity Learning team. Your students can register for them.
            </p>
            <div class="divider"></div>

            <?php if (!$global): ?>
                <p class="muted">No global activities yet.</p>
            <?php else: ?>
                <?php foreach ($global as $a): ?>
                    <div class="kv">
                        <div>
                            <div class="flex" style="gap:.4rem;align-items:center;flex-wrap:wrap">
                                <strong><?= Helper::e($a['title']) ?></strong>
                                <span class="badge badge-primary"><?= Helper::e(ucfirst($a['category'])) ?></span>
                            </div>
                            <div class="muted" style="font-size:.82rem">
                                <?php if ($a['event_date']): ?>📅 <?= date('d M Y', strtotime($a['event_date'])) ?><?php endif; ?>
                                <?php if ($a['venue']): ?> · 📍 <?= Helper::e($a['venue']) ?><?php endif; ?>
                            </div>
                        </div>
                        <div class="flex" style="gap:.3rem">
                            <a class="btn btn-ghost btn-sm" href="/activity?slug=<?= urlencode($a['slug']) ?>" target="_blank">Preview</a>
                            <a class="btn btn-primary btn-sm" href="/teacher/activity_results?activity_id=<?= (int)$a['id'] ?>">Results</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
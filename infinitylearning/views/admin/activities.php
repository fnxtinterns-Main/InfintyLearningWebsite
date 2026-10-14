<?php
use App\Activity;
use App\Auth;
use App\Helper;

Auth::requireRole('admin');

$statusFilter = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

$list = Activity::list([
    'status'   => $statusFilter   ?: null,
    'category' => $categoryFilter ?: null,
]);

$pageTitle = 'Activities — Admin';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/admin/_sidebar.php'; ?>
    <div>
        <div class="flex between" style="align-items:flex-start">
            <div>
                <h1>Activities</h1>
                <p class="muted">Publish new activities, competitions and events. No code changes needed.</p>
            </div>
            <a href="/admin/activities/new" class="btn btn-primary">+ New activity</a>
        </div>

        <!-- ===== Filters ===== -->
        <div class="filters mt-2">
            <a href="/admin/activities" class="<?= $statusFilter === '' && $categoryFilter === '' ? 'active' : '' ?>">All</a>
            <a href="?status=published" class="<?= $statusFilter === 'published' ? 'active' : '' ?>">Published</a>
            <a href="?status=draft" class="<?= $statusFilter === 'draft' ? 'active' : '' ?>">Draft</a>
            <a href="?status=closed" class="<?= $statusFilter === 'closed' ? 'active' : '' ?>">Closed</a>
            <a href="?status=archived" class="<?= $statusFilter === 'archived' ? 'active' : '' ?>">Archived</a>
        </div>

        <div class="filters">
            <a href="?<?= $statusFilter ? 'status=' . urlencode($statusFilter) : '' ?>" class="<?= $categoryFilter === '' ? 'active' : '' ?>">All categories</a>
            <?php foreach (ACTIVITY_CATEGORIES as $slug => $label): ?>
                <a href="?category=<?= urlencode($slug) ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?>"
                   class="<?= $categoryFilter === $slug ? 'active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <!-- ===== List ===== -->
        <?php if (!$list): ?>
            <div class="card text-center mt-3">
                <p class="muted">No activities found. Create your first one using the button above.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap mt-3">
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $a): ?>
                        <tr>
                            <td><?= (int)$a['id'] ?></td>
                            <td>
                                <a href="/activity?slug=<?= urlencode($a['slug']) ?>" target="_blank"
                                   style="color:inherit;text-decoration:none">
                                    <strong><?= Helper::e($a['title']) ?></strong>
                                </a>
                                <?php if (!empty($a['short_description'])): ?>
                                    <div class="muted" style="font-size:.78rem">
                                        <?= Helper::e(mb_substr($a['short_description'], 0, 80)) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-primary"><?= Helper::e($a['category']) ?></span></td>
                            <td>
                                <?= $a['event_date'] ? date('d M Y', strtotime($a['event_date'])) : '—' ?>
                                <?php if ($a['start_time']): ?>
                                    <div class="muted" style="font-size:.78rem"><?= substr($a['start_time'], 0, 5) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= Helper::e($a['venue'] ?: '—') ?></td>
                            <td>
                                <span class="badge badge-<?=
                                    $a['status'] === 'published' ? 'success' :
                                    ($a['status'] === 'draft' ? 'muted' :
                                    ($a['status'] === 'closed' ? 'info' : 'danger'))
                                ?>">
                                    <?= Helper::e($a['status']) ?>
                                </span>
                            </td>
                            <td><?= !empty($a['featured']) ? '⭐' : '—' ?></td>
                            <td>
                                <div class="flex" style="gap:.3rem;flex-wrap:wrap">
                                    <a class="btn btn-ghost btn-sm"
                                       href="/admin/activities/edit?id=<?= (int)$a['id'] ?>">Edit</a>
                                    <a class="btn btn-ghost btn-sm"
                                       href="/activity?slug=<?= urlencode($a['slug']) ?>"
                                       target="_blank">Preview</a>
                                    <form method="post" action="/admin/activity-delete-handler.php"
                                          style="display:inline"
                                          onsubmit="return confirm('Delete this activity? Registrations will also be removed.')">
                                        <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                        <button class="btn btn-danger btn-sm">Delete</button>
                                    </form>
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
<?php
use App\Auth;
use App\Helper;
use App\Session;
use App\Announcement;
use App\Database;

Auth::requireLogin();
if (Auth::role() !== 'admin') { http_response_code(403); exit('Forbidden'); }

$isSuper   = Auth::isSuperAdmin();
$schoolId  = Auth::schoolId();

// Super admin sees everything. School admin sees global + their own school.
$list = Announcement::listForAdmin($isSuper ? -1 : $schoolId, 100);

// For the scope dropdown (super admin only)
$schools = $isSuper
    ? Database::connect()->query("SELECT id, name FROM schools WHERE status='active' ORDER BY name")->fetchAll()
    : [];

$pageTitle = 'Announcements — ' . ($isSuper ? 'Admin' : 'School Admin');
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php
    if ($isSuper) require VIEW_PATH . '/admin/_sidebar.php';
    else         require VIEW_PATH . '/school/_sidebar.php';
    ?>
    <div>
        <h1>Announcements</h1>
        <p class="muted">
            <?php if ($isSuper): ?>
                Post a global announcement for everyone, or scope it to a specific school.
            <?php else: ?>
                Post an announcement for your school's teachers and students.
            <?php endif; ?>
        </p>

        <!-- ============ Create form ============ -->
        <form method="post" action="/admin/announcement-save-handler.php" class="card mt-2">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">

            <h3 style="margin-bottom:.75rem">New announcement</h3>

            <?php if ($isSuper): ?>
                <div class="form-group">
                    <label>Scope *</label>
                    <select name="school_id" required>
                        <option value="">🌐 Global — everyone</option>
                        <?php foreach ($schools as $s): ?>
                            <option value="<?= (int)$s['id'] ?>">🏫 <?= Helper::e($s['name']) ?> only</option>
                        <?php endforeach; ?>
                    </select>
                    <small>Global = visible to all schools, teachers and students. School = visible only to that school's members.</small>
                </div>
            <?php else: ?>
                <div class="alert alert-info" style="margin-bottom:1rem">
                    This announcement will be visible only to <strong>your school's</strong> members.
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required maxlength="200">
            </div>

            <div class="form-group">
                <label>Message *</label>
                <textarea name="body" rows="5" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Audience</label>
                    <select name="audience">
                        <option value="all">Everyone</option>
                        <option value="students">Students only</option>
                        <option value="teachers">Teachers only</option>
                        <option value="admins">Admins only</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pin to top?</label>
                    <select name="pinned">
                        <option value="0">No</option>
                        <option value="1">Yes — pin to top</option>
                    </select>
                </div>
            </div>

            <button class="btn btn-primary">📣 Post announcement</button>
        </form>

        <!-- ============ List ============ -->
        <div class="card mt-3">
            <div class="flex between">
                <h3>Posted announcements</h3>
                <span class="pill"><?= count($list) ?></span>
            </div>
            <div class="divider"></div>

            <?php if (!$list): ?>
                <p class="muted">No announcements yet.</p>
            <?php else: foreach ($list as $a): ?>
                <div class="kv" style="align-items:flex-start">
                    <div style="flex:1">
                        <div class="flex" style="gap:.5rem;align-items:center;flex-wrap:wrap">
                            <strong><?= Helper::e($a['title']) ?></strong>
                            <?php if ($a['pinned']): ?><span class="badge badge-primary">Pinned</span><?php endif; ?>
                            <?php if (!empty($a['school_name'])): ?>
                                <span class="badge badge-brand"><?= Helper::e($a['school_name']) ?></span>
                            <?php else: ?>
                                <span class="badge badge-info">Global</span>
                            <?php endif; ?>
                            <span class="badge badge-muted"><?= Helper::e(ucfirst($a['audience'])) ?></span>
                        </div>
                        <div style="font-size:.9rem;margin-top:.35rem;white-space:pre-wrap"><?= Helper::e($a['body']) ?></div>
                        <div class="muted" style="font-size:.78rem;margin-top:.4rem">
                            Posted <?= date('d M Y H:i', strtotime($a['created_at'])) ?>
                            <?php if (!empty($a['creator_email'])): ?> · by <?= Helper::e($a['creator_email']) ?><?php endif; ?>
                        </div>
                    </div>
                    <form method="post" action="/admin/announcement-delete-handler.php" style="display:inline"
                          onsubmit="return confirm('Delete this announcement?')">
                        <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                        <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                        <button class="btn btn-ghost btn-sm">Delete</button>
                    </form>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
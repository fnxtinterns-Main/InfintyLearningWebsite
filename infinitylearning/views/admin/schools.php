<?php
use App\Auth;
use App\School;
use App\Helper;
use App\Session;
use App\Database;
use App\Mailer;

Auth::requireSuperAdmin();
$me = Auth::user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Session::verifyCsrf($_POST['csrf'] ?? null)) {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            $result = School::createWithAdmin($_POST, (int)$me['id']);
            $_SESSION['_school_created'] = $result;

            // Send welcome email
            try {
                Mailer::send(
                    $result['admin_email'],
                    'Welcome to Infinity Learning — School Admin access',
                    Mailer::schoolAdminWelcomeEmail(
                        $_POST['principal_name'] ?? 'School Administrator',
                        $result['admin_email'],
                        $_POST['name'] ?? 'Your School',
                        $result['code']
                    )
                );
                Session::flash('success', 'School created. Welcome email sent to admin.');
            } catch (\Throwable $e) {
                error_log('School welcome email failed: ' . $e->getMessage());
                Session::flash('warning', 'School created, but welcome email failed. Share credentials manually below.');
            }

        } elseif ($action === 'update') {
            School::update((int)$_POST['id'], $_POST);
            Session::flash('success', 'School updated.');

        } elseif ($action === 'archive') {
            School::archive((int)$_POST['id']);
            Session::flash('success', 'School archived. All users suspended and content hidden. Data is preserved — restore anytime.');

        } elseif ($action === 'restore') {
            School::restore((int)$_POST['id']);
            Session::flash('success', 'School restored. Users can log in again and content is visible.');

        } elseif ($action === 'delete') {
            School::delete((int)$_POST['id']);
            Session::flash('warning', 'School permanently deleted. This cannot be undone.');

        } elseif ($action === 'status') {
            School::setStatus((int)$_POST['id'], $_POST['status'] ?? 'active');
            Session::flash('success', 'School status updated.');
        }
    } catch (\Throwable $e) {
        Session::flash('error', 'Action failed: ' . $e->getMessage());
    }
    Helper::redirect('/admin/schools');
}

$editing = isset($_GET['edit']) ? School::find((int)$_GET['edit']) : null;
$schools = School::allWithArchived();

$justCreated = null;
if (!empty($_SESSION['_school_created']) && is_array($_SESSION['_school_created'])) {
    $justCreated = $_SESSION['_school_created'];
    unset($_SESSION['_school_created']);
}

$db = Database::connect();
$adminRows = $db->query("
    SELECT s.id AS school_id, u.id AS admin_id, u.email AS admin_email, u.last_login_at
    FROM schools s
    LEFT JOIN users u ON u.id = s.admin_user_id
")->fetchAll();
$schoolAdmins = [];
foreach ($adminRows as $r) $schoolAdmins[(int)$r['school_id']] = $r;

$pageTitle = 'Schools — Admin';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/admin/_sidebar.php'; ?>
    <div>

        <div class="dashboard-header">
            <div class="dashboard-header-text">
                <span class="kicker">🏫 SCHOOLS</span>
                <h1>Onboard &amp; manage schools</h1>
                <p class="muted">Each school gets its own admin who manages their teachers and students.</p>
            </div>
        </div>

        <?php if (is_array($justCreated)): ?>
            <div class="card" style="border-left:4px solid #16A34A">
                <h3 style="margin:0 0 .5rem 0">✅ School created successfully</h3>
                <p class="muted" style="font-size:.9rem;margin-bottom:.75rem">
                    A welcome email has been sent to <strong><?= Helper::e((string)($justCreated['admin_email'] ?? '')) ?></strong>.
                    You can also share these credentials as a backup.
                </p>
                <div class="kv"><span>School code</span><span><code><?= Helper::e((string)($justCreated['code'] ?? '')) ?></code></span></div>
                <div class="kv"><span>Admin email</span><span><code><?= Helper::e((string)($justCreated['admin_email'] ?? '')) ?></code></span></div>
                <div class="kv"><span>Admin password</span><span><code><?= Helper::e((string)($justCreated['admin_password'] ?? '')) ?></code></span></div>
                <p class="muted mt-2" style="font-size:.82rem">⚠️ The password is only shown once. Save it now.</p>
            </div>
        <?php endif; ?>

        <!-- ============ SCHOOLS LIST ============ -->
        <?php if (!$schools): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🏫</div>
                <h3>No schools yet</h3>
                <p>Click "Onboard a new school" below to add the first one.</p>
            </div>
        <?php else: ?>
            <div class="school-cards">
                <?php foreach ($schools as $s):
                    $st = School::stats((int)$s['id']);
                    $admin = $schoolAdmins[(int)$s['id']] ?? null;
                    $isArchived = $s['status'] === 'archived';
                ?>
                    <div class="card school-card"<?= $isArchived ? ' style="opacity:.85;border-left:4px solid #7A5C4A"' : '' ?>>
                        <div class="flex between" style="align-items:flex-start;gap:.5rem;flex-wrap:wrap">
                            <div>
                                <div class="flex" style="gap:.5rem;align-items:center;flex-wrap:wrap">
                                    <h3 style="margin:0"><?= Helper::e($s['name']) ?></h3>
                                    <span class="badge badge-<?=
                                        $s['status'] === 'active'   ? 'success' :
                                        ($s['status'] === 'pending' ? 'primary' :
                                        ($s['status'] === 'archived' ? 'muted' : 'danger'))
                                    ?>">
                                        <?= Helper::e($s['status']) ?>
                                    </span>
                                </div>
                                <p class="muted" style="font-size:.85rem;margin-top:.15rem">
                                    <?php if ($s['code']): ?><code><?= Helper::e($s['code']) ?></code> · <?php endif; ?>
                                    <?php if ($s['board']): ?><?= Helper::e($s['board']) ?><?php endif; ?>
                                    <?php if ($s['city']): ?> · <?= Helper::e($s['city']) ?><?= $s['state'] ? ', ' . Helper::e($s['state']) : '' ?><?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-4 mt-2">
                            <div class="mini-stat"><span class="mini-stat-n"><?= (int)$st['students'] ?></span><span class="mini-stat-l">Students</span></div>
                            <div class="mini-stat"><span class="mini-stat-n"><?= (int)$st['teachers'] ?></span><span class="mini-stat-l">Teachers</span></div>
                            <div class="mini-stat"><span class="mini-stat-n"><?= (int)$st['batches'] ?></span><span class="mini-stat-l">Batches</span></div>
                            <div class="mini-stat" style="border-color:#F59E0B"><span class="mini-stat-n"><?= (int)$st['pending'] ?></span><span class="mini-stat-l">Pending</span></div>
                        </div>

                        <div class="divider"></div>

                        <?php if ($admin && !empty($admin['admin_email'])): ?>
                            <div class="kv"><span>Admin</span><span><?= Helper::e($admin['admin_email']) ?></span></div>
                            <div class="kv"><span>Last login</span><span><?= $admin['last_login_at'] ? date('d M Y', strtotime($admin['last_login_at'])) : '—' ?></span></div>
                        <?php else: ?>
                            <div class="kv"><span>Admin</span><span class="muted">not assigned</span></div>
                        <?php endif; ?>
                        <div class="kv">
                            <span>Parent summary</span>
                            <span>
                                <?php if (!empty($s['allow_weekly_summary'])): ?>
                                    <span class="badge badge-success" style="font-size:.75rem;">Allowed</span>
                                <?php else: ?>
                                    <span class="badge" style="background:var(--surface-2); color:var(--il-muted); font-size:.75rem;">Disabled by Super Admin</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <?php if ($isArchived): ?>
                            <div class="alert alert-warning" style="font-size:.82rem;padding:.6rem .8rem;margin-top:.75rem">
                                This school is archived. <strong><?= (int)$st['suspended'] ?> user(s)</strong> are suspended and cannot log in. Restore to reactivate everyone and their content.
                            </div>
                        <?php endif; ?>

                        <div class="flex mt-2" style="gap:.3rem;flex-wrap:wrap">
                            <a class="btn btn-ghost btn-sm" href="/admin/schools?edit=<?= (int)$s['id'] ?>">Edit</a>
                            <a class="btn btn-ghost btn-sm" href="/admin/users?school=<?= (int)$s['id'] ?>">Users</a>
                            <a class="btn btn-ghost btn-sm" href="/admin/teachers?school=<?= (int)$s['id'] ?>">Teachers</a>

                            <?php if ($isArchived): ?>
                                <form method="post" style="display:inline"
                                      onsubmit="return confirm('Restore this school?\n\nAll users will be reactivated and all content (batches, lessons, activities) will be visible again. They resume exactly where they left off.')">
                                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                    <input type="hidden" name="action" value="restore">
                                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                    <button class="btn btn-success btn-sm">♻ Restore</button>
                                </form>
                                <form method="post" style="display:inline"
                                      onsubmit="return confirm('PERMANENTLY DELETE this school?\n\nThis will:\n• Delete the school record\n• Suspend and unlink every user\n• Archive all content\n\nTHIS CANNOT BE UNDONE.')">
                                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                    <button class="btn btn-danger btn-sm">✕ Delete forever</button>
                                </form>
                            <?php else: ?>
                                <form method="post" style="display:inline"
                                      onsubmit="return confirm('Archive this school?\n\n• All teachers, students, and the school admin will be suspended and logged out\n• Batches, lessons, and activities will be hidden\n\n✅ ALL data is preserved. You can restore anytime.')">
                                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                    <input type="hidden" name="action" value="archive">
                                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                    <button class="btn btn-danger btn-sm">📦 Archive</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ============ CREATE/EDIT FORM ============ -->
        <div class="card mt-3">
            <h3><?= $editing ? 'Edit school' : 'Onboard a new school' ?></h3>
            <p class="muted" style="font-size:.9rem">
                <?php if (!$editing): ?>
                    We'll auto-generate a unique <strong>school code</strong> and an <strong>admin account</strong>. A welcome email with login instructions will be sent automatically.
                <?php endif; ?>
            </p>
            <div class="divider"></div>

            <form method="post">
                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
                <?php if ($editing): ?>
                    <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label>School name *</label>
                        <input type="text" name="name" required value="<?= Helper::e($editing['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Principal name</label>
                        <input type="text" name="principal_name" value="<?= Helper::e($editing['principal_name'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Board</label>
                        <select name="board">
                            <option value="">— select —</option>
                            <?php foreach (['CBSE','ICSE','State Board','IB','IGCSE','Other'] as $b): ?>
                                <option value="<?= $b ?>" <?= ($editing['board'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="<?= Helper::e($editing['city'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="state" value="<?= Helper::e($editing['state'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>School phone</label>
                        <input type="tel" name="phone" value="<?= Helper::e($editing['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" value="<?= Helper::e($editing['address'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Official contact email</label>
                        <input type="email" name="contact_email" value="<?= Helper::e($editing['contact_email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Official contact phone</label>
                        <input type="tel" name="contact_phone" value="<?= Helper::e($editing['contact_phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Student capacity</label>
                        <input type="number" name="student_capacity" min="1" max="10000"
                               value="<?= Helper::e((string)($editing['student_capacity'] ?? 500)) ?>">
                    </div>
                    <div class="form-group">
                        <label>Teacher capacity</label>
                        <input type="number" name="teacher_capacity" min="1" max="2000"
                               value="<?= Helper::e((string)($editing['teacher_capacity'] ?? 100)) ?>">
                    </div>
                </div>

                <div class="form-group" style="background:var(--surface-2); padding:14px 16px; border-radius:10px; border:1px solid var(--il-border); margin-top:12px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:700; margin-bottom:4px;">
                        <input type="checkbox" name="allow_weekly_summary" value="1" <?= !empty($editing['allow_weekly_summary']) ? 'checked' : '' ?> style="width:18px; height:18px; cursor:pointer;">
                        <span>📧 Allow Weekly Parent Summaries for this school</span>
                    </label>
                    <p class="muted" style="font-size:.82rem; margin:0 0 0 28px; line-height:1.4;">
                        When checked, the school admin can toggle and send automated weekly learning summaries to parents. Leave unchecked for teacher training institutes, coaching cohorts, or schools where parent summaries are not required.
                    </p>
                </div>

                <?php if (!$editing): ?>
                    <div class="divider"></div>
                    <h3 style="margin-bottom:.75rem;font-size:1rem">School admin account</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Admin email *</label>
                            <input type="email" name="admin_email" required placeholder="e.g. principal@cps.edu">
                            <small>Welcome email will be sent to this address.</small>
                        </div>
                        <div class="form-group">
                            <label>Admin phone *</label>
                            <input type="tel" name="admin_phone" required placeholder="e.g. +919876543210">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="flex mt-2">
                    <button class="btn btn-primary btn-lg"><?= $editing ? 'Save changes' : 'Create school + admin' ?></button>
                    <?php if ($editing): ?>
                        <a href="/admin/schools" class="btn btn-ghost">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
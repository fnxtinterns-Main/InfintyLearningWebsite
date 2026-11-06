<?php
use App\Auth;
use App\Helper;
use App\TeacherNote;
use App\Batch;
use App\Database;
use App\Session;

Auth::requireRole('teacher', 'admin');
$user = Auth::user();
$userId = (int)$user['id'];
$schoolId = Auth::schoolId();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
        Session::flash('error', 'Invalid token. Please try again.');
        Helper::redirect('/teacher/notes');
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category = trim($_POST['category'] ?? 'general');
        $studentUserId = !empty($_POST['student_user_id']) ? (int)$_POST['student_user_id'] : null;
        $batchId = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;

        if ($title === '' || $content === '') {
            Session::flash('error', 'Please provide both title and note content.');
        } else {
            TeacherNote::create($userId, $schoolId, $title, $content, $studentUserId, $batchId, $category);
            Session::flash('success', 'Teacher note saved successfully. It is now accessible to School Admins.');
        }
        Helper::redirect('/teacher/notes');
    } elseif ($action === 'delete') {
        $noteId = (int)($_POST['id'] ?? 0);
        if ($noteId > 0) {
            TeacherNote::delete($noteId, $userId, Auth::isAdmin());
            Session::flash('success', 'Note removed.');
        }
        Helper::redirect('/teacher/notes');
    }
}

$notes = TeacherNote::listForTeacher($userId);
$batches = Batch::all($userId);

// Fetch enrolled students for dropdown
$db = Database::connect();
$students = [];
try {
    if ($schoolId) {
        $stStmt = $db->prepare("
            SELECT u.id, s.full_name, s.current_class, s.roll_number 
            FROM users u
            JOIN students s ON s.user_id = u.id
            WHERE u.school_id = ? AND u.role = 'student' AND u.status = 'active'
            ORDER BY s.full_name ASC
        ");
        $stStmt->execute([$schoolId]);
        $students = $stStmt->fetchAll();
    }
} catch (\Throwable $e) {}

$pageTitle = 'Teacher Notes — Confidential Academic Logs';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <main class="admin-main">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <a href="/teacher" class="back-link">← Teacher Hub</a>
                <h1 style="margin:4px 0 0; font-size:24px;">📝 Teacher Notes &amp; Confidential Logs</h1>
                <p style="color:var(--il-muted); font-size:14px; margin:4px 0 0;">
                    Record observations, student performance insights, or academic evaluations. 
                    <strong>Only School Admins can view these notes; students never have access.</strong>
                </p>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <span class="badge" style="background:rgba(239,68,68,0.12); color:#dc2626; font-weight:700; padding:6px 12px; border-radius:8px;">
                    🔒 Admin-Only Visibility (Hidden From Students)
                </span>
            </div>
        </div>

        <?php if ($msg = Session::flash('success')): ?>
            <div class="alert alert-success mt-2"><?= Helper::e($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = Session::flash('error')): ?>
            <div class="alert alert-danger mt-2"><?= Helper::e($err) ?></div>
        <?php endif; ?>

        <!-- CREATE NEW NOTE CARD -->
        <div class="card mt-3" style="border-radius:14px; border:1px solid var(--il-border); padding:22px;">
            <h3 style="margin:0 0 4px; font-size:18px;">➕ Add Confidential Note</h3>
            <p class="muted" style="margin:0 0 16px; font-size:13px;">Save an academic remark or observation for administrative records.</p>

            <form method="post" action="/teacher/notes">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">

                <div class="form-row" style="display:flex; gap:12px; flex-wrap:wrap;">
                    <div class="form-group" style="flex:2; min-width:240px;">
                        <label style="font-weight:700; font-size:13px;">Note Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Assessment observation, behavioural remark, or curriculum feedback">
                    </div>
                    <div class="form-group" style="flex:1; min-width:180px;">
                        <label style="font-weight:700; font-size:13px;">Category</label>
                        <select name="category" class="form-control">
                            <option value="academic">Academic Progress</option>
                            <option value="behavior">Behavior &amp; Discipline</option>
                            <option value="counseling">Counseling Followup</option>
                            <option value="attendance">Attendance Remark</option>
                            <option value="general" selected>General Note</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display:flex; gap:12px; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label style="font-weight:700; font-size:13px;">Associate Student (Optional)</label>
                        <select name="student_user_id" class="form-control">
                            <option value="">-- General / Class-wide Note --</option>
                            <?php foreach ($students as $st): ?>
                                <option value="<?= (int)$st['id'] ?>">
                                    <?= Helper::e($st['full_name']) ?> (Class <?= Helper::e($st['current_class'] ?: 'N/A') ?><?= !empty($st['roll_number']) ? ' · Roll ' . Helper::e($st['roll_number']) : '' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label style="font-weight:700; font-size:13px;">Associate Batch (Optional)</label>
                        <select name="batch_id" class="form-control">
                            <option value="">-- No specific batch --</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= Helper::e($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label style="font-weight:700; font-size:13px;">Detailed Note Content *</label>
                    <textarea name="content" class="form-control" rows="4" required placeholder="Write your confidential notes here. Only you and school administrators can read this log..."></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary" style="font-weight:700; padding:10px 24px;">
                        💾 Save Note
                    </button>
                </div>
            </form>
        </div>

        <!-- NOTES LIST -->
        <div class="card mt-4" style="border-radius:14px; padding:22px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="margin:0; font-size:18px;">📋 Your Recorded Notes (<?= count($notes) ?>)</h3>
                <span class="muted" style="font-size:12px;">Visible to school administration</span>
            </div>

            <?php if (empty($notes)): ?>
                <div style="text-align:center; padding:36px 20px; color:var(--il-muted);">
                    <div style="font-size:36px; margin-bottom:8px;">📝</div>
                    <h4>No teacher notes created yet</h4>
                    <p style="font-size:13px; margin:4px 0 0;">Create confidential observations above to document student progress.</p>
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <?php foreach ($notes as $n): ?>
                        <div style="background:var(--surface-1); border:1px solid var(--il-border); border-radius:12px; padding:16px 20px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
                                        <span class="badge badge-primary" style="font-size:11px; text-transform:uppercase;">
                                            <?= Helper::e($n['category']) ?>
                                        </span>
                                        <?php if (!empty($n['student_user_id'])): ?>
                                            <span class="badge" style="background:#f3e8ff; color:#6b21a8; font-weight:700; font-size:11px;">
                                                👩‍🎓 <?= Helper::e($n['student_name']) ?> (<?= Helper::e($n['current_class'] ?: 'Class') ?>)
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($n['batch_name'])): ?>
                                            <span class="badge badge-secondary" style="font-size:11px;">
                                                🏫 <?= Helper::e($n['batch_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="muted" style="font-size:12px;">
                                            📅 <?= date('d M Y, H:i', strtotime($n['created_at'])) ?>
                                        </span>
                                    </div>
                                    <h4 style="margin:0 0 6px; font-size:16px; color:var(--il-text);"><?= Helper::e($n['title']) ?></h4>
                                    <div style="font-size:14px; line-height:1.6; color:var(--il-text); white-space:pre-wrap;"><?= Helper::e($n['content']) ?></div>
                                </div>
                                <form method="post" action="/teacher/notes" onsubmit="return confirm('Delete this teacher note?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                    <button type="submit" class="btn btn-ghost btn-xs" style="color:#ef4444;" title="Delete Note">🗑️</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

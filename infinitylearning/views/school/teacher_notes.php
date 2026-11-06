<?php
use App\Auth;
use App\Helper;
use App\TeacherNote;
use App\School;
use App\Session;

Auth::requireRole('school_admin', 'admin');
$user = Auth::user();
$schoolId = Auth::schoolId() ?: 1;
$school = School::find($schoolId);

// Handle delete if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
        Session::flash('error', 'Invalid token.');
        Helper::redirect('/school/teacher-notes');
    }
    $noteId = (int)($_POST['id'] ?? 0);
    if ($noteId > 0) {
        TeacherNote::delete($noteId, (int)$user['id'], true);
        Session::flash('success', 'Teacher note deleted.');
    }
    Helper::redirect('/school/teacher-notes');
}

$notes = TeacherNote::listForSchool($schoolId);

$pageTitle = 'Teacher Notes & Logs — School Admin';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <main class="admin-main" style="padding:20px;">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <a href="/school" class="back-link">← School Dashboard</a>
                <h1 style="margin:4px 0 0; font-size:24px;">📝 Teacher Notes &amp; Faculty Logs</h1>
                <p style="color:var(--il-muted); font-size:14px; margin:4px 0 0;">
                    Review notes, confidential feedback, and observations logged by teachers across your institution.
                </p>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <span class="badge" style="background:rgba(37,99,235,0.12); color:#2563eb; font-weight:700; padding:6px 12px; border-radius:8px;">
                    🔒 Administrator Access Only
                </span>
            </div>
        </div>

        <?php if ($msg = Session::flash('success')): ?>
            <div class="alert alert-success mt-2"><?= Helper::e($msg) ?></div>
        <?php endif; ?>

        <div class="card mt-3" style="border-radius:14px; padding:22px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="margin:0; font-size:18px;">All Faculty Notes (<?= count($notes) ?>)</h3>
                <span class="muted" style="font-size:12px;">Sorted by most recent</span>
            </div>

            <?php if (empty($notes)): ?>
                <div style="text-align:center; padding:40px 20px; color:var(--il-muted);">
                    <div style="font-size:36px; margin-bottom:8px;">📋</div>
                    <h4>No teacher notes recorded yet</h4>
                    <p style="font-size:13px; margin:4px 0 0;">When faculty members submit confidential notes or student observations, they will appear here.</p>
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:16px;">
                    <?php foreach ($notes as $n): ?>
                        <div style="background:var(--surface-1); border:1px solid var(--il-border); border-left:4px solid var(--il-primary); border-radius:12px; padding:18px 22px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                                <div style="flex:1; min-width:280px;">
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
                                        <span class="badge" style="background:#e0e7ff; color:#3730a3; font-weight:800; font-size:11.5px;">
                                            👨‍🏫 <?= Helper::e($n['teacher_name']) ?>
                                            <?php if (!empty($n['expert_field'])): ?> (<?= Helper::e($n['expert_field']) ?>)<?php endif; ?>
                                        </span>
                                        <span class="badge badge-secondary" style="font-size:11px; text-transform:uppercase;">
                                            <?= Helper::e($n['category']) ?>
                                        </span>
                                        <?php if (!empty($n['student_user_id'])): ?>
                                            <span class="badge" style="background:#f3e8ff; color:#6b21a8; font-weight:700; font-size:11px;">
                                                👩‍🎓 Student: <?= Helper::e($n['student_name']) ?> (<?= Helper::e($n['current_class'] ?: 'N/A') ?>)
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($n['batch_name'])): ?>
                                            <span class="badge" style="background:#f1f5f9; color:#475569; font-size:11px;">
                                                🏫 <?= Helper::e($n['batch_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="muted" style="font-size:12px;">
                                            📅 <?= date('d M Y, H:i', strtotime($n['created_at'])) ?>
                                        </span>
                                    </div>
                                    <h4 style="margin:2px 0 6px; font-size:16px; color:var(--il-text);"><?= Helper::e($n['title']) ?></h4>
                                    <div style="font-size:14px; line-height:1.6; color:var(--il-text); white-space:pre-wrap;"><?= Helper::e($n['content']) ?></div>
                                </div>
                                <form method="post" action="/school/teacher-notes" onsubmit="return confirm('Delete this note?')">
                                    <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                    <button type="submit" class="btn btn-ghost btn-xs" style="color:#ef4444;" title="Delete">🗑️</button>
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

<?php
use App\Auth;
use App\Helper;
use App\DoubtFeed;
use App\Database;

$teacher = Auth::requireTeacher();
$userId = (int)Auth::id();
$schoolId = Auth::schoolId();

$statusFilter = $_GET['status'] ?? '';
$allDoubts = DoubtFeed::listForTeacher($userId, $schoolId);

$openCount = 0;
$answeredCount = 0;
foreach ($allDoubts as $d) {
    if ($d['status'] === 'open') $openCount++;
    if ($d['status'] === 'answered') $answeredCount++;
}

$doubts = $allDoubts;
if ($statusFilter) {
    $doubts = array_filter($allDoubts, fn($d) => $d['status'] === $statusFilter);
}

$pageTitle = 'Student Doubts & Q&A Inbox — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <main class="admin-main">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <a href="/teacher" class="back-link">← Teacher Portal</a>
                <h1 style="margin:4px 0 0; font-size:24px;">💬 Ask a Doubt — Student Q&A Inbox</h1>
                <p style="color:var(--il-muted); font-size:14px; margin:4px 0 0;">
                    Review questions submitted by students across your lessons. Provide verified answers and clarify doubts.
                </p>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="card mb-4" style="padding:14px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div style="display:flex; gap:8px;">
                    <a href="/teacher/doubts" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-ghost' ?>">
                        All Doubts (<?= count($allDoubts) ?>)
                    </a>
                    <a href="/teacher/doubts?status=open" class="btn btn-sm <?= $statusFilter === 'open' ? 'btn-primary' : 'btn-ghost' ?>">
                        ⏳ Unanswered (<?= $openCount ?>)
                    </a>
                    <a href="/teacher/doubts?status=answered" class="btn btn-sm <?= $statusFilter === 'answered' ? 'btn-primary' : 'btn-ghost' ?>">
                        ✅ Answered (<?= $answeredCount ?>)
                    </a>
                </div>
            </div>
        </div>

        <?php if (empty($doubts)): ?>
            <div class="card text-center py-5">
                <span style="font-size:42px;">🎉</span>
                <h3 style="margin:12px 0 6px;">Inbox is all clear!</h3>
                <p class="muted" style="margin:0; font-size:14px;">
                    <?= !empty($statusFilter) ? 'No student doubts found with this filter.' : 'No student doubts waiting for answers right now.' ?>
                </p>
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:16px;">
                <?php foreach ($doubts as $d): ?>
                    <div class="card" style="padding:20px; border-left: 4px solid <?= $d['status'] === 'open' ? '#f59e0b' : '#10b981' ?>;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                            <div style="flex:1;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
                                    <strong><?= Helper::e($d['student_name'] ?: 'Student') ?></strong>
                                    <?php if (!empty($d['current_class'])): ?>
                                        <span class="pill" style="font-size:11px;">Class <?= Helper::e($d['current_class']) ?></span>
                                    <?php endif; ?>
                                    <span class="muted" style="font-size:12px;">· <?= date('d M Y, H:i', strtotime($d['created_at'])) ?></span>
                                    <?php if (!empty($d['subject_name'])): ?>
                                        <span class="badge" style="background:rgba(59,130,246,0.15); color:#3b82f6; font-size:11px; font-weight:700;">
                                            📚 <?= Helper::e($d['subject_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge badge-primary" style="font-size:11px;">
                                        📖 <?= Helper::e($d['lesson_title']) ?>
                                    </span>
                                </div>
                                <h3 style="margin:8px 0 6px; font-size:17px; color:var(--il-text);"><?= Helper::e($d['title']) ?></h3>
                                <div style="font-size:14px; color:var(--il-text); line-height:1.5;">
                                    <?= nl2br(Helper::e($d['body'])) ?>
                                </div>
                                <?php if (!empty($d['attachment_url'])): ?>
                                    <div style="margin-top:12px;">
                                        <a href="<?= Helper::e($d['attachment_url']) ?>" target="_blank" style="display:inline-block; border-radius:8px; overflow:hidden; border:1px solid var(--il-border); max-width:240px;">
                                            <img src="<?= Helper::e($d['attachment_url']) ?>" alt="Doubt attachment" style="max-height:160px; max-width:100%; object-fit:contain; display:block;">
                                        </a>
                                        <div style="font-size:11px; color:var(--il-muted); margin-top:3px;">📎 Click image to view full size</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="badge <?= $d['status'] === 'answered' ? 'badge-success' : 'badge-warning' ?>" style="font-size:12px; padding:4px 10px;">
                                    <?= $d['status'] === 'answered' ? '✅ Answered' : '⏳ Pending Answer' ?>
                                </span>
                            </div>
                        </div>

                        <!-- REPLIES & DISCUSSION THREAD (if any) -->
                        <?php if (!empty($d['replies'])): ?>
                            <div style="margin-top:16px; padding-top:14px; border-top:1px dashed var(--il-border); display:flex; flex-direction:column; gap:10px;">
                                <div style="font-size:12px; font-weight:700; color:var(--il-muted); text-transform:uppercase; letter-spacing:0.04em;">
                                    Thread Replies (<?= count($d['replies']) ?>)
                                </div>
                                <?php foreach ($d['replies'] as $rep): ?>
                                    <div style="background:var(--surface-1); padding:10px 14px; border-radius:8px; border:1px solid <?= !empty($rep['is_teacher_endorsed']) ? 'rgba(16,185,129,0.35)' : 'var(--il-border)' ?>;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                            <div style="display:flex; align-items:center; gap:6px;">
                                                <strong style="font-size:12px; color:var(--il-text);"><?= Helper::e($rep['author_name']) ?></strong>
                                                <span class="badge <?= ($rep['user_role'] ?? '') === 'teacher' ? 'badge-success' : 'badge-secondary' ?>" style="font-size:10px; padding:1px 6px;">
                                                    <?= ucfirst($rep['user_role'] ?? 'user') ?>
                                                </span>
                                                <?php if (!empty($rep['is_teacher_endorsed'])): ?>
                                                    <span style="color:#10b981; font-weight:700; font-size:11px;">⭐ Verified Answer</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="muted" style="font-size:11px;"><?= date('d M, H:i', strtotime($rep['created_at'])) ?></span>
                                        </div>
                                        <div style="font-size:13px; color:var(--il-text); line-height:1.4;">
                                            <?= nl2br(Helper::e($rep['reply_text'])) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Teacher Reply Box -->
                        <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--il-border);">
                            <form class="doubt-reply-form" style="display:flex; gap:10px; flex-wrap:wrap;">
                                <input type="hidden" name="action" value="reply">
                                <input type="hidden" name="doubt_id" value="<?= (int)$d['id'] ?>">
                                <input type="text" name="reply_text" class="input form-control" placeholder="Type your verified teacher explanation here..." required style="flex:1; min-width:240px;">
                                <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap; font-weight:600;">
                                    ⭐ Endorse & Post Answer
                                </button>
                                <a href="/lesson?id=<?= (int)$d['lesson_id'] ?>#lesson-doubts-section" class="btn btn-secondary btn-sm" target="_blank" title="View in lesson page">
                                    🔍 View Thread
                                </a>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.doubt-reply-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = form.querySelector('button');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Posting...';
            const fd = new FormData(form);

            try {
                const res = await fetch('/lesson-doubt-handler.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Failed to post answer.');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            } catch (err) {
                alert('Network error.');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    });
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

<?php
use App\Auth;
use App\Helper;
use App\DoubtFeed;
use App\Database;
use App\Session;

Auth::requireLogin();
$user = Auth::user();
$role = Auth::role();
$userId = (int)$user['id'];

// If a teacher or admin visits, they can view a specific student's doubts if student_id is passed, or redirect
$targetStudentId = $userId;
if (in_array($role, ['admin', 'teacher', 'school'], true) && !empty($_GET['student_id'])) {
    $targetStudentId = (int)$_GET['student_id'];
}

$statusFilter = $_GET['status'] ?? '';
$doubts = DoubtFeed::listForStudent($targetStudentId, !empty($statusFilter) ? ['status' => $statusFilter] : []);
$stats = DoubtFeed::getStudentStats($targetStudentId);

// Fetch lessons available for this student so they can ask a new doubt
$db = Database::connect();
$lessons = [];
try {
    $lq = $db->query("
        SELECT l.id, l.title, s.name as subject_name 
        FROM lessons l
        LEFT JOIN subjects s ON s.id = l.subject_id
        WHERE l.published = 1
        ORDER BY l.title ASC
        LIMIT 100
    ");
    $lessons = $lq->fetchAll();
} catch (\Throwable $e) {}

$pageTitle = 'My Doubts & Questions — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm">
    <div class="container-narrow">
        <!-- HEADER -->
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <a href="/student?id=<?= $targetStudentId ?>" class="back-link">← Student Profile</a>
                <h1 style="margin:4px 0 0; font-size:24px;">💬 Student Doubt & Q&A Portal</h1>
                <p style="color:var(--il-muted); font-size:14px; margin:4px 0 0;">
                    Ask questions, get verified answers from your teachers, and track your resolved doubts.
                </p>
            </div>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <button type="button" class="btn btn-primary btn-sm" onclick="toggleAskModal()" style="font-weight:700;">
                    ➕ Ask a New Doubt
                </button>
                <a href="/dashboard" class="btn btn-secondary btn-sm">Dashboard</a>
            </div>
        </div>

        <?php if ($flash = Session::flash('success')): ?>
            <div class="alert alert-success mt-2" style="padding:12px 16px; border-radius:10px; margin-bottom:16px;">
                <?= Helper::e($flash) ?>
            </div>
        <?php endif; ?>
        <?php if ($err = Session::flash('error')): ?>
            <div class="alert alert-danger mt-2" style="padding:12px 16px; border-radius:10px; margin-bottom:16px;">
                <?= Helper::e($err) ?>
            </div>
        <?php endif; ?>

        <!-- STAT TILES -->
        <div class="grid grid-3 mt-3 mb-4">
            <div class="stat">
                <span class="ico">❓</span>
                <span class="n" style="font-size:24px; font-weight:800;"><?= $stats['total'] ?></span>
                <span class="l">Total Doubts Asked</span>
            </div>
            <div class="stat" style="border-left-color:#10b981;">
                <span class="ico">✅</span>
                <span class="n" style="font-size:24px; font-weight:800; color:#10b981;"><?= $stats['answered'] ?></span>
                <span class="l">Answered by Teachers</span>
            </div>
            <div class="stat" style="border-left-color:#f59e0b;">
                <span class="ico">⏳</span>
                <span class="n" style="font-size:24px; font-weight:800; color:#f59e0b;"><?= $stats['open'] ?></span>
                <span class="l">Awaiting Teacher</span>
            </div>
        </div>

        <!-- ASK A DOUBT CARD / FORM -->
        <div id="askDoubtCard" class="card mb-4" style="border:1.5px solid var(--il-primary); display:none; background:var(--surface-0); border-radius:14px; padding:22px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h3 style="margin:0; font-size:18px;">💡 Post a Doubt to Your Teachers</h3>
                <button type="button" onclick="toggleAskModal()" class="btn btn-ghost btn-sm" style="font-size:16px; padding:2px 8px;">✕</button>
            </div>
            <form action="/lesson-doubt-handler.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="ask">
                <input type="hidden" name="redirect" value="/student/doubts">

                <div class="form-group mb-3">
                    <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px;">Select Lesson / Subject <span style="color:#ef4444;">*</span></label>
                    <select name="lesson_id" class="input form-control" required style="width:100%;">
                        <option value="">-- Choose Lesson --</option>
                        <?php foreach ($lessons as $l): ?>
                            <option value="<?= (int)$l['id'] ?>">
                                <?= Helper::e($l['subject_name'] ? "[{$l['subject_name']}] " : "") . Helper::e($l['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px;">Question Title <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="title" class="input form-control" placeholder="e.g. How to solve question 4 from Chapter 2 trigonometry?" required style="width:100%;">
                </div>

                <div class="form-group mb-3">
                    <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px;">Detailed Explanation / What step are you stuck on? <span style="color:#ef4444;">*</span></label>
                    <textarea name="body" class="input form-control" rows="4" placeholder="Explain your doubt in detail so your teachers can give you a clear, step-by-step solution..." required style="width:100%;"></textarea>
                </div>

                <div class="form-group mb-3">
                    <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px;">Attach Photo or Diagram (Optional)</label>
                    <input type="file" name="attachment" accept="image/*" class="input form-control" style="width:100%; padding:6px;">
                    <small class="muted">Upload screenshot, textbook snippet, or photo of your rough calculation (PNG, JPG, WebP).</small>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                    <button type="button" onclick="toggleAskModal()" class="btn btn-secondary btn-sm">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="font-weight:700; padding:8px 18px;">🚀 Post Doubt</button>
                </div>
            </form>
        </div>

        <!-- FILTER TABS -->
        <div class="card mb-3" style="padding:12px 18px; border-radius:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div style="display:flex; gap:8px;">
                    <a href="/student/doubts" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-ghost' ?>">
                        All Doubts (<?= $stats['total'] ?>)
                    </a>
                    <a href="/student/doubts?status=answered" class="btn btn-sm <?= $statusFilter === 'answered' ? 'btn-primary' : 'btn-ghost' ?>">
                        ✅ Answered (<?= $stats['answered'] ?>)
                    </a>
                    <a href="/student/doubts?status=open" class="btn btn-sm <?= $statusFilter === 'open' ? 'btn-primary' : 'btn-ghost' ?>">
                        ⏳ Awaiting Answer (<?= $stats['open'] ?>)
                    </a>
                </div>
                <div>
                    <span class="muted" style="font-size:12px;">Questions asked by you across all lessons</span>
                </div>
            </div>
        </div>

        <!-- DOUBTS LIST -->
        <?php if (empty($doubts)): ?>
            <div class="card text-center py-5" style="border-radius:14px; background:var(--surface-0);">
                <span style="font-size:46px;">💡</span>
                <h3 style="margin:12px 0 6px;">No doubts found</h3>
                <p class="muted" style="margin:0 0 16px; font-size:14px;">
                    <?= !empty($statusFilter) ? "No doubts found matching this filter." : "You haven't asked any doubts yet. Have a question while studying? Ask your teachers now!" ?>
                </p>
                <button type="button" class="btn btn-primary btn-sm" onclick="toggleAskModal()" style="font-weight:700;">
                    ➕ Ask Your First Doubt
                </button>
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:16px;">
                <?php foreach ($doubts as $d): ?>
                    <div class="card" style="padding:20px; border-radius:14px; border-left: 4px solid <?= $d['status'] === 'answered' ? '#10b981' : '#f59e0b' ?>;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                            <div style="flex:1;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
                                    <?php if (!empty($d['subject_name'])): ?>
                                        <span class="badge badge-brand" style="font-size:11px;"><?= Helper::e($d['subject_name']) ?></span>
                                    <?php endif; ?>
                                    <span class="badge badge-primary" style="font-size:11px;">📖 <?= Helper::e($d['lesson_title']) ?></span>
                                    <span class="muted" style="font-size:12px;">· Asked on <?= date('d M Y, h:i A', strtotime($d['created_at'])) ?></span>
                                </div>
                                <h3 style="margin:6px 0 8px; font-size:17px; color:var(--il-text);"><?= Helper::e($d['title']) ?></h3>
                                <div style="font-size:14px; color:var(--il-text); line-height:1.5;">
                                    <?= nl2br(Helper::e($d['body'])) ?>
                                </div>
                                <?php if (!empty($d['attachment_url'])): ?>
                                    <div style="margin-top:12px;">
                                        <a href="<?= Helper::e($d['attachment_url']) ?>" target="_blank" style="display:inline-block; border-radius:8px; overflow:hidden; border:1px solid var(--il-border);">
                                            <img src="<?= Helper::e($d['attachment_url']) ?>" alt="Doubt attachment" style="max-height:160px; max-width:100%; object-fit:contain; display:block;">
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="badge <?= $d['status'] === 'answered' ? 'badge-success' : 'badge-warning' ?>" style="font-size:12px; padding:4px 10px;">
                                    <?= $d['status'] === 'answered' ? '✅ Answered' : '⏳ Awaiting Teacher' ?>
                                </span>
                            </div>
                        </div>

                        <!-- REPLIES & TEACHER EXPLANATIONS -->
                        <?php if (!empty($d['replies'])): ?>
                            <div style="margin-top:18px; padding-top:14px; border-top:1px dashed var(--il-border); display:flex; flex-direction:column; gap:12px;">
                                <div style="font-size:12px; font-weight:700; color:var(--il-muted); text-transform:uppercase; letter-spacing:0.04em;">
                                    Answers & Discussion (<?= count($d['replies']) ?>)
                                </div>
                                <?php foreach ($d['replies'] as $rep): ?>
                                    <div style="background:var(--surface-1); padding:12px 14px; border-radius:10px; border:1px solid <?= !empty($rep['is_teacher_endorsed']) ? 'rgba(16,185,129,0.35)' : 'var(--il-border)' ?>;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <strong style="font-size:13px; color:var(--il-text);"><?= Helper::e($rep['author_name']) ?></strong>
                                                <?php if (!empty($rep['is_teacher_endorsed']) || ($rep['user_role'] ?? '') === 'teacher'): ?>
                                                    <span class="badge badge-success" style="font-size:10px; padding:2px 6px;">⭐ Verified Teacher Answer</span>
                                                <?php elseif (($rep['user_role'] ?? '') === 'student'): ?>
                                                    <span class="pill" style="font-size:10px; padding:2px 6px;">You / Student</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="muted" style="font-size:11px;"><?= date('d M, h:i A', strtotime($rep['created_at'])) ?></span>
                                        </div>
                                        <div style="font-size:13px; color:var(--il-text); line-height:1.5;">
                                            <?= nl2br(Helper::e($rep['reply_text'])) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div style="margin-top:14px; padding:10px 14px; background:var(--surface-2); border-radius:8px; font-size:12px; color:var(--il-muted);">
                                ⏳ Your question is in the teacher review queue. You will see teacher responses here once answered.
                            </div>
                        <?php endif; ?>

                        <!-- REPLY / FOLLOW-UP BOX -->
                        <div style="margin-top:14px; padding-top:12px; border-top:1px solid var(--il-border);">
                            <form action="/lesson-doubt-handler.php" method="POST" style="display:flex; gap:8px; align-items:center;">
                                <input type="hidden" name="action" value="reply">
                                <input type="hidden" name="doubt_id" value="<?= (int)$d['id'] ?>">
                                <input type="hidden" name="redirect" value="/student/doubts">
                                <input type="text" name="reply_text" class="input form-control" placeholder="Ask a follow-up or reply to teacher..." required style="flex:1; font-size:13px;">
                                <button type="submit" class="btn btn-secondary btn-sm" style="white-space:nowrap; font-weight:600;">Reply</button>
                                <a href="/lesson?id=<?= (int)$d['lesson_id'] ?>#lesson-doubts-section" class="btn btn-ghost btn-sm" target="_blank" title="View in lesson page">Open Lesson →</a>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function toggleAskModal() {
    const el = document.getElementById('askDoubtCard');
    if (!el) return;
    if (el.style.display === 'none' || el.style.display === '') {
        el.style.display = 'block';
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        el.style.display = 'none';
    }
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

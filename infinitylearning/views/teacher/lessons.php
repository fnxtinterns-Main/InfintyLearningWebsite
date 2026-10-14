<?php
use App\Auth;
use App\Helper;
use App\Lesson;
use App\Session;
use App\CurriculumCloner;
use App\Database;

Auth::requireLogin();
if (Auth::role() !== 'teacher') { http_response_code(403); exit('Forbidden'); }

$me      = Auth::user();
$filter  = $_GET['filter'] ?? '';

$all      = Lesson::listByCreator((int)$me['id']);
$lessons  = $all;

$db = Database::connect();
$myBatches = $db->query("SELECT * FROM batches WHERE teacher_id = " . (int)$me['id'] . " ORDER BY name ASC")->fetchAll();
if (empty($myBatches) && !empty($me['school_id'])) {
    $myBatches = $db->query("SELECT * FROM batches WHERE school_id = " . (int)$me['school_id'] . " ORDER BY name ASC")->fetchAll();
}
$curriculumPacks = CurriculumCloner::getPacks();

// Apply quick filters
if ($filter === 'published') {
    $lessons = array_filter($all, fn($l) => (int)$l['published'] === 1);
} elseif ($filter === 'draft') {
    $lessons = array_filter($all, fn($l) => (int)$l['published'] === 0);
}

// Stats
$stats = [
    'total'       => count($all),
    'published'   => count(array_filter($all, fn($l) => (int)$l['published'] === 1)),
    'drafts'      => count(array_filter($all, fn($l) => (int)$l['published'] === 0)),
    'attachments' => array_sum(array_map(fn($l) => count($l['attachments_array'] ?? []), $all)),
];

$pageTitle = 'My Lessons — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <div>
        <div class="flex between" style="align-items:flex-start; flex-wrap:wrap; gap:10px;">
            <div>
                <h1>📚 My Lessons</h1>
                <p class="muted">Lessons you've uploaded. Visible to your school (or a specific batch if targeted).</p>
            </div>
            <div class="flex" style="gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('clone-modal').style.display='flex'">
                    📦 Clone Ready Curriculum
                </button>
                <a href="/teacher/lessons/new" class="btn btn-primary">+ New lesson</a>
            </div>
        </div>

        <!-- ============ STAT TILES ============ -->
        <div class="grid grid-4 mt-3">
            <div class="stat"><span class="ico">📚</span><span class="n"><?= (int)$stats['total'] ?></span><span class="l">Total</span></div>
            <div class="stat" style="border-left:4px solid #16A34A"><span class="ico">✅</span><span class="n"><?= (int)$stats['published'] ?></span><span class="l">Published</span></div>
            <div class="stat" style="border-left:4px solid #F59E0B"><span class="ico">📝</span><span class="n"><?= (int)$stats['drafts'] ?></span><span class="l">Drafts</span></div>
            <div class="stat" style="border-left:4px solid #2563EB"><span class="ico">📎</span><span class="n"><?= (int)$stats['attachments'] ?></span><span class="l">Attachments</span></div>
        </div>

        <!-- ============ FILTERS ============ -->
        <div class="filters mt-3">
            <a href="?" class="<?= $filter === '' ? 'active' : '' ?>">All</a>
            <a href="?filter=published" class="<?= $filter === 'published' ? 'active' : '' ?>">Published</a>
            <a href="?filter=draft" class="<?= $filter === 'draft' ? 'active' : '' ?>">Drafts</a>
        </div>

        <!-- ============ LESSON LIST ============ -->
        <?php if (!$lessons): ?>
            <div class="card text-center mt-3">
                <img src="/assets/img/oldman.png" alt=""
                     style="max-height:110px;margin-bottom:.75rem;opacity:.85"
                     onerror="this.style.display='none'">
                <p class="muted">
                    <?php if ($filter): ?>
                        No lessons match this filter.
                    <?php else: ?>
                        You haven't uploaded any lessons yet.
                    <?php endif; ?>
                </p>
                <a href="/teacher/lessons/new" class="btn btn-primary mt-2">+ Create your first lesson</a>
            </div>
        <?php else: ?>
            <div class="grid grid-2 mt-3">
                <?php foreach ($lessons as $l):
                    $files = $l['attachments_array'] ?? [];
                    $fileCount = count($files);
                    $exts = array_unique(array_map(fn($f) => strtoupper($f['ext'] ?? ''), $files));
                ?>
                    <div class="card card-hover" style="padding:1.25rem">
                        <div class="flex between" style="align-items:flex-start;gap:.5rem">
                            <div class="flex" style="gap:.4rem;flex-wrap:wrap">
                                <span class="badge badge-primary"><?= Helper::e($l['subject_name']) ?></span>
                                <?php if (!empty($l['batch_name'])): ?>
                                    <span class="badge badge-info">🎓 <?= Helper::e($l['batch_name']) ?></span>
                                <?php endif; ?>
                                <span class="badge badge-<?= $l['published'] ? 'success' : 'muted' ?>">
                                    <?= $l['published'] ? 'published' : 'draft' ?>
                                </span>
                            </div>
                            <span class="muted" style="font-size:.75rem;white-space:nowrap">
                                <?= date('d M Y', strtotime($l['created_at'])) ?>
                            </span>
                        </div>

                        <h3 class="mt-2" style="margin-bottom:.35rem">
                            <a href="/lesson?id=<?= (int)$l['id'] ?>" style="color:inherit;text-decoration:none">
                                <?= Helper::e($l['title']) ?>
                            </a>
                        </h3>

                        <?php if (!empty($l['summary'])): ?>
                            <p class="muted" style="font-size:.88rem;margin-bottom:.5rem">
                                <?= Helper::e(mb_substr($l['summary'], 0, 140)) ?>
                            </p>
                        <?php endif; ?>

                        <div class="flex" style="gap:.4rem;flex-wrap:wrap;margin-top:.5rem">
                            <span class="pill">⏱ <?= (int)$l['estimated_minutes'] ?> min</span>
                            <span class="pill"><?= Helper::e(ucfirst($l['difficulty'])) ?></span>
                            <?php if ($fileCount > 0): ?>
                                <span class="pill" title="<?= Helper::e(implode(', ', $exts)) ?>">
                                    📎 <?= $fileCount ?> file<?= $fileCount > 1 ? 's' : '' ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="flex mt-2" style="gap:.3rem;flex-wrap:wrap">
                            <a class="btn btn-secondary btn-sm" href="/lesson?id=<?= (int)$l['id'] ?>" target="_blank">👁 Preview</a>
                            <a class="btn btn-ghost btn-sm" href="/teacher/lessons/edit?id=<?= (int)$l['id'] ?>">✏️ Edit</a>
                            <form method="post" action="/teacher/lesson-delete-handler.php" style="display:inline"
                                  onsubmit="return confirm('Delete this lesson permanently?')">
                                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
                                <button class="btn btn-danger btn-sm">🗑 Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============ CLONE CURRICULUM MODAL ============ -->
<div id="clone-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);z-index:9999;align-items:center;justify-content:center;padding:1rem">
    <div class="card" style="max-width:720px;width:100%;max-height:90vh;overflow-y:auto;position:relative;padding:24px;border-radius:14px;box-shadow:0 20px 40px rgba(0,0,0,0.3)">
        <div class="flex items-center justify-between" style="margin-bottom:1rem">
            <div>
                <h2 style="margin:0;font-size:1.35rem">📦 Clone Ready-to-Use Curriculum Pack</h2>
                <p class="muted" style="margin:.2rem 0 0;font-size:.85rem">1-click import verified syllabi, structured lesson plans, and 10-MCQ quizzes into your classroom batch.</p>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('clone-modal').style.display='none'" style="font-size:1.2rem;line-height:1">✕</button>
        </div>

        <form action="/curriculum-clone-handler.php" method="POST">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">

            <div class="form-group" style="margin-bottom:1.2rem">
                <label style="font-weight:700;display:block;margin-bottom:.4rem;font-size:.9rem">Target Batch for Imported Lessons *</label>
                <select name="batch_id" class="input" required style="width:100%">
                    <option value="">-- Choose which batch will receive these lessons --</option>
                    <?php foreach ($myBatches as $b): ?>
                        <option value="<?= (int)$b['id'] ?>"><?= Helper::e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($myBatches)): ?>
                    <p style="color:var(--danger);font-size:.8rem;margin-top:.3rem">No batches found. Please create a batch first before cloning.</p>
                <?php endif; ?>
            </div>

            <div style="font-weight:700;margin-bottom:.6rem;font-size:.9rem">Select Curriculum Pack *</div>
            <div style="display:flex;flex-direction:column;gap:.9rem;margin-bottom:1.5rem">
                <?php foreach ($curriculumPacks as $pKey => $p): ?>
                    <label class="card p-3" style="cursor:pointer;border:2px solid var(--border);border-radius:10px;display:flex;align-items:flex-start;gap:.8rem;transition:border-color .2s;background:var(--surface)">
                        <input type="radio" name="pack_id" value="<?= Helper::e($pKey) ?>" required style="margin-top:.3rem;accent-color:var(--primary)">
                        <div style="flex:1">
                            <div class="flex items-center justify-between">
                                <strong style="font-size:1.02rem;color:var(--text)"><?= Helper::e($p['title']) ?></strong>
                                <span class="badge badge-primary"><?= Helper::e($p['badge']) ?></span>
                            </div>
                            <p class="muted" style="font-size:.85rem;margin:.3rem 0 .5rem"><?= Helper::e($p['description']) ?></p>
                            <div class="flex" style="gap:.4rem;flex-wrap:wrap">
                                <?php foreach ($p['lessons'] as $pl): ?>
                                    <span class="pill" style="font-size:.74rem">📖 <?= Helper::e($pl['title']) ?> (<?= (int)$pl['minutes'] ?>m)</span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('clone-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:.6rem 1.8rem;font-weight:700" <?= empty($myBatches) ? 'disabled' : '' ?>>
                    🚀 Clone Lessons & 10-MCQ Quizzes
                </button>
            </div>
        </form>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
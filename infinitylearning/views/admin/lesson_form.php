<?php
use App\Auth;
use App\Helper;
use App\Lesson;
use App\Session;
use App\Batch;
use App\Database;

Auth::requireLogin();
if (!in_array(Auth::role(), ['admin','teacher'], true)) { http_response_code(403); exit('Forbidden'); }

$isSuper    = Auth::isSuperAdmin();
$isSchool   = Auth::isSchoolAdmin();
$isTeacher  = Auth::role() === 'teacher';
$me         = Auth::user();

$editing = isset($_GET['id']);
$lesson  = null;
$existingAttachments = [];

if ($editing) {
    $lesson = Lesson::find((int)$_GET['id']);
    if (!$lesson) { Helper::redirect('/admin/lessons'); }

    // Ownership checks
    if ($isTeacher && (int)$lesson['created_by'] !== (int)$me['id']) {
        http_response_code(403); exit('You can only edit your own lessons.');
    }
    if ($isSchool && (int)$lesson['school_id'] !== (int)$me['school_id']) {
        http_response_code(403); exit('You cannot edit this lesson.');
    }

    $existingAttachments = $lesson['attachments_array'] ?? [];
}

$subjects = Lesson::subjects();

// ---------- Batch list depends on role ----------
$batches = [];
if ($isSuper) {
    $batches = Database::connect()->query("
        SELECT b.id, b.name, b.subject, sc.name AS school_name
        FROM batches b LEFT JOIN schools sc ON sc.id = b.school_id
        WHERE b.status='active' ORDER BY b.created_at DESC
    ")->fetchAll();
} else {
    $batches = Batch::all($isTeacher ? (int)$me['id'] : null);
    if ($isSchool) {
        // School admin sees all school batches (not just their own — they don't own any)
        $stmt = Database::connect()->prepare("
            SELECT b.id, b.name, b.subject
            FROM batches b
            WHERE b.school_id = ? AND b.status='active'
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([(int)$me['school_id']]);
        $batches = $stmt->fetchAll();
    }
}

// ---------- Schools list (super admin only) ----------
$schools = $isSuper
    ? Database::connect()->query("SELECT id, name FROM schools WHERE status='active' ORDER BY name")->fetchAll()
    : [];

$pageTitle = ($editing ? 'Edit Lesson' : 'New Lesson') . ' — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php
    if ($isSuper)       require VIEW_PATH . '/admin/_sidebar.php';
    elseif ($isSchool)  require VIEW_PATH . '/school/_sidebar.php';
    else                require VIEW_PATH . '/teacher/_sidebar.php';
    ?>
    <div>
        <h1><?= $editing ? 'Edit Lesson' : 'New Lesson' ?></h1>
        <p class="muted">
            <?php if ($isSuper): ?>
                Publish globally, or scope to a specific school / batch.
            <?php elseif ($isSchool): ?>
                This lesson will be visible to your school only.
            <?php else: ?>
                Your lesson will be visible to your school. You can also target a specific batch.
            <?php endif; ?>
        </p>

        <form method="post" action="/admin/lesson-save-handler.php" class="card mt-2" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= (int)$lesson['id'] ?>">
            <?php endif; ?>

            <!-- ============== SCOPE (super admin only) ============== -->
            <?php if ($isSuper): ?>
                <div class="form-group">
                    <label>Scope *</label>
                    <select name="school_id" required>
                        <option value="">🌐 Global — everyone sees this</option>
                        <?php foreach ($schools as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"
                                <?= $editing && (int)($lesson['school_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>
                                🏫 <?= Helper::e($s['name']) ?> only
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Choose "Global" for platform-wide lessons, or select a school to keep it private.</small>
                </div>
            <?php elseif ($isSchool): ?>
                <div class="alert alert-info" style="margin-bottom:1rem">
                    This lesson will be visible to <strong>your school only</strong>.
                </div>
            <?php endif; ?>

            <!-- ============== BATCH TARGETING (all roles, optional) ============== -->
            <div class="form-group">
                <label>Target specific batch <span class="muted">(optional)</span></label>
                <select name="batch_id">
                    <option value="">— Whole school / everyone —</option>
                    <?php foreach ($batches as $b): ?>
                        <option value="<?= (int)$b['id'] ?>"
                            <?= $editing && (int)($lesson['batch_id'] ?? 0) === (int)$b['id'] ? 'selected' : '' ?>>
                            <?= Helper::e($b['name']) ?>
                            <?php if (!empty($b['subject'])): ?> · <?= Helper::e($b['subject']) ?><?php endif; ?>
                            <?php if (!empty($b['school_name'])): ?> (<?= Helper::e($b['school_name']) ?>)<?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Leave empty to share with the whole school. Choose a batch to target only its students.</small>
            </div>

            <div class="divider"></div>

            <!-- ============== BASIC FIELDS ============== -->
            <div class="form-row">
                <div class="form-group">
                    <label>Subject *</label>
                    <select name="subject_id" required>
                        <option value="">— choose a subject —</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"
                                <?= $editing && (int)$lesson['subject_id'] === (int)$s['id'] ? 'selected' : '' ?>>
                                <?= Helper::e($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Difficulty</label>
                    <select name="difficulty">
                        <?php foreach (['easy','medium','hard'] as $d): ?>
                            <option value="<?= $d ?>" <?= ($lesson['difficulty'] ?? 'medium') === $d ? 'selected' : '' ?>>
                                <?= ucfirst($d) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required maxlength="200"
                       value="<?= Helper::e($lesson['title'] ?? '') ?>"
                       placeholder="e.g. Introduction to Fractions">
            </div>

            <div class="form-group">
                <label>Summary</label>
                <input type="text" name="summary" maxlength="300"
                       value="<?= Helper::e($lesson['summary'] ?? '') ?>"
                       placeholder="One-line description for the lesson card">
            </div>

            <div class="form-group">
                <label>Content</label>
                <textarea name="content" rows="8"
                          placeholder="Write the lesson content here. Plain text is fine."><?= Helper::e($lesson['content'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Video URL (embed)</label>
                    <input type="url" name="video_url"
                           value="<?= Helper::e($lesson['video_url'] ?? '') ?>"
                           placeholder="https://www.youtube.com/embed/xxxxx">
                    <small>Use the /embed/ form for YouTube URLs.</small>
                </div>
                <div class="form-group">
                    <label>Estimated minutes</label>
                    <input type="number" name="estimated_minutes" min="1" max="600"
                           value="<?= Helper::e((string)($lesson['estimated_minutes'] ?? 15)) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Publish status</label>
                <select name="published">
                    <option value="1" <?= ($lesson['published'] ?? 1) == 1 ? 'selected' : '' ?>>Published (students see it)</option>
                    <option value="0" <?= ($lesson['published'] ?? 1) == 0 ? 'selected' : '' ?>>Draft (hidden from students)</option>
                </select>
            </div>

            <!-- ============== EXISTING ATTACHMENTS (edit only) ============== -->
            <?php if ($editing && $existingAttachments): ?>
                <div class="divider"></div>
                <h3 style="font-size:1rem;margin-bottom:.5rem">📎 Current attachments</h3>
                <p class="muted" style="font-size:.85rem">Uncheck to remove a file from this lesson.</p>

                <div class="attachment-grid">
                    <?php foreach ($existingAttachments as $a):
                        $ext  = strtolower($a['ext'] ?? pathinfo($a['path'], PATHINFO_EXTENSION));
                        $icon = Lesson::fileIcon($ext);
                        $sizeKb = $a['size'] ? round($a['size'] / 1024) : null;
                    ?>
                        <label class="attachment-chip">
                            <input type="checkbox" name="keep_attachments[]"
                                   value="<?= Helper::e($a['path']) ?>" checked>
                            <span class="attachment-chip-icon"><?= $icon ?></span>
                            <span class="attachment-chip-body">
                                <strong><?= Helper::e($a['name']) ?></strong>
                                <small><?= strtoupper($ext) ?><?= $sizeKb ? ' · ' . $sizeKb . ' KB' : '' ?></small>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ============== NEW UPLOAD ============== -->
            <div class="divider"></div>
            <div class="form-group">
                <label>Add attachments</label>
                <input type="file" name="attachments[]" multiple
                       accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                <small>
                    Allowed: JPG, PNG, WebP, GIF (max 4MB each),
                    PDF, DOC/DOCX, XLS/XLSX (max 10MB each),
                    PPT/PPTX (max 15MB each).
                    Max 10 files, total up to 40MB per lesson.
                </small>
            </div>

            <!-- ============== SUBMIT ============== -->
            <div class="flex mt-2">
                <button class="btn btn-primary"><?= $editing ? 'Save changes' : 'Publish lesson' ?></button>
                <a href="<?= $isTeacher ? '/teacher/lessons' : '/admin/lessons' ?>" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
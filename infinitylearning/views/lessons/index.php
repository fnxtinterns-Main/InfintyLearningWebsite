<?php
use App\Lesson;
use App\Helper;
use App\Auth;

$subjects  = Lesson::subjects();
$subjectId = isset($_GET['subject']) ? (int)$_GET['subject'] : null;

$lessons = Lesson::listForViewer([
    'subject_id' => $subjectId,
    'limit'      => 200,
]);

$user = Auth::user();
$pageTitle = 'Lessons — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';

// Count lessons per subject for the tile badges
$countsBySubject = [];
foreach ($lessons as $l) {
    $countsBySubject[(int)$l['subject_id']] = ($countsBySubject[(int)$l['subject_id']] ?? 0) + 1;
}
// Note: countsBySubject only reflects current filter scope; that's fine for display
?>

<!-- ============================================================
     PAGE HERO
     ============================================================ -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">📚 LESSON LIBRARY</span>
                <h1>Structured learning,<br>from classroom to mastery</h1>
                <p>
                    Bite-sized lessons across all subjects — with PDFs, videos and study notes
                    curated by teachers and school admins.
                </p>
            </div>
            <div class="page-hero-stats">
                <div class="page-hero-stat">
                    <div class="page-hero-stat-n"><?= count($lessons) ?></div>
                    <div class="page-hero-stat-l">Lessons live</div>
                </div>
                <div class="page-hero-stat">
                    <div class="page-hero-stat-n"><?= count($subjects) ?></div>
                    <div class="page-hero-stat-l">Subjects</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SUBJECT TILES
     ============================================================ -->
<section class="section-sm">
    <div class="container">
        <div class="subject-grid">
            <a href="/lessons" class="subject-tile <?= !$subjectId ? 'active' : '' ?>">
                <div class="subject-tile-icon" style="background:var(--il-grad-soft);color:var(--il-primary)">✨</div>
                <div class="subject-tile-name">All subjects</div>
                <div class="subject-tile-count"><?= count($lessons) ?></div>
            </a>
            <?php foreach ($subjects as $s): ?>
                <a href="/lessons?subject=<?= (int)$s['id'] ?>"
                   class="subject-tile <?= $subjectId === (int)$s['id'] ? 'active' : '' ?>">
                    <div class="subject-tile-icon"
                         style="background:<?= Helper::e($s['color'] ?: '#FF7A18') ?>22;color:<?= Helper::e($s['color'] ?: '#FF7A18') ?>">
                        <?= Helper::e($s['icon'] ?: '📖') ?>
                    </div>
                    <div class="subject-tile-name"><?= Helper::e($s['name']) ?></div>
                    <?php if (!empty($countsBySubject[(int)$s['id']])): ?>
                        <div class="subject-tile-count"><?= (int)$countsBySubject[(int)$s['id']] ?></div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     RESULTS
     ============================================================ -->
<section class="section" style="padding-top:0">
    <div class="container">
        <?php if ($user && !empty($user['school_id']) && Auth::role() !== 'admin'): ?>
            <div class="results-meta">
                Showing global lessons + lessons from your school.
            </div>
        <?php endif; ?>

        <?php if (!$lessons): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📖</div>
                <h3>No lessons yet</h3>
                <p>
                    <?php if ($subjectId): ?>
                        No lessons for this subject yet — check back soon.
                    <?php else: ?>
                        Lessons are added regularly. Check back soon.
                    <?php endif; ?>
                </p>
                <a href="/lessons" class="btn btn-primary">See all subjects</a>
            </div>
        <?php else: ?>
            <div class="grid grid-3">
                <?php foreach ($lessons as $l):
                    $attachments = $l['attachments_array'] ?? [];
                    $fileCount   = count($attachments);
                    $hasPdf = false;
                    foreach ($attachments as $a) {
                        if (strtolower($a['ext'] ?? '') === 'pdf') { $hasPdf = true; break; }
                    }
                ?>
                    <a class="activity-card activity-card-lg reveal" href="/lesson?id=<?= (int)$l['id'] ?>">
                        <div class="lesson-cover"
                             style="background:linear-gradient(135deg,<?= Helper::e($l['subject_color'] ?: '#FF7A18') ?>22,var(--surface-2))">
                            <span class="lesson-cover-icon">📖</span>
                            <div class="lesson-cover-badges">
                                <span class="badge badge-primary"><?= Helper::e($l['subject_name']) ?></span>
                                <?php if (!empty($l['school_name'])): ?>
                                    <span class="badge badge-brand"><?= Helper::e($l['school_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="activity-body">
                            <h3><?= Helper::e($l['title']) ?></h3>
                            <p><?= Helper::e(mb_substr($l['summary'] ?? '', 0, 110)) ?></p>
                            <div class="activity-meta">
                                <span>⏱ <?= (int)$l['estimated_minutes'] ?> min</span>
                                <span class="pill"><?= Helper::e($l['difficulty']) ?></span>
                                <?php if ($fileCount > 0): ?>
                                    <span class="pill">📎 <?= $fileCount ?></span>
                                <?php endif; ?>
                                <?php if ($hasPdf): ?>
                                    <span class="pill">📄 PDF</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
<?php
use App\Activity;
use App\Helper;
use App\Auth;

$cat = $_GET['category'] ?? '';
$q   = trim($_GET['q'] ?? '');

$user = Auth::user();
$activities = Activity::listForViewer([
    'category' => $cat,
    'status'   => 'published',
    'q'        => $q,
]);

$pageTitle = 'Activities & Competitions — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';

$totalCount = count($activities);
$categoryCounts = [];
foreach ($activities as $a) {
    $categoryCounts[$a['category']] = ($categoryCounts[$a['category']] ?? 0) + 1;
}
?>

<!-- ============================================================
     PAGE HERO
     ============================================================ -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">🏆 ACTIVITIES &amp; COMPETITIONS</span>
                <h1>Discover events that shape<br>tomorrow's leaders</h1>
                <p>
                    Creative, academic, sports, cultural and technology events —
                    curated for every learner, scoped to your school and beyond.
                </p>
            </div>
            <div class="page-hero-stats">
                <div class="page-hero-stat">
                    <div class="page-hero-stat-n"><?= $totalCount ?></div>
                    <div class="page-hero-stat-l">Events live</div>
                </div>
                <div class="page-hero-stat">
                    <div class="page-hero-stat-n"><?= count(ACTIVITY_CATEGORIES) ?></div>
                    <div class="page-hero-stat-l">Categories</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     FILTERS + SEARCH
     ============================================================ -->
<section class="section-sm">
    <div class="container">
        <div class="filters-bar">
            <div class="filters">
                <a href="/activities" class="<?= $cat === '' ? 'active' : '' ?>">
                    All
                    <?php if ($cat === '' && $totalCount > 0): ?>
                        <span class="filter-count"><?= $totalCount ?></span>
                    <?php endif; ?>
                </a>
                <?php foreach (ACTIVITY_CATEGORIES as $slug => $label): ?>
                    <a href="/activities?category=<?= urlencode($slug) ?>"
                       class="<?= $cat === $slug ? 'active' : '' ?>">
                        <?= $label ?>
                        <?php if (!empty($categoryCounts[$slug])): ?>
                            <span class="filter-count"><?= (int)$categoryCounts[$slug] ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <form method="get" class="search-box">
                <?php if ($cat): ?>
                    <input type="hidden" name="category" value="<?= Helper::e($cat) ?>">
                <?php endif; ?>
                <span class="search-icon">🔍</span>
                <input type="text" name="q" placeholder="Search activities…" value="<?= Helper::e($q) ?>">
                <?php if ($q): ?>
                    <a href="/activities<?= $cat ? '?category=' . urlencode($cat) : '' ?>" class="search-clear">×</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>

<!-- ============================================================
     RESULTS
     ============================================================ -->
<section class="section" style="padding-top:0">
    <div class="container">
        <?php if (!$activities): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🎨</div>
                <h3>No activities found</h3>
                <p>
                    <?php if ($q || $cat): ?>
                        Try changing the filter or search term.
                    <?php else: ?>
                        New events are added regularly — check back soon.
                    <?php endif; ?>
                </p>
                <?php if ($q || $cat): ?>
                    <a href="/activities" class="btn btn-primary">Show all activities</a>
                <?php else: ?>
                    <a href="/register?type=student" class="btn btn-primary">Create your free account</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if ($q || $cat): ?>
                <div class="results-meta">
                    Showing <strong><?= $totalCount ?></strong> result<?= $totalCount === 1 ? '' : 's' ?>
                    <?php if ($cat): ?> in <strong><?= Helper::e(ACTIVITY_CATEGORIES[$cat] ?? $cat) ?></strong><?php endif; ?>
                    <?php if ($q): ?> for "<strong><?= Helper::e($q) ?></strong>"<?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-3">
                <?php foreach ($activities as $a):
                    $cover = $a['cover_image'] ?: '/assets/img/placeholder-activity.png';
                    $isGlobal = empty($a['school_id']);
                ?>
                    <a class="activity-card activity-card-lg reveal" href="/activity?slug=<?= urlencode($a['slug']) ?>">
                        <div class="activity-card-cover-wrap">
                            <img class="activity-cover" src="<?= Helper::e($cover) ?>" alt=""
                                 onerror="this.style.display='none'">
                            <div class="activity-card-badges">
                                <span class="badge badge-primary"><?= ucfirst($a['category']) ?></span>
                                <?php if ($isGlobal): ?>
                                    <span class="badge badge-info">Global</span>
                                <?php elseif (!empty($a['school_name'])): ?>
                                    <span class="badge badge-brand"><?= Helper::e($a['school_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($a['featured'])): ?>
                                <div class="activity-card-featured">⭐ Featured</div>
                            <?php endif; ?>
                        </div>
                        <div class="activity-body">
                            <h3><?= Helper::e($a['title']) ?></h3>
                            <div class="activity-meta">
                                <?php if ($a['event_date']): ?>
                                    <span>📅 <?= date('d M Y', strtotime($a['event_date'])) ?></span>
                                <?php endif; ?>
                                <?php if ($a['venue']): ?>
                                    <span>📍 <?= Helper::e($a['venue']) ?></span>
                                <?php endif; ?>
                                <?php if (($a['fee'] ?? 0) > 0): ?>
                                    <span>💰 ₹<?= number_format((float)$a['fee'], 0) ?></span>
                                <?php else: ?>
                                    <span>🎟 Free</span>
                                <?php endif; ?>
                            </div>
                            <p><?= Helper::e(mb_substr($a['short_description'] ?? '', 0, 120)) ?></p>
                            <div class="actions">
                                <span class="btn btn-secondary btn-sm">View details →</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
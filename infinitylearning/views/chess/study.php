<?php
use App\Helper;
use App\Auth;
use App\Chess\ChessRating;
use App\Chess\ChessCurriculum;

Auth::requireLogin();
$user = Auth::user();
$userId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

$chessProfile = ChessRating::getOrCreate($userId, $schoolId);
$activeTier = $_GET['tier'] ?? $chessProfile['tier'];
$activeCat  = $_GET['category'] ?? null;

$tierInfo = ChessRating::TIERS[$activeTier] ?? ChessRating::TIERS['learner'];
$lessons = ChessCurriculum::getForTier($activeTier, $activeCat);

$pageTitle = 'Study Curriculum — Rising Rooks Chess Academy';
require VIEW_PATH . '/layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/chess.css">

<main class="page-body">
<div class="container">

    <!-- Header -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                <a href="/chess" class="btn btn-sm btn-outline-secondary">← Chess Hub</a>
                <span class="chess-badge badge-<?= Helper::e($activeTier) ?>">
                    <?= $tierInfo['badge'] ?> <?= Helper::e($tierInfo['label']) ?> Batch
                </span>
            </div>
            <h1 style="margin:0; font-size:26px; font-family:'Fraunces', serif;">
                Study & Curriculum Tracks
            </h1>
        </div>

        <!-- Tier Switcher -->
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
            <?php foreach (ChessRating::TIERS as $k => $t): ?>
                <a href="/chess/study?tier=<?= $k ?>" class="btn btn-sm <?= ($activeTier === $k) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <?= $t['badge'] ?> <?= $t['label'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Category Filter Pills -->
    <div style="display:flex; gap:10px; margin-bottom:28px; flex-wrap:wrap;">
        <a href="/chess/study?tier=<?= $activeTier ?>" class="btn btn-sm <?= empty($activeCat) ? 'btn-dark' : 'btn-outline-secondary' ?>">
            All Modules
        </a>
        <a href="/chess/study?tier=<?= $activeTier ?>&category=opening" class="btn btn-sm <?= ($activeCat === 'opening') ? 'btn-dark' : 'btn-outline-secondary' ?>">
            📖 Openings
        </a>
        <a href="/chess/study?tier=<?= $activeTier ?>&category=middlegame" class="btn btn-sm <?= ($activeCat === 'middlegame') ? 'btn-dark' : 'btn-outline-secondary' ?>">
            ⚔️ Middlegame Strategy
        </a>
        <a href="/chess/study?tier=<?= $activeTier ?>&category=endgame" class="btn btn-sm <?= ($activeCat === 'endgame') ? 'btn-dark' : 'btn-outline-secondary' ?>">
            👑 Endgame Technique
        </a>
        <a href="/chess/study?tier=<?= $activeTier ?>&category=tactics" class="btn btn-sm <?= ($activeCat === 'tactics') ? 'btn-dark' : 'btn-outline-secondary' ?>">
            ⚡ Tactics & Traps
        </a>
    </div>

    <!-- LESSONS LIST -->
    <?php if (empty($lessons)): ?>
        <div class="card" style="padding:48px; text-align:center; color:var(--il-muted);">
            No curriculum lessons found for this tier and filter. Check back soon or switch tiers above!
        </div>
    <?php else: ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">
            <?php foreach ($lessons as $les): ?>
                <div class="curriculum-card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                        <span class="badge" style="background:#F1F5F9; color:#475569; font-weight:700; text-transform:uppercase;">
                            <?= Helper::e($les['category']) ?>
                        </span>
                        <span style="font-size:12px; color:var(--il-muted); font-weight:600;">
                            #<?= (int)$les['sort_order'] ?>
                        </span>
                    </div>

                    <h3 style="margin:0 0 6px; font-size:18px; color:var(--il-text);">
                        <?= Helper::e($les['title']) ?>
                    </h3>
                    <div style="font-size:13px; color:var(--il-primary); font-weight:600; margin-bottom:10px;">
                        <?= Helper::e($les['subtitle'] ?? '') ?>
                    </div>
                    <p style="font-size:13px; color:var(--il-muted); line-height:1.5; margin:0 0 16px;">
                        <?= Helper::e($les['description']) ?>
                    </p>

                    <?php if (!empty($les['key_concepts'])): ?>
                        <div style="background:var(--surface-0); padding:10px 12px; border-radius:var(--r-md); font-size:12px; margin-bottom:16px;">
                            <strong style="color:var(--il-text);">Key Takeaway:</strong> <?= Helper::e($les['key_concepts']) ?>
                        </div>
                    <?php endif; ?>

                    <div style="background:#FEF3C7; border:1px solid #FCD34D; border-radius:var(--r-md); padding:8px 12px; font-family:'JetBrains Mono', monospace; font-size:12px; color:#92400E; margin-bottom:16px;">
                        Line: <?= Helper::e($les['solution_pgn'] ?? 'Standard Development') ?>
                    </div>

                    <a href="/chess/play?mode=bot" class="btn btn-sm btn-outline-primary" style="width:100%;">
                        Practice This on Board →
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
</main>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

<?php
use App\Helper;
use App\Auth;
use App\Database;
use App\Chess\ChessRating;
use App\Chess\ChessGame;

Auth::requireSuperAdmin();

$db = Database::connect();

// Platform stats
$totalPlayers = (int)$db->query("SELECT COUNT(*) FROM chess_ratings")->fetchColumn();
$totalGames   = (int)$db->query("SELECT COUNT(*) FROM chess_games")->fetchColumn();
$calibrated   = (int)$db->query("SELECT COUNT(*) FROM chess_ratings WHERE calibration_completed = 1")->fetchColumn();

// Tier distribution across platform
$tierRows = $db->query("SELECT tier, COUNT(*) as cnt FROM chess_ratings GROUP BY tier")->fetchAll();
$tierDist = ['learner'=>0, 'rookie'=>0, 'intermediate'=>0, 'master'=>0, 'grandmaster'=>0];
foreach ($tierRows as $tr) {
    $tierDist[$tr['tier']] = (int)$tr['cnt'];
}

// Top rated students across all schools
$topStudents = $db->query("
    SELECT cr.*, s.full_name, sch.name AS school_name
    FROM chess_ratings cr
    JOIN students s ON s.user_id = cr.student_user_id
    LEFT JOIN schools sch ON sch.id = cr.school_id
    ORDER BY cr.current_rating DESC LIMIT 20
")->fetchAll();

$pageTitle = 'Rising Rooks Chess Academy — Super Admin Overview';
require VIEW_PATH . '/layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/chess.css">

<main class="page-body">
<div class="container">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <div style="display:flex; align-items:center; gap:16px;">
            <img src="/assets/img/chess/chess_logo.jpg" alt="Rising Rooks" style="width:54px; height:54px; border-radius:12px; object-fit:cover;">
            <div>
                <h1 style="margin:0; font-size:24px; font-family:'Fraunces', serif;">
                    Rising Rooks Chess Academy • Platform Overview
                </h1>
                <p style="margin:2px 0 0; color:var(--il-muted); font-size:13px;">
                    Platform-wide rating bell curves, school championship rankings, and curriculum oversight.
                </p>
            </div>
        </div>

        <a href="/chess" class="btn btn-outline-primary">Open Chess Hub ♟️</a>
    </div>

    <!-- STAT METRICS -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin-bottom:32px;">
        <div class="card" style="padding:20px;">
            <div style="font-size:12px; color:var(--il-muted); text-transform:uppercase; font-weight:700;">Total Players</div>
            <div style="font-size:32px; font-weight:800; font-family:'JetBrains Mono', monospace; color:var(--chess-navy); margin:6px 0;">
                <?= $totalPlayers ?>
            </div>
            <div style="font-size:12px; color:#16A34A; font-weight:600;">Enrolled Students</div>
        </div>

        <div class="card" style="padding:20px;">
            <div style="font-size:12px; color:var(--il-muted); text-transform:uppercase; font-weight:700;">Games Played</div>
            <div style="font-size:32px; font-weight:800; font-family:'JetBrains Mono', monospace; color:var(--il-primary); margin:6px 0;">
                <?= $totalGames ?>
            </div>
            <div style="font-size:12px; color:var(--il-muted);">Recorded PGN Matches</div>
        </div>

        <div class="card" style="padding:20px;">
            <div style="font-size:12px; color:var(--il-muted); text-transform:uppercase; font-weight:700;">Fully Calibrated</div>
            <div style="font-size:32px; font-weight:800; font-family:'JetBrains Mono', monospace; color:#2563EB; margin:6px 0;">
                <?= $calibrated ?>
            </div>
            <div style="font-size:12px; color:var(--il-muted);">Completed 5-Match Gauntlet</div>
        </div>
    </div>

    <!-- GLOBAL TIER DISTRIBUTION -->
    <div class="card" style="padding:24px; margin-bottom:32px;">
        <h3 style="margin:0 0 16px; font-size:18px;">Global Tier Distribution</h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
            <?php foreach (ChessRating::TIERS as $k => $t): ?>
                <div style="background:var(--surface-0); padding:16px; border-radius:var(--r-md); border-top:3px solid <?= $t['color'] ?>;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span><?= $t['badge'] ?> <strong><?= $t['label'] ?></strong></span>
                        <span style="font-family:'JetBrains Mono', monospace; font-weight:800; font-size:16px;"><?= $tierDist[$k] ?></span>
                    </div>
                    <div style="font-size:11px; color:var(--il-muted); margin-top:4px;"><?= $t['min'] ?>–<?= $t['max'] ?> Elo</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PLATFORM TOP PLAYERS TABLE -->
    <div class="card" style="padding:24px;">
        <h3 style="margin:0 0 16px; font-size:18px;">Platform Hall of Fame (Top 20 Rated Rooks)</h3>
        <div style="overflow-x:auto;">
            <table class="table" style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="border-bottom:2px solid var(--il-border); text-align:left; color:var(--il-muted); font-size:12px; text-transform:uppercase;">
                        <th style="padding:10px;">Rank</th>
                        <th style="padding:10px;">Player</th>
                        <th style="padding:10px;">School</th>
                        <th style="padding:10px;">Rating</th>
                        <th style="padding:10px;">Tier</th>
                        <th style="padding:10px;">Games Won</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; foreach ($topStudents as $tp): ?>
                        <tr style="border-bottom:1px solid var(--il-border);">
                            <td style="padding:10px; font-weight:800; font-family:'JetBrains Mono', monospace;">#<?= $rank++ ?></td>
                            <td style="padding:10px; font-weight:600;"><?= Helper::e($tp['full_name']) ?></td>
                            <td style="padding:10px; color:var(--il-muted);"><?= Helper::e($tp['school_name'] ?? 'Direct') ?></td>
                            <td style="padding:10px; font-family:'JetBrains Mono', monospace; font-weight:800; color:var(--chess-navy);">
                                <?= (int)$tp['current_rating'] ?>
                            </td>
                            <td style="padding:10px;">
                                <span class="chess-badge badge-<?= Helper::e($tp['tier']) ?>">
                                    <?= ucfirst(Helper::e($tp['tier'])) ?>
                                </span>
                            </td>
                            <td style="padding:10px; font-family:'JetBrains Mono', monospace; color:#16A34A; font-weight:700;">
                                <?= (int)$tp['wins'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

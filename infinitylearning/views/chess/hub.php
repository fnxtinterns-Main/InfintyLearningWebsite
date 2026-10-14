<?php
use App\Helper;
use App\Auth;
use App\Chess\ChessRating;
use App\Chess\ChessGame;
use App\Chess\ChessCurriculum;
use App\Chess\ChessLive;

Auth::requireLogin();
$user = Auth::user();
$role = Auth::role();

// Role-based routing: Teachers, School Admins, and Super Admins get their dedicated dashboards
if ($role === 'teacher') {
    require VIEW_PATH . '/teacher/chess.php';
    return;
} elseif ($role === 'school') {
    require VIEW_PATH . '/school/chess.php';
    return;
} elseif ($role === 'admin') {
    require VIEW_PATH . '/admin/chess.php';
    return;
}

$userId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

// Get or initialize chess rating profile
$chessProfile = ChessRating::getOrCreate($userId, $schoolId);
$tierKey = $chessProfile['tier'];
$tierInfo = ChessRating::TIERS[$tierKey] ?? ChessRating::TIERS['learner'];

// Check active live coaching sessions
$activeLive = ChessLive::getActiveSessionsForUser($userId, Auth::role());

// Recent games & leaderboard
$recentGames = ChessGame::getStudentGames($userId, 5);
$leaderboard = ChessGame::getSchoolLeaderboard($schoolId, 5);

$pageTitle = 'Rising Rooks Chess Academy — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/chess.css">

<main class="page-body">
<div class="container">

    <!-- ==================== HERO BRAND BANNER ==================== -->
    <div class="chess-hero-banner">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:20px;">
            <div style="display:flex; align-items:center; gap:20px;">
                <img src="/assets/img/chess/chess_logo.jpg" alt="Rising Rooks Chess Academy"
                     style="width:90px; height:90px; border-radius:18px; object-fit:cover; border:2px solid var(--chess-gold); box-shadow:0 8px 16px rgba(0,0,0,0.3);">
                <div>
                    <span style="font-size:12px; font-weight:700; letter-spacing:0.1em; color:var(--chess-gold); text-transform:uppercase;">
                        Infinity Learning • Rising Rooks
                    </span>
                    <h1 style="color:#fff; font-size:28px; margin:4px 0 6px; font-family:'Fraunces', serif;">
                        Rising Rooks Chess Academy
                    </h1>
                    <p style="color:#CBD5E1; font-size:14px; margin:0; max-width:540px;">
                        "Think Deeper, Rise Faster." Calibrate your ranking, attend live classes with FIDE coaches, master tactics, and compete in the Rooks League.
                    </p>
                </div>
            </div>

            <!-- Current Rating Widget -->
            <div style="background:rgba(255,255,255,0.08); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.15); border-radius:16px; padding:16px 24px; text-align:center; min-width:180px;">
                <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.05em; color:#94A3B8;">Current Rating</div>
                <div style="font-size:36px; font-weight:800; font-family:'JetBrains Mono', monospace; color:var(--chess-gold-light); margin:4px 0;">
                    <?= (int)$chessProfile['current_rating'] ?>
                </div>
                <div class="chess-badge badge-<?= Helper::e($tierKey) ?>">
                    <span><?= $tierInfo['badge'] ?></span>
                    <span><?= Helper::e($tierInfo['label']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== CALIBRATION GAUNTLET ALERT ==================== -->
    <?php if (!$chessProfile['calibration_completed']): ?>
    <div class="calibration-tracker">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div>
                <h3 style="color:#1E3A8A; margin:0 0 4px; font-size:18px;">
                    🎯 5-Match Placement Gauntlet
                </h3>
                <p style="color:#3B82F6; font-size:14px; margin:0;">
                    Play 5 calibration matches against Stockfish AI to determine your initial ranking and assign you to your training batch (0–500 Learner, 501–1000 Rookie, etc.).
                </p>
            </div>
            <a href="/chess/play?mode=calibration" class="btn btn-primary" style="background:#2563EB; border-color:#1D4ED8; font-weight:700;">
                Play Match #<?= min(5, (int)$chessProfile['calibration_matches_played'] + 1) ?> →
            </a>
        </div>

        <div class="calibration-dots">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php
                    $isDone = ($i <= (int)$chessProfile['calibration_matches_played']);
                    $isActive = ($i === (int)$chessProfile['calibration_matches_played'] + 1);
                ?>
                <div class="calib-dot <?= $isDone ? 'completed' : ($isActive ? 'active' : '') ?>">
                    <?= $isDone ? '✓ Match ' . $i : 'Match ' . $i ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ==================== PROMOTION GAUNTLET CALLOUT ==================== -->
    <?php if ($chessProfile['in_promotion_battle']): ?>
    <div class="promo-battle-box">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div>
                <span class="badge" style="background:#EAB308; color:#000; font-weight:800; margin-bottom:6px;">PROMOTION BATTLE ACTIVE</span>
                <h3 style="color:#713F12; margin:4px 0; font-size:20px;">
                    ⚔️ Threshold Challenge: Qualify for <?= Helper::e(ChessRating::nextTier($tierKey) ?? 'Master') ?>!
                </h3>
                <p style="color:#854D0E; font-size:14px; margin:0;">
                    You reached the upper tier limit! You have 3 matches to score at least <strong>1 win and 1 draw</strong> to stay up and advance.
                    (Matches played: <?= (int)$chessProfile['promotion_matches_played'] ?>/3 | Wins: <?= (int)$chessProfile['promotion_wins'] ?> | Draws: <?= (int)$chessProfile['promotion_draws'] ?>)
                </p>
            </div>
            <a href="/chess/play?mode=promotion" class="btn btn-primary" style="background:#CA8A04; border-color:#A16207; font-weight:700;">
                Continue Battle →
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- ==================== ACTIVE LIVE COACHING SESSIONS ==================== -->
    <?php if (!empty($activeLive)): ?>
    <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:var(--r-lg); padding:16px 20px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <span style="font-size:24px;">🎙️</span>
            <div>
                <strong style="color:#065F46;">Live Class in Session!</strong>
                <div style="font-size:13px; color:#047857;">Your coach is broadcasting an interactive board session.</div>
            </div>
        </div>
        <a href="/chess/live?session_id=<?= (int)$activeLive[0]['id'] ?>" class="btn btn-sm btn-primary" style="background:#059669; border-color:#047857;">
            Join Live Class 🔴
        </a>
    </div>
    <?php endif; ?>

    <!-- ==================== MAIN FEATURE TILES (BOTS, PUZZLES, STUDY, COACHING, HISTORY) ==================== -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:18px; margin-bottom:32px;">

        <!-- 1. Play with Bots Arena -->
        <a href="/chess/bots" style="text-decoration:none; color:inherit;">
            <div class="card" style="padding:22px; height:100%; border-top:4px solid #FF7A18; transition:all 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
                <div style="font-size:32px; margin-bottom:10px;">🤖</div>
                <h3 style="margin:0 0 6px; font-size:17px; color:var(--il-text);">Play with Bots</h3>
                <p style="font-size:12px; color:var(--il-muted); margin:0 0 14px; line-height:1.5;">
                    Spar with 5 Academy Bots from 100 to 2700 Rating: Rising Pawn, Knight, Bishop, Rook & Queen.
                </p>
                <span class="btn btn-sm btn-outline-primary" style="width:100%; font-weight:700;">Select Bot →</span>
            </div>
        </a>

        <!-- 2. Daily Tactics & Puzzles -->
        <a href="/chess/puzzles" style="text-decoration:none; color:inherit;">
            <div class="card" style="padding:22px; height:100%; border-top:4px solid #EAB308; transition:all 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
                <div style="font-size:32px; margin-bottom:10px;">🧩</div>
                <h3 style="margin:0 0 6px; font-size:17px; color:var(--il-text);">Daily Puzzles</h3>
                <p style="font-size:12px; color:var(--il-muted); margin:0 0 14px; line-height:1.5;">
                    Sharpen calculation with daily tactics: royal forks, back-rank mates, pins and skewers to earn XP.
                </p>
                <span class="btn btn-sm btn-outline-primary" style="width:100%; border-color:#CA8A04; color:#CA8A04; font-weight:700;">Solve Puzzles →</span>
            </div>
        </a>

        <!-- 3. Study Tracks -->
        <a href="/chess/study" style="text-decoration:none; color:inherit;">
            <div class="card" style="padding:22px; height:100%; border-top:4px solid #2563EB; transition:all 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
                <div style="font-size:32px; margin-bottom:10px;">📖</div>
                <h3 style="margin:0 0 6px; font-size:17px; color:var(--il-text);">Study Curriculum</h3>
                <p style="font-size:12px; color:var(--il-muted); margin:0 0 14px; line-height:1.5;">
                    Openings, Middlegame strategy, and Rook endgames tailored for your <strong><?= Helper::e($tierInfo['label']) ?></strong> batch.
                </p>
                <span class="btn btn-sm btn-outline-primary" style="width:100%; border-color:#2563EB; color:#2563EB; font-weight:700;">Open Lessons →</span>
            </div>
        </a>

        <!-- 4. Live 1-on-1 Coaching -->
        <a href="/chess/live" style="text-decoration:none; color:inherit;">
            <div class="card" style="padding:22px; height:100%; border-top:4px solid #16A34A; transition:all 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
                <div style="font-size:32px; margin-bottom:10px;">🎙️</div>
                <h3 style="margin:0 0 6px; font-size:17px; color:var(--il-text);">Live Coaching</h3>
                <p style="font-size:12px; color:var(--il-muted); margin:0 0 14px; line-height:1.5;">
                    Interactive demo dual-board with your school coach. Spar and review together in real time.
                </p>
                <span class="btn btn-sm btn-outline-primary" style="width:100%; border-color:#16A34A; color:#16A34A; font-weight:700;">Enter Room →</span>
            </div>
        </a>

        <!-- 5. Match History & Analysis -->
        <a href="/chess/history" style="text-decoration:none; color:inherit;">
            <div class="card" style="padding:22px; height:100%; border-top:4px solid #7C2D5C; transition:all 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
                <div style="font-size:32px; margin-bottom:10px;">📜</div>
                <h3 style="margin:0 0 6px; font-size:17px; color:var(--il-text);">Match History</h3>
                <p style="font-size:12px; color:var(--il-muted); margin:0 0 14px; line-height:1.5;">
                    Review your game records, step through moves with the live Eval Bar, and identify mistakes.
                </p>
                <span class="btn btn-sm btn-outline-primary" style="width:100%; border-color:#7C2D5C; color:#7C2D5C; font-weight:700;">Review Vault →</span>
            </div>
        </a>

    </div>

    <!-- ==================== RECENT GAMES & LEADERBOARD ==================== -->
    <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:28px;">

        <!-- Recent Games -->
        <div class="card" style="padding:24px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <h3 style="margin:0; font-size:18px;">My Recent Games</h3>
                <a href="/chess/history" style="font-size:13px; color:var(--il-primary); font-weight:600;">View Match Vault & Analysis →</a>
            </div>

            <?php if (empty($recentGames)): ?>
                <div style="text-align:center; padding:32px; color:var(--il-muted);">
                    No matches played yet. Start your 5-game calibration series to begin!
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <?php foreach ($recentGames as $g): ?>
                        <?php
                            $isWhite = ((int)$g['white_user_id'] === $userId);
                            $botRatingTarget = (int)($g['bot_rating_target'] ?? 0);
                            $botDifficulty = (int)($g['bot_difficulty'] ?? 0);

                            if ($g['game_type'] === 'bot' || $g['game_type'] === 'calibration' || empty($g['black_user_id'])) {
                                if ($botRatingTarget >= 2500 || $botDifficulty >= 10) {
                                    $opponent = 'Rising Queen (2700)';
                                } elseif ($botRatingTarget >= 1800 || $botDifficulty >= 8) {
                                    $opponent = 'Rising Rook (2000)';
                                } elseif ($botRatingTarget >= 1100 || $botDifficulty >= 5) {
                                    $opponent = 'Rising Bishop (1250)';
                                } elseif ($botRatingTarget >= 600 || $botDifficulty >= 3) {
                                    $opponent = 'Rising Knight (750)';
                                } else {
                                    $opponent = 'Rising Pawn (100)';
                                }
                            } else {
                                $opponent = $isWhite ? ($g['black_name'] ?? 'Opponent') : ($g['white_name'] ?? 'Opponent');
                            }
                            $won = ($g['result'] === '1-0' && $isWhite) || ($g['result'] === '0-1' && !$isWhite);
                            $draw = ($g['result'] === '1/2-1/2');
                        ?>
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px; background:var(--surface-0); border-radius:var(--r-md); border:1px solid var(--il-border);">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <span style="font-size:18px;"><?= $won ? '🏆' : ($draw ? '🤝' : '⚔️') ?></span>
                                <div>
                                    <strong style="font-size:14px;"><?= Helper::e($opponent) ?></strong>
                                    <div style="font-size:12px; color:var(--il-muted);">
                                        <?= ucfirst(Helper::e($g['game_type'])) ?> • <?= (int)$g['moves_count'] ?> moves • <?= date('d M', strtotime($g['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <span class="badge" style="background:<?= $won ? '#DCFCE7' : ($draw ? '#FEF3C7' : '#FEE2E2') ?>; color:<?= $won ? '#166534' : ($draw ? '#92400E' : '#991B1B') ?>; font-weight:700;">
                                    <?= $won ? 'WIN' : ($draw ? 'DRAW' : 'LOSS') ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Leaderboard -->
        <div class="card" style="padding:24px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <h3 style="margin:0; font-size:18px;">School Leaderboard</h3>
                <span style="font-size:12px; color:var(--il-muted);">Top Rated Rooks</span>
            </div>

            <div style="display:flex; flex-direction:column; gap:8px;">
                <?php $rank = 1; foreach ($leaderboard as $l): ?>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:<?= ($l['student_user_id'] == $userId) ? 'var(--il-primary-soft)' : 'var(--surface-0)' ?>; border-radius:var(--r-md);">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="font-weight:800; font-size:14px; width:20px; color:var(--il-muted);">#<?= $rank++ ?></span>
                            <div>
                                <strong style="font-size:14px;"><?= Helper::e($l['full_name']) ?></strong>
                                <div style="font-size:11px; color:var(--il-muted);"><?= ucfirst(Helper::e($l['tier'])) ?></div>
                            </div>
                        </div>
                        <span style="font-family:'JetBrains Mono', monospace; font-weight:700; font-size:15px; color:var(--chess-navy);">
                            <?= (int)$l['current_rating'] ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>
</main>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

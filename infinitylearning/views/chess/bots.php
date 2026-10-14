<?php
use App\Helper;
use App\Auth;
use App\Chess\ChessRating;

Auth::requireLogin();
$user = Auth::user();
$userId = (int)($user['id'] ?? 0);
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

try {
    $chessProfile = ChessRating::getOrCreate($userId, $schoolId);
} catch (\Throwable $e) {
    $chessProfile = [
        'current_rating' => 400,
        'tier' => 'learner',
    ];
}

$bots = defined('App\Chess\ChessRating::BOT_PROFILES') ? ChessRating::BOT_PROFILES : [
    'rising_pawn' => [
        'id'          => 'rising_pawn',
        'name'        => 'Rising Pawn',
        'rating'      => 100,
        'avatar'      => '♟️',
        'tier'        => 'Learner',
        'skill_level' => 0,
        'bot_level'   => 1,
        'description' => 'Gentle beginner sparring partner. Teaches piece values and basic board defense.',
    ],
    'rising_knight' => [
        'id'          => 'rising_knight',
        'name'        => 'Rising Knight',
        'rating'      => 750,
        'avatar'      => '♞',
        'tier'        => 'Rookie',
        'skill_level' => 4,
        'bot_level'   => 3,
        'description' => 'Tactical apprentice bot that actively hunts for knight forks and center outposts.',
    ],
    'rising_bishop' => [
        'id'          => 'rising_bishop',
        'name'        => 'Rising Bishop',
        'rating'      => 1250,
        'avatar'      => '♝',
        'tier'        => 'Intermediate',
        'skill_level' => 9,
        'bot_level'   => 5,
        'description' => 'Sharp intermediate player utilizing diagonal pins, discovered attacks, and mating nets.',
    ],
    'rising_rook' => [
        'id'          => 'rising_rook',
        'name'        => 'Rising Rook',
        'rating'      => 2000,
        'avatar'      => '♜',
        'tier'        => 'Master',
        'skill_level' => 15,
        'bot_level'   => 8,
        'description' => 'Relentless master calculating 10+ moves deep with punishing positional squeeze.',
    ],
    'rising_queen' => [
        'id'          => 'rising_queen',
        'name'        => 'Rising Queen',
        'rating'      => 2700,
        'avatar'      => '♛',
        'tier'        => 'Grandmaster',
        'skill_level' => 20,
        'bot_level'   => 10,
        'description' => 'Superhuman Grandmaster titan running full-strength Stockfish calculation.',
    ],
];

$pageTitle = 'Play with Rising Academy Bots — Rising Rooks';
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
                <span class="badge" style="background:#EEF2FF; color:#4338CA; font-weight:700;">
                    🤖 Academy Bot Arena
                </span>
            </div>
            <h1 style="margin:0; font-size:26px; font-family:'Fraunces', serif;">
                Play with Rising Academy Bots
            </h1>
            <p style="color:var(--il-muted); font-size:14px; margin:4px 0 0;">
                Choose your sparring partner from Level 1 (100 Rating) to Grandmaster (2700 Rating). Driven by calibrated Stockfish AI.
            </p>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
            <a href="/chess/history" class="btn btn-sm btn-outline-secondary">📜 Match History</a>
            <a href="/chess/puzzles" class="btn btn-sm btn-outline-secondary">🧩 Daily Puzzles</a>
        </div>
    </div>

    <!-- BOT CARDS GRID -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:22px; margin-bottom:36px;">
        <?php foreach ($bots as $key => $bot): ?>
            <div class="card" style="padding:24px; border-radius:18px; display:flex; flex-direction:column; justify-content:space-between; border:2px solid <?= ($key === 'rising_rook') ? 'var(--chess-gold)' : 'var(--il-border)' ?>; position:relative; overflow:hidden; box-shadow:0 8px 20px rgba(0,0,0,0.04);">
                <?php if ($key === 'rising_queen'): ?>
                    <div style="position:absolute; top:12px; right:12px; background:linear-gradient(135deg, #EC4899, #8B5CF6); color:#fff; font-size:10px; font-weight:800; text-transform:uppercase; padding:3px 8px; border-radius:12px; letter-spacing:0.05em;">
                        MAX DIFFICULTY
                    </div>
                <?php endif; ?>

                <div>
                    <div style="display:flex; align-items:center; gap:16px; margin-bottom:14px;">
                        <div style="width:60px; height:60px; border-radius:16px; background:var(--surface-2); display:flex; align-items:center; justify-content:center; font-size:32px; box-shadow:inset 0 2px 4px rgba(0,0,0,0.06);">
                            <?= $bot['avatar'] ?>
                        </div>
                        <div>
                            <h3 style="margin:0 0 2px; font-size:20px; font-family:'Fraunces', serif;">
                                <?= Helper::e($bot['name']) ?>
                            </h3>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-family:'JetBrains Mono', monospace; font-size:16px; font-weight:800; color:var(--il-primary);">
                                    <?= (int)$bot['rating'] ?>
                                </span>
                                <span class="badge" style="background:#F1F5F9; color:#475569; font-size:11px; font-weight:700;">
                                    <?= Helper::e($bot['tier']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <p style="font-size:13px; color:var(--il-muted); line-height:1.5; margin:0 0 18px; min-height:40px;">
                        <?= Helper::e($bot['description']) ?>
                    </p>
                </div>

                <div>
                    <!-- Time Selector for this bot -->
                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px; font-weight:700; color:var(--il-muted); text-transform:uppercase; display:block; margin-bottom:4px;">
                            Time Control
                        </label>
                        <select id="time_<?= $key ?>" class="form-control" style="font-size:12px; font-weight:600; padding:6px 10px;">
                            <option value="rapid_10" selected>⏱️ Rapid 10 min</option>
                            <option value="blitz_5">🔥 Blitz 5 min</option>
                            <option value="blitz_3">🔥 Blitz 3 min</option>
                            <option value="bullet_1">⚡ Bullet 1 min</option>
                            <option value="classical_30">🏆 Classical 30 min</option>
                        </select>
                    </div>

                    <!-- Side Selection (White, Random, Black) -->
                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px; font-weight:700; color:var(--il-muted); text-transform:uppercase; display:block; margin-bottom:4px;">
                            Choose Side
                        </label>
                        <div style="display:grid; grid-template-columns:1fr 1.1fr 1fr; gap:6px;">
                            <label style="display:flex; align-items:center; justify-content:center; gap:4px; padding:6px 2px; background:var(--surface-0); border:1px solid var(--il-border); border-radius:6px; cursor:pointer; font-size:11px; font-weight:700; margin:0;">
                                <input type="radio" name="color_<?= $key ?>" value="white" checked style="margin:0;"> ⚪ White
                            </label>
                            <label style="display:flex; align-items:center; justify-content:center; gap:4px; padding:6px 2px; background:var(--surface-0); border:1px solid var(--il-border); border-radius:6px; cursor:pointer; font-size:11px; font-weight:700; margin:0;">
                                <input type="radio" name="color_<?= $key ?>" value="random" style="margin:0;"> 🎲 Random
                            </label>
                            <label style="display:flex; align-items:center; justify-content:center; gap:4px; padding:6px 2px; background:var(--surface-0); border:1px solid var(--il-border); border-radius:6px; cursor:pointer; font-size:11px; font-weight:700; margin:0;">
                                <input type="radio" name="color_<?= $key ?>" value="black" style="margin:0;"> ⚫ Black
                            </label>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary" style="width:100%; font-weight:700;" onclick="playBot('<?= $key ?>')">
                        Play vs <?= Helper::e($bot['name']) ?> ⚔️
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
</main>

<script>
function playBot(botKey) {
    const timeEl = document.getElementById('time_' + botKey);
    const timeVal = timeEl ? timeEl.value : 'rapid_10';
    const colorEl = document.querySelector(`input[name="color_${botKey}"]:checked`);
    const colorVal = colorEl ? colorEl.value : 'white';
    window.location.href = `/chess/play?mode=bot&bot=${botKey}&time=${timeVal}&color=${colorVal}`;
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

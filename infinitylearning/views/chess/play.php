<?php
use App\Helper;
use App\Auth;
use App\Chess\ChessRating;
use App\Chess\ChessGame;

Auth::requireLogin();
$user = Auth::user();
$userId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

$chessProfile = ChessRating::getOrCreate($userId, $schoolId);
$mode = $_GET['mode'] ?? ($chessProfile['calibration_completed'] ? 'bot' : 'calibration');

// Time Control options
$timeControlKey = $_GET['time'] ?? 'rapid_10';
$timeControls = [
    'bullet_1'     => ['name' => '⚡ Bullet 1 min',     'seconds' => 60],
    'bullet_2'     => ['name' => '⚡ Bullet 2 min',     'seconds' => 120],
    'blitz_3'      => ['name' => '🔥 Blitz 3 min',      'seconds' => 180],
    'blitz_5'      => ['name' => '🔥 Blitz 5 min',      'seconds' => 300],
    'rapid_10'     => ['name' => '⏱️ Rapid 10 min',     'seconds' => 600],
    'rapid_15'     => ['name' => '⏱️ Rapid 15 min',     'seconds' => 900],
    'classical_30' => ['name' => '🏆 Classical 30 min', 'seconds' => 1800],
];
if (!isset($timeControls[$timeControlKey])) {
    $timeControlKey = 'rapid_10';
}
$gameDurationSeconds = $timeControls[$timeControlKey]['seconds'];

$matchNum = min(5, (int)$chessProfile['calibration_matches_played'] + 1);

// Side / Color Selection (White, Black, or Random)
$rawColor = strtolower(trim($_GET['color'] ?? 'white'));
if ($rawColor === 'random') {
    $assignedColor = (mt_rand(0, 1) === 0) ? 'white' : 'black';
} elseif ($rawColor === 'black') {
    $assignedColor = 'black';
} else {
    $assignedColor = 'white';
}
$isUserWhite = ($assignedColor === 'white');

// Match Edition Piece Sets (Synchronized with chess-engine.js and CSS skins)
$themeMap = [
    1 => ['id' => 'neo_emerald',    'name' => 'Neo Emerald (Tournament)', 'icon' => '♟️'],
    2 => ['id' => 'royal_wood',     'name' => 'Royal Wood (Luxury)',      'icon' => '🪵'],
    3 => ['id' => 'cyber_neon',     'name' => 'Cyber Neon (Futuristic)',  'icon' => '⚡'],
    4 => ['id' => 'gothic_crimson', 'name' => 'Gothic Crimson (Citadel)', 'icon' => '⚔️'],
    5 => ['id' => 'ocean_blue',     'name' => 'Ocean Blue (Grandmaster)', 'icon' => '🌊'],
];
$currentTheme = $themeMap[$matchNum]['id'] ?? 'neo_emerald';

// Opponent Bot Selection
$botProfiles = ChessRating::BOT_PROFILES;
if ($mode === 'calibration') {
    $botInfo = ChessRating::CALIBRATION_BOTS[$matchNum];
    $opponentName = $botInfo['name'];
    $opponentRating = $botInfo['target_rating'];
    $opponentAvatar = $botInfo['avatar'] ?? '🤖';
    $botLevel = $botInfo['bot_level'];
} elseif ($mode === 'promotion') {
    $opponentName = 'Rising Rook (Gatekeeper)';
    $opponentRating = (int)$chessProfile['current_rating'] + 150;
    $opponentAvatar = '⚔️';
    $botLevel = 8;
} else {
    $selectedBotKey = $_GET['bot'] ?? 'rising_rook';
    if (isset($botProfiles[$selectedBotKey])) {
        $botConfig = $botProfiles[$selectedBotKey];
        $opponentName = $botConfig['name'];
        $opponentRating = $botConfig['rating'];
        $opponentAvatar = $botConfig['avatar'];
        $botLevel = $botConfig['bot_level'];
    } else {
        $opponentName = 'Rising Rook';
        $opponentRating = 2000;
        $opponentAvatar = '♜';
        $botLevel = 8;
    }
}

$pageTitle = 'Play Chess vs ' . Helper::e($opponentName) . ' — Rising Rooks';
require VIEW_PATH . '/layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/chess.css?v=<?= time() ?>">
<style id="boardDynamicThemeStyle">
.board-theme-neo_emerald .chess-square.light { background-color: #f0fdf4 !important; }
.board-theme-neo_emerald .chess-square.dark  { background-color: #16a34a !important; }
.board-theme-royal_wood .chess-square.light  { background-color: #fef3c7 !important; }
.board-theme-royal_wood .chess-square.dark   { background-color: #b45309 !important; }
.board-theme-cyber_neon .chess-square.light  { background-color: #0f172a !important; }
.board-theme-cyber_neon .chess-square.dark   { background-color: #0284c7 !important; }
.board-theme-gothic_crimson .chess-square.light { background-color: #f5f5f4 !important; }
.board-theme-gothic_crimson .chess-square.dark  { background-color: #991b1b !important; }
.board-theme-ocean_blue .chess-square.light  { background-color: #e0f2fe !important; }
.board-theme-ocean_blue .chess-square.dark   { background-color: #0369a1 !important; }
</style>

<main class="page-body">
<div class="container">

    <!-- Top Navigation Bar -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="/chess" class="btn btn-sm btn-outline-secondary">← Back to Hub</a>
            <h1 style="margin:0; font-size:24px; font-family:'Fraunces', serif;">
                <?php if ($mode === 'calibration'): ?>
                    🎯 Calibration Match <?= $matchNum ?> of 5
                <?php elseif ($mode === 'promotion'): ?>
                    ⚔️ Promotion Battle (Match <?= (int)$chessProfile['promotion_matches_played'] + 1 ?> of 3)
                <?php else: ?>
                    ⚔️ Play vs <?= Helper::e($opponentName) ?>
                <?php endif; ?>
            </h1>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
            <span class="chess-badge badge-<?= Helper::e($chessProfile['tier']) ?>">
                Your Rating: <?= (int)$chessProfile['current_rating'] ?> (<?= ucfirst(Helper::e($chessProfile['tier'])) ?>)
            </span>
            <a href="/chess/bots" class="btn btn-sm btn-outline-primary">🤖 Switch Bot</a>
        </div>
    </div>

    <!-- CHESS PLAY ARENA GRID -->
    <div class="chess-grid">

        <!-- BOARD SECTION -->
        <div class="chess-board-wrap">

            <!-- Top Player (Opponent / Bot) -->
            <div class="player-bar">
                <div class="player-info">
                    <div class="player-avatar" id="opponentAvatar"><?= $opponentAvatar ?></div>
                    <div>
                        <strong id="opponentName"><?= Helper::e($opponentName) ?> <span style="font-size:12px; font-weight:600; opacity:0.85;"><?= $isUserWhite ? '(Black ⚫)' : '(White ⚪)' ?></span></strong>
                        <div style="font-size:12px; color:var(--il-muted);" id="opponentRating">
                            Rating: <?= (int)$opponentRating ?>
                        </div>
                    </div>
                </div>
                <div class="player-clock <?= !$isUserWhite ? 'active' : '' ?>" id="opponentClock">
                    <?= sprintf('%02d:%02d', floor($gameDurationSeconds / 60), $gameDurationSeconds % 60) ?>
                </div>
            </div>

            <!-- Chessboard Stage WITH Vertical Eval Bar beside it (Chess.com Style) -->
            <div class="board-stage-wrapper" style="display:flex; justify-content:center; align-items:stretch; gap:10px; max-width:560px; margin:0 auto;">
                <!-- Vertical Stockfish Eval Bar -->
                <div class="eval-bar-wrapper" id="evalBarWrapper" title="Live Stockfish Advantage Stream">
                    <div class="eval-bar-fill" id="evalBarFill" style="height: 50%;"></div>
                    <div class="eval-bar-badge" id="evalBarBadge">0.0</div>
                </div>

                <!-- Interactive Chessboard Stage -->
                <div class="board-stage board-theme-<?= Helper::e($currentTheme) ?>" style="flex:1; margin:0;">
                    <div id="boardContainer" style="width:100%; height:100%;"></div>
                </div>
            </div>

            <!-- Bottom Player (User) -->
            <div class="player-bar bottom">
                <div class="player-info">
                    <div class="player-avatar"><?= $isUserWhite ? '♔' : '♚' ?></div>
                    <div>
                        <strong><?= Helper::e($user['email']) ?> <span style="font-size:12px; font-weight:600; opacity:0.85;"><?= $isUserWhite ? '(White ⚪)' : '(Black ⚫)' ?></span></strong>
                        <div style="font-size:12px; color:var(--il-muted);">
                            Rating: <?= (int)$chessProfile['current_rating'] ?> (<?= ucfirst(Helper::e($chessProfile['tier'])) ?>)
                        </div>
                    </div>
                </div>
                <div class="player-clock <?= $isUserWhite ? 'active' : '' ?>" id="userClock">
                    <?= sprintf('%02d:%02d', floor($gameDurationSeconds / 60), $gameDurationSeconds % 60) ?>
                </div>
            </div>

        </div>

        <!-- SIDEBAR CONTROLS & MOVE LOG -->
        <div class="chess-sidebar-card">

            <div style="margin-bottom:18px;">
                <h3 style="margin:0 0 6px; font-size:16px;">Game Details</h3>
                <div style="font-size:13px; color:var(--il-muted); line-height:1.5;">
                    <?php if ($mode === 'calibration'): ?>
                        <strong>Placement Calibration:</strong> Play accurately against <?= Helper::e($opponentName) ?>. Stockfish analyzes every move to accurately place you into your academy batch.
                    <?php elseif ($mode === 'promotion'): ?>
                        <strong>Promotion Rule:</strong> Score at least <strong>1 Win + 1 Draw</strong> across 3 matches to officially rise to the next rank!
                    <?php else: ?>
                        <strong>Calibrated Sparring:</strong> Playing against <strong><?= Helper::e($opponentName) ?></strong> (Rating <?= (int)$opponentRating ?>). Full Stockfish UCI calculations with live evaluation stream.
                    <?php endif; ?>
                </div>
            </div>

            <!-- Time Control Selector -->
            <div style="margin-bottom:16px; background:var(--surface-0); padding:12px 14px; border-radius:var(--r-md); border:1px solid var(--il-border);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:12px; font-weight:700; color:var(--il-muted); text-transform:uppercase; letter-spacing:0.04em;">⏱️ Time Control</span>
                    <span class="badge" style="background:#EEF2FF; color:#4338CA; font-size:11px; font-weight:700; padding:2px 8px;">
                        <?= $timeControls[$timeControlKey]['name'] ?>
                    </span>
                </div>
                <select id="timeControlSelect" class="form-control" style="font-size:13px; font-weight:600; padding:6px 10px;">
                    <?php foreach ($timeControls as $k => $tc): ?>
                        <option value="<?= $k ?>" <?= ($k === $timeControlKey) ? 'selected' : '' ?>>
                            <?= $tc['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Match Edition Piece Style Selector -->
            <div style="margin-bottom:16px; background:var(--surface-0); padding:12px 14px; border-radius:var(--r-md); border:1px solid var(--il-border);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:12px; font-weight:700; color:var(--il-muted); text-transform:uppercase; letter-spacing:0.04em;">🎨 Board & Piece Skin</span>
                    <span class="badge" style="background:#E0E7FF; color:#3730A3; font-size:11px; font-weight:700; padding:2px 8px;">
                        Match <?= $matchNum ?> Edition
                    </span>
                </div>
                <select id="pieceThemeSelect" class="form-control" style="font-size:13px; font-weight:600; padding:6px 10px;">
                    <?php foreach ($themeMap as $num => $th): ?>
                        <option value="<?= $th['id'] ?>" <?= ($th['id'] === $currentTheme) ? 'selected' : '' ?>>
                            <?= $th['icon'] ?> <?= $th['name'] ?> <?= ($num === $matchNum) ? '★ (Active Match)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Move History -->
            <div style="margin-bottom:18px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:13px; font-weight:700;">Notation (PGN)</span>
                    <span id="gameStatusText" style="font-size:12px; color:var(--il-primary); font-weight:600;">
                        <?= $isUserWhite ? 'White to move (Your turn)' : Helper::e($opponentName) . ' calculating opening move...' ?>
                    </span>
                </div>
                <div class="move-history-table" id="moveHistoryBox">
                    <div style="color:var(--il-muted); padding:8px 0;">No moves yet. Make your opening move!</div>
                </div>
            </div>

            <!-- Action Controls -->
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="button" class="btn btn-outline-primary" id="btnShowHint" style="width:100%; font-weight:700;">
                    💡 Show Tactical Hint
                </button>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn btn-outline-danger" id="btnResign" style="flex:1;">
                        Resign 🏳️
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnNewGame" style="flex:1;">
                        Restart 🔄
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>
</main>

<!-- Result Modal -->
<div id="resultModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(6px);">
    <div style="background:#fff; border-radius:20px; padding:32px; max-width:440px; width:90%; text-align:center; box-shadow:0 24px 48px rgba(0,0,0,0.3);">
        <div id="modalIcon" style="font-size:54px; margin-bottom:12px;">🏆</div>
        <h2 id="modalTitle" style="margin:0 0 8px; font-size:24px; font-family:'Fraunces', serif;">Game Over</h2>
        <p id="modalDesc" style="color:var(--il-muted); font-size:15px; margin:0 0 20px; line-height:1.5;"></p>
        <div id="modalRatingChange" style="font-size:22px; font-weight:800; font-family:'JetBrains Mono', monospace; color:var(--il-primary); margin-bottom:20px;"></div>
        <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
            <a href="/chess/play?mode=<?= Helper::e($mode) ?>&bot=<?= Helper::e($_GET['bot'] ?? 'rising_rook') ?>&time=<?= Helper::e($timeControlKey) ?>&color=<?= Helper::e($rawColor) ?>" class="btn btn-primary" id="modalNextBtn">Play Again ⚔️</a>
            <a href="/chess/history" class="btn btn-outline-primary">📜 Review Match</a>
            <a href="/chess" class="btn btn-outline-secondary">Academy Hub</a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
<script>
if (typeof window.Chess === 'undefined') {
    document.write('<script src="/assets/js/chess.min.js"><\/script>');
}
</script>
<script src="/assets/js/chess-engine.js?v=<?= time() ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gameMode = <?= json_encode($mode) ?>;
    const initialTheme = <?= json_encode($currentTheme) ?>;
    const opponentName = <?= json_encode($opponentName) ?>;
    const userColorChoice = <?= json_encode($assignedColor) ?>; // 'white' or 'black'
    const rawColorParam = <?= json_encode($rawColor) ?>;
    let currentGameId = null;
    let board = null;

    // --- CHESS CLOCK LOGIC (Count-down by second, flag fall on 0) ---
    const totalMatchSeconds = <?= (int)$gameDurationSeconds ?>;
    let userSeconds = totalMatchSeconds;
    let oppSeconds = totalMatchSeconds;
    let clockInterval = null;
    let clockRunning = false;

    function formatTime(totalSecs) {
        const m = Math.floor(totalSecs / 60).toString().padStart(2, '0');
        const s = (totalSecs % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    }

    function updateClockDisplay() {
        const uEl = document.getElementById('userClock');
        const oEl = document.getElementById('opponentClock');
        if (uEl) {
            uEl.textContent = formatTime(userSeconds);
            if (userSeconds <= 30) uEl.classList.add('low-time');
            else uEl.classList.remove('low-time');
        }
        if (oEl) {
            oEl.textContent = formatTime(oppSeconds);
            if (oppSeconds <= 30) oEl.classList.add('low-time');
            else oEl.classList.remove('low-time');
        }
    }

    function tickClock() {
        if (!board || board.game.game_over()) {
            stopClock();
            return;
        }

        const turn = board.game.turn();
        const userSide = (board.options.orientation === 'black') ? 'b' : 'w';
        const uEl = document.getElementById('userClock');
        const oEl = document.getElementById('opponentClock');

        if (turn === userSide) {
            // Student's turn
            userSeconds--;
            if (uEl) uEl.classList.add('active');
            if (oEl) oEl.classList.remove('active');

            if (userSeconds <= 0) {
                userSeconds = 0;
                updateClockDisplay();
                stopClock();
                const botWinResult = (userSide === 'w') ? '0-1' : '1-0';
                board.triggerGameOver(botWinResult, 'Flag Fall — You ran out of time!');
                return;
            }
        } else {
            // Opponent Bot's turn
            oppSeconds--;
            if (oEl) oEl.classList.add('active');
            if (uEl) uEl.classList.remove('active');

            if (oppSeconds <= 0) {
                oppSeconds = 0;
                updateClockDisplay();
                stopClock();
                const userWinResult = (userSide === 'w') ? '1-0' : '0-1';
                board.triggerGameOver(userWinResult, `Flag Fall — ${opponentName} ran out of time!`);
                return;
            }
        }

        updateClockDisplay();
    }

    function startClock() {
        if (clockRunning) return;
        clockRunning = true;
        clockInterval = setInterval(tickClock, 1000);
    }

    function stopClock() {
        clearInterval(clockInterval);
        clockRunning = false;
        const uEl = document.getElementById('userClock');
        const oEl = document.getElementById('opponentClock');
        if (uEl) uEl.classList.remove('active', 'low-time');
        if (oEl) oEl.classList.remove('active', 'low-time');
    }

    // Start game on backend
    function initMatch() {
        const action = (gameMode === 'calibration') ? 'start_calibration' : (gameMode === 'promotion' ? 'start_promotion' : 'start_bot');
        fetch('/chess-game-handler.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                'action': action,
                'target_rating': '<?= (int)$opponentRating ?>',
                'bot_level': '<?= (int)$botLevel ?>',
                'player_color': userColorChoice
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.game) {
                currentGameId = data.game.id;
            }
        })
        .catch(err => {
            console.warn('Backend match init notice:', err);
        });
    }

    // Restore saved board theme
    let activeSkin = initialTheme;
    try {
        const savedSkin = localStorage.getItem('rising_rooks_board_theme');
        if (savedSkin) activeSkin = savedSkin;
    } catch(e) {}

    const themeSelect = document.getElementById('pieceThemeSelect');
    if (themeSelect) {
        themeSelect.value = activeSkin;
        themeSelect.addEventListener('change', function() {
            const chosen = this.value;
            try {
                localStorage.setItem('rising_rooks_board_theme', chosen);
            } catch(e) {}
            if (board) {
                board.setPieceTheme(chosen);
            }
            const stage = document.querySelector('.board-stage');
            if (stage) {
                stage.className = 'board-stage board-theme-' + chosen;
            }
        });
    }

    // Initialize interactive board with Piece Theme, Calibrated Bot and Live Eval Bar
    try {
        board = new RisingRooksBoard('boardContainer', {
            orientation: userColorChoice,
            gameType: 'bot',
            botLevel: <?= (int)$botLevel ?>,
            botRating: <?= (int)$opponentRating ?>,
            pieceTheme: activeSkin,
            evalBarId: 'evalBarFill',
            evalBadgeId: 'evalBarBadge',
            onMove: function(move, game) {
                startClock();
                updatePgnBox(game);
                const isHumanTurn = (game.turn() === (userColorChoice === 'black' ? 'b' : 'w'));
                document.getElementById('gameStatusText').textContent = isHumanTurn ? 'Your turn to move' : `${opponentName} calculating...`;

                if (currentGameId) {
                    fetch('/chess-game-handler.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: new URLSearchParams({
                            'action': 'save_move',
                            'game_id': currentGameId,
                            'fen': game.fen(),
                            'pgn': game.pgn(),
                            'moves_count': game.history().length
                        })
                    }).catch(e => console.warn('Save move notice:', e));
                }
            },
            onGameOver: function(result, reason, game) {
                stopClock();
                finishMatch(result, reason);
            }
        });

        // Ensure board stage has initial active skin class
        const initialStage = document.querySelector('.board-stage');
        if (initialStage && activeSkin) {
            initialStage.className = 'board-stage board-theme-' + activeSkin;
        }

        // If student is Black, White Bot moves first: start the clock immediately and trigger opening move!
        if (userColorChoice === 'black') {
            startClock();
            setTimeout(function() {
                if (board && board.game && board.game.history().length === 0 && board.game.turn() === 'w') {
                    board.makeBotMove();
                }
            }, 800);
        }

        // Active turn watchdog: ensures bot never stalls on its turn
        setInterval(function() {
            if (!board || !board.game || board.game.game_over()) return;
            const isHumanTurn = (board.game.turn() === (userColorChoice === 'black' ? 'b' : 'w'));
            if (!isHumanTurn) {
                if (!window.__lastBotCheck || (Date.now() - window.__lastBotCheck > 3500)) {
                    window.__lastBotCheck = Date.now();
                    board.makeBotMove();
                }
            }
        }, 2500);
    } catch (e) {
        console.error('Chessboard initialization error:', e);
        document.getElementById('boardContainer').innerHTML = '<div style="color:#fff; padding:20px; text-align:center;">Board initialization error: ' + e.message + '</div>';
    }

    // Time Control Change
    const timeSelect = document.getElementById('timeControlSelect');
    if (timeSelect) {
        timeSelect.addEventListener('change', function() {
            const botParam = <?= json_encode($_GET['bot'] ?? 'rising_rook') ?>;
            window.location.href = `/chess/play?mode=${encodeURIComponent(gameMode)}&bot=${encodeURIComponent(botParam)}&time=${encodeURIComponent(this.value)}&color=${encodeURIComponent(rawColorParam)}`;
        });
    }

    // Show Hint Button Listener
    const btnHint = document.getElementById('btnShowHint');
    if (btnHint) {
        btnHint.addEventListener('click', function() {
            if (board) {
                board.showHint();
            }
        });
    }

    function updatePgnBox(game) {
        const moves = game.history();
        const box = document.getElementById('moveHistoryBox');
        if (!moves.length) {
            box.innerHTML = '<div style="color:var(--il-muted); padding:8px 0;">No moves yet. Make your opening move!</div>';
            return;
        }

        let html = '';
        for (let i = 0; i < moves.length; i += 2) {
            const moveNum = (i / 2) + 1;
            const w = moves[i] || '';
            const b = moves[i + 1] || '';
            html += `<div class="move-row">
                <span class="move-col-num">${moveNum}.</span>
                <span class="move-col-white">${w}</span>
                <span class="move-col-black">${b}</span>
            </div>`;
        }
        box.innerHTML = html;
        box.scrollTop = box.scrollHeight;
    }

    function finishMatch(result, termination) {
        stopClock();
        if (!currentGameId) return;

        fetch('/chess-game-handler.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                'action': 'finish_game',
                'game_id': currentGameId,
                'result': result,
                'termination': termination,
                'player_color': userColorChoice
            })
        })
        .then(r => r.json())
        .then(data => {
            showResultModal(result, termination, data);
        });
    }

    function showResultModal(result, termination, data) {
        const modal = document.getElementById('resultModal');
        const icon = document.getElementById('modalIcon');
        const title = document.getElementById('modalTitle');
        const desc = document.getElementById('modalDesc');
        const rating = document.getElementById('modalRatingChange');

        const won = (result === '1-0' && userColorChoice === 'white') || (result === '0-1' && userColorChoice === 'black');
        const draw = (result === '1/2-1/2');

        icon.textContent = won ? '🎉' : (draw ? '🤝' : '⚔️');
        title.textContent = won ? 'Victory!' : (draw ? 'Draw' : 'Defeat');
        desc.textContent = termination;

        if (data && data.rating) {
            rating.textContent = `New Rating: ${data.rating.current_rating} (${data.rating.tier.toUpperCase()})`;
        }

        modal.style.display = 'flex';
    }

    document.getElementById('btnResign').addEventListener('click', function() {
        if (confirm('Are you sure you want to resign this match?')) {
            stopClock();
            const resignResult = (userColorChoice === 'white') ? '0-1' : '1-0';
            board.triggerGameOver(resignResult, 'Resignation');
        }
    });

    document.getElementById('btnNewGame').addEventListener('click', function() {
        if (confirm('Restart match from the beginning?')) {
            stopClock();
            location.reload();
        }
    });

    initMatch();
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

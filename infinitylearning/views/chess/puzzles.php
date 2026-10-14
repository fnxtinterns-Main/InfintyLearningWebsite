<?php
use App\Helper;
use App\Auth;
use App\Chess\ChessRating;
use App\Chess\ChessPuzzles;

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

$todayDate = date('Y-m-d');
$todayFormatted = date('l, F j, Y');
$dailyPuzzles = ChessPuzzles::getDailyPuzzles($todayDate);

$pageTitle = 'Daily Tactics & Puzzles — Rising Rooks';
require VIEW_PATH . '/layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/chess.css?v=<?= time() ?>">

<main class="page-body">
<div class="container">

    <!-- Header -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px; flex-wrap:wrap;">
                <a href="/chess" class="btn btn-sm btn-outline-secondary">← Chess Hub</a>
                <span class="badge" style="background:#FEF3C7; color:#92400E; font-weight:700;">
                    📅 <?= Helper::e($todayFormatted) ?>
                </span>
                <span class="badge" style="background:#EEF2FF; color:#4338CA; font-weight:700;">
                    ⏳ Next Puzzles in <span id="midnightCountdown">--:--:--</span>
                </span>
            </div>
            <h1 style="margin:0; font-size:26px; font-family:'Fraunces', serif;">
                Daily Tactics & Puzzles
            </h1>
            <p style="color:var(--il-muted); font-size:14px; margin:4px 0 0;">
                Sharpen your tactical vision with today's 5 official academy puzzles. Complete all 5 to earn your daily streak & +50 XP! A fresh set of puzzles drops every day at midnight.
            </p>
        </div>

        <div style="display:flex; gap:10px;">
            <a href="/chess/bots" class="btn btn-sm btn-primary">🤖 Play Bot</a>
            <a href="/chess/history" class="btn btn-sm btn-outline-secondary">📜 Match History</a>
        </div>
    </div>

    <!-- Progress Tracker -->
    <div style="background:var(--surface-1); border:1px solid var(--il-border); border-radius:var(--r-lg); padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <span style="font-size:13px; font-weight:700; color:var(--il-muted); text-transform:uppercase;">Daily Progress:</span>
            <div style="display:flex; gap:8px;" id="puzzleDotsContainer">
                <span class="badge puzzle-dot active" id="dot-0">1</span>
                <span class="badge puzzle-dot" id="dot-1">2</span>
                <span class="badge puzzle-dot" id="dot-2">3</span>
                <span class="badge puzzle-dot" id="dot-3">4</span>
                <span class="badge puzzle-dot" id="dot-4">5</span>
            </div>
        </div>
        <div style="font-size:13px; font-weight:700; color:var(--il-primary);" id="puzzleProgressText">
            Puzzle 1 of 5
        </div>
    </div>

    <!-- PUZZLE ARENA GRID -->
    <div class="chess-grid">

        <!-- BOARD SECTION -->
        <div class="chess-board-wrap">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div style="font-weight:700; font-size:15px;" id="puzzleThemeTag">
                    🎯 Puzzle #1: Royal Fork
                </div>
                <div id="puzzleTurnIndicator" class="badge" style="background:#DCFCE7; color:#166534; font-weight:700; padding:6px 12px;">
                    White to move & win material
                </div>
            </div>

            <!-- Chessboard Container -->
            <div class="board-stage">
                <div id="puzzleBoardContainer" style="width:100%; height:100%;"></div>
            </div>
        </div>

        <!-- SIDEBAR INFO & SOLVER CONTROLS -->
        <div class="chess-sidebar-card">
            <div style="margin-bottom:20px;">
                <h3 style="margin:0 0 8px; font-size:18px; font-family:'Fraunces', serif;" id="puzzleTitle">
                    Tactical Strike: The Knight Fork
                </h3>
                <p style="font-size:13px; color:var(--il-muted); line-height:1.5; margin:0;" id="puzzleDesc">
                    White to move. Jump your Knight to deliver check and fork the unprotected Black Rook!
                </p>
            </div>

            <!-- Dynamic Result Alert Box -->
            <div id="puzzleFeedbackBox" style="padding:14px; border-radius:var(--r-md); background:var(--surface-0); border:1px solid var(--il-border); font-size:13px; font-weight:600; text-align:center; margin-bottom:20px; min-height:48px; display:flex; align-items:center; justify-content:center;">
                🤔 Make your move on the board!
            </div>

            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="button" class="btn btn-outline-primary" id="btnPuzzleHint" style="width:100%; font-weight:600;">
                    💡 Show Tactical Hint
                </button>
                <button type="button" class="btn btn-primary" id="btnNextPuzzle" style="width:100%; font-weight:700;" disabled>
                    Next Puzzle →
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnResetPuzzle" style="width:100%;">
                    Reset Position 🔄
                </button>
            </div>
        </div>

    </div>

</div>
</main>

<!-- ALL 5 PUZZLES COMPLETED CELEBRATION MODAL -->
<div id="allPuzzlesCompletedModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(6px);">
    <div style="background:#fff; border-radius:20px; padding:36px; max-width:480px; width:92%; text-align:center; box-shadow:0 24px 48px rgba(0,0,0,0.3); animation:popIn 0.3s ease;">
        <div style="font-size:58px; margin-bottom:12px;">🏆</div>
        <h2 style="margin:0 0 8px; font-size:24px; font-family:'Fraunces', serif;">
            Daily Tactical Gauntlet Complete!
        </h2>
        <p style="color:var(--il-muted); font-size:15px; margin:0 0 20px; line-height:1.5;">
            Outstanding calculation! You have mastered all <strong>5 daily tactical puzzles</strong> for today.
        </p>

        <div style="display:flex; justify-content:center; gap:16px; margin-bottom:22px;">
            <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:12px; padding:12px 18px; text-align:center;">
                <div style="font-size:22px; font-weight:800; color:#1E40AF;">+50 XP</div>
                <div style="font-size:11px; font-weight:700; color:#3B82F6; text-transform:uppercase;">Academy XP</div>
            </div>
            <div style="background:#FEF3C7; border:1px solid #FDE68A; border-radius:12px; padding:12px 18px; text-align:center;">
                <div style="font-size:22px; font-weight:800; color:#92400E;">🔥 Active</div>
                <div style="font-size:11px; font-weight:700; color:#D97706; text-transform:uppercase;">Daily Streak</div>
            </div>
        </div>

        <div style="background:var(--surface-0); border-radius:10px; padding:12px 16px; font-size:13px; color:var(--il-muted); margin-bottom:24px; border:1px solid var(--il-border); line-height:1.4;">
            ⏰ Today's puzzle set is complete! Fresh puzzles unlock tomorrow at <strong>00:00 midnight</strong>.
        </div>

        <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
            <a href="/chess/bots" class="btn btn-primary" style="font-weight:700;">🤖 Spar with Bots</a>
            <a href="/chess/play" class="btn btn-outline-primary">⚔️ Play Ranked Match</a>
            <a href="/chess" class="btn btn-outline-secondary">Academy Hub</a>
        </div>
    </div>
</div>

<style>
.puzzle-dot {
    background: #E2E8F0;
    color: #475569;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
}
.puzzle-dot.active {
    background: #3B82F6;
    color: #fff;
}
.puzzle-dot.completed {
    background: #10B981;
    color: #fff;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
<script>
if (typeof window.Chess === 'undefined') {
    document.write('<script src="/assets/js/chess.min.js"><\/script>');
}
</script>
<script src="/assets/js/chess-engine.js?v=<?= time() ?>"></script>
<script>
// Today's 5 Certified Daily Tactical Puzzles (Dynamic daily rotation from ChessPuzzles)
const PUZZLE_BANK = <?= json_encode($dailyPuzzles) ?>;
const TODAY_KEY = <?= json_encode(date('Ymd')) ?>;

let currentPuzzleIdx = 0;
let puzzleBoard = null;
const completedPuzzles = new Set();

// Live Midnight Countdown Timer (Next set drops at 00:00 midnight)
function updateMidnightCountdown() {
    const now = new Date();
    const tomorrow = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0);
    const diffMs = tomorrow - now;
    if (diffMs <= 0) {
        location.reload();
        return;
    }
    const h = Math.floor(diffMs / 3600000).toString().padStart(2, '0');
    const m = Math.floor((diffMs % 3600000) / 60000).toString().padStart(2, '0');
    const s = Math.floor((diffMs % 60000) / 1000).toString().padStart(2, '0');
    const el = document.getElementById('midnightCountdown');
    if (el) el.textContent = `${h}:${m}:${s}`;
}
setInterval(updateMidnightCountdown, 1000);
updateMidnightCountdown();

document.addEventListener('DOMContentLoaded', function() {
    loadPuzzle(0);
});

function updateProgressDots() {
    for (let i = 0; i < PUZZLE_BANK.length; i++) {
        const dot = document.getElementById('dot-' + i);
        if (!dot) continue;
        dot.className = 'badge puzzle-dot';
        if (completedPuzzles.has(i)) {
            dot.classList.add('completed');
            dot.textContent = '✓';
        } else if (i === currentPuzzleIdx) {
            dot.classList.add('active');
            dot.textContent = (i + 1);
        } else {
            dot.textContent = (i + 1);
        }
    }
    document.getElementById('puzzleProgressText').textContent = `Puzzle ${currentPuzzleIdx + 1} of ${PUZZLE_BANK.length}`;
}

function loadPuzzle(idx) {
    currentPuzzleIdx = idx;
    const p = PUZZLE_BANK[idx];
    if (!p) return;

    updateProgressDots();

    document.getElementById('puzzleTitle').textContent = p.title;
    document.getElementById('puzzleThemeTag').textContent = `🎯 Puzzle #${idx + 1}: ${p.theme}`;
    document.getElementById('puzzleDesc').textContent = p.desc;
    document.getElementById('puzzleTurnIndicator').textContent = p.turnDesc;
    document.getElementById('puzzleFeedbackBox').innerHTML = '🤔 Make your move on the board!';
    document.getElementById('puzzleFeedbackBox').style.background = 'var(--surface-0)';
    document.getElementById('puzzleFeedbackBox').style.color = 'var(--il-text)';
    document.getElementById('btnNextPuzzle').disabled = true;

    if (!puzzleBoard) {
        puzzleBoard = new RisingRooksBoard('puzzleBoardContainer', {
            orientation: 'white',
            gameType: 'puzzle',
            pieceTheme: 'neo_emerald',
            onMove: function(move, game) {
                checkPuzzleMove(move);
            }
        });
    }

    puzzleBoard.setPosition(p.fenActual);
}

function checkPuzzleMove(move) {
    const p = PUZZLE_BANK[currentPuzzleIdx];
    const box = document.getElementById('puzzleFeedbackBox');

    if (move.from === p.correctFrom && move.to === p.correctTo) {
        // Correct move!
        completedPuzzles.add(currentPuzzleIdx);
        updateProgressDots();

        box.innerHTML = '🎉 <strong>Brilliant move! Puzzle Solved (+10 XP)</strong>';
        box.style.background = '#DCFCE7';
        box.style.color = '#166534';
        document.getElementById('btnNextPuzzle').disabled = false;

        // If this was the 5th and final puzzle, show the completion modal!
        if (completedPuzzles.size === PUZZLE_BANK.length) {
            setTimeout(showGauntletCompleteModal, 900);
        }
    } else {
        // Incorrect
        box.innerHTML = '❌ <em>Not the winning move. Resetting position...</em>';
        box.style.background = '#FEE2E2';
        box.style.color = '#991B1B';
        setTimeout(() => {
            puzzleBoard.setPosition(p.fenActual);
            box.innerHTML = '🤔 Try again or use a hint!';
            box.style.background = 'var(--surface-0)';
            box.style.color = 'var(--il-text)';
        }, 900);
    }
}

function showGauntletCompleteModal() {
    const modal = document.getElementById('allPuzzlesCompletedModal');
    if (modal) modal.style.display = 'flex';
}

document.getElementById('btnPuzzleHint').addEventListener('click', function() {
    const p = PUZZLE_BANK[currentPuzzleIdx];
    const box = document.getElementById('puzzleFeedbackBox');
    box.innerHTML = `💡 <strong>Hint:</strong> Focus on moving the piece located on <strong>${p.correctFrom.toUpperCase()}</strong>!`;
    box.style.background = '#FEF3C7';
    box.style.color = '#92400E';
});

document.getElementById('btnNextPuzzle').addEventListener('click', function() {
    if (currentPuzzleIdx + 1 < PUZZLE_BANK.length) {
        loadPuzzle(currentPuzzleIdx + 1);
    } else {
        showGauntletCompleteModal();
    }
});

document.getElementById('btnResetPuzzle').addEventListener('click', function() {
    loadPuzzle(currentPuzzleIdx);
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

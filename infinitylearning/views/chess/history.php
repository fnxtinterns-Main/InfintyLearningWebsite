<?php
use App\Helper;
use App\Auth;
use App\Chess\ChessGame;
use App\Chess\ChessRating;

Auth::requireLogin();
$user = Auth::user();
$userId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

$chessProfile = ChessRating::getOrCreate($userId, $schoolId);
$games = ChessGame::getStudentGames($userId, 30);

$pageTitle = 'Match History & Game Review — Rising Rooks';
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
                <span class="badge" style="background:#F1F5F9; color:#475569; font-weight:700;">
                    📜 Match Vault
                </span>
            </div>
            <h1 style="margin:0; font-size:26px; font-family:'Fraunces', serif;">
                Match History & Game Analysis
            </h1>
            <p style="color:var(--il-muted); font-size:14px; margin:4px 0 0;">
                Review your previous matches, study tactical blunders, and explore move-by-move evaluations with the engine.
            </p>
        </div>

        <div style="display:flex; gap:10px;">
            <a href="/chess/bots" class="btn btn-sm btn-primary">🤖 Play vs Bot</a>
            <a href="/chess/puzzles" class="btn btn-sm btn-outline-secondary">🧩 Puzzles</a>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN LAYOUT (HISTORY TABLE + LIVE REVIEW BOARD) -->
    <div style="display:grid; grid-template-columns:minmax(320px, 1fr) 460px; gap:28px; align-items:start;" id="historyLayout">

        <!-- GAMES LIST TABLE -->
        <div class="card" style="padding:0; overflow:hidden; border-radius:18px;">
            <div style="padding:18px 24px; border-bottom:1px solid var(--il-border); background:var(--surface-0); display:flex; justify-content:space-between; align-items:center;">
                <strong style="font-size:16px;">Recent Matches (<?= count($games) ?>)</strong>
                <span style="font-size:12px; color:var(--il-muted);">Click any match to review on board</span>
            </div>

            <?php if (empty($games)): ?>
                <div style="padding:48px 24px; text-align:center; color:var(--il-muted);">
                    <div style="font-size:40px; margin-bottom:8px;">♟️</div>
                    <div style="font-size:16px; font-weight:600; color:var(--il-text);">No matches recorded yet</div>
                    <p style="margin:4px 0 16px; font-size:13px;">Play a calibration match or spar with the Rising Bots to start building your record.</p>
                    <a href="/chess/bots" class="btn btn-sm btn-primary">Play Your First Game →</a>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--il-border); background:var(--surface-1); color:var(--il-muted); font-size:11px; text-transform:uppercase; letter-spacing:0.05em;">
                                <th style="padding:12px 18px;">Result</th>
                                <th style="padding:12px 18px;">Opponent</th>
                                <th style="padding:12px 18px;">Type</th>
                                <th style="padding:12px 18px;">Moves</th>
                                <th style="padding:12px 18px;">Date</th>
                                <th style="padding:12px 18px; text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($games as $idx => $g): ?>
                                <?php
                                    $isWhite = ($g['white_user_id'] == $userId);
                                    $won = ($g['result'] === '1-0' && $isWhite) || ($g['result'] === '0-1' && !$isWhite);
                                    $draw = ($g['result'] === '1/2-1/2' || $g['result'] === 'draw');
                                    $botTarget = (int)($g['bot_rating_target'] ?? 0);
                                    if ($g['game_type'] === 'bot' || $g['game_type'] === 'calibration' || empty($g['black_user_id'])) {
                                        if ($botTarget >= 2500) $oppName = 'Rising Queen (2700)';
                                        elseif ($botTarget >= 1800) $oppName = 'Rising Rook (2000)';
                                        elseif ($botTarget >= 1100) $oppName = 'Rising Bishop (1250)';
                                        elseif ($botTarget >= 600) $oppName = 'Rising Knight (750)';
                                        else $oppName = 'Rising Pawn (100)';
                                    } else {
                                        $oppName = $g['black_name'] ?: 'Opponent';
                                    }
                                    $resultBadge = $won ? 'Victory' : ($draw ? 'Draw' : 'Defeat');
                                    $badgeColor = $won ? '#DCFCE7; color:#166534;' : ($draw ? '#F1F5F9; color:#475569;' : '#FEE2E2; color:#991B1B;');
                                ?>
                                <tr style="border-bottom:1px solid var(--il-border); cursor:pointer;" class="match-history-row"
                                    data-pgn="<?= Helper::e($g['pgn'] ?? '') ?>"
                                    data-fen="<?= Helper::e($g['current_fen'] ?? '') ?>"
                                    data-opp="<?= Helper::e($oppName) ?>"
                                    data-res="<?= Helper::e($resultBadge) ?>"
                                    onclick="loadGameForReview(this)">
                                    <td style="padding:12px 18px;">
                                        <span class="badge" style="background:<?= $badgeColor ?> font-weight:700; font-size:11px;">
                                            <?= $resultBadge ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 18px; font-weight:600;">
                                        <?= Helper::e($oppName) ?>
                                    </td>
                                    <td style="padding:12px 18px; color:var(--il-muted); text-transform:capitalize;">
                                        <?= Helper::e($g['game_type']) ?>
                                    </td>
                                    <td style="padding:12px 18px; font-family:'JetBrains Mono', monospace;">
                                        <?= (int)($g['moves_count'] ?? 0) ?>
                                    </td>
                                    <td style="padding:12px 18px; color:var(--il-muted); font-size:12px;">
                                        <?= date('M d, H:i', strtotime($g['created_at'])) ?>
                                    </td>
                                    <td style="padding:12px 18px; text-align:right;">
                                        <button type="button" class="btn btn-sm btn-outline-primary" style="padding:3px 10px; font-size:12px;">
                                            Review 🔍
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- INTERACTIVE GAME REVIEW BOARD & EVAL BAR -->
        <div class="card" style="padding:20px; border-radius:18px; position:sticky; top:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <div>
                    <h3 style="margin:0; font-size:16px;" id="reviewMatchTitle">Select a match to analyze</h3>
                    <div style="font-size:12px; color:var(--il-muted);" id="reviewMatchSubtitle">Step through moves with engine evaluation</div>
                </div>
                <div style="font-family:'JetBrains Mono', monospace; font-size:14px; font-weight:700; color:var(--il-primary);" id="reviewEvalDisplay">
                    0.0
                </div>
            </div>

            <!-- BOARD STAGE WITH INTEGRATED EVAL BAR -->
            <div style="display:flex; gap:10px; align-items:stretch; margin-bottom:16px;">
                <!-- Vertical Eval Bar -->
                <div style="width:20px; background:#1e293b; border-radius:6px; overflow:hidden; display:flex; flex-direction:column; justify-content:flex-end; border:1px solid rgba(0,0,0,0.2); position:relative;" id="reviewEvalBarContainer">
                    <div id="reviewEvalWhiteBar" style="width:100%; height:50%; background:#ffffff; transition:height 0.3s ease;"></div>
                </div>

                <!-- 64-square Board Container -->
                <div class="board-stage" style="flex:1; max-width:380px; aspect-ratio:1/1;">
                    <div id="reviewBoardContainer" style="width:100%; height:100%;"></div>
                </div>
            </div>

            <!-- STEPPING CONTROLS -->
            <div style="display:flex; justify-content:center; gap:8px; margin-bottom:16px;">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="reviewStepFirst()" title="Start">|◀</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="reviewStepPrev()" title="Previous Move">◀ Back</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="reviewStepNext()" title="Next Move">Next ▶</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="reviewStepLast()" title="Current End">▶|</button>
            </div>

            <!-- NOTATION CHIPS CONTAINER -->
            <div style="background:var(--surface-0); border-radius:var(--r-md); padding:10px 12px; max-height:120px; overflow-y:auto; font-size:12px; line-height:1.8; font-family:'JetBrains Mono', monospace;" id="reviewMovesList">
                Select a game on the left to load moves notation.
            </div>
        </div>

    </div>

</div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
<script>
if (typeof window.Chess === 'undefined') {
    document.write('<script src="/assets/js/chess.min.js"><\/script>');
}
</script>
<script src="/assets/js/chess-engine.js"></script>
<script>
let reviewGame = new window.Chess();
let reviewBoard = null;
let currentMoves = [];
let currentMoveIdx = 0;

document.addEventListener('DOMContentLoaded', function() {
    reviewBoard = new RisingRooksBoard('reviewBoardContainer', {
        interactive: false,
        pieceTheme: 'neo_emerald'
    });

    // Auto-select first game if available
    const firstRow = document.querySelector('.match-history-row');
    if (firstRow) {
        loadGameForReview(firstRow);
    }
});

function loadGameForReview(row) {
    document.querySelectorAll('.match-history-row').forEach(r => r.style.background = '');
    row.style.background = 'var(--surface-2)';

    const pgn = row.dataset.pgn || '';
    const opp = row.dataset.opp || 'Opponent';
    const res = row.dataset.res || 'Match';

    document.getElementById('reviewMatchTitle').textContent = `vs ${opp} (${res})`;

    reviewGame.reset();
    if (pgn) {
        reviewGame.load_pgn(pgn);
        currentMoves = reviewGame.history({ verbose: true });
    } else {
        currentMoves = [];
    }

    renderMovesList();
    reviewStepLast();
}

function renderMovesList() {
    const box = document.getElementById('reviewMovesList');
    if (!currentMoves.length) {
        box.innerHTML = '<span style="color:var(--il-muted);">No notation available for this match.</span>';
        return;
    }

    let html = '';
    for (let i = 0; i < currentMoves.length; i++) {
        if (i % 2 === 0) {
            html += `<strong style="color:var(--il-muted);">${(i/2)+1}.</strong> `;
        }
        html += `<span id="moveChip_${i}" class="move-chip" style="cursor:pointer; padding:2px 4px; border-radius:4px;" onclick="goToMove(${i})">${currentMoves[i].san}</span> `;
    }
    box.innerHTML = html;
}

function goToMove(targetIdx) {
    reviewGame.reset();
    for (let i = 0; i <= targetIdx; i++) {
        reviewGame.move(currentMoves[i]);
    }
    currentMoveIdx = targetIdx + 1;
    updateReviewBoardUI();
}

function reviewStepFirst() {
    reviewGame.reset();
    currentMoveIdx = 0;
    updateReviewBoardUI();
}

function reviewStepPrev() {
    if (currentMoveIdx > 0) {
        currentMoveIdx--;
        reviewGame.undo();
        updateReviewBoardUI();
    }
}

function reviewStepNext() {
    if (currentMoveIdx < currentMoves.length) {
        reviewGame.move(currentMoves[currentMoveIdx]);
        currentMoveIdx++;
        updateReviewBoardUI();
    }
}

function reviewStepLast() {
    reviewGame.reset();
    for (const m of currentMoves) {
        reviewGame.move(m);
    }
    currentMoveIdx = currentMoves.length;
    updateReviewBoardUI();
}

function updateReviewBoardUI() {
    reviewBoard.setPosition(reviewGame.fen());

    // Highlight active chip
    document.querySelectorAll('.move-chip').forEach(c => c.style.background = '');
    if (currentMoveIdx > 0) {
        const activeChip = document.getElementById(`moveChip_${currentMoveIdx - 1}`);
        if (activeChip) activeChip.style.background = '#FEF08A';
    }

    // Update Eval Bar (Mock/Fast material+positional estimate for review)
    let whiteScore = 0;
    const pieceVals = { p: 1, n: 3.2, b: 3.3, r: 5, q: 9, k: 0 };
    for (const piece of reviewGame.board().flat()) {
        if (piece) {
            const v = pieceVals[piece.type] || 0;
            whiteScore += (piece.color === 'w') ? v : -v;
        }
    }

    const evalEl = document.getElementById('reviewEvalDisplay');
    const evalBar = document.getElementById('reviewEvalWhiteBar');
    evalEl.textContent = (whiteScore > 0 ? '+' : '') + whiteScore.toFixed(1);

    // Height calculation (clamp 10% to 90%)
    let pct = 50 + (whiteScore * 4);
    pct = Math.max(10, Math.min(90, pct));
    evalBar.style.height = pct + '%';
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

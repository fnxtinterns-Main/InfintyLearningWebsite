<?php
use App\Helper;
use App\Auth;
use App\Database;
use App\Chess\ChessRating;
use App\Chess\ChessGame;

Auth::requireRole('teacher');
$user = Auth::user();
$teacherId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : 0;

$db = Database::connect();

// Fetch assigned students for this teacher
$stmt = $db->prepare("
    SELECT cr.*, s.full_name, s.current_class, u.email, cca.notes AS assignment_notes
    FROM chess_coach_assignments cca
    JOIN chess_ratings cr ON cr.student_user_id = cca.student_user_id
    JOIN students s ON s.user_id = cr.student_user_id
    JOIN users u ON u.id = cr.student_user_id
    WHERE cca.teacher_user_id = ?
    ORDER BY cr.current_rating DESC
");
$stmt->execute([$teacherId]);
$myStudents = $stmt->fetchAll();

// Fetch recent games of assigned students
$studentIds = array_column($myStudents, 'student_user_id');
$recentStudentGames = [];
if (!empty($studentIds)) {
    $inClause = implode(',', array_map('intval', $studentIds));
    $recentStudentGames = $db->query("
        SELECT cg.*, sw.full_name AS white_name, sb.full_name AS black_name
        FROM chess_games cg
        LEFT JOIN students sw ON sw.user_id = cg.white_user_id
        LEFT JOIN students sb ON sb.user_id = cg.black_user_id
        WHERE cg.white_user_id IN ($inClause) OR cg.black_user_id IN ($inClause)
        ORDER BY cg.created_at DESC LIMIT 15
    ")->fetchAll();
}

$pageTitle = 'Chess Coach Dashboard — Rising Rooks';
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
                    Chess Coach Dashboard
                </h1>
                <p style="margin:2px 0 0; color:var(--il-muted); font-size:13px;">
                    Review your assigned students' tactical games, write study feedback, and host live 1-on-1 coaching sessions.
                </p>
            </div>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="/chess/live" class="btn btn-primary" style="background:#16A34A; border-color:#15803D; font-weight:700;">
                🎙️ Start Live 1-on-1 Session
            </a>
            <a href="/chess/bots" class="btn btn-outline-primary">🤖 Test Bots</a>
            <a href="/chess/puzzles" class="btn btn-outline-secondary">⚡ Puzzles</a>
            <a href="/chess/history" class="btn btn-outline-secondary">📜 Review History</a>
        </div>
    </div>

    <!-- ASSIGNED STUDENTS CARDS -->
    <div style="margin-bottom:32px;">
        <h3 style="margin:0 0 16px; font-size:18px;">My Assigned Mentees (<?= count($myStudents) ?>)</h3>

        <?php if (empty($myStudents)): ?>
            <div class="card" style="padding:40px; text-align:center; color:var(--il-muted);">
                You have not been assigned any 1-on-1 students yet by the school admin.
            </div>
        <?php else: ?>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">
                <?php foreach ($myStudents as $st): ?>
                    <div class="card" style="padding:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                            <div>
                                <strong style="font-size:16px; color:var(--il-text);"><?= Helper::e($st['full_name']) ?></strong>
                                <div style="font-size:12px; color:var(--il-muted);">Class <?= Helper::e($st['current_class']) ?> • <?= Helper::e($st['email']) ?></div>
                            </div>
                            <span class="chess-badge badge-<?= Helper::e($st['tier']) ?>">
                                <?= ucfirst(Helper::e($st['tier'])) ?>
                            </span>
                        </div>

                        <div style="background:var(--surface-0); border-radius:var(--r-md); padding:10px 14px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div style="font-size:11px; text-transform:uppercase; color:var(--il-muted);">Current Rating</div>
                                <div style="font-size:22px; font-weight:800; font-family:'JetBrains Mono', monospace; color:var(--chess-navy);">
                                    <?= (int)$st['current_rating'] ?>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:11px; text-transform:uppercase; color:var(--il-muted);">Record</div>
                                <div style="font-size:13px; font-family:'JetBrains Mono', monospace; font-weight:700;">
                                    <span style="color:#16A34A;"><?= (int)$st['wins'] ?>W</span> -
                                    <span style="color:#D97706;"><?= (int)$st['draws'] ?>D</span> -
                                    <span style="color:#DC2626;"><?= (int)$st['losses'] ?>L</span>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($st['assignment_notes'])): ?>
                            <div style="font-size:12px; color:var(--il-muted); background:#EFF6FF; border-left:3px solid #3B82F6; padding:6px 10px; margin-bottom:14px; border-radius:4px;">
                                <strong>Admin Note:</strong> <?= Helper::e($st['assignment_notes']) ?>
                            </div>
                        <?php endif; ?>

                        <form action="/chess-live-handler.php" method="POST">
                            <input type="hidden" name="action" value="create_session">
                            <input type="hidden" name="title" value="1-on-1 Coaching with <?= Helper::e($st['full_name']) ?>">
                            <input type="hidden" name="session_type" value="1-on-1">
                            <input type="hidden" name="student_user_id" value="<?= (int)$st['student_user_id'] ?>">
                            <input type="hidden" name="csrf" value="<?= \App\Session::csrfToken() ?>">
                            <button type="submit" class="btn btn-sm btn-outline-primary" style="width:100%; font-weight:700;">
                                🎙️ Launch 1-on-1 Live Room
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- RECENT GAMES OF MENTEES FOR REVIEW -->
    <div class="card" style="padding:24px;">
        <h3 style="margin:0 0 16px; font-size:18px;">Recent Mentee Games (Analyze Moves & Notes)</h3>

        <?php if (empty($recentStudentGames)): ?>
            <div style="text-align:center; padding:32px; color:var(--il-muted);">
                No recent games from your assigned students yet.
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="table" style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--il-border); text-align:left; color:var(--il-muted); font-size:12px; text-transform:uppercase;">
                            <th style="padding:10px;">Date</th>
                            <th style="padding:10px;">White</th>
                            <th style="padding:10px;">Black</th>
                            <th style="padding:10px;">Type</th>
                            <th style="padding:10px;">Result</th>
                            <th style="padding:10px;">Moves</th>
                            <th style="padding:10px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentStudentGames as $rg): ?>
                            <?php
                                $botTarget = (int)($rg['bot_rating_target'] ?? 0);
                                $botName = 'Rising Pawn (100)';
                                if ($botTarget >= 2500) $botName = 'Rising Queen (2700)';
                                elseif ($botTarget >= 1800) $botName = 'Rising Rook (2000)';
                                elseif ($botTarget >= 1100) $botName = 'Rising Bishop (1250)';
                                elseif ($botTarget >= 600) $botName = 'Rising Knight (750)';

                                $whiteName = !empty($rg['white_name']) ? $rg['white_name'] : $botName;
                                $blackName = !empty($rg['black_name']) ? $rg['black_name'] : $botName;
                            ?>
                            <tr style="border-bottom:1px solid var(--il-border);">
                                <td style="padding:10px; font-size:13px; color:var(--il-muted);">
                                    <?= date('d M, H:i', strtotime($rg['created_at'])) ?>
                                </td>
                                <td style="padding:10px; font-weight:600;">
                                    <?= Helper::e($whiteName) ?>
                                </td>
                                <td style="padding:10px; font-weight:600;">
                                    <?= Helper::e($blackName) ?>
                                </td>
                                <td style="padding:10px;">
                                    <span class="badge" style="background:#F1F5F9; color:#475569; text-transform:capitalize;">
                                        <?= Helper::e($rg['game_type']) ?>
                                    </span>
                                </td>
                                <td style="padding:10px; font-family:'JetBrains Mono', monospace; font-weight:700;">
                                    <?= Helper::e($rg['result']) ?>
                                </td>
                                <td style="padding:10px; font-family:'JetBrains Mono', monospace;">
                                    <?= (int)$rg['moves_count'] ?>
                                </td>
                                <td style="padding:10px;">
                                    <button type="button" class="btn btn-xs btn-outline-primary btn-view-pgn"
                                        data-white="<?= Helper::e($whiteName) ?>"
                                        data-black="<?= Helper::e($blackName) ?>"
                                        data-result="<?= Helper::e($rg['result']) ?>"
                                        data-type="<?= Helper::e($rg['game_type'] ?? 'match') ?>"
                                        data-moves="<?= (int)$rg['moves_count'] ?>"
                                        data-date="<?= Helper::e(date('d M Y, H:i', strtotime($rg['created_at']))) ?>"
                                        data-fen="<?= Helper::e($rg['current_fen'] ?? '') ?>"
                                        data-pgn="<?= Helper::e($rg['pgn'] ?? '') ?>">
                                        View PGN 📋
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>
</main>

<!-- PGN Review & Interactive Stepper Modal -->
<div id="pgnReviewModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(6px); padding:20px;">
    <div style="background:#ffffff; border-radius:20px; max-width:860px; width:100%; max-height:92vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 24px 60px rgba(0,0,0,0.35);">
        <!-- Modal Header -->
        <div style="padding:16px 24px; border-bottom:1px solid var(--il-border); display:flex; justify-content:space-between; align-items:center; background:var(--surface-0);">
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:18px;">📋</span>
                    <h3 id="pgnModalMatchTitle" style="margin:0; font-size:17px; font-weight:700;">Student Game Review</h3>
                    <span id="pgnModalResultBadge" class="badge" style="background:#F1F5F9; color:#475569; font-weight:700; font-size:12px;">1-0</span>
                </div>
                <div id="pgnModalMeta" style="font-size:12px; color:var(--il-muted); margin-top:2px;">
                    White vs Black • 0 Moves • Date
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closePgnModal()" style="border-radius:50%; width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:bold; line-height:1;">
                &times;
            </button>
        </div>

        <!-- Modal Body: Two-column layout (Board Stepper left, PGN & Moves right) -->
        <div style="padding:20px 24px; overflow-y:auto; flex:1; display:grid; grid-template-columns:320px 1fr; gap:20px; align-items:start;" id="pgnModalGrid">
            <!-- Left: Board Stage & Stepper Controls -->
            <div>
                <div class="board-stage" style="width:100%; max-width:320px; aspect-ratio:1/1; margin:0 auto 12px; border-radius:12px; overflow:hidden; box-shadow:0 4px 14px rgba(0,0,0,0.12);">
                    <div id="teacherReviewBoardContainer" style="width:100%; height:100%;"></div>
                </div>

                <!-- Stepping Controls -->
                <div style="display:flex; justify-content:center; gap:6px; margin-bottom:8px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="teacherReviewStepFirst()" title="Start">|◀</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="teacherReviewStepPrev()" title="Previous Move">◀ Back</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="teacherReviewStepNext()" title="Next Move">Next ▶</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="teacherReviewStepLast()" title="Final Move">▶|</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="teacherReviewFlipBoard()" title="Flip Board">🔄 Flip</button>
                </div>
                <div id="teacherReviewStepIndicator" style="text-align:center; font-size:12px; color:var(--il-muted); font-family:'JetBrains Mono', monospace;">
                    Move 0 / 0
                </div>
            </div>

            <!-- Right: Move Chips & Raw PGN -->
            <div style="display:flex; flex-direction:column; gap:16px;">
                <!-- Move Chips -->
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <span style="font-size:12px; font-weight:700; text-transform:uppercase; color:var(--il-muted);">
                            Move Notation (Click any move)
                        </span>
                    </div>
                    <div id="teacherMovesList" style="background:var(--surface-0); border:1px solid var(--il-border); border-radius:8px; padding:10px 12px; max-height:140px; overflow-y:auto; font-size:12px; line-height:1.8; font-family:'JetBrains Mono', monospace;">
                        No moves recorded.
                    </div>
                </div>

                <!-- Raw PGN Box & Copy Button -->
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <span style="font-size:12px; font-weight:700; text-transform:uppercase; color:var(--il-muted);">
                            PGN Text
                        </span>
                        <button type="button" class="btn btn-xs btn-primary" id="btnCopyPgn" onclick="copyModalPgn()">
                            📋 Copy PGN
                        </button>
                    </div>
                    <textarea id="teacherRawPgnText" readonly style="width:100%; height:120px; font-family:'JetBrains Mono', monospace; font-size:11px; padding:10px; border-radius:8px; border:1px solid var(--il-border); background:#F8FAFC; resize:vertical; line-height:1.4; color:#1E293B;"></textarea>
                    <div style="font-size:11px; color:var(--il-muted); margin-top:4px;">
                        Copy and paste directly into Chess.com or Lichess for computer analysis.
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="padding:12px 24px; border-top:1px solid var(--il-border); display:flex; justify-content:flex-end; gap:10px; background:var(--surface-0);">
            <button type="button" class="btn btn-secondary" onclick="closePgnModal()">Close</button>
        </div>
    </div>
</div>

<style>
@media (max-width: 720px) {
    #pgnModalGrid {
        grid-template-columns: 1fr !important;
    }
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
let teacherReviewGame = null;
let teacherReviewBoard = null;
let teacherReviewMoves = [];
let teacherCurrentMoveIdx = 0;

document.addEventListener('DOMContentLoaded', function() {
    const ChessConstructor = (typeof window.Chess === 'function')
        ? window.Chess
        : (window.Chess && typeof window.Chess.Chess === 'function' ? window.Chess.Chess : null);

    if (ChessConstructor) {
        teacherReviewGame = new ChessConstructor();
    }

    document.querySelectorAll('.btn-view-pgn').forEach(btn => {
        btn.addEventListener('click', function() {
            openPgnModal(this);
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePgnModal();
    });

    const modal = document.getElementById('pgnReviewModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) closePgnModal();
        });
    }
});

function openPgnModal(btn) {
    const white = btn.dataset.white || 'White';
    const black = btn.dataset.black || 'Black';
    const result = btn.dataset.result || '*';
    const moves = btn.dataset.moves || '0';
    const date = btn.dataset.date || '';
    let pgn = btn.dataset.pgn || '';
    const fen = btn.dataset.fen || '';

    // Title & badge
    document.getElementById('pgnModalMatchTitle').textContent = `${white} vs ${black}`;
    const badge = document.getElementById('pgnModalResultBadge');
    badge.textContent = result;
    if (result === '1-0') {
        badge.style.background = '#DCFCE7';
        badge.style.color = '#166534';
    } else if (result === '0-1') {
        badge.style.background = '#FEE2E2';
        badge.style.color = '#991B1B';
    } else {
        badge.style.background = '#F1F5F9';
        badge.style.color = '#475569';
    }
    document.getElementById('pgnModalMeta').textContent = `Result: ${result} • ${moves} Moves • ${date}`;

    // If PGN is missing or blank, construct minimal PGN headers
    if (!pgn.trim()) {
        pgn = `[Event "Academy Game"]\n[Site "Rising Rooks"]\n[Date "${date}"]\n[White "${white}"]\n[Black "${black}"]\n[Result "${result}"]\n\n*`;
    }
    document.getElementById('teacherRawPgnText').value = pgn;

    // Initialize board if not created yet
    if (!teacherReviewBoard && window.RisingRooksBoard) {
        try {
            teacherReviewBoard = new RisingRooksBoard('teacherReviewBoardContainer', {
                interactive: false,
                pieceTheme: 'neo_emerald',
                orientation: 'white'
            });
        } catch (e) {
            console.error('Board init error:', e);
        }
    }

    if (!teacherReviewGame) {
        const ChessConstructor = (typeof window.Chess === 'function')
            ? window.Chess
            : (window.Chess && typeof window.Chess.Chess === 'function' ? window.Chess.Chess : null);
        if (ChessConstructor) {
            teacherReviewGame = new ChessConstructor();
        }
    }

    teacherReviewMoves = [];
    if (teacherReviewGame) {
        teacherReviewGame.reset();
        if (pgn) {
            try {
                teacherReviewGame.load_pgn(pgn);
                teacherReviewMoves = teacherReviewGame.history({ verbose: true });
            } catch (e) {
                console.warn('Could not parse PGN:', e);
                if (fen) {
                    try { teacherReviewGame.load(fen); } catch(ex){}
                }
            }
        }
    }

    renderTeacherMovesList();
    teacherReviewStepLast();

    const modal = document.getElementById('pgnReviewModal');
    modal.style.display = 'flex';
}

function closePgnModal() {
    const modal = document.getElementById('pgnReviewModal');
    if (modal) modal.style.display = 'none';
}

function renderTeacherMovesList() {
    const box = document.getElementById('teacherMovesList');
    if (!teacherReviewMoves || !teacherReviewMoves.length) {
        box.innerHTML = '<span style="color:var(--il-muted);">No moves notation recorded for this match.</span>';
        return;
    }

    let html = '';
    for (let i = 0; i < teacherReviewMoves.length; i++) {
        if (i % 2 === 0) {
            html += `<strong style="color:var(--il-muted); margin-left:${i>0 ? '6px' : '0'};">${(i/2)+1}.</strong> `;
        }
        html += `<span id="tMoveChip_${i}" class="t-move-chip" style="cursor:pointer; padding:2px 5px; border-radius:4px; font-weight:500;" onclick="teacherGoToMove(${i})">${teacherReviewMoves[i].san}</span> `;
    }
    box.innerHTML = html;
}

function teacherGoToMove(targetIdx) {
    if (!teacherReviewGame) return;
    teacherReviewGame.reset();
    for (let i = 0; i <= targetIdx; i++) {
        teacherReviewGame.move(teacherReviewMoves[i]);
    }
    teacherCurrentMoveIdx = targetIdx + 1;
    updateTeacherBoardUI();
}

function teacherReviewStepFirst() {
    if (!teacherReviewGame) return;
    teacherReviewGame.reset();
    teacherCurrentMoveIdx = 0;
    updateTeacherBoardUI();
}

function teacherReviewStepPrev() {
    if (!teacherReviewGame) return;
    if (teacherCurrentMoveIdx > 0) {
        teacherCurrentMoveIdx--;
        teacherReviewGame.undo();
        updateTeacherBoardUI();
    }
}

function teacherReviewStepNext() {
    if (!teacherReviewGame) return;
    if (teacherCurrentMoveIdx < teacherReviewMoves.length) {
        teacherReviewGame.move(teacherReviewMoves[teacherCurrentMoveIdx]);
        teacherCurrentMoveIdx++;
        updateTeacherBoardUI();
    }
}

function teacherReviewStepLast() {
    if (!teacherReviewGame) return;
    teacherReviewGame.reset();
    for (const m of teacherReviewMoves) {
        teacherReviewGame.move(m);
    }
    teacherCurrentMoveIdx = teacherReviewMoves.length;
    updateTeacherBoardUI();
}

function teacherReviewFlipBoard() {
    if (!teacherReviewBoard) return;
    teacherReviewBoard.options.orientation = (teacherReviewBoard.options.orientation === 'white') ? 'black' : 'white';
    teacherReviewBoard.render();
}

function updateTeacherBoardUI() {
    if (teacherReviewBoard && teacherReviewGame) {
        teacherReviewBoard.setPosition(teacherReviewGame.fen());
    }

    // Highlight active chip
    document.querySelectorAll('.t-move-chip').forEach(c => c.style.background = '');
    if (teacherCurrentMoveIdx > 0) {
        const activeChip = document.getElementById(`tMoveChip_${teacherCurrentMoveIdx - 1}`);
        if (activeChip) {
            activeChip.style.background = '#FEF08A';
            activeChip.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    const indicator = document.getElementById('teacherReviewStepIndicator');
    if (indicator) {
        indicator.textContent = `Move ${teacherCurrentMoveIdx} / ${teacherReviewMoves.length}`;
    }
}

function copyModalPgn() {
    const textEl = document.getElementById('teacherRawPgnText');
    const btn = document.getElementById('btnCopyPgn');
    if (!textEl) return;

    const textToCopy = textEl.value;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(textToCopy).then(showCopied).catch(fallbackCopy);
    } else {
        fallbackCopy();
    }

    function fallbackCopy() {
        textEl.select();
        try {
            document.execCommand('copy');
            showCopied();
        } catch (e) {
            alert('Please select and copy the text manually.');
        }
    }

    function showCopied() {
        if (!btn) return;
        const oldText = btn.innerHTML;
        btn.innerHTML = '✅ Copied!';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');
        setTimeout(() => {
            btn.innerHTML = oldText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
        }, 2000);
    }
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

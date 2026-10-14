<?php
use App\Helper;
use App\Auth;
use App\Chess\ChessLive;
use App\Chess\ChessRating;

Auth::requireLogin();
$user = Auth::user();
$userId = (int)$user['id'];
$role = Auth::role();
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : 0;

$sessionId = (int)($_GET['session_id'] ?? 0);
$session = $sessionId ? ChessLive::getSession($sessionId) : null;

// If teacher and no active session, show form to launch one
$isTeacher = ($role === 'teacher' || Auth::isSchoolAdmin() || Auth::isSuperAdmin());

$pageTitle = 'Live Chess Coaching Room — Rising Rooks';
require VIEW_PATH . '/layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/chess.css">

<main class="page-body">
<div class="container">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="/chess" class="btn btn-sm btn-outline-secondary">← Chess Hub</a>
            <h1 style="margin:0; font-size:24px; font-family:'Fraunces', serif;">
                🎙️ Live Interactive Coaching Room
            </h1>
        </div>

        <div style="display:flex;align-items:center;gap:8px;">
            <a href="/live/whiteboard" target="_blank" class="btn btn-sm btn-secondary" style="font-weight:600">
                🎨 Interactive Whiteboard
            </a>
            <?php if ($session): ?>
                <span class="badge" style="background:#DCFCE7; color:#166534; font-weight:700; padding:6px 12px; font-size:13px;">
                    🔴 Session Live
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$session && $isTeacher): ?>
        <!-- Teacher Launch New Session Card -->
        <div class="card" style="max-width:600px; margin:40px auto; padding:32px;">
            <div style="text-align:center; margin-bottom:24px;">
                <span style="font-size:48px;">♟️👨‍🏫</span>
                <h2 style="margin:8px 0; font-size:22px;">Start a Live Coaching Session</h2>
                <p style="color:var(--il-muted); font-size:14px; margin:0;">
                    Broadcast an interactive board to a student or your entire batch. Both of you will be able to play and analyze together in real time.
                </p>
            </div>

            <form action="/chess-live-handler.php" method="POST">
                <input type="hidden" name="action" value="create_session">
                <input type="hidden" name="csrf" value="<?= \App\Session::csrfToken() ?>">

                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label" style="font-weight:600; margin-bottom:6px; display:block;">Session Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Italian Game Deep Dive & 1-1 Sparring" required>
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label" style="font-weight:600; margin-bottom:6px; display:block;">Session Type</label>
                    <select name="session_type" class="form-control">
                        <option value="1-on-1">1-on-1 Student Mentoring</option>
                        <option value="batch_class">Batch Interactive Class</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:24px;">
                    <label class="form-label" style="font-weight:600; margin-bottom:6px; display:block;">Student User ID (Optional for 1-on-1)</label>
                    <input type="number" name="student_user_id" class="form-control" placeholder="e.g. 6">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; font-weight:700;">
                    Launch Live Board Now 🚀
                </button>
            </form>
        </div>

    <?php elseif (!$session): ?>
        <!-- Student Waiting for Coach Card -->
        <div class="card" style="max-width:540px; margin:60px auto; padding:40px; text-align:center;">
            <span style="font-size:48px;">⌛</span>
            <h2 style="margin:12px 0 8px; font-size:20px;">No Active Coaching Session Right Now</h2>
            <p style="color:var(--il-muted); font-size:14px; margin:0 0 24px; line-height:1.5;">
                When your assigned school chess coach starts a live 1-on-1 session or class, a notification will appear on your dashboard.
            </p>
            <a href="/chess" class="btn btn-primary">Return to Chess Hub</a>
        </div>

    <?php else: ?>
        <!-- ACTIVE LIVE DUAL-BOARD -->
        <div class="chess-grid">

            <!-- Live Board -->
            <div class="chess-board-wrap">
                <div class="player-bar">
                    <div class="player-info">
                        <div class="player-avatar">👨‍🏫</div>
                        <div>
                            <strong>Coach: <?= Helper::e($session['teacher_name'] ?? 'Coach') ?></strong>
                            <div style="font-size:12px; color:var(--il-muted);">Live Broadcaster</div>
                        </div>
                    </div>
                    <span class="badge" style="background:#E0E7FF; color:#3730A3;">Demo Mode</span>
                </div>

                <div class="board-stage">
                    <div id="liveBoardContainer" style="width:100%; height:100%;"></div>
                </div>

                <div class="player-bar bottom">
                    <div class="player-info">
                        <div class="player-avatar">🎓</div>
                        <div>
                            <strong>Student: <?= Helper::e($session['student_name'] ?? 'Student') ?></strong>
                            <div style="font-size:12px; color:var(--il-muted);">Live Participant</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coach Live Controls -->
            <div class="chess-sidebar-card">
                <h3 style="margin:0 0 8px; font-size:18px;"><?= Helper::e($session['title']) ?></h3>
                <p style="font-size:13px; color:var(--il-muted); margin:0 0 16px;">
                    Moves on this board are synchronized in real time between coach and student.
                </p>

                <!-- Notation -->
                <div style="margin-bottom:16px;">
                    <span style="font-size:13px; font-weight:700;">Live Notation:</span>
                    <div class="move-history-table" id="livePgnBox" style="height:180px; margin-top:6px;">
                        <div style="color:var(--il-muted);">Ready for moves...</div>
                    </div>
                </div>

                <!-- Teacher Controls -->
                <?php if ($isTeacher): ?>
                    <div style="display:flex; flex-direction:column; gap:10px; border-top:1px solid var(--il-border); padding-top:16px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetBoard">
                            Reset Board to Starting Position 🔄
                        </button>
                        <form action="/chess-live-handler.php" method="POST" onsubmit="return confirm('End this live coaching session?');">
                            <input type="hidden" name="action" value="end_session">
                            <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                            <input type="hidden" name="csrf" value="<?= \App\Session::csrfToken() ?>">
                            <button type="submit" class="btn btn-sm btn-danger" style="width:100%;">
                                End Live Session 🛑
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
        <script>
        if (typeof window.Chess === 'undefined') {
            document.write('<script src="/assets/js/chess.min.js"><\/script>');
        }
        </script>
        <script src="/assets/js/chess-engine.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sessionId = <?= (int)$session['id'] ?>;
            const isTeacher = <?= $isTeacher ? 'true' : 'false' ?>;
            let board = null;

            try {
                board = new RisingRooksBoard('liveBoardContainer', {
                    orientation: 'white',
                    gameType: 'live_coaching',
                    onMove: function(move, game) {
                        syncStateToServer(game.fen(), game.pgn());
                    }
                });
            } catch (e) {
                console.error('Failed to initialize live coaching board:', e);
                document.getElementById('liveBoardContainer').innerHTML = '<div style="color:#fff; padding:20px; text-align:center;">Board error: ' + e.message + '</div>';
            }

            // Polling every 1.5s for live move sync
            setInterval(function() {
                fetch(`/chess-live-handler.php?action=get_state&session_id=${sessionId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.session) {
                            if (data.session.current_fen && data.session.current_fen !== board.game.fen()) {
                                board.setPosition(data.session.current_fen);
                                document.getElementById('livePgnBox').textContent = data.session.pgn || '';
                            }
                        }
                    });
            }, 1500);

            function syncStateToServer(fen, pgn) {
                fetch('/chess-live-handler.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        'action': 'sync_move',
                        'session_id': sessionId,
                        'fen': fen,
                        'pgn': pgn
                    })
                });
            }

            const resetBtn = document.getElementById('btnResetBoard');
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    board.reset();
                    syncStateToServer(board.game.fen(), '');
                });
            }
        });
        </script>
    <?php endif; ?>

</div>
</main>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

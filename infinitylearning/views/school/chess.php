<?php
use App\Helper;
use App\Auth;
use App\Database;
use App\Chess\ChessRating;
use App\Chess\ChessGame;

$schoolId = Auth::requireSchoolAdmin();
$school = \App\School::find($schoolId);

$db = Database::connect();

// Fetch student tier distribution for this school
$stmt = $db->prepare("
    SELECT tier, COUNT(*) as cnt
    FROM chess_ratings
    WHERE school_id = ?
    GROUP BY tier
");
$stmt->execute([$schoolId]);
$tierCounts = ['learner'=>0, 'rookie'=>0, 'intermediate'=>0, 'master'=>0, 'grandmaster'=>0];
foreach ($stmt->fetchAll() as $r) {
    $tierCounts[$r['tier']] = (int)$r['cnt'];
}

// Fetch all students with chess ratings
$stmt = $db->prepare("
    SELECT cr.*, s.full_name, s.current_class, u.email,
           t.full_name AS coach_name
    FROM chess_ratings cr
    JOIN students s ON s.user_id = cr.student_user_id
    JOIN users u ON u.id = cr.student_user_id
    LEFT JOIN chess_coach_assignments cca ON cca.student_user_id = cr.student_user_id
    LEFT JOIN teachers t ON t.user_id = cca.teacher_user_id
    WHERE cr.school_id = ?
    ORDER BY cr.current_rating DESC
");
$stmt->execute([$schoolId]);
$students = $stmt->fetchAll();

// Fetch school teachers for assignment dropdown
$stmt = $db->prepare("
    SELECT u.id, t.full_name, t.expert_field
    FROM users u
    JOIN teachers t ON t.user_id = u.id
    WHERE u.school_id = ? AND u.status = 'active'
    ORDER BY t.full_name ASC
");
$stmt->execute([$schoolId]);
$teachers = $stmt->fetchAll();

$pageTitle = 'Rising Rooks Chess Academy — School Management';
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
                    Rising Rooks Chess Academy • <?= Helper::e($school['name']) ?>
                </h1>
                <p style="margin:2px 0 0; color:var(--il-muted); font-size:13px;">
                    Oversee tier distributions, assign chess trainers, and track inter-school tournament readiness.
                </p>
            </div>
        </div>

        <a href="/chess" class="btn btn-outline-primary">Open Chess Hub ♟️</a>
    </div>

    <!-- TIER DISTRIBUTION CARDS -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); gap:16px; margin-bottom:32px;">
        <?php foreach (ChessRating::TIERS as $k => $t): ?>
            <div class="card" style="padding:18px; border-left:4px solid <?= $t['color'] ?>;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:20px;"><?= $t['badge'] ?></span>
                    <span class="badge" style="background:#F1F5F9; color:#475569; font-weight:700;"><?= $t['min'] ?>–<?= $t['max'] ?> Elo</span>
                </div>
                <div style="font-size:26px; font-weight:800; font-family:'JetBrains Mono', monospace; margin:8px 0 4px; color:var(--il-text);">
                    <?= $tierCounts[$k] ?>
                </div>
                <div style="font-size:13px; font-weight:700; color:var(--il-text);"><?= $t['label'] ?></div>
                <div style="font-size:11px; color:var(--il-muted);"><?= $t['academy'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ASSIGN COACH MODAL / DRAWER FORM -->
    <div class="card" style="padding:24px; margin-bottom:32px;">
        <h3 style="margin:0 0 16px; font-size:18px;">🎯 Assign Coach / Mentor to Student</h3>
        <form action="/school/chess-assign-handler.php" method="POST" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)) 120px; gap:16px; align-items:flex-end;">
            <input type="hidden" name="csrf" value="<?= \App\Session::csrfToken() ?>">

            <div class="form-group">
                <label class="form-label" style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Select Student</label>
                <select name="student_user_id" class="form-control" required>
                    <option value="">-- Choose Student --</option>
                    <?php foreach ($students as $st): ?>
                        <option value="<?= (int)$st['student_user_id'] ?>">
                            <?= Helper::e($st['full_name']) ?> (<?= ucfirst(Helper::e($st['tier'])) ?> - <?= (int)$st['current_rating'] ?> Elo)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Assign Teacher / Coach</label>
                <select name="teacher_user_id" class="form-control" required>
                    <option value="">-- Choose Coach --</option>
                    <?php foreach ($teachers as $tc): ?>
                        <option value="<?= (int)$tc['id'] ?>">
                            <?= Helper::e($tc['full_name']) ?> (<?= Helper::e($tc['expert_field'] ?? 'Trainer') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Mentoring Notes</label>
                <input type="text" name="notes" class="form-control" placeholder="e.g. Focus on endgames & tactics">
            </div>

            <button type="submit" class="btn btn-primary" style="font-weight:700; height:42px;">
                Assign
            </button>
        </form>
    </div>

    <!-- STUDENTS LEADERBOARD & RATINGS TABLE -->
    <div class="card" style="padding:24px;">
        <h3 style="margin:0 0 16px; font-size:18px;">Enrolled Students (Ranked by Rating)</h3>

        <?php if (empty($students)): ?>
            <div style="text-align:center; padding:32px; color:var(--il-muted);">
                No students enrolled in chess yet. They will appear here once they begin their 5-match calibration.
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="table" style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--il-border); text-align:left; color:var(--il-muted); font-size:12px; text-transform:uppercase;">
                            <th style="padding:10px;">Rank</th>
                            <th style="padding:10px;">Student</th>
                            <th style="padding:10px;">Rating</th>
                            <th style="padding:10px;">Tier / Batch</th>
                            <th style="padding:10px;">W / D / L</th>
                            <th style="padding:10px;">Status</th>
                            <th style="padding:10px;">Assigned Coach</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; foreach ($students as $s): ?>
                            <tr style="border-bottom:1px solid var(--il-border);">
                                <td style="padding:12px 10px; font-weight:800; font-family:'JetBrains Mono', monospace; color:var(--il-muted);">
                                    #<?= $rank++ ?>
                                </td>
                                <td style="padding:12px 10px;">
                                    <strong><?= Helper::e($s['full_name']) ?></strong>
                                    <div style="font-size:12px; color:var(--il-muted);"><?= Helper::e($s['email']) ?></div>
                                </td>
                                <td style="padding:12px 10px; font-family:'JetBrains Mono', monospace; font-weight:800; font-size:15px; color:var(--chess-navy);">
                                    <?= (int)$s['current_rating'] ?>
                                </td>
                                <td style="padding:12px 10px;">
                                    <span class="chess-badge badge-<?= Helper::e($s['tier']) ?>">
                                        <?= ucfirst(Helper::e($s['tier'])) ?>
                                    </span>
                                </td>
                                <td style="padding:12px 10px; font-family:'JetBrains Mono', monospace; font-size:13px;">
                                    <span style="color:#16A34A; font-weight:700;"><?= (int)$s['wins'] ?>W</span> /
                                    <span style="color:#D97706;"><?= (int)$s['draws'] ?>D</span> /
                                    <span style="color:#DC2626;"><?= (int)$s['losses'] ?>L</span>
                                </td>
                                <td style="padding:12px 10px;">
                                    <?php if ($s['calibration_completed']): ?>
                                        <span class="badge" style="background:#DCFCE7; color:#166534;">Calibrated</span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#FEF3C7; color:#92400E;">Match <?= (int)$s['calibration_matches_played'] ?>/5</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px 10px;">
                                    <?= $s['coach_name'] ? '<strong style="color:var(--il-primary);">' . Helper::e($s['coach_name']) . '</strong>' : '<span style="color:var(--il-muted);">Unassigned</span>' ?>
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

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

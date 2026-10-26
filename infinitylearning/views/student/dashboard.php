<?php
use App\Auth;
use App\Helper;
use App\Activity;
use App\Batch;
use App\Attendance;
use App\Lesson;
use App\Announcement;
use App\Database;
use App\Chess\ChessRating;

$user = Auth::user();
$student = [];
try {
    $student = Auth::findStudentByUserId((int)$user['id']) ?: [];
} catch (\Throwable $e) {}

$chessProfile = [
    'tier' => 'Bronze',
    'current_rating' => 1200,
    'calibration_completed' => true,
    'in_promotion_battle' => false,
    'wins' => 0,
    'games_played' => 0
];
try {
    $chessProfile = ChessRating::getOrCreate((int)$user['id'], Auth::schoolId());
} catch (\Throwable $e) {}

$batches = [];
try {
    $batches = Batch::forStudent((int)$user['id']) ?: [];
} catch (\Throwable $e) {}

$att = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
try {
    $att = Attendance::summaryForStudent((int)$user['id']) ?: $att;
} catch (\Throwable $e) {}

$lessons = [];
try {
    $lessons = Lesson::listForViewer(['limit' => 4]) ?: [];
} catch (\Throwable $e) {}

$upcoming = [];
try {
    $upcoming = Activity::upcoming(3, Auth::schoolId()) ?: [];
} catch (\Throwable $e) {}

$ann = [];
try {
    $ann = Announcement::latestForViewer('students', 3) ?: [];
} catch (\Throwable $e) {}

$parent = null;
try {
    $db = Database::connect();
    $parentStmt = $db->prepare("
        SELECT p.* FROM parents p
        JOIN student_parents sp ON sp.parent_id = p.id
        WHERE sp.student_user_id = ? AND sp.is_primary = 1
        LIMIT 1
    ");
    $parentStmt->execute([(int)$user['id']]);
    $parent = $parentStmt->fetch() ?: null;
} catch (\Throwable $e) {}

$upcomingAssignments = [];
try {
    $upcomingAssignments = \App\ActivityAssignment::upcomingForStudent((int)$user['id'], 5);
} catch (\Throwable $e) {}

$studentExams = [
    'categories' => [],
    'all_tests'  => [],
    'summary'    => ['total_tests' => 0, 'total_obtained' => 0, 'total_max' => 0, 'average_pct' => 0]
];
try {
    $studentExams = \App\AcademicAssessment::getStudentExamHistory((int)$user['id']);
} catch (\Throwable $e) {}

$doubtStats = ['total' => 0, 'answered' => 0, 'open' => 0];
try {
    $doubtStats = \App\DoubtFeed::getStudentStats((int)$user['id']);
} catch (\Throwable $e) {}

$dailyRoutine = [];
try {
    $dailyRoutine = \App\Timetable::getStudentSchedule((int)$user['id']) ?: [];
} catch (\Throwable $e) {}

$pageTitle = 'Dashboard — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';

$totalAtt = array_sum($att) ?: 1;
$presentPct = (int)round(($att['present'] ?? 0) / $totalAtt * 100);
$absentPct  = (int)round(($att['absent'] ?? 0)  / $totalAtt * 100);
$latePct    = (int)round(($att['late'] ?? 0)    / $totalAtt * 100);
$excusedPct = (int)round(($att['excused'] ?? 0) / $totalAtt * 100);

$examSummary = $studentExams['summary'] ?? ['total_tests' => 0, 'average_pct' => 0];
$latestTest = !empty($studentExams['all_tests'][0]) ? $studentExams['all_tests'][0] : null;
?>
<section class="section-sm">
    <div class="container-narrow" style="max-width:none">

        <!-- ============ WELCOME HEADER ============ -->
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px; align-items:center;">
            <div style="display:flex; align-items:center; gap:16px;">
                <?php if (!empty($schoolBranding['crest_logo'])): ?>
                    <div class="school-header-crest-badge" style="width:64px; height:64px; border-radius:12px; background:#FFFFFF; border:1.5px solid var(--il-border); box-shadow:0 2px 6px rgba(0,0,0,0.08); display:flex; align-items:center; justify-content:center; padding:4px; overflow:hidden; flex-shrink:0;">
                        <img src="<?= htmlspecialchars($schoolBranding['crest_logo']) ?>" alt="<?= Helper::e($schoolBranding['name'] ?? 'School') ?>" style="width:100%; height:100%; object-fit:contain;" onerror="this.closest('.school-header-crest-badge').style.display='none';">
                    </div>
                <?php endif; ?>
                <div class="dashboard-header-text">
                    <span class="kicker">👋 WELCOME BACK</span>
                    <h1 style="margin-top:2px; font-weight:800; letter-spacing:-0.02em;"><?= Helper::e($student['full_name'] ?? 'Student') ?></h1>
                    <p class="muted" style="margin-top:4px;"><?= Helper::e(!empty($student['current_class']) ? $student['current_class'] : 'Your learning snapshot') ?><?= !empty($schoolBranding['name']) ? ' · ' . Helper::e($schoolBranding['name']) : (!empty($student['school_name']) ? ' · ' . Helper::e($student['school_name']) : '') ?></p>
                </div>
            </div>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <a href="/student?id=<?= (int)$user['id'] ?>" class="btn btn-outline-primary btn-sm" style="font-weight:700;">🌱 Development Profile</a>
                <a href="/student/report-card" class="btn btn-primary btn-sm" style="font-weight:700;">📄 Academic Report Card</a>
                <a href="/student/doubts" class="btn btn-secondary btn-sm">💬 Doubts</a>
                <a href="/parent-pass" class="btn btn-secondary btn-sm" title="Share or view student progress card">📱 Parent Pass</a>
            </div>
        </div>

        <!-- ============ TODAY'S CLASS ROUTINE (DAILY ROUTINE) ============ -->
        <div class="card mt-2" style="border-radius:14px; padding:18px 22px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:12px;">
                <div>
                    <h3 style="margin:0; display:flex; align-items:center; gap:8px; font-size:17px;">
                        <span>🗓️</span> Today's Class Routine (<?= date('l, d M') ?>)
                    </h3>
                    <p class="muted" style="margin:2px 0 0; font-size:12.5px;">Your scheduled class periods, timings, and faculty for today.</p>
                </div>
                <a href="/student/timetable" class="btn btn-outline-primary btn-sm" style="font-weight:700;">
                    🗓️ View Weekly Routine →
                </a>
            </div>

            <?php if (empty($dailyRoutine)): ?>
                <div style="text-align:center; padding:20px; background:var(--surface-1); border-radius:10px; border:1px dashed var(--il-border);">
                    <span style="font-size:24px;">🏖️</span>
                    <p class="muted" style="margin:6px 0 0; font-size:13px;">No class periods scheduled for today. Check your <a href="/student/timetable" style="font-weight:600; text-decoration:underline;">Weekly Routine</a> for the complete 6-day timetable.</p>
                </div>
            <?php else: ?>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:10px;">
                    <?php foreach ($dailyRoutine as $slot): 
                        $subColor = !empty($slot['subject_color']) ? $slot['subject_color'] : '#2563EB';
                        $subIcon  = !empty($slot['subject_icon'])  ? $slot['subject_icon']  : '📖';
                    ?>
                        <div style="background:var(--surface-1); border:1px solid var(--il-border); border-left:3.5px solid <?= Helper::e($subColor) ?>; border-radius:10px; padding:10px 12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <span class="badge" style="background:<?= Helper::e($subColor) ?>18; color:<?= Helper::e($subColor) ?>; font-size:10.5px; font-weight:700;">
                                    Period <?= (int)$slot['period_number'] ?>
                                </span>
                                <span class="muted" style="font-size:11px; font-family:'JetBrains Mono', monospace; font-weight:600;">
                                    <?= \App\Timetable::formatTime12($slot['start_time']) ?> - <?= \App\Timetable::formatTime12($slot['end_time']) ?>
                                </span>
                            </div>
                            <h4 style="margin:2px 0; font-size:13.5px; display:flex; align-items:center; gap:5px;">
                                <span><?= Helper::e($subIcon) ?></span>
                                <?= Helper::e($slot['subject_name'] ?? 'Subject') ?>
                            </h4>
                            <div class="muted" style="font-size:11px; margin-top:3px;">
                                👨‍🏫 <?= Helper::e($slot['teacher_name'] ?? 'Assigned Faculty') ?>
                                <?php if (!empty($slot['room_number'])): ?>
                                    · 📍 Rm <?= Helper::e($slot['room_number']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============ ACADEMIC EXAMINATIONS & RESULTS BANNER ============ -->
        <div class="card mt-2" style="background:linear-gradient(135deg, rgba(37,99,235,0.06), rgba(16,185,129,0.06)); border:1px solid rgba(37,99,235,0.22); border-radius:14px; padding:18px 22px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div style="font-size:36px; line-height:1;">📊</div>
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span style="font-size:18px; font-weight:800; color:var(--il-text);">
                                Academic Examination Performance
                            </span>
                            <?php if ($examSummary['total_tests'] > 0): ?>
                                <span class="badge badge-success" style="font-size:11px; padding:3px 8px;">
                                    ⭐ <?= $examSummary['average_pct'] ?>% Overall Avg
                                </span>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size:11px; padding:3px 8px;">
                                    📝 New Tests Ongoing
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="muted" style="font-size:12.5px; margin-top:4px;">
                            <?php if ($latestTest): ?>
                                Latest: <strong><?= Helper::e($latestTest['title']) ?></strong> (<?= Helper::e($latestTest['subject_name']) ?>) — <strong style="color:var(--il-primary);"><?= $latestTest['score_obtained'] ?> / <?= $latestTest['total_marks'] ?> (<?= $latestTest['percentage'] ?>%)</strong>
                            <?php else: ?>
                                Unit Tests, Pen-Paper Tests & Term Exams evaluated by your teachers appear here and on your official report card.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- EXAM QUICK ACTIONS & PROGRESS -->
                <div style="min-width:240px; flex:1; max-width:360px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:13px; font-weight:700; margin-bottom:6px;">
                        <span>Academic Standing</span>
                        <span style="color:#2563EB; font-weight:800;"><?= $examSummary['total_tests'] ?> Tests Logged</span>
                    </div>
                    <div style="background:var(--surface-2); border-radius:999px; height:9px; overflow:hidden; border:1px solid var(--il-border);">
                        <div style="background:linear-gradient(90deg, #2563EB, #10B981); height:100%; width:<?= min(100, (int)$examSummary['average_pct']) ?>%; border-radius:999px; transition:width 0.5s ease;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:11.5px; color:var(--il-muted); margin-top:6px;">
                        <span>Avg: <?= $examSummary['average_pct'] ?>%</span>
                        <a href="/student/report-card" style="color:var(--il-primary); font-weight:700; text-decoration:underline;">View Report Card →</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ STAT TILES ============ -->
        <div class="grid grid-5 mt-3" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));">
            <div class="stat">
                <span class="ico">🏫</span>
                <span class="n"><?= count($batches) ?></span>
                <span class="l">Batches</span>
            </div>
            <div class="stat" style="border-left-color:#16A34A">
                <span class="ico">✅</span>
                <span class="n"><?= $presentPct ?>%</span>
                <span class="l">Attendance</span>
            </div>
            <div class="stat" style="border-left-color:#2563EB">
                <span class="ico">📚</span>
                <span class="n"><?= count($lessons) ?></span>
                <span class="l">Lessons</span>
            </div>
            <div class="stat" style="border-left-color:#6366f1">
                <span class="ico">💬</span>
                <span class="n"><?= (int)$doubtStats['total'] ?></span>
                <span class="l">Doubts (<?= (int)$doubtStats['answered'] ?> ans)</span>
            </div>
            <div class="stat" style="border-left-color:#7C2D5C">
                <span class="ico">🏆</span>
                <span class="n"><?= count($upcoming) ?></span>
                <span class="l">Events</span>
            </div>
        </div>

        <!-- ============ RISING ROOKS CHESS WIDGET ============ -->
        <div class="card mt-3" style="background:linear-gradient(135deg, #0A192F 0%, #122A5A 100%); color:#fff; border:none; padding:20px 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <img src="/assets/img/chess/chess_logo.jpg" alt="Rising Rooks" style="width:60px; height:60px; border-radius:14px; object-fit:cover; border:2px solid #BE9420;">
                    <div>
                        <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:#FCD34D; font-weight:700;">Rising Rooks Chess Academy</div>
                        <h3 style="color:#fff; margin:2px 0 4px; font-size:18px;">
                            Tier: <?= ucfirst(Helper::e($chessProfile['tier'])) ?> • <?= (int)$chessProfile['current_rating'] ?> Elo
                        </h3>
                        <p style="color:#94A3B8; font-size:12px; margin:0;">
                            <?php if (!$chessProfile['calibration_completed']): ?>
                                🎯 Placement match <?= min(5, (int)$chessProfile['calibration_matches_played'] + 1) ?> of 5 waiting!
                            <?php elseif ($chessProfile['in_promotion_battle']): ?>
                                ⚔️ Promotion Battle in progress (<?= (int)$chessProfile['promotion_matches_played'] ?>/3 matches played)
                            <?php else: ?>
                                <?= (int)$chessProfile['wins'] ?> wins • <?= (int)$chessProfile['games_played'] ?> matches played
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div style="display:flex; gap:10px;">
                    <a href="/chess/play" class="btn btn-sm btn-primary" style="background:#FF7A18; border-color:#FF7A18; font-weight:700;">
                        Play Chess ♟️
                    </a>
                    <a href="/chess" class="btn btn-sm btn-outline-light" style="border-color:rgba(255,255,255,0.4); color:#fff;">
                        Open Hub →
                    </a>
                </div>
            </div>
        </div>

        <!-- ============ NEXT UP ASSIGNMENTS ============ -->
        <?php if ($upcomingAssignments): ?>
            <div class="card mt-3">
                <div class="flex between">
                    <h3>🎯 Next up</h3>
                    <span class="pill"><?= count($upcomingAssignments) ?> upcoming</span>
                </div>
                <p class="muted" style="font-size:.88rem">Activities your teacher scheduled for your batch.</p>
                <div class="divider"></div>

                <?php foreach ($upcomingAssignments as $a):
                    $cover = $a['activity_cover'] ?: '/assets/img/placeholder-activity.png'; ?>
                    <div class="flex" style="gap:1rem;padding:.85rem 0;border-bottom:1px dashed var(--il-border);align-items:flex-start">
                        <img src="<?= Helper::e($cover) ?>" alt=""
                             style="width:64px;height:64px;border-radius:10px;object-fit:cover;flex-shrink:0">
                        <div style="flex:1">
                            <div class="flex" style="gap:.5rem;align-items:center;flex-wrap:wrap">
                                <strong><?= Helper::e($a['activity_title']) ?></strong>
                                <span class="badge badge-primary"><?= Helper::e(ucfirst($a['activity_category'])) ?></span>
                            </div>
                            <div class="muted" style="font-size:.85rem;margin-top:.15rem">
                                <?php if ($a['scheduled_date']): ?>
                                    📅 <?= date('d M Y', strtotime($a['scheduled_date'])) ?>
                                    <?php if ($a['scheduled_time']): ?> · <?= substr($a['scheduled_time'], 0, 5) ?><?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($a['instructions'])): ?>
                                <div class="muted" style="font-size:.85rem;margin-top:.35rem;padding:.5rem .65rem;background:var(--surface-2);border-radius:8px">
                                    📝 <?= Helper::e($a['instructions']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="/activity?slug=<?= urlencode($a['activity_slug']) ?>" class="btn btn-ghost btn-sm">Open</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ============ ANNOUNCEMENTS ============ -->
        <?php if ($ann): ?>
            <div class="card mt-3">
                <h3>📣 Announcements</h3>
                <div class="divider"></div>
                <?php foreach ($ann as $a): ?>
                    <div class="kv">
                        <div>
                            <div class="flex" style="gap:.4rem;align-items:center;flex-wrap:wrap">
                                <strong><?= Helper::e($a['title']) ?></strong>
                                <?php if (!empty($a['school_name'])): ?>
                                    <span class="badge badge-brand"><?= Helper::e($a['school_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-info">Global</span>
                                <?php endif; ?>
                            </div>
                            <div class="muted" style="font-size:.85rem"><?= Helper::e($a['body']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ============ TWO COLUMNS ============ -->
        <div class="grid grid-2 mt-3">
            <div class="card">
                <div class="flex between" style="align-items:center;">
                    <h3 style="margin:0;">👤 Your Profile</h3>
                    <div style="display:flex; gap:6px;">
                        <a href="/student/doubts" class="btn btn-outline-primary btn-sm" style="font-size:11px; padding:3px 8px; font-weight:600;">💬 Doubt Portal</a>
                        <a href="/student?id=<?= (int)$user['id'] ?>" class="btn btn-ghost btn-sm" style="font-size:11px; padding:3px 8px;">Full profile →</a>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="kv"><span>Email</span><span><?= Helper::e($user['email']) ?></span></div>
                <div class="kv"><span>Phone</span><span><?= Helper::e(Helper::displayPhone($user['phone'])) ?></span></div>
                <div class="kv"><span>Class</span><span><?= Helper::e(!empty($student['current_class']) ? $student['current_class'] : '—') ?></span></div>
                <div class="kv"><span>Board</span><span><?= Helper::e(!empty($student['school_board']) ? $student['school_board'] : '—') ?></span></div>
                <div class="kv"><span>Academic year</span><span><?= Helper::e(!empty($student['academic_year']) ? $student['academic_year'] : '—') ?></span></div>
                <div class="kv"><span>Interests</span><span><?= Helper::e(!empty($student['interests']) ? $student['interests'] : '—') ?></span></div>
            </div>

            <div class="card">
                <h3>👨‍👩‍👧 Parent / Guardian</h3>
                <div class="divider"></div>
                <?php if (!$parent): ?>
                    <p class="muted">No parent info on file. <a href="/profile">Add it here</a>.</p>
                <?php else: ?>
                    <div class="kv"><span>Name</span><span><?= Helper::e($parent['full_name']) ?></span></div>
                    <div class="kv"><span>Relationship</span><span><?= Helper::e(ucfirst($parent['relationship'])) ?></span></div>
                    <div class="kv"><span>Phone</span><span><?= Helper::e(Helper::displayPhone($parent['phone'])) ?></span></div>
                    <div class="kv"><span>Email</span><span><?= Helper::e($parent['email'] ?: '—') ?></span></div>
                    <div class="kv"><span>Occupation</span><span><?= Helper::e($parent['occupation'] ?: '—') ?></span></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============ ATTENDANCE BAR GRAPH ============ -->
        <div class="card mt-3" style="border-radius:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
                        <span>📊</span> Attendance Graph
                    </h3>
                    <p class="muted" style="margin:2px 0 0; font-size:12.5px;">Visual breakdown of recorded attendance status across all enrolled batches.</p>
                </div>
                <div style="display:flex; gap:14px; font-size:12px; flex-wrap:wrap;">
                    <span style="display:inline-flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:#16A34A;"></span> Present (<?= $presentPct ?>%)</span>
                    <span style="display:inline-flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:#DC2626;"></span> Absent (<?= $absentPct ?>%)</span>
                    <span style="display:inline-flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:#F59E0B;"></span> Late (<?= $latePct ?>%)</span>
                    <span style="display:inline-flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:#2563EB;"></span> Excused (<?= $excusedPct ?>%)</span>
                </div>
            </div>
            <div class="divider"></div>

            <?php if ($totalAtt === 1 && array_sum($att) === 0): ?>
                <p class="muted" style="text-align:center; padding:20px 0;">No attendance records logged yet by your teachers.</p>
            <?php else: 
                $barItems = [
                    ['label' => 'Present', 'count' => (int)$att['present'], 'pct' => $presentPct, 'color' => '#16A34A', 'gradient' => 'linear-gradient(180deg, #22C55E 0%, #16A34A 100%)', 'icon' => '✅'],
                    ['label' => 'Absent',  'count' => (int)$att['absent'],  'pct' => $absentPct,  'color' => '#DC2626', 'gradient' => 'linear-gradient(180deg, #EF4444 0%, #DC2626 100%)', 'icon' => '✕'],
                    ['label' => 'Late',    'count' => (int)$att['late'],    'pct' => $latePct,    'color' => '#F59E0B', 'gradient' => 'linear-gradient(180deg, #FBBF24 0%, #F59E0B 100%)', 'icon' => '⏱️'],
                    ['label' => 'Excused', 'count' => (int)$att['excused'], 'pct' => $excusedPct, 'color' => '#2563EB', 'gradient' => 'linear-gradient(180deg, #3B82F6 0%, #2563EB 100%)', 'icon' => 'ℹ️'],
                ];
            ?>
                <!-- Vertical Bar Chart Container -->
                <div style="position:relative; padding:20px 10px 10px 45px; background:var(--surface-1); border-radius:12px; border:1px solid var(--il-border);">
                    <!-- Y-Axis Gridlines & Labels -->
                    <div style="position:absolute; left:12px; top:20px; bottom:40px; display:flex; flex-direction:column; justify-content:space-between; font-size:10px; color:var(--il-muted); font-family:'JetBrains Mono', monospace; text-align:right; width:26px;">
                        <span>100%</span>
                        <span>75%</span>
                        <span>50%</span>
                        <span>25%</span>
                        <span>0%</span>
                    </div>

                    <div style="position:absolute; left:45px; right:15px; top:20px; bottom:40px; display:flex; flex-direction:column; justify-content:space-between; pointer-events:none;">
                        <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                        <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                        <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                        <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                        <div style="border-bottom:1.5px solid var(--il-border); height:0;"></div>
                    </div>

                    <!-- Bars Area -->
                    <div style="display:flex; justify-content:space-around; align-items:flex-end; height:180px; position:relative; z-index:2;">
                        <?php foreach ($barItems as $item): 
                            $barH = $item['pct'] > 0 ? max(8, min(100, $item['pct'])) : 0;
                        ?>
                            <div style="display:flex; flex-direction:column; align-items:center; width:22%; max-width:120px; height:100%; justify-content:flex-end;">
                                <!-- Value Badge Above Bar -->
                                <div style="font-size:11px; font-weight:800; color:<?= $item['color'] ?>; margin-bottom:6px; white-space:nowrap;">
                                    <?= $item['pct'] ?>%
                                </div>
                                
                                <!-- Bar Pillar -->
                                <div style="width:100%; max-width:68px; height:<?= $barH ?>%; background:<?= $item['gradient'] ?>; border-radius:8px 8px 2px 2px; box-shadow:0 3px 8px <?= $item['color'] ?>33; min-height:<?= $item['pct'] > 0 ? '8px' : '3px' ?>; transition:height 0.6s cubic-bezier(0.4, 0, 0.2, 1); display:flex; align-items:flex-start; justify-content:center; padding-top:4px;">
                                </div>

                                <!-- Label Below Bar -->
                                <div style="margin-top:8px; text-align:center;">
                                    <div style="font-weight:700; font-size:12px; color:var(--il-text);">
                                        <?= $item['icon'] ?> <?= $item['label'] ?>
                                    </div>
                                    <div class="muted" style="font-size:11px;">
                                        <?= $item['count'] ?> <?= $item['count'] === 1 ? 'day' : 'days' ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============ BATCHES + LESSONS ============ -->
        <div class="grid grid-2 mt-3">
            <div class="card">
                <h3>🏫 Your Batches</h3>
                <div class="divider"></div>
                <?php if (!$batches): ?>
                    <p class="muted">No batches yet — ask your teacher to add you.</p>
                <?php else: foreach ($batches as $b): ?>
                    <div class="kv"><span><?= Helper::e($b['name']) ?></span><span class="pill"><?= Helper::e($b['subject'] ?: 'General') ?></span></div>
                <?php endforeach; endif; ?>
            </div>

            <div class="card">
                <div class="flex between">
                    <h3>📚 Continue Learning</h3>
                    <a href="/lessons" class="btn btn-ghost btn-sm">All lessons</a>
                </div>
                <div class="divider"></div>
                <?php if (!$lessons): ?>
                    <p class="muted">No lessons published yet.</p>
                <?php else: foreach ($lessons as $l): ?>
                    <a class="kv" href="/lesson?id=<?= (int)$l['id'] ?>" style="text-decoration:none;color:inherit">
                        <span><?= Helper::e($l['title']) ?></span>
                        <span class="pill"><?= Helper::e($l['subject_name']) ?></span>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- ============ UPCOMING ACTIVITIES ============ -->
        <?php if ($upcoming): ?>
            <div class="mt-3">
                <h3 class="mb-2">🏆 Upcoming Activities</h3>
                <div class="grid grid-3">
                    <?php foreach ($upcoming as $a):
                        $cover = $a['cover_image'] ?: '/assets/img/placeholder-activity.png'; ?>
                        <a class="activity-card" href="/activity?slug=<?= urlencode($a['slug']) ?>">
                            <img class="activity-cover" src="<?= Helper::e($cover) ?>" alt="">
                            <div class="activity-body">
                                <span class="badge badge-primary"><?= ucfirst($a['category']) ?></span>
                                <h3><?= Helper::e($a['title']) ?></h3>
                                <div class="activity-meta">
                                    <?php if ($a['event_date']): ?><span>📅 <?= date('d M', strtotime($a['event_date'])) ?></span><?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
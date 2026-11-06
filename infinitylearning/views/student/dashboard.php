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

$att = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'leave' => 0];
try {
    $att = Attendance::summaryForStudent((int)$user['id']) ?: $att;
} catch (\Throwable $e) {}

$recentAttendance = [];
try {
    $recentAttendance = Attendance::recentForStudent((int)$user['id'], 14);
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

        <!-- ============ TWO COLUMNS: USER DETAILS & STUDENT REPORTS ============ -->
        <div class="grid grid-2 mt-3">
            <!-- 1. USER DETAILS (ESSENTIAL REGISTRATION INFO) -->
            <div class="card" style="border-radius:14px; padding:22px;">
                <div class="flex between" style="align-items:center;">
                    <h3 style="margin:0; font-size:17px; display:flex; align-items:center; gap:8px;">
                        <span>👤</span> Student Details
                    </h3>
                    <span class="badge" style="background:rgba(37,99,235,0.1); color:#2563EB; font-weight:800; font-size:12px;">
                        Reg. ID: <?= Helper::e(!empty($student['registration_number']) ? $student['registration_number'] : ('REG-' . (int)$user['id'])) ?>
                    </span>
                </div>
                <div class="divider"></div>
                <div class="kv"><span>Full Name</span><strong><?= Helper::e($student['full_name'] ?? 'Scholar') ?></strong></div>
                <div class="kv"><span>Registration ID</span><code style="font-weight:700; color:var(--il-primary);"><?= Helper::e(!empty($student['registration_number']) ? $student['registration_number'] : ('REG-' . (int)$user['id'])) ?></code></div>
                <div class="kv"><span>Current Class</span><span><?= Helper::e(!empty($student['current_class']) ? $student['current_class'] : '—') ?></span></div>
                <?php if (!empty($student['roll_number'])): ?>
                    <div class="kv"><span>Roll Number</span><span><?= Helper::e($student['roll_number']) ?></span></div>
                <?php endif; ?>
                <div class="kv"><span>Institution / School</span><span><?= Helper::e(!empty($student['school_name']) ? $student['school_name'] : 'Infinity Learning') ?></span></div>
                <div class="kv"><span>Registered Email</span><span><?= Helper::e($user['email']) ?></span></div>
                <div class="kv"><span>Registered Phone</span><span><?= Helper::e(Helper::displayPhone($user['phone'])) ?></span></div>
            </div>

            <!-- 2. STUDENT REPORTS SECTION (REPLACES PARENT CARD) -->
            <div class="card" style="border-radius:14px; padding:22px; border:1px solid var(--il-border);">
                <div class="flex between" style="align-items:center;">
                    <h3 style="margin:0; font-size:17px; display:flex; align-items:center; gap:8px;">
                        <span>📄</span> Student Reports &amp; Records
                    </h3>
                    <span class="badge badge-success" style="font-size:11px;">Verified Scholastic Record</span>
                </div>
                <div class="divider"></div>
                
                <p class="muted" style="font-size:13px; line-height:1.5; margin-bottom:14px;">
                    Review your official academic transcripts, attendance summary registers, and evaluation cards.
                </p>

                <div style="display:flex; flex-direction:column; gap:10px;">
                    <a href="/student/report-card" class="btn btn-primary btn-block" style="font-weight:700; padding:10px 14px; display:flex; justify-content:space-between; align-items:center;">
                        <span>📄 Official Academic Report Card</span>
                        <span>Open →</span>
                    </a>
                    <a href="/student/assessments" class="btn btn-secondary btn-block" style="font-weight:600; padding:10px 14px; display:flex; justify-content:space-between; align-items:center;">
                        <span>📝 Exam &amp; Unit Test Evaluations</span>
                        <span>View Results →</span>
                    </a>
                    <a href="/student/timetable" class="btn btn-outline-secondary btn-block" style="font-weight:600; padding:10px 14px; display:flex; justify-content:space-between; align-items:center;">
                        <span>🗓️ Class Routine &amp; Attendance Schedule</span>
                        <span>Check →</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- ============ ATTENDANCE & TIMELINE GRAPH ============ -->
        <?php 
        $totalRecorded = array_sum($att);
        $avgAttendanceRate = $totalRecorded > 0 ? (int)round((($att['present'] ?? 0) + ($att['late'] ?? 0)) / $totalRecorded * 100) : $presentPct;
        ?>
        <div class="card mt-3" style="border-radius:16px; padding:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div>
                    <h3 style="margin:0; font-size:18px; display:flex; align-items:center; gap:8px;">
                        <span>📊</span> Daily Attendance Graph &amp; Timeline
                    </h3>
                    <p class="muted" style="margin:3px 0 0; font-size:13px;">Daily attendance logs and clean comparative attendance ratio.</p>
                </div>
                
                <!-- Average Attendance Percentage Badge -->
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="background:rgba(22,163,74,0.1); border:1.5px solid #16A34A; border-radius:10px; padding:6px 14px; text-align:right;">
                        <span style="font-size:11px; text-transform:uppercase; font-weight:800; color:#16A34A; display:block;">Average Attendance</span>
                        <strong style="font-size:20px; color:#16A34A;"><?= $avgAttendanceRate ?>%</strong>
                    </div>
                </div>
            </div>
            <div class="divider"></div>

            <!-- DAILY ATTENDANCE TIMELINE VIEW -->
            <div style="margin-bottom:20px;">
                <div style="font-size:13px; font-weight:700; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;">
                    <span>📅 Daily Attendance Timeline (Recent Days)</span>
                    <span class="muted" style="font-size:11.5px;">🟢 Present · 🔴 Absent · 🟡 Late · 🔵 Excused · 🟣 Leave</span>
                </div>
                <?php if (empty($recentAttendance)): ?>
                    <div style="padding:14px; background:var(--surface-1); border-radius:10px; text-align:center; font-size:12.5px; color:var(--il-muted); border:1px dashed var(--il-border);">
                        Daily attendance timeline will automatically populate as your teachers record daily class attendance.
                    </div>
                <?php else: ?>
                    <div style="display:flex; gap:8px; overflow-x:auto; padding-bottom:6px;">
                        <?php foreach (array_reverse($recentAttendance) as $rAtt): 
                            $st = $rAtt['status'] ?? 'present';
                            $dotColor = match($st) {
                                'present' => '#16A34A',
                                'absent'  => '#DC2626',
                                'late'    => '#F59E0B',
                                'excused' => '#2563EB',
                                'leave'   => '#8B5CF6',
                                default   => '#94A3B8'
                            };
                            $badgeLabel = ucfirst($st);
                        ?>
                            <div style="background:var(--surface-1); border:1px solid var(--il-border); border-radius:10px; padding:8px 10px; min-width:85px; text-align:center; flex-shrink:0;">
                                <div style="font-size:10.5px; color:var(--il-muted); margin-bottom:4px; font-weight:600;">
                                    <?= date('d M', strtotime($rAtt['attendance_date'])) ?>
                                </div>
                                <div style="width:12px; height:12px; border-radius:50%; background:<?= $dotColor ?>; margin:0 auto 4px;"></div>
                                <div style="font-size:10px; font-weight:800; color:<?= $dotColor ?>;">
                                    <?= $badgeLabel ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- CLEAN ATTENDANCE GRAPH (LATE, EXCUSED, LEAVE REMOVED FROM PILLARS) -->
            <?php if ($totalAtt === 1 && array_sum($att) === 0): ?>
                <p class="muted" style="text-align:center; padding:20px 0;">No attendance records logged yet by your teachers.</p>
            <?php else: 
                // Clean graph: only Present vs Absent
                $cleanBarItems = [
                    ['label' => 'Present', 'count' => (int)$att['present'], 'pct' => $presentPct, 'color' => '#16A34A', 'gradient' => 'linear-gradient(180deg, #22C55E 0%, #16A34A 100%)', 'icon' => '✅'],
                    ['label' => 'Absent',  'count' => (int)$att['absent'],  'pct' => $absentPct,  'color' => '#DC2626', 'gradient' => 'linear-gradient(180deg, #EF4444 0%, #DC2626 100%)', 'icon' => '✕'],
                ];
            ?>
                <!-- Vertical Bar Chart Container (Clean Layout) -->
                <div style="background:var(--surface-1); border-radius:14px; border:1px solid var(--il-border); padding:20px 20px 16px 16px; margin-bottom:20px;">
                    <!-- Chart Area: Y-Axis Rail + Plot Area -->
                    <div style="display:flex; height:180px; position:relative; max-width:540px; margin:0 auto;">
                        <!-- Y-Axis Rail -->
                        <div style="width:48px; display:flex; flex-direction:column; justify-content:space-between; text-align:right; padding-right:12px; font-size:11px; font-weight:600; color:var(--il-muted); font-family:var(--font-mono, monospace); user-select:none;">
                            <span style="transform:translateY(-50%);">100%</span>
                            <span style="transform:translateY(-50%);">75%</span>
                            <span style="transform:translateY(-50%);">50%</span>
                            <span style="transform:translateY(-50%);">25%</span>
                            <span style="transform:translateY(50%);">0%</span>
                        </div>

                        <!-- Plot Area (Gridlines + Bars) -->
                        <div style="position:relative; flex:1; height:100%;">
                            <!-- Horizontal Gridlines -->
                            <div style="position:absolute; inset:0; display:flex; flex-direction:column; justify-content:space-between; pointer-events:none;">
                                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                                <div style="border-bottom:2px solid var(--il-border); height:0;"></div>
                            </div>

                            <!-- Bars Area -->
                            <div style="position:absolute; inset:0; display:flex; justify-content:space-around; align-items:flex-end; z-index:2;">
                                <?php foreach ($cleanBarItems as $item): 
                                    $barH = $item['pct'] > 0 ? max(4, min(100, $item['pct'])) : 0;
                                ?>
                                    <div style="display:flex; flex-direction:column; align-items:center; width:36%; max-width:140px; height:100%; justify-content:flex-end;">
                                        <!-- Value Badge Above Bar -->
                                        <div style="font-size:12px; font-weight:800; color:<?= $item['color'] ?>; margin-bottom:6px; white-space:nowrap; background:<?= $item['color'] ?>15; padding:2px 8px; border-radius:999px;">
                                            <?= $item['pct'] ?>%
                                        </div>
                                        
                                        <!-- Bar Pillar -->
                                        <div style="width:100%; max-width:80px; height:<?= $barH ?>%; background:<?= $item['gradient'] ?>; border-radius:8px 8px 2px 2px; box-shadow:0 4px 12px <?= $item['color'] ?>33; min-height:<?= $item['pct'] > 0 ? '6px' : '3px' ?>; transition:height 0.6s cubic-bezier(0.4, 0, 0.2, 1);">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- X-Axis Labels (Aligned below bars, offset by Y-Axis width 48px) -->
                    <div style="display:flex; margin-left:48px; justify-content:space-around; margin-top:12px; padding-top:6px; max-width:540px; margin:12px auto 0;">
                        <?php foreach ($cleanBarItems as $item): ?>
                            <div style="width:36%; max-width:140px; text-align:center;">
                                <div style="font-weight:700; font-size:13px; color:var(--il-text);">
                                    <?= $item['icon'] ?> <?= $item['label'] ?>
                                </div>
                                <div class="muted" style="font-size:11.5px; margin-top:2px;">
                                    <?= $item['count'] ?> <?= $item['count'] === 1 ? 'day' : 'days' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ATTENDANCE-RELATED INFORMATION BAR (LATE ARRIVALS, EXCUSED, LEAVE) -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-top:16px;">
                    <div style="background:var(--surface-1); border:1px solid var(--il-border); border-left:3.5px solid #F59E0B; border-radius:10px; padding:12px 14px;">
                        <span class="muted" style="font-size:11.5px; font-weight:700; text-transform:uppercase;">⏱️ Late Arrivals</span>
                        <div style="font-size:18px; font-weight:800; color:#F59E0B; margin-top:2px;">
                            <?= (int)($att['late'] ?? 0) ?> <span style="font-size:12px; font-weight:600; color:var(--il-muted);"><?= (int)($att['late'] ?? 0) === 1 ? 'day' : 'days' ?></span>
                        </div>
                    </div>
                    <div style="background:var(--surface-1); border:1px solid var(--il-border); border-left:3.5px solid #2563EB; border-radius:10px; padding:12px 14px;">
                        <span class="muted" style="font-size:11.5px; font-weight:700; text-transform:uppercase;">ℹ️ Excused Absences</span>
                        <div style="font-size:18px; font-weight:800; color:#2563EB; margin-top:2px;">
                            <?= (int)($att['excused'] ?? 0) ?> <span style="font-size:12px; font-weight:600; color:var(--il-muted);"><?= (int)($att['excused'] ?? 0) === 1 ? 'day' : 'days' ?></span>
                        </div>
                    </div>
                    <div style="background:var(--surface-1); border:1px solid var(--il-border); border-left:3.5px solid #8B5CF6; border-radius:10px; padding:12px 14px;">
                        <span class="muted" style="font-size:11.5px; font-weight:700; text-transform:uppercase;">🏖️ Leave</span>
                        <div style="font-size:18px; font-weight:800; color:#8B5CF6; margin-top:2px;">
                            <?= (int)($att['leave'] ?? 0) ?> <span style="font-size:12px; font-weight:600; color:var(--il-muted);"><?= (int)($att['leave'] ?? 0) === 1 ? 'day' : 'days' ?></span>
                        </div>
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
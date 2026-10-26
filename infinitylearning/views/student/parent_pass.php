<?php
declare(strict_types=1);

use App\{Auth, Database, Helper, StudentProfile, Timetable, FeeManagement, AcademicAssessment};

$currentUser = Auth::user();

// Determine which student we are viewing
$studentUserId = 0;
if (!empty($_GET['student_id'])) {
    $reqId = (int)$_GET['student_id'];
    // Allow teacher, admin, school to inspect any student, or parent of student
    if (in_array($currentUser['role'] ?? '', ['admin', 'school', 'teacher'], true)) {
        $studentUserId = $reqId;
    } elseif (($currentUser['role'] ?? '') === 'parent') {
        // verify parent relationship
        $db = Database::connect();
        $chk = $db->prepare("SELECT 1 FROM student_parents sp JOIN parents p ON p.id=sp.parent_id WHERE p.user_id = ? AND sp.student_user_id = ?");
        $chk->execute([(int)$currentUser['id'], $reqId]);
        if ($chk->fetch()) {
            $studentUserId = $reqId;
        }
    }
}

if (!$studentUserId && ($currentUser['role'] ?? '') === 'student') {
    $studentUserId = (int)$currentUser['id'];
}

if (!$studentUserId) {
    // If user is a parent without student_id query, find their first child
    if (($currentUser['role'] ?? '') === 'parent') {
        $db = Database::connect();
        $chk = $db->prepare("SELECT sp.student_user_id FROM student_parents sp JOIN parents p ON p.id=sp.parent_id WHERE p.user_id = ? LIMIT 1");
        $chk->execute([(int)$currentUser['id']]);
        $studentUserId = (int)$chk->fetchColumn();
    }
}

if (!$studentUserId && in_array($currentUser['role'] ?? '', ['admin', 'school', 'teacher'], true)) {
    try {
        $db = Database::connect();
        $firstSt = $db->query("SELECT user_id FROM students ORDER BY id ASC LIMIT 1")->fetchColumn();
        if ($firstSt) {
            $studentUserId = (int)$firstSt;
        }
    } catch (\Throwable $e) {}
}

if (!$studentUserId) {
    Helper::redirect('/dashboard');
}

$db = Database::connect();
$student = null;
try {
    $stmt = $db->prepare("
        SELECT u.id as user_id, u.email, u.phone, s.full_name,
               s.current_class, s.school_name, s.school_board, s.profile_image as avatar,
               sch.id as school_id, sch.name as official_school_name,
               COALESCE(sch.crest_logo, sch.logo) as school_logo, sch.phone as school_phone,
               sch.contact_email as school_email,
               sch.brand_primary_color as primary_color
        FROM users u
        JOIN students s ON s.user_id = u.id
        LEFT JOIN schools sch ON sch.id = u.school_id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$studentUserId]);
    $student = $stmt->fetch();
} catch (\Throwable $e) {
    error_log('Parent pass student query error: ' . $e->getMessage());
}

if (!$student) {
    http_response_code(404);
    echo "Student record not found.";
    exit;
}

$schoolId = !empty($student['school_id']) ? (int)$student['school_id'] : 0;
$gamification = [
    'current_streak' => 0,
    'longest_streak' => 0,
    'active_days'    => 0,
    'has_today'      => false,
    'total_xp'       => 0,
    'level'          => 1,
    'cur_level_base' => 0,
    'next_level_xp'  => 100,
    'level_progress' => 0,
    'rank_title'     => 'Novice'
];
try {
    $gamification = StudentProfile::calculateStreakAndXP($studentUserId);
} catch (\Throwable $e) {}

// Complete Student Profile Data (Attendance, Exam categories)
$stdProfile = null;
try {
    $stdProfile = StudentProfile::build($studentUserId);
} catch (\Throwable $e) {
    error_log('Parent pass StudentProfile::build error: ' . $e->getMessage());
}

// Attendance breakdown
$attCounts = $stdProfile['attendance']['raw'] ?? ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
$totalAttDays = max(0, (int)($stdProfile['attendance']['total'] ?? array_sum($attCounts)));
$attPercentage = (int)($stdProfile['attendance']['present_pct'] ?? ($totalAttDays > 0 ? round(($attCounts['present'] / $totalAttDays) * 100) : 100));

// Exam categories and tests
$examCats = $stdProfile['exams']['categories'] ?? [];
$recentTests = $stdProfile['exams']['all_tests'] ?? [];
if (empty($recentTests)) {
    try {
        $testsStmt = $db->prepare("
            SELECT sub.score_obtained as marks_obtained, sub.submitted_at, sub.teacher_feedback as feedback,
                   a.title, a.assessment_type, a.total_marks, sub.status,
                   sub.id as submission_id
            FROM assessment_submissions sub
            JOIN academic_assessments a ON a.id = sub.assessment_id
            WHERE sub.student_user_id = ?
            ORDER BY sub.submitted_at DESC
            LIMIT 5
        ");
        $testsStmt->execute([$studentUserId]);
        $recentTests = $testsStmt->fetchAll();
    } catch (\Throwable $e) {}
}

// Monthly Fee Status
$fees = [];
try {
    $fees = FeeManagement::list([
        'student_user_id' => $studentUserId,
    ]);
} catch (\Throwable $e) {}
$latestFee = $fees[0] ?? null;

$pageTitle = 'Parent Progress Pass — ' . ($student['full_name'] ?? 'Student');
require VIEW_PATH . '/layouts/header.php';
?>

<div class="pass-wrapper" style="max-width:760px; margin:20px auto 40px; padding:0 12px;">
    <!-- TOP TOOLBAR -->
    <div class="no-print" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
        <a href="javascript:history.back()" class="btn btn-secondary btn-sm">← Back</a>
        <div style="display:flex; gap:8px;">
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm">🖨️ Print Pass</button>
            <a href="https://api.whatsapp.com/send?text=Check%20out%20<?= urlencode($student['full_name']) ?>'s%20Student%20Progress%20Pass%20on%20Infinity%20Learning!" target="_blank" class="btn btn-sm" style="background:#25D366; color:#fff; border:none; font-weight:700;">
                📲 Share Pass
            </a>
        </div>
    </div>

    <!-- MOBILE-FIRST PASS CARD -->
    <div class="card pass-card" style="padding:0; overflow:hidden; border-radius:18px; border:1px solid var(--il-border); box-shadow:0 14px 40px rgba(0,0,0,0.12); background:var(--surface-0);">
        <!-- HERO BRANDING BANNER -->
        <div style="background:linear-gradient(135deg, <?= !empty($student['primary_color']) ? htmlspecialchars($student['primary_color']) : '#4f46e5' ?>, #2563eb); color:#fff; padding:24px 20px; position:relative;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
                <div>
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.1em; opacity:0.85; font-weight:700;">
                        <?= Helper::e($student['official_school_name'] ?: $student['school_name'] ?: 'Infinity Learning Academy') ?>
                    </div>
                    <h1 style="font-size:22px; margin:4px 0 2px; color:#fff; font-weight:800;">Parent Progress Pass</h1>
                    <div style="font-size:12px; opacity:0.9;">Academic & Holistic Growth Snapshot</div>
                </div>
                <div style="text-align:right;">
                    <span class="badge" style="background:rgba(255,255,255,0.22); color:#fff; font-size:11px; padding:4px 10px; border-radius:999px;">
                        <?= date('F Y') ?>
                    </span>
                </div>
            </div>

            <!-- STUDENT PROFILE PILL -->
            <div style="display:flex; align-items:center; gap:14px; margin-top:18px; background:rgba(0,0,0,0.18); padding:12px 14px; border-radius:14px; backdrop-filter:blur(6px);">
                <div style="width:52px; height:52px; border-radius:50%; background:#fff; color:#4f46e5; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:800; border:2px solid rgba(255,255,255,0.8); flex-shrink:0;">
                    <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                </div>
                <div style="flex:1;">
                    <div style="font-size:17px; font-weight:800; color:#fff;"><?= Helper::e($student['full_name']) ?></div>
                    <div style="font-size:12px; color:rgba(255,255,255,0.88); margin-top:2px;">
                        <strong><?= Helper::e(!empty($student['current_class']) ? $student['current_class'] : 'Student') ?></strong>
                        <?php if (!empty($student['section'])): ?> · Sec <?= Helper::e($student['section']) ?><?php endif; ?>
                        <?php if (!empty($student['roll_number'])): ?> · Roll #<?= Helper::e($student['roll_number']) ?><?php endif; ?>
                        <?php if (!empty($student['admission_number'])): ?> · Adm: <?= Helper::e($student['admission_number']) ?><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div style="padding:20px;">
            <!-- GAMIFICATION & STREAK BAR -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:18px;">
                <div class="card" style="padding:14px; margin:0; border-radius:12px; background:var(--surface-1); border:1px solid var(--il-border); display:flex; align-items:center; gap:12px;">
                    <div style="font-size:32px;">🔥</div>
                    <div>
                        <div style="font-size:16px; font-weight:800; color:var(--il-text);">
                            <?= $gamification['current_streak'] ?> Day Streak
                        </div>
                        <div style="font-size:11px; color:var(--il-muted);">
                            <?= $gamification['has_today'] ? '✓ Active Today' : 'Daily Study Habit' ?>
                        </div>
                    </div>
                </div>

                <div class="card" style="padding:14px; margin:0; border-radius:12px; background:var(--surface-1); border:1px solid var(--il-border); display:flex; align-items:center; gap:12px;">
                    <div style="font-size:32px;">⚡</div>
                    <div>
                        <div style="font-size:16px; font-weight:800; color:var(--il-text);">
                            Level <?= $gamification['level'] ?>
                        </div>
                        <div style="font-size:11px; color:#f59e0b; font-weight:700;">
                            <?= $gamification['rank_title'] ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 1. ATTENDANCE GRAPH (VERTICAL BARS)
                 ========================================== -->
            <?php
            $presentW = $totalAttDays > 0 ? round((($attCounts['present'] ?? 0) / $totalAttDays) * 100, 1) : 0;
            $absentW  = $totalAttDays > 0 ? round((($attCounts['absent'] ?? 0)  / $totalAttDays) * 100, 1) : 0;
            $lateW    = $totalAttDays > 0 ? round((($attCounts['late'] ?? 0)    / $totalAttDays) * 100, 1) : 0;
            $excusedW = $totalAttDays > 0 ? round((($attCounts['excused'] ?? 0) / $totalAttDays) * 100, 1) : 0;

            $attBars = [
                ['label' => 'Present', 'count' => (int)($attCounts['present'] ?? 0), 'pct' => $presentW, 'color' => '#16A34A', 'gradient' => 'linear-gradient(180deg, #22C55E 0%, #16A34A 100%)', 'icon' => '✅'],
                ['label' => 'Absent',  'count' => (int)($attCounts['absent'] ?? 0),  'pct' => $absentW,  'color' => '#DC2626', 'gradient' => 'linear-gradient(180deg, #EF4444 0%, #DC2626 100%)', 'icon' => '✕'],
                ['label' => 'Late',    'count' => (int)($attCounts['late'] ?? 0),    'pct' => $lateW,    'color' => '#F59E0B', 'gradient' => 'linear-gradient(180deg, #FBBF24 0%, #F59E0B 100%)', 'icon' => '⏱️'],
                ['label' => 'Excused', 'count' => (int)($attCounts['excused'] ?? 0), 'pct' => $excusedW, 'color' => '#2563EB', 'gradient' => 'linear-gradient(180deg, #3B82F6 0%, #2563EB 100%)', 'icon' => 'ℹ️'],
            ];
            ?>
            <div class="card" style="padding:16px; margin:0 0 18px; border-radius:14px; background:var(--surface-1); border:1px solid var(--il-border);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <div>
                        <div style="font-size:14px; font-weight:800; color:var(--il-text); display:flex; align-items:center; gap:6px;">
                            <span>📊</span> Attendance Graph
                        </div>
                        <div style="font-size:11.5px; color:var(--il-muted); margin-top:2px;">
                            Total Recorded: <?= $totalAttDays ?> days
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:18px; font-weight:800; color:<?= $attPercentage >= 75 ? '#10b981' : '#ef4444' ?>;">
                            <?= $attPercentage ?>%
                        </span>
                        <div style="font-size:10.5px; color:var(--il-muted);">Present Rate</div>
                    </div>
                </div>

                <!-- Vertical Attendance Bars -->
                <div style="position:relative; padding:18px 10px 10px 42px; background:var(--surface-0); border-radius:10px; border:1px solid var(--il-border);">
                    <!-- Y-Axis Gridlines & Labels -->
                    <div style="position:absolute; left:8px; top:18px; bottom:38px; display:flex; flex-direction:column; justify-content:space-between; font-size:9px; color:var(--il-muted); font-family:monospace; text-align:right; width:26px;">
                        <span>100%</span>
                        <span>50%</span>
                        <span>0%</span>
                    </div>
                    <div style="position:absolute; left:42px; right:10px; top:18px; bottom:38px; display:flex; flex-direction:column; justify-content:space-between; pointer-events:none;">
                        <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                        <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                        <div style="border-bottom:1.5px solid var(--il-border); height:0;"></div>
                    </div>

                    <div style="display:flex; justify-content:space-around; align-items:flex-end; height:130px; position:relative; z-index:2;">
                        <?php foreach ($attBars as $item): 
                            $barH = $item['pct'] > 0 ? max(8, min(100, $item['pct'])) : 0;
                        ?>
                            <div style="display:flex; flex-direction:column; align-items:center; width:22%; max-width:80px; height:100%; justify-content:flex-end;">
                                <div style="font-size:10.5px; font-weight:800; color:<?= $item['color'] ?>; margin-bottom:4px; white-space:nowrap;">
                                    <?= $item['pct'] ?>%
                                </div>
                                <div style="width:100%; max-width:44px; height:<?= $barH ?>%; background:<?= $item['gradient'] ?>; border-radius:6px 6px 2px 2px; box-shadow:0 3px 8px <?= $item['color'] ?>33; min-height:<?= $item['pct'] > 0 ? '6px' : '3px' ?>; transition:height 0.5s ease;">
                                </div>
                                <div style="margin-top:8px; text-align:center;">
                                    <div style="font-weight:700; font-size:11px; color:var(--il-text);">
                                        <?= $item['label'] ?>
                                    </div>
                                    <div class="muted" style="font-size:10px;">
                                        <?= $item['count'] ?>d
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 2. MARKS TREND GRAPH (EXAMS & TESTS)
                 ========================================== -->
            <?php
            $examMeta = [
                'unit_test'  => ['label' => 'Unit Tests', 'code' => 'UT',  'color' => '#2563EB', 'gradient' => 'linear-gradient(180deg, #3B82F6 0%, #2563EB 100%)'],
                'pen_paper'  => ['label' => 'Pen & Paper', 'code' => 'PPT', 'color' => '#7C3AED', 'gradient' => 'linear-gradient(180deg, #8B5CF6 0%, #7C3AED 100%)'],
                'mid_term'   => ['label' => 'Mid Term',    'code' => 'MID', 'color' => '#059669', 'gradient' => 'linear-gradient(180deg, #10B981 0%, #059669 100%)'],
                'final_term' => ['label' => 'Final Term',  'code' => 'FIN', 'color' => '#D97706', 'gradient' => 'linear-gradient(180deg, #F59E0B 0%, #D97706 100%)'],
            ];
            ?>
            <div class="card" style="padding:16px; margin:0 0 18px; border-radius:14px; background:var(--surface-1); border:1px solid var(--il-border);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <div>
                        <div style="font-size:14px; font-weight:800; color:var(--il-text); display:flex; align-items:center; gap:6px;">
                            <span>📈</span> Marks Trend Graph
                        </div>
                        <div style="font-size:11.5px; color:var(--il-muted); margin-top:2px;">
                            Assessment &amp; Examination performance breakdown
                        </div>
                    </div>
                    <a href="/school/report-card?student_id=<?= $studentUserId ?>" style="font-size:11px; color:var(--primary); text-decoration:none; font-weight:700;">Full Report Card →</a>
                </div>

                <!-- Vertical Exam Trend Bars -->
                <div style="position:relative; padding:18px 10px 10px 42px; background:var(--surface-0); border-radius:10px; border:1px solid var(--il-border); margin-bottom:12px;">
                    <div style="position:absolute; left:8px; top:18px; bottom:38px; display:flex; flex-direction:column; justify-content:space-between; font-size:9px; color:var(--il-muted); font-family:monospace; text-align:right; width:26px;">
                        <span>100%</span>
                        <span>50%</span>
                        <span>0%</span>
                    </div>
                    <div style="position:absolute; left:42px; right:10px; top:18px; bottom:38px; display:flex; flex-direction:column; justify-content:space-between; pointer-events:none;">
                        <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                        <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                        <div style="border-bottom:1.5px solid var(--il-border); height:0;"></div>
                    </div>

                    <div style="display:flex; justify-content:space-around; align-items:flex-end; height:130px; position:relative; z-index:2;">
                        <?php foreach ($examMeta as $mKey => $mInfo): 
                            $mCat = $examCats[$mKey] ?? ['avg_pct' => 0, 'records' => []];
                            $mPct = (float)($mCat['avg_pct'] ?? 0);
                            $mCount = count($mCat['records'] ?? []);
                            $mBarH = $mPct > 0 ? max(8, min(100, $mPct)) : 0;
                        ?>
                            <div style="display:flex; flex-direction:column; align-items:center; width:22%; max-width:80px; height:100%; justify-content:flex-end;">
                                <div style="font-size:10.5px; font-weight:800; color:<?= $mInfo['color'] ?>; margin-bottom:4px; white-space:nowrap;">
                                    <?= $mPct ?>%
                                </div>
                                <div style="width:100%; max-width:44px; height:<?= $mBarH ?>%; background:<?= $mInfo['gradient'] ?>; border-radius:6px 6px 2px 2px; box-shadow:0 3px 8px <?= $mInfo['color'] ?>33; min-height:<?= $mPct > 0 ? '6px' : '3px' ?>; transition:height 0.5s ease;">
                                </div>
                                <div style="margin-top:8px; text-align:center;">
                                    <div style="font-weight:700; font-size:11px; color:var(--il-text);">
                                        <?= $mInfo['label'] ?>
                                    </div>
                                    <div class="muted" style="font-size:10px;">
                                        <?= $mCount ?> <?= $mCount === 1 ? 'test' : 'tests' ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Mini summary pills for exam types -->
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:6px;">
                    <?php foreach ($examMeta as $mKey => $mInfo): 
                        $mCat = $examCats[$mKey] ?? ['avg_pct' => 0, 'records' => []];
                        $mPct = (float)($mCat['avg_pct'] ?? 0);
                    ?>
                        <div style="background:var(--surface-2); border-radius:8px; padding:6px; text-align:center; border:1px solid var(--il-border);">
                            <div style="font-size:10px; color:var(--il-muted); font-weight:600;"><?= $mInfo['code'] ?></div>
                            <div style="font-size:12px; font-weight:800; color:<?= $mInfo['color'] ?>; margin-top:2px;">
                                <?= $mPct > 0 ? "{$mPct}%" : '—' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- RECENT ACADEMIC EXAMS & QUIZZES -->
            <div class="card" style="padding:16px; margin:0 0 18px; border-radius:12px; background:var(--surface-1); border:1px solid var(--il-border);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div style="font-size:13px; font-weight:700; color:var(--il-text);">📝 Recent Test & Exam Performance</div>
                    <a href="/school/report-card?student_id=<?= $studentUserId ?>" style="font-size:11px; color:var(--primary); text-decoration:none; font-weight:600;">Official Report Card →</a>
                </div>
                <?php if (!empty($recentTests)): ?>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <?php foreach ($recentTests as $t): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 10px; background:var(--surface-2); border-radius:8px; font-size:12px; border:1px solid var(--il-border);">
                                <div>
                                    <div style="font-weight:700; color:var(--il-text);"><?= Helper::e($t['title']) ?></div>
                                    <div class="muted" style="font-size:10px; margin-top:2px;">
                                        <?= strtoupper($t['assessment_type']) ?> · <?= date('d M Y', strtotime($t['submitted_at'])) ?>
                                    </div>
                                </div>
                                <div style="text-align:right;">
                                    <span style="font-weight:800; font-size:14px; color:var(--primary);">
                                        <?= (float)($t['marks_obtained'] ?? 0) ?> / <?= (float)$t['total_marks'] ?>
                                    </span>
                                    <?php
                                    $calculatedGrade = '—';
                                    if (!empty($t['total_marks']) && (float)$t['total_marks'] > 0) {
                                        $pct = ((float)($t['marks_obtained'] ?? 0) / (float)$t['total_marks']) * 100;
                                        if ($pct >= 90) $calculatedGrade = 'A+';
                                        elseif ($pct >= 75) $calculatedGrade = 'A';
                                        elseif ($pct >= 60) $calculatedGrade = 'B';
                                        elseif ($pct >= 40) $calculatedGrade = 'C';
                                        else $calculatedGrade = 'D';
                                    }
                                    ?>
                                    <span class="badge badge-success" style="font-size:10px; margin-left:4px;"><?= $calculatedGrade ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="font-size:12px; color:var(--il-muted); text-align:center; padding:10px 0;">
                        No graded assessments logged yet.
                    </div>
                <?php endif; ?>
            </div>

            <!-- MONTHLY FEE STATUS -->
            <div class="card" style="padding:16px; margin:0 0 18px; border-radius:12px; background:var(--surface-1); border:1px solid var(--il-border);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div style="font-size:13px; font-weight:700; color:var(--il-text);">💳 Monthly Fee Status</div>
                    <?php if ($latestFee): ?>
                        <a href="/student/fee-receipt?id=<?= (int)$latestFee['id'] ?>" target="_blank" style="font-size:11px; color:var(--primary); text-decoration:none; font-weight:600;">Latest Receipt →</a>
                    <?php endif; ?>
                </div>
                <?php if ($latestFee): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:var(--surface-2); border-radius:8px; font-size:13px; border:1px solid var(--il-border);">
                        <div>
                            <strong><?= Helper::e($latestFee['fee_month'] ?? '') ?> (AY <?= Helper::e($latestFee['academic_year'] ?? '') ?>)</strong>
                            <div class="muted" style="font-size:11px; margin-top:2px;">
                                Total Due: ₹<?= number_format((float)($latestFee['amount_due'] ?? 0), 2) ?>
                                <?php if (!empty($latestFee['amount_paid']) && (float)$latestFee['amount_paid'] > 0): ?>
                                    · Paid: ₹<?= number_format((float)$latestFee['amount_paid'], 2) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <?php $feeStatus = $latestFee['status'] ?? 'pending'; ?>
                            <?php if ($feeStatus === 'paid'): ?>
                                <span class="badge badge-success" style="font-size:12px; padding:4px 10px;">✓ Paid</span>
                            <?php elseif ($feeStatus === 'partial'): ?>
                                <span class="badge badge-warning" style="font-size:12px; padding:4px 10px;">Partial</span>
                            <?php else: ?>
                                <span class="badge badge-danger" style="font-size:12px; padding:4px 10px;">Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="font-size:12px; color:var(--il-muted); text-align:center; padding:10px 0;">
                        No fee invoice recorded.
                    </div>
                <?php endif; ?>
            </div>

            <!-- SCHOOL CONTACT BAR -->
            <?php if (!empty($student['school_phone']) || !empty($student['school_email'])): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:var(--surface-2); border-radius:10px; font-size:12px; border:1px solid var(--il-border);">
                    <div>
                        <strong style="color:var(--il-text);">Need assistance?</strong>
                        <div class="muted" style="font-size:11px;">Connect with school administration</div>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <?php if (!empty($student['school_phone'])): ?>
                            <a href="tel:<?= htmlspecialchars($student['school_phone']) ?>" class="btn btn-secondary btn-sm" style="font-size:11px; padding:4px 10px;">📞 Call</a>
                        <?php endif; ?>
                        <?php if (!empty($student['school_email'])): ?>
                            <a href="mailto:<?= htmlspecialchars($student['school_email']) ?>" class="btn btn-secondary btn-sm" style="font-size:11px; padding:4px 10px;">✉️ Email</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, .navbar, .admin-side, footer { display: none !important; }
    body { background: #fff !important; }
    .pass-wrapper { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
    .pass-card { box-shadow: none !important; border: 1px solid #ccc !important; }
}
</style>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

<?php
use App\Auth;
use App\Helper;
use App\StudentProfile;
use App\Database;
use App\Attendance;
use App\AcademicAssessment;
use App\Batch;
use App\Timetable;
use App\DoubtFeed;

Auth::requireLogin();

$viewer      = Auth::user();
$viewerRole  = Auth::role();
$studentId   = (int)($_GET['id'] ?? 0);
if (!$studentId) { 
    if ($viewerRole === 'student') {
        $studentId = (int)$viewer['id'];
    } else {
        Helper::redirect('/dashboard'); 
    }
}

// Access control
$canView = false;
if (in_array($viewerRole, ['admin', 'school_admin', 'school'], true)) {
    $canView = true;
} elseif ($viewerRole === 'student') {
    if ((int)$viewer['id'] === $studentId) {
        $canView = true;
    } else {
        try {
            $db = Database::connect();
            $chk = $db->prepare("SELECT user_id FROM students WHERE (id = ? OR user_id = ?) AND user_id = ? LIMIT 1");
            $chk->execute([$studentId, $studentId, (int)$viewer['id']]);
            if ($chk->fetch()) {
                $canView = true;
                $studentId = (int)$viewer['id'];
            }
        } catch (\Throwable $e) {}
    }
} elseif ($viewerRole === 'parent') {
    // Parents can view their registered children
    try {
        $db = Database::connect();
        $q = $db->prepare("
            SELECT sp.student_user_id FROM student_parents sp
            JOIN parents p ON p.id = sp.parent_id
            WHERE p.user_id = ? AND (
                sp.student_user_id = ? 
                OR sp.student_user_id = (SELECT user_id FROM students WHERE id = ? LIMIT 1)
            )
            LIMIT 1
        ");
        $q->execute([(int)$viewer['id'], $studentId, $studentId]);
        if ($row = $q->fetch()) {
            $canView = true;
            $studentId = (int)$row['student_user_id'];
        }
    } catch (\Throwable $e) {}
} elseif ($viewerRole === 'teacher') {
    // Teachers can view students in their school, their batches, or their mentees
    try {
        $db = Database::connect();
        $q = $db->prepare("
            SELECT u.id FROM users u
            LEFT JOIN students s ON s.user_id = u.id
            WHERE (u.id = ? OR s.id = ?) AND (
                u.school_id = (SELECT school_id FROM users WHERE id = ?)
                OR EXISTS (SELECT 1 FROM students st WHERE st.user_id = u.id AND st.mentor_user_id = ?)
                OR EXISTS (
                    SELECT 1 FROM batch_students bs
                    JOIN batches b ON b.id = bs.batch_id
                    WHERE bs.student_user_id = u.id AND b.teacher_id = ?
                )
            )
            LIMIT 1
        ");
        $q->execute([$studentId, $studentId, (int)$viewer['id'], (int)$viewer['id'], (int)$viewer['id']]);
        if ($row = $q->fetch()) {
            $canView = true;
            $studentId = (int)$row['id'];
        }
    } catch (\Throwable $e) {}
}

if (!$canView) {
    http_response_code(403);
    exit('You do not have access to this profile.');
}

$p = null;
try {
    $p = StudentProfile::build($studentId);
} catch (\Throwable $e) {
    error_log('StudentProfile::build error: ' . $e->getMessage());
}

if (!$p) { 
    http_response_code(404); 
    require VIEW_PATH . '/404.php'; 
    return; 
}

$isOwnProfile = ((int)$viewer['id'] === $studentId);

// Smart back URL
$backUrl = '/dashboard';
if ($viewerRole === 'school_admin' || $viewerRole === 'admin') {
    $backUrl = '/school/students';
} elseif ($viewerRole === 'teacher') {
    $backUrl = '/teacher/batches';
} elseif ($viewerRole === 'parent') {
    $backUrl = '/dashboard';
}

$pageTitle = (!empty($p['full_name']) ? $p['full_name'] : 'Student') . ' — Development Profile';
require VIEW_PATH . '/layouts/header.php';

$achievements = $p['achievements'] ?? [];
$achievementCounts = StudentProfile::achievementCounts($achievements);
$nameForInit = !empty($p['full_name']) ? (string)$p['full_name'] : (!empty($p['email']) ? (string)$p['email'] : 'Student');
$initial = mb_strtoupper(mb_substr($nameForInit, 0, 1));

// Dynamic age
$computedAge = Helper::calculateStudentAge(!empty($p['age']) ? (int)$p['age'] : null, $p['created_at'] ?? null);

// Resolve canonical user ID and all candidate IDs
$resolvedStudentUserId = (int)($p['u_user_id'] ?? $p['user_id'] ?? $p['id'] ?? $studentId);
$candidateIds = array_values(array_unique(array_filter([
    $resolvedStudentUserId,
    (int)($p['student_table_id'] ?? 0),
    (int)($p['s_student_id'] ?? 0),
    (int)$studentId
])));

// Data extract
$att = $p['attendance']['raw'] ?? ['present'=>0,'absent'=>0,'late'=>0,'excused'=>0];
$dailyAtt = $p['attendance']['daily_records'] ?? [];
$routine = $p['daily_routine'] ?? [];
$exams = $p['exams'] ?? ['categories'=>[], 'all_tests'=>[], 'summary'=>['total_tests'=>0,'average_pct'=>0]];
$examCats = $exams['categories'] ?? [];
$allTests = $exams['all_tests'] ?? [];
$examSummary = $exams['summary'] ?? ['total_tests'=>0,'average_pct'=>0];

// Fallback 1: Attendance Summary
if (empty($p['attendance']['total']) || (int)$p['attendance']['total'] === 0 || array_sum($att) === 0) {
    try {
        foreach ($candidateIds as $cid) {
            $attDirect = Attendance::summaryForStudent($cid);
            if (array_sum($attDirect) > 0) {
                $att = $attDirect;
                $p['attendance']['raw'] = $att;
                $p['attendance']['total'] = array_sum($att);
                $p['attendance']['present_pct'] = (int)round(($att['present'] / $p['attendance']['total']) * 100);
                break;
            }
        }
    } catch (\Throwable $e) {}
}

// Fallback 2: Daily Attendance Records
if (empty($dailyAtt)) {
    try {
        $inClause = implode(',', array_fill(0, count($candidateIds), '?'));
        $db = Database::connect();
        $attStmt = $db->prepare("
            SELECT a.id, a.batch_id, a.attendance_date, a.status, a.remarks, COALESCE(b.name, '') AS batch_name
            FROM attendance a
            LEFT JOIN batches b ON b.id = a.batch_id
            WHERE a.student_user_id IN ($inClause)
            ORDER BY a.attendance_date ASC
        ");
        $attStmt->execute($candidateIds);
        $dailyAtt = $attStmt->fetchAll() ?: [];
    } catch (\Throwable $e) {
        try {
            $attStmt2 = $db->prepare("
                SELECT a.id, a.batch_id, a.attendance_date, a.status, a.remarks, '' AS batch_name
                FROM attendance a
                WHERE a.student_user_id IN ($inClause)
                ORDER BY a.attendance_date ASC
            ");
            $attStmt2->execute($candidateIds);
            $dailyAtt = $attStmt2->fetchAll() ?: [];
        } catch (\Throwable $e2) {}
    }

    if (!empty($dailyAtt)) {
        $p['attendance']['daily_records'] = $dailyAtt;
        if (array_sum($att) === 0) {
            $att = ['present'=>0,'absent'=>0,'late'=>0,'excused'=>0];
            foreach ($dailyAtt as $da) {
                $st = strtolower(trim((string)($da['status'] ?? 'present')));
                if (isset($att[$st])) $att[$st]++; else $att['present']++;
            }
            $p['attendance']['raw'] = $att;
            $p['attendance']['total'] = array_sum($att);
            $p['attendance']['present_pct'] = (int)round(($att['present'] / $p['attendance']['total']) * 100);
        }
    }
}

$totalAtt = max(1, (int)($p['attendance']['total'] ?? array_sum($att)));

// Fallback 3: Exams & Tests
if (empty($examCats) || (int)($examSummary['total_tests'] ?? 0) === 0) {
    try {
        foreach ($candidateIds as $cid) {
            $examHistory = AcademicAssessment::getStudentExamHistory($cid);
            if (!empty($examHistory['all_tests'])) {
                $exams = $examHistory;
                $examCats = $exams['categories'] ?? [];
                $allTests = $exams['all_tests'] ?? [];
                $examSummary = $exams['summary'] ?? ['total_tests'=>0,'average_pct'=>0];
                $p['exams'] = $exams;
                break;
            }
        }
    } catch (\Throwable $e) {}
}

// Fallback 4: Batches
if (empty($p['batches'])) {
    try {
        foreach ($candidateIds as $cid) {
            $bList = Batch::forStudent($cid);
            if (!empty($bList)) {
                $p['batches'] = $bList;
                break;
            }
        }
    } catch (\Throwable $e) {}
}

// Fallback 5: Daily Routine
if (empty($routine)) {
    try {
        foreach ($candidateIds as $cid) {
            $r = Timetable::getStudentSchedule($cid);
            if (!empty($r)) {
                $routine = $r;
                $p['daily_routine'] = $routine;
                break;
            }
        }
    } catch (\Throwable $e) {}
}

// Fallback 6: Doubts
if (empty($p['doubts_stats']['total'])) {
    try {
        foreach ($candidateIds as $cid) {
            $ds = DoubtFeed::getStudentStats($cid);
            if (!empty($ds['total'])) {
                $p['doubts_stats'] = $ds;
                $p['recent_doubts'] = DoubtFeed::listForStudent($cid);
                break;
            }
        }
    } catch (\Throwable $e) {}
}
?>

<section class="section" style="max-width:1160px; margin:0 auto; padding-bottom:50px;">
<?php try { ?>
    <!-- BACK BUTTON & TOP BREADCRUMB -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
        <a href="<?= htmlspecialchars($backUrl) ?>" onclick="if(window.history.length > 1){ window.history.back(); return false; }" class="btn btn-secondary btn-sm" style="font-weight:600;">
            ← Back
        </a>
        <div style="display:flex; gap:8px;">
            <?php if ($viewerRole === 'admin' || $viewerRole === 'school_admin'): ?>
                <a href="/profile?user_id=<?= (int)$studentId ?>" class="btn btn-secondary btn-sm" style="font-weight:700;">
                    ✏️ Edit Student Profile
                </a>
            <?php endif; ?>
            <a href="/student/report-card?student_id=<?= (int)$studentId ?>" class="btn btn-primary btn-sm" style="font-weight:700;">
                📄 Official Report Card
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                🖨️ Print Profile
            </button>
        </div>
    </div>

    <!-- ================================================================
         1. STUDENT HEADER CARD
         ================================================================ -->
    <div class="card" style="padding:24px 28px; border-radius:16px;">
        <div class="flex" style="gap:1.5rem;align-items:center;flex-wrap:wrap">
            <?php if (!empty($p['profile_image'])): ?>
                <img src="<?= Helper::e($p['profile_image']) ?>" alt="<?= Helper::e($nameForInit) ?>"
                     style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--il-primary-soft)">
            <?php else: ?>
                <span class="avatar avatar-initial" data-role="student"
                      style="width:96px;height:96px;font-size:2.2rem;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg, #2563EB, #7C3AED);color:#fff;font-weight:800;">
                    <?= Helper::e($initial) ?>
                </span>
            <?php endif; ?>

            <div style="flex:1;min-width:240px">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px;">
                    <div>
                        <h1 style="margin:0 0 4px; font-size:24px; font-weight:800; letter-spacing:-0.02em;"><?= Helper::e($p['full_name'] ?? 'Unnamed student') ?></h1>
                        <p class="muted" style="margin:0; font-size:14px;">
                            <?php if (!empty($p['current_class'])): ?><strong><?= Helper::e($p['current_class']) ?></strong> · <?php endif; ?>
                            <?php if (!empty($p['school_name'])): ?><?= Helper::e($p['school_name']) ?><?php endif; ?>
                            <?php if (!empty($p['school_board'])): ?> (<?= Helper::e($p['school_board']) ?>)<?php endif; ?>
                            · Scholar ID #<?= (int)$studentId ?>
                        </p>
                    </div>
                    <div>
                        <span class="badge badge-success" style="padding:4px 10px; font-size:12px;">Active Scholar</span>
                    </div>
                </div>

                <div class="flex mt-2" style="flex-wrap:wrap;gap:.5rem; align-items:center;">
                    <?php if (!empty($p['registration_number'])): ?>
                        <span class="pill" style="font-weight:600; background:rgba(37,99,235,0.08); color:var(--il-primary);">Reg No: <?= Helper::e($p['registration_number']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['roll_number'])): ?>
                        <span class="pill" style="font-weight:600;">Roll: <?= Helper::e($p['roll_number']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['gender'])): ?>
                        <span class="pill"><?= Helper::e(ucfirst(str_replace('_',' ',(string)$p['gender']))) ?></span>
                    <?php endif; ?>
                    <?php if ($computedAge): ?>
                        <span class="pill">Age <?= (int)$computedAge ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['mentor_name'])): ?>
                        <span class="pill" style="background:#f3e8ff; color:#6b21a8; font-weight:600;">👨‍🏫 Mentor: <?= Helper::e($p['mentor_name']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['city'])): ?>
                        <span class="pill">📍 <?= Helper::e($p['city']) ?><?= !empty($p['state']) ? ', '.Helper::e($p['state']) : '' ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['academic_year'])): ?>
                        <span class="pill">AY <?= Helper::e($p['academic_year']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['phone'])): ?>
                        <span class="pill">📞 <?= Helper::e($p['phone']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="flex mt-2" style="gap:8px; flex-wrap:wrap; align-items:center;">
                    <?php if ($isOwnProfile): ?>
                        <a href="/profile" class="btn btn-secondary btn-sm">👁️ View Bio</a>
                        <span class="badge badge-info" style="font-size:11px; padding:4px 8px; border-radius:6px;">🔒 Verified by School</span>
                    <?php endif; ?>
                    <a href="/student/doubts?student_id=<?= (int)$studentId ?>" class="btn btn-outline-primary btn-sm" style="font-weight:700;">
                        💬 Doubt Portal
                    </a>
                    <a href="/parent-pass?student_id=<?= (int)$studentId ?>" class="btn btn-outline-primary btn-sm">
                        📱 Parent Progress Pass
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================
         TOP HIGH-LEVEL METRICS TILES
         ================================================================ -->
    <div class="grid grid-5 mt-3" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));">
        <div class="stat" style="border-left-color:#16A34A;">
            <span class="ico">✅</span>
            <span class="n"><?= (int)($p['attendance']['present_pct'] ?? 0) ?>%</span>
            <span class="l">Attendance</span>
        </div>
        <div class="stat" style="border-left-color:#2563EB;">
            <span class="ico">📚</span>
            <span class="n"><?= (int)($p['lessons']['completed'] ?? 0) ?> / <?= max((int)($p['lessons']['completed'] ?? 0), (int)($p['lessons']['total'] ?? 0)) ?></span>
            <span class="l">Lessons</span>
        </div>
        <div class="stat" style="border-left-color:#7C3AED;">
            <span class="ico">🏫</span>
            <span class="n"><?= count($p['batches'] ?? []) ?></span>
            <span class="l">Batches</span>
        </div>
        <div class="stat" style="border-left-color:#D97706;">
            <span class="ico">📝</span>
            <span class="n"><?= (int)($examSummary['total_tests'] ?? 0) ?> (<?= $examSummary['average_pct'] ?>%)</span>
            <span class="l">Exams & Tests</span>
        </div>
        <div class="stat" style="border-left-color:#059669;">
            <span class="ico">💬</span>
            <span class="n"><?= (int)($p['doubts_stats']['total'] ?? 0) ?></span>
            <span class="l">Doubts Asked</span>
        </div>
    </div>

    <!-- ================================================================
         2. ATTENDANCE BAR GRAPH & TIMELINE (AT TOP)
         ================================================================ -->
    <div class="card mt-3" style="border-radius:16px;">
        <div class="flex between" style="align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
                    <span>📅</span> Daily Attendance Graph &amp; Timeline
                </h3>
                <p class="muted" style="margin:2px 0 0; font-size:13px;">Complete day-by-day attendance history recorded by teachers and mentors.</p>
            </div>
            <div style="display:flex; align-items:center; gap:14px; font-size:12px; flex-wrap:wrap;">
                <span style="display:inline-flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:#16A34A; display:inline-block;"></span> Present (<?= (int)($att['present'] ?? 0) ?>)</span>
                <span style="display:inline-flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:#DC2626; display:inline-block;"></span> Absent (<?= (int)($att['absent'] ?? 0) ?>)</span>
                <span style="display:inline-flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:#F59E0B; display:inline-block;"></span> Late (<?= (int)($att['late'] ?? 0) ?>)</span>
                <span style="display:inline-flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:#2563EB; display:inline-block;"></span> Excused (<?= (int)($att['excused'] ?? 0) ?>)</span>
            </div>
        </div>
        <div class="divider"></div>

        <?php
        $presentW = $totalAtt > 0 ? round((($att['present'] ?? 0) / $totalAtt) * 100, 1) : 0;
        $absentW  = $totalAtt > 0 ? round((($att['absent'] ?? 0)  / $totalAtt) * 100, 1) : 0;
        $lateW    = $totalAtt > 0 ? round((($att['late'] ?? 0)    / $totalAtt) * 100, 1) : 0;
        $excusedW = $totalAtt > 0 ? round((($att['excused'] ?? 0) / $totalAtt) * 100, 1) : 0;

        $attBars = [
            ['label' => 'Present', 'count' => (int)($att['present'] ?? 0), 'pct' => $presentW, 'color' => '#16A34A', 'gradient' => 'linear-gradient(180deg, #22C55E 0%, #16A34A 100%)', 'icon' => '✅'],
            ['label' => 'Absent',  'count' => (int)($att['absent'] ?? 0),  'pct' => $absentW,  'color' => '#DC2626', 'gradient' => 'linear-gradient(180deg, #EF4444 0%, #DC2626 100%)', 'icon' => '✕'],
            ['label' => 'Late',    'count' => (int)($att['late'] ?? 0),    'pct' => $lateW,    'color' => '#F59E0B', 'gradient' => 'linear-gradient(180deg, #FBBF24 0%, #F59E0B 100%)', 'icon' => '⏱️'],
            ['label' => 'Excused', 'count' => (int)($att['excused'] ?? 0), 'pct' => $excusedW, 'color' => '#2563EB', 'gradient' => 'linear-gradient(180deg, #3B82F6 0%, #2563EB 100%)', 'icon' => 'ℹ️'],
        ];
        ?>

        <!-- VERTICAL ATTENDANCE BAR GRAPH -->
        <div style="position:relative; padding:24px 14px 14px 50px; background:var(--surface-1); border-radius:14px; border:1px solid var(--il-border); margin-bottom:20px;">
            <!-- Y-Axis Gridlines & Labels -->
            <div style="position:absolute; left:14px; top:24px; bottom:45px; display:flex; flex-direction:column; justify-content:space-between; font-size:10px; color:var(--il-muted); font-family:'JetBrains Mono', monospace; text-align:right; width:28px;">
                <span>100%</span>
                <span>75%</span>
                <span>50%</span>
                <span>25%</span>
                <span>0%</span>
            </div>

            <div style="position:absolute; left:50px; right:16px; top:24px; bottom:45px; display:flex; flex-direction:column; justify-content:space-between; pointer-events:none;">
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1.5px solid var(--il-border); height:0;"></div>
            </div>

            <!-- Bars Area -->
            <div style="display:flex; justify-content:space-around; align-items:flex-end; height:190px; position:relative; z-index:2;">
                <?php foreach ($attBars as $item): 
                    $barH = $item['pct'] > 0 ? max(8, min(100, $item['pct'])) : 0;
                ?>
                    <div style="display:flex; flex-direction:column; align-items:center; width:22%; max-width:140px; height:100%; justify-content:flex-end;">
                        <!-- Value Badge Above Bar -->
                        <div style="font-size:12px; font-weight:800; color:<?= $item['color'] ?>; margin-bottom:6px; white-space:nowrap;">
                            <?= $item['pct'] ?>%
                        </div>
                        
                        <!-- Bar Pillar -->
                        <div style="width:100%; max-width:76px; height:<?= $barH ?>%; background:<?= $item['gradient'] ?>; border-radius:8px 8px 2px 2px; box-shadow:0 4px 12px <?= $item['color'] ?>33; min-height:<?= $item['pct'] > 0 ? '8px' : '3px' ?>; transition:height 0.6s cubic-bezier(0.4, 0, 0.2, 1); display:flex; align-items:flex-start; justify-content:center;">
                        </div>

                        <!-- Label Below Bar -->
                        <div style="margin-top:10px; text-align:center;">
                            <div style="font-weight:700; font-size:12.5px; color:var(--il-text);">
                                <?= $item['icon'] ?> <?= $item['label'] ?>
                            </div>
                            <div class="muted" style="font-size:11.5px;">
                                <?= $item['count'] ?> <?= $item['count'] === 1 ? 'day' : 'days' ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- DAY-BY-DAY ATTENDANCE TIMELINE CARDS -->
        <h4 style="margin:20px 0 10px; font-size:14px; font-weight:700; display:flex; align-items:center; gap:6px;">
            <span>🗓️</span> Day-by-Day Recorded History (<?= count($dailyAtt) ?> Entries)
        </h4>

        <?php if (empty($dailyAtt)): ?>
            <div style="text-align:center; padding:24px; background:var(--surface-1); border-radius:12px; border:1px dashed var(--il-border);">
                <span style="font-size:28px;">📅</span>
                <p class="muted" style="margin:8px 0 0;">No individual day attendance records logged yet for this scholar.</p>
            </div>
        <?php else: ?>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap:10px;">
                <?php foreach ($dailyAtt as $da): 
                    $status = strtolower((string)$da['status']);
                    $bgColor = '#F0FDF4'; $borderColor = '#86EFAC'; $textColor = '#166534'; $badgeLabel = 'Present'; $icon = '✓';
                    if ($status === 'absent') {
                        $bgColor = '#FEF2F2'; $borderColor = '#FCA5A5'; $textColor = '#991B1B'; $badgeLabel = 'Absent'; $icon = '✕';
                    } elseif ($status === 'late') {
                        $bgColor = '#FFFBEB'; $borderColor = '#FCD34D'; $textColor = '#92400E'; $badgeLabel = 'Late'; $icon = '⏱️';
                    } elseif ($status === 'excused') {
                        $bgColor = '#EFF6FF'; $borderColor = '#93C5FD'; $textColor = '#1E40AF'; $badgeLabel = 'Excused'; $icon = 'ℹ️';
                    }
                    $dateObj = !empty($da['attendance_date']) ? strtotime($da['attendance_date']) : time();
                ?>
                    <div style="background:<?= $bgColor ?>; border:1.5px solid <?= $borderColor ?>; border-radius:10px; padding:10px 12px; display:flex; flex-direction:column; justify-content:space-between; min-height:78px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:11px; font-weight:700; color:<?= $textColor ?>;"><?= date('D', $dateObj) ?></span>
                            <span style="font-size:11px; font-weight:800; color:<?= $textColor ?>;"><?= $icon ?> <?= $badgeLabel ?></span>
                        </div>
                        <div style="margin:4px 0 2px;">
                            <strong style="font-size:15px; color:#1e293b;"><?= date('d M', $dateObj) ?></strong>
                            <span style="font-size:10.5px; color:#64748b;"><?= date('Y', $dateObj) ?></span>
                        </div>
                        <?php if (!empty($da['batch_name'])): ?>
                            <div style="font-size:10px; color:#475569; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= Helper::e($da['batch_name']) ?>">
                                <?= Helper::e($da['batch_name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================================================================
         3. EXAM BAR GRAPHS & RESULTS (UNIT TEST, PEN PAPER, MID TERM, FINAL TERM)
         ================================================================ -->
    <div class="card mt-3" style="border-radius:16px;">
        <div class="flex between" style="align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
                    <span>📊</span> Academic Examination Graphs &amp; Results
                </h3>
                <p class="muted" style="margin:2px 0 0; font-size:13px;">Performance comparison across Unit Tests, Pen &amp; Paper Tests, Mid-Term, and Final Exams.</p>
            </div>
            <div style="display:flex; gap:8px;">
                <a href="/student/report-card?student_id=<?= (int)$studentId ?>" class="btn btn-primary btn-sm" style="font-weight:700;">
                    📄 Generate Official Report Card →
                </a>
            </div>
        </div>
        <div class="divider"></div>

        <?php 
        $examTypesList = [
            'unit_test'  => ['label' => 'Unit Tests (UT)',         'icon' => '📝', 'color' => '#2563EB', 'gradient' => 'linear-gradient(180deg, #3B82F6 0%, #2563EB 100%)', 'desc' => 'Chapter-wise formative tests'],
            'pen_paper'  => ['label' => 'Pen & Paper Tests (PPT)', 'icon' => '📄', 'color' => '#7C3AED', 'gradient' => 'linear-gradient(180deg, #8B5CF6 0%, #7C3AED 100%)', 'desc' => 'Descriptive class evaluations'],
            'mid_term'   => ['label' => 'Mid-Term Exams',          'icon' => '📊', 'color' => '#059669', 'gradient' => 'linear-gradient(180deg, #10B981 0%, #059669 100%)', 'desc' => 'Half-yearly summative exam'],
            'final_term' => ['label' => 'Final Term Exams',        'icon' => '🎓', 'color' => '#D97706', 'gradient' => 'linear-gradient(180deg, #F59E0B 0%, #D97706 100%)', 'desc' => 'Annual final exam results'],
        ];
        ?>

        <!-- VERTICAL EXAM BAR GRAPH -->
        <div style="position:relative; padding:24px 14px 14px 50px; background:var(--surface-1); border-radius:14px; border:1px solid var(--il-border); margin-bottom:24px;">
            <!-- Y-Axis Gridlines & Labels -->
            <div style="position:absolute; left:14px; top:24px; bottom:45px; display:flex; flex-direction:column; justify-content:space-between; font-size:10px; color:var(--il-muted); font-family:'JetBrains Mono', monospace; text-align:right; width:28px;">
                <span>100%</span>
                <span>75%</span>
                <span>50%</span>
                <span>25%</span>
                <span>0%</span>
            </div>

            <div style="position:absolute; left:50px; right:16px; top:24px; bottom:45px; display:flex; flex-direction:column; justify-content:space-between; pointer-events:none;">
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1.5px solid var(--il-border); height:0;"></div>
            </div>

            <!-- Exam Bars -->
            <div style="display:flex; justify-content:space-around; align-items:flex-end; height:200px; position:relative; z-index:2;">
                <?php foreach ($examTypesList as $typeKey => $meta):
                    $catData = $examCats[$typeKey] ?? ['avg_pct' => 0, 'records' => [], 'total_obtained' => 0, 'total_max' => 0];
                    $pct = (float)($catData['avg_pct'] ?? 0);
                    $count = count($catData['records'] ?? []);
                    $barH = $pct > 0 ? max(8, min(100, $pct)) : 0;
                ?>
                    <div style="display:flex; flex-direction:column; align-items:center; width:22%; max-width:150px; height:100%; justify-content:flex-end;">
                        <!-- Value Badge Above Bar -->
                        <div style="font-size:12px; font-weight:800; color:<?= $meta['color'] ?>; margin-bottom:6px; white-space:nowrap; background:<?= $meta['color'] ?>15; padding:2px 8px; border-radius:999px;">
                            <?= $pct ?>%
                        </div>
                        
                        <!-- Bar Pillar -->
                        <div style="width:100%; max-width:76px; height:<?= $barH ?>%; background:<?= $meta['gradient'] ?>; border-radius:8px 8px 2px 2px; box-shadow:0 4px 12px <?= $meta['color'] ?>33; min-height:<?= $pct > 0 ? '8px' : '3px' ?>; transition:height 0.6s cubic-bezier(0.4, 0, 0.2, 1); display:flex; align-items:flex-start; justify-content:center;">
                        </div>

                        <!-- Label Below Bar -->
                        <div style="margin-top:10px; text-align:center;">
                            <div style="font-weight:700; font-size:12px; color:var(--il-text);">
                                <?= $meta['icon'] ?> <?= $meta['label'] ?>
                            </div>
                            <div class="muted" style="font-size:11px; margin-top:2px;">
                                <?= $count ?> <?= $count === 1 ? 'Exam' : 'Exams' ?> (<?= round((float)$catData['total_obtained'], 1) ?>/<?= round((float)$catData['total_max'], 1) ?> pts)
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 4 Examination Performance Cards / Mini Breakdown -->
        <div class="grid grid-4" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:12px; margin-bottom:20px;">
            <?php 
            foreach ($examTypesList as $typeKey => $meta):
                $catData = $examCats[$typeKey] ?? ['avg_pct' => 0, 'records' => [], 'total_obtained' => 0, 'total_max' => 0];
                $pct = (float)($catData['avg_pct'] ?? 0);
                $count = count($catData['records'] ?? []);
            ?>
                <div style="background:var(--surface-1); border:1px solid var(--il-border); border-top:3.5px solid <?= $meta['color'] ?>; border-radius:12px; padding:12px 14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-size:18px;"><?= $meta['icon'] ?></span>
                        <span class="badge" style="background:<?= $meta['color'] ?>1a; color:<?= $meta['color'] ?>; font-weight:800; font-size:11px;">
                            <?= $pct ?>%
                        </span>
                    </div>
                    <h5 style="margin:0 0 2px; font-size:13px;"><?= $meta['label'] ?></h5>
                    <p class="muted" style="margin:0 0 8px; font-size:11px;"><?= $meta['desc'] ?></p>

                    <!-- Mini Bar -->
                    <div style="background:var(--surface-2); border-radius:999px; height:6px; overflow:hidden; border:1px solid var(--il-border);">
                        <div style="background:<?= $meta['color'] ?>; height:100%; width:<?= min(100, $pct) ?>%; border-radius:999px;"></div>
                    </div>

                    <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--il-muted); margin-top:6px;">
                        <span><?= $count ?> <?= $count === 1 ? 'Exam' : 'Exams' ?></span>
                        <span><?= round((float)$catData['total_obtained'], 1) ?> / <?= round((float)$catData['total_max'], 1) ?> pts</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Detailed Marks Records Table -->
        <h4 style="margin:24px 0 12px; font-size:15px; font-weight:700;">📋 Itemized Academic Marks Sheet</h4>
        <?php if (empty($allTests)): ?>
            <div style="text-align:center; padding:28px 20px; background:var(--surface-1); border-radius:12px; border:1px dashed var(--il-border);">
                <span style="font-size:28px;">📝</span>
                <h5 style="margin:6px 0 2px;">No Academic Assessment Marks Logged Yet</h5>
                <p class="muted" style="margin:0; font-size:12.5px;">When teachers enter Unit Test, Pen-Paper, or Term marks in their Teacher Hub, they will display here automatically.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Exam / Test Title</th>
                            <th>Category</th>
                            <th>Subject</th>
                            <th>Score Obtained</th>
                            <th>Max Marks</th>
                            <th>Percentage</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allTests as $t): 
                            $passed = !empty($t['is_passed']);
                            $typeLabel = $examTypesList[$t['assessment_type']]['label'] ?? ucfirst(str_replace('_',' ', $t['assessment_type']));
                            $typeColor = $examTypesList[$t['assessment_type']]['color'] ?? '#6366f1';
                        ?>
                            <tr>
                                <td><?= Helper::e($t['date_str'] ?? '—') ?></td>
                                <td><strong><?= Helper::e($t['title'] ?? 'Assessment') ?></strong></td>
                                <td>
                                    <span class="badge" style="background:<?= $typeColor ?>15; color:<?= $typeColor ?>; font-size:11px; font-weight:700;">
                                        <?= Helper::e($typeLabel) ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight:600;"><?= Helper::e($t['subject_name'] ?? '—') ?></span>
                                </td>
                                <td>
                                    <strong style="font-size:14px; color:<?= $passed ? '#16A34A' : '#DC2626' ?>;">
                                        <?= $t['score_obtained'] ?>
                                    </strong>
                                </td>
                                <td><?= $t['total_marks'] ?></td>
                                <td>
                                    <span style="font-weight:700;"><?= $t['percentage'] ?>%</span>
                                </td>
                                <td>
                                    <?php if ($passed): ?>
                                        <span class="badge badge-success" style="font-size:11px;">✓ Passed</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger" style="font-size:11px;">Needs Improvement</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================================================================
         5. BATCHES
         ================================================================ -->
    <div class="card mt-3" style="border-radius:16px;">
        <div class="flex between" style="align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h3 style="margin:0;">🏫 Enrolled Batches</h3>
                <p class="muted" style="margin:2px 0 0; font-size:13px;">Active academic and extracurricular cohorts this student attends.</p>
            </div>
            <span class="badge badge-primary"><?= count($p['batches'] ?? []) ?> Enrolled</span>
        </div>
        <div class="divider"></div>

        <?php if (empty($p['batches'])): ?>
            <p class="muted" style="text-align:center; padding:16px 0;">No batches currently assigned.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Batch Name</th><th>Subject</th><th>Class / Level</th><th>Teacher / Mentor</th></tr></thead>
                    <tbody>
                    <?php foreach ($p['batches'] as $b): ?>
                        <tr>
                            <td><strong><?= Helper::e($b['name'] ?? 'Batch') ?></strong></td>
                            <td><?= Helper::e($b['subject'] ?? '—') ?></td>
                            <td><span class="pill"><?= Helper::e($b['class_level'] ?? '—') ?></span></td>
                            <td>👨‍🏫 <?= Helper::e($b['teacher_name'] ?? 'Unassigned') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================================================================
         6. STUDENT DOUBT & Q&A PORTAL
         ================================================================ -->
    <div class="card mt-3" style="border-radius:16px;">
        <div class="flex between" style="align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h3 style="margin:0;">💬 Student Doubt &amp; Q&amp;A Portal</h3>
                <p class="muted" style="font-size:13px; margin:2px 0 0;">Questions asked across lessons and verified teacher explanations.</p>
            </div>
            <div>
                <a href="/student/doubts?student_id=<?= (int)$studentId ?>" class="btn btn-outline-primary btn-sm">
                    Open Full Doubt Portal →
                </a>
            </div>
        </div>
        <div class="divider"></div>

        <?php
        $doubtsStats = $p['doubts_stats'] ?? ['total'=>0,'answered'=>0,'open'=>0];
        $recentDoubts = $p['recent_doubts'] ?? [];
        ?>

        <div class="grid grid-3 mb-3">
            <div class="stat">
                <span class="ico">❓</span>
                <span class="n"><?= (int)($doubtsStats['total'] ?? 0) ?></span>
                <span class="l">Total Doubts Asked</span>
            </div>
            <div class="stat" style="border-left-color:#10b981;">
                <span class="ico">✅</span>
                <span class="n" style="color:#10b981;"><?= (int)($doubtsStats['answered'] ?? 0) ?></span>
                <span class="l">Answered by Teachers</span>
            </div>
            <div class="stat" style="border-left-color:#f59e0b;">
                <span class="ico">⏳</span>
                <span class="n" style="color:#f59e0b;"><?= (int)($doubtsStats['open'] ?? 0) ?></span>
                <span class="l">Awaiting Response</span>
            </div>
        </div>

        <?php if (empty($recentDoubts)): ?>
            <p class="muted" style="text-align:center; padding:16px 0;">No doubts recorded yet. Students can ask questions directly from any lesson or via the Doubt Portal.</p>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php foreach (array_slice($recentDoubts, 0, 4) as $d): ?>
                    <div style="padding:12px 14px; border-radius:10px; background:var(--surface-1); border:1px solid var(--il-border); border-left:4px solid <?= ($d['status'] ?? '') === 'answered' ? '#10b981' : '#f59e0b' ?>;">
                        <div class="flex between" style="align-items:flex-start; gap:10px; flex-wrap:wrap;">
                            <div>
                                <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px; flex-wrap:wrap;">
                                    <?php if (!empty($d['subject_name'])): ?>
                                        <span class="badge badge-brand" style="font-size:10px;"><?= Helper::e($d['subject_name']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($d['lesson_title'])): ?>
                                        <span class="badge badge-primary" style="font-size:10px;">📖 <?= Helper::e($d['lesson_title']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($d['created_at'])): ?>
                                        <span class="muted" style="font-size:11px;">· <?= date('d M Y', strtotime((string)$d['created_at'])) ?></span>
                                    <?php endif; ?>
                                </div>
                                <strong style="font-size:14px; color:var(--il-text);"><?= Helper::e($d['title'] ?? '') ?></strong>
                                <p class="muted" style="font-size:12px; margin:4px 0 0; line-height:1.4;">
                                    <?= Helper::e(mb_substr((string)($d['body'] ?? ''), 0, 160)) ?><?= mb_strlen((string)($d['body'] ?? '')) > 160 ? '...' : '' ?>
                                </p>
                            </div>
                            <div>
                                <span class="badge <?= ($d['status'] ?? '') === 'answered' ? 'badge-success' : 'badge-warning' ?>" style="font-size:11px;">
                                    <?= ($d['status'] ?? '') === 'answered' ? '✅ Answered' : '⏳ Pending' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================================================================
         7. HOLISTIC GROWTH & DEVELOPMENT SNAPSHOT (5 KEY AREAS)
         ================================================================ -->
    <div class="card mt-3" style="border-radius:16px;">
        <div class="flex between" style="align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
                    <span>🌱</span> Holistic Growth &amp; Development Graph
                </h3>
                <p class="muted" style="margin:2px 0 0; font-size:13px;">Scholar participation across the 5 core personality and experiential growth pillars.</p>
            </div>
            <span class="badge badge-brand">5 Growth Pillars</span>
        </div>
        <div class="divider"></div>

        <?php
        $areas = [
            'academic'        => ['icon' => '📘', 'label' => 'Academic',        'color' => '#FF7A18', 'gradient' => 'linear-gradient(180deg, #FB923C 0%, #EA580C 100%)'],
            'sports'          => ['icon' => '⚽', 'label' => 'Sports',          'color' => '#16A34A', 'gradient' => 'linear-gradient(180deg, #22C55E 0%, #16A34A 100%)'],
            'creative'        => ['icon' => '🎨', 'label' => 'Creative',        'color' => '#A855F7', 'gradient' => 'linear-gradient(180deg, #C084FC 0%, #9333EA 100%)'],
            'communication'   => ['icon' => '💬', 'label' => 'Communication',   'color' => '#2563EB', 'gradient' => 'linear-gradient(180deg, #38BDF8 0%, #2563EB 100%)'],
            'problem_solving' => ['icon' => '🧩', 'label' => 'Problem Solving', 'color' => '#F59E0B', 'gradient' => 'linear-gradient(180deg, #FCD34D 0%, #D97706 100%)'],
        ];
        $maxCount = 1;
        foreach ($areas as $k => $_) {
            $maxCount = max($maxCount, count($p['activity_profiles'][$k] ?? []));
        }
        ?>

        <!-- VERTICAL 5-PILLAR BAR GRAPH -->
        <div style="position:relative; padding:20px 10px 10px 45px; background:var(--surface-1); border-radius:12px; border:1px solid var(--il-border); margin-bottom:14px;">
            <!-- Y-Axis Gridlines & Labels -->
            <div style="position:absolute; left:12px; top:20px; bottom:40px; display:flex; flex-direction:column; justify-content:space-between; font-size:10px; color:var(--il-muted); font-family:'JetBrains Mono', monospace; text-align:right; width:26px;">
                <span><?= $maxCount ?></span>
                <span><?= (int)round($maxCount * 0.75) ?></span>
                <span><?= (int)round($maxCount * 0.5) ?></span>
                <span><?= (int)round($maxCount * 0.25) ?></span>
                <span>0</span>
            </div>

            <div style="position:absolute; left:45px; right:15px; top:20px; bottom:40px; display:flex; flex-direction:column; justify-content:space-between; pointer-events:none;">
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1px dashed var(--il-border); height:0;"></div>
                <div style="border-bottom:1.5px solid var(--il-border); height:0;"></div>
            </div>

            <!-- 5 Pillar Bars Area -->
            <div style="display:flex; justify-content:space-around; align-items:flex-end; height:170px; position:relative; z-index:2;">
                <?php foreach ($areas as $key => $meta):
                    $count = count($p['activity_profiles'][$key] ?? []);
                    $pct = (int)round($count / max(1, $maxCount) * 100);
                    $barH = $count > 0 ? max(10, min(100, $pct)) : 0;
                ?>
                    <div style="display:flex; flex-direction:column; align-items:center; width:18%; max-width:110px; height:100%; justify-content:flex-end;">
                        <!-- Value Badge -->
                        <div style="font-size:11px; font-weight:800; color:<?= $meta['color'] ?>; margin-bottom:5px; white-space:nowrap;">
                            <?= $count ?>
                        </div>
                        
                        <!-- Bar Pillar -->
                        <div style="width:100%; max-width:58px; height:<?= $barH ?>%; background:<?= $meta['gradient'] ?>; border-radius:6px 6px 2px 2px; box-shadow:0 3px 8px <?= $meta['color'] ?>33; min-height:<?= $count > 0 ? '8px' : '3px' ?>; transition:height 0.6s cubic-bezier(0.4, 0, 0.2, 1);">
                        </div>

                        <!-- Label Below Bar -->
                        <div style="margin-top:8px; text-align:center;">
                            <div style="font-weight:700; font-size:11.5px; color:var(--il-text);">
                                <?= $meta['icon'] ?> <?= $meta['label'] ?>
                            </div>
                            <div class="muted" style="font-size:10.5px;">
                                <?= $count ?> <?= $count === 1 ? 'activity' : 'activities' ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ================================================================
         8. ACHIEVEMENTS & CERTIFICATES & PARENTS
         ================================================================ -->
    <div class="grid grid-2 mt-3">
        <!-- ACHIEVEMENTS -->
        <div class="card" style="border-radius:16px;">
            <h3>🏅 Achievements</h3>
            <div class="divider"></div>
            <?php if (empty($p['achievements'])): ?>
                <p class="muted">No achievements recorded yet.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <?php foreach ($p['achievements'] as $ac): ?>
                        <div style="border-left:3px solid var(--il-primary); padding:8px 12px; background:var(--surface-1); border-radius:6px;">
                            <div class="flex between">
                                <strong><?= Helper::e($ac['title'] ?? 'Achievement') ?></strong>
                                <span class="badge badge-primary" style="font-size:10px;"><?= Helper::e(str_replace('_',' ',(string)($ac['category'] ?? 'other'))) ?></span>
                            </div>
                            <?php if (!empty($ac['description'])): ?>
                                <p class="muted" style="font-size:.82rem; margin:3px 0 0;"><?= Helper::e($ac['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- CERTIFICATES -->
        <div class="card" style="border-radius:16px;">
            <h3>📜 Certificates</h3>
            <div class="divider"></div>
            <?php if (empty($p['certificates'])): ?>
                <p class="muted">No certificates issued yet.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <?php foreach ($p['certificates'] as $c): ?>
                        <div class="flex between" style="padding:8px 0; border-bottom:1px dashed var(--il-border); align-items:center;">
                            <div>
                                <strong><?= Helper::e($c['title'] ?? 'Certificate') ?></strong>
                                <div class="muted" style="font-size:11px;"><?= Helper::e((string)($c['type'] ?? 'participation')) ?></div>
                            </div>
                            <?php if (!empty($c['certificate_url'])): ?>
                                <a class="btn btn-ghost btn-sm" href="<?= Helper::e($c['certificate_url']) ?>" target="_blank">Download</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- FAMILY INFORMATION -->
    <div class="card mt-3" style="border-radius:16px;">
        <h3>👨‍👩‍👧 Family &amp; Guardians</h3>
        <div class="divider"></div>
        <?php if (empty($p['parents'])): ?>
            <p class="muted">No parent or guardian details linked yet.</p>
        <?php else: ?>
            <div class="grid grid-2">
                <?php foreach ($p['parents'] as $par): ?>
                    <div style="background:var(--surface-1); padding:14px; border-radius:10px; border:1px solid var(--il-border);">
                        <div class="kv"><span>Name</span><strong><?= Helper::e($par['full_name'] ?? '—') ?></strong></div>
                        <div class="kv"><span>Relationship</span><span><?= Helper::e(ucfirst((string)($par['relationship'] ?? 'parent'))) ?></span></div>
                        <div class="kv"><span>Phone</span><span><?= Helper::e($par['phone'] ?? '—') ?></span></div>
                        <div class="kv"><span>Email</span><span><?= Helper::e($par['email'] ?? '—') ?></span></div>
                        <div class="kv"><span>Occupation</span><span><?= Helper::e($par['occupation'] ?? '—') ?></span></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php } catch (\Throwable $e) {
    error_log('Student profile render error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo '<div class="alert alert-error" style="margin:20px 0;">An error occurred while loading profile details. Please try again.</div>';
} ?>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
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

// Attendance Summary
$attCounts = ['present' => 0, 'absent' => 0, 'late' => 0];
try {
    $attStmt = $db->prepare("
        SELECT status, COUNT(*) as cnt 
        FROM attendance 
        WHERE student_user_id = ? 
        GROUP BY status
    ");
    $attStmt->execute([$studentUserId]);
    foreach ($attStmt->fetchAll() as $r) {
        if (isset($attCounts[$r['status']])) {
            $attCounts[$r['status']] = (int)$r['cnt'];
        }
    }
} catch (\Throwable $e) {}
$totalAttDays = array_sum($attCounts);
$attPercentage = $totalAttDays > 0 ? (int)round(($attCounts['present'] / $totalAttDays) * 100) : 100;

// Academic Assessments / Recent Tests
$recentTests = [];
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

// Monthly Fee Status
$fees = [];
try {
    $fees = FeeManagement::list([
        'student_user_id' => $studentUserId,
    ]);
} catch (\Throwable $e) {}
$latestFee = $fees[0] ?? null;

// Today's Routine / Class Timetable
$dayOfWeek = date('l');
$routine = [];
try {
    if (!empty($studentUserId)) {
        $routine = Timetable::getStudentSchedule($studentUserId);
    }
    if (empty($routine) && $schoolId && !empty($student['current_class'])) {
        $routine = Timetable::getForClass($schoolId, (string)$student['current_class'], $dayOfWeek, (string)($student['section'] ?? 'A'));
    }
} catch (\Throwable $e) {}

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

            <!-- ATTENDANCE OVERVIEW -->
            <div class="card" style="padding:16px; margin:0 0 18px; border-radius:12px; background:var(--surface-1); border:1px solid var(--il-border);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <div style="font-size:13px; font-weight:700; color:var(--il-text);">📊 Attendance Metric</div>
                    <div style="font-size:16px; font-weight:800; color:<?= $attPercentage >= 75 ? '#10b981' : '#ef4444' ?>;">
                        <?= $attPercentage ?>% Overall
                    </div>
                </div>
                <div style="background:var(--surface-2); border-radius:999px; height:8px; overflow:hidden; border:1px solid var(--il-border);">
                    <div style="background:<?= $attPercentage >= 75 ? '#10b981' : '#ef4444' ?>; height:100%; width:<?= $attPercentage ?>%;"></div>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--il-muted); margin-top:6px;">
                    <span>Present: <strong><?= $attCounts['present'] ?></strong> days</span>
                    <span>Late: <strong><?= $attCounts['late'] ?></strong> days</span>
                    <span>Absent: <strong><?= $attCounts['absent'] ?></strong> days</span>
                </div>
            </div>

            <!-- TODAY'S CLASS ROUTINE -->
            <div class="card" style="padding:16px; margin:0 0 18px; border-radius:12px; background:var(--surface-1); border:1px solid var(--il-border);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div style="font-size:13px; font-weight:700; color:var(--il-text);">🗓️ Today's Routine (<?= $dayOfWeek ?>)</div>
                    <a href="/timetable" style="font-size:11px; color:var(--primary); text-decoration:none; font-weight:600;">Full Timetable →</a>
                </div>
                <?php if (!empty($routine)): ?>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <?php foreach ($routine as $slot): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 10px; background:var(--surface-2); border-radius:8px; font-size:12px; border:1px solid var(--il-border);">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span class="badge badge-secondary" style="font-size:10px;">P<?= (int)$slot['period_number'] ?></span>
                                    <strong style="color:var(--il-text);"><?= Helper::e($slot['subject_name'] ?? 'Class') ?></strong>
                                    <?php if (!empty($slot['teacher_name'])): ?>
                                        <span class="muted" style="font-size:11px;">(<?= Helper::e($slot['teacher_name']) ?>)</span>
                                    <?php endif; ?>
                                </div>
                                <span class="muted" style="font-family:monospace; font-size:11px;">
                                    <?= substr($slot['start_time'], 0, 5) ?> - <?= substr($slot['end_time'], 0, 5) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="font-size:12px; color:var(--il-muted); text-align:center; padding:10px 0;">
                        No classes scheduled for today or weekend break.
                    </div>
                <?php endif; ?>
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

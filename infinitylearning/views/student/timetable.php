<?php
use App\Auth;
use App\Helper;
use App\Timetable;

Auth::requireRole('student');
$user = Auth::user();
$studentId = (int)$user['id'];

$student = Auth::findStudentByUserId($studentId);
$schoolId = (int)($user['school_id'] ?? 1);

$rawClass = !empty($student['current_class']) ? $student['current_class'] : 'Grade 8';
$classLevel = $rawClass;
$section = 'A';
if (preg_match('/([0-9]+|Grade\s*[0-9]+|Class\s*[0-9]+)\s*[- ]*\s*([A-Z])/i', $rawClass, $m)) {
    $classLevel = trim($m[1]);
    $section = strtoupper(trim($m[2]));
}

$periods = Timetable::getPeriodsForSchool($schoolId);
$grid = Timetable::getGridForClass($schoolId, $classLevel, $section);
$todayDay = strtolower(date('l'));
$todayRoutine = Timetable::getStudentSchedule($studentId);

$pageTitle = 'Weekly Class Routine — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm">
    <div class="container-narrow" style="max-width:1200px;">
        <!-- HEADER -->
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px; margin-bottom:18px;">
            <div>
                <span class="kicker">🗓️ ACADEMIC TIMETABLE</span>
                <h1 style="margin:2px 0 0; font-size:24px;">Weekly Class Routine (Monday – Saturday)</h1>
                <p class="muted" style="margin-top:4px;">
                    Class: <strong><?= Helper::e($classLevel) ?></strong> (Section <?= Helper::e($section) ?>)
                    · Bell schedule and assigned teachers
                </p>
            </div>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    🖨️ Print Routine
                </button>
                <a href="/dashboard" class="btn btn-secondary btn-sm">
                    ← Back to Dashboard
                </a>
            </div>
        </div>

        <!-- PRINT ONLY HEADER -->
        <div class="only-print" style="display:none; margin-bottom:14px; border-bottom:2px solid #000; padding-bottom:8px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="margin:0; font-size:20px; font-weight:800;">Official Weekly Routine</h2>
                    <p style="margin:2px 0 0; font-size:13px; color:#444;">Class: <?= Helper::e($classLevel) ?> - Section <?= Helper::e($section) ?> · Student: <?= Helper::e($student['full_name'] ?? $user['email']) ?></p>
                </div>
                <div style="text-align:right; font-size:12px; color:#666;">
                    Generated on <?= date('d M Y') ?>
                </div>
            </div>
        </div>

        <!-- TODAY'S SCHEDULE BANNER -->
        <?php if (!empty($todayRoutine)): ?>
            <div class="card mb-3" style="background:linear-gradient(135deg, rgba(37,99,235,0.06), rgba(16,185,129,0.06)); border:1.5px solid rgba(37,99,235,0.2); border-radius:14px; padding:16px 20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:20px;">⚡</span>
                        <strong style="font-size:15px; color:var(--il-text);">Today's Schedule (<?= date('l, d M') ?>)</strong>
                        <span class="badge badge-success" style="font-size:11px;">Active Today</span>
                    </div>
                    <span class="muted" style="font-size:12px;"><?= count($todayRoutine) ?> periods scheduled today</span>
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                    <?php foreach ($todayRoutine as $ts): 
                        $subColor = !empty($ts['subject_color']) ? $ts['subject_color'] : '#2563EB';
                        $subIcon = !empty($ts['subject_icon']) ? $ts['subject_icon'] : '📖';
                    ?>
                        <div style="background:var(--surface-0); border:1px solid var(--il-border); border-left:3.5px solid <?= Helper::e($subColor) ?>; border-radius:10px; padding:10px 12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; font-size:11px; margin-bottom:4px;">
                                <span class="badge" style="background:<?= Helper::e($subColor) ?>18; color:<?= Helper::e($subColor) ?>; font-weight:700;">
                                    Period <?= (int)$ts['period_number'] ?>
                                </span>
                                <span class="muted" style="font-family:'JetBrains Mono', monospace; font-weight:600;">
                                    <?= Timetable::formatTime12($ts['start_time']) ?>
                                </span>
                            </div>
                            <div style="font-weight:700; font-size:13.5px; color:var(--il-text); display:flex; align-items:center; gap:5px;">
                                <span><?= Helper::e($subIcon) ?></span>
                                <?= Helper::e($ts['subject_name']) ?>
                            </div>
                            <div class="muted" style="font-size:11.5px; margin-top:2px;">
                                👨‍🏫 <?= Helper::e($ts['teacher_name']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- FULL 6-DAY WEEKLY TIMETABLE GRID (DESKTOP / TABLET) -->
        <div class="card" style="padding:0; overflow-x:auto; border-radius:16px; margin-bottom:20px;">
            <div style="padding:16px 20px; border-bottom:1px solid var(--il-border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px;">
                    <span>📅</span> Complete 6-Day Weekly Schedule Grid
                </h3>
                <span class="muted" style="font-size:12px;">Mon – Sat · All bell periods</span>
            </div>
            <table style="width:100%; border-collapse:collapse; text-align:center; font-size:12.5px; min-width:880px;">
                <thead>
                    <tr style="background:var(--surface-0); border-bottom:2px solid var(--il-border); font-size:11px; text-transform:uppercase; color:var(--il-muted);">
                        <th style="padding:14px 16px; width:110px; text-align:left;">Day</th>
                        <?php foreach ($periods as $pInfo): 
                            $isBreak = !empty($pInfo['is_break']);
                        ?>
                            <th style="padding:10px 8px; border-left:1px solid var(--il-border); <?= $isBreak ? 'background:rgba(245,158,11,0.08);' : '' ?>">
                                <div style="font-weight:700; color:<?= $isBreak ? '#b45309' : 'var(--il-text)' ?>;">
                                    <?= Helper::e($pInfo['label']) ?>
                                </div>
                                <div style="font-size:10px; color:var(--il-muted); font-weight:normal; margin-top:2px; font-family:'JetBrains Mono', monospace;">
                                    <?= Timetable::formatTime12($pInfo['start']) ?> - <?= Timetable::formatTime12($pInfo['end']) ?>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Timetable::DAYS as $day): 
                        $isToday = ($day === $todayDay);
                    ?>
                        <tr style="border-bottom:1px solid var(--il-border); <?= $isToday ? 'background:rgba(37,99,235,0.03);' : '' ?>">
                            <td style="padding:14px 16px; font-weight:700; text-align:left; background:var(--surface-0); text-transform:capitalize; color:var(--il-text); border-left:<?= $isToday ? '4px solid #2563EB' : 'none' ?>;">
                                <div><?= $day ?></div>
                                <?php if ($isToday): ?>
                                    <span class="badge badge-primary" style="font-size:9.5px; padding:2px 6px; margin-top:3px; display:inline-block;">TODAY</span>
                                <?php endif; ?>
                            </td>
                            <?php foreach ($periods as $pInfo): 
                                $isBreak = !empty($pInfo['is_break']);
                                $pNum = (int)$pInfo['period_number'];
                                $slot = $grid[$day][$pNum] ?? null;
                            ?>
                                <td style="padding:8px 6px; border-left:1px solid var(--il-border); vertical-align:top; min-width:95px; <?= $isBreak ? 'background:rgba(245,158,11,0.04);' : '' ?>">
                                    <?php if ($isBreak): ?>
                                        <div style="padding:12px 2px; font-size:11px; color:#d97706; font-weight:600; font-style:italic;">
                                            ☕ <?= Helper::e($pInfo['label']) ?>
                                        </div>
                                    <?php elseif ($slot): 
                                        $subColor = !empty($slot['subject_color']) ? $slot['subject_color'] : '#2563EB';
                                        $subIcon  = !empty($slot['subject_icon'])  ? $slot['subject_icon']  : '📖';
                                    ?>
                                        <div style="background:var(--surface-0); border:1px solid var(--il-border); border-top:3px solid <?= Helper::e($subColor) ?>; border-radius:8px; padding:8px 6px; text-align:center;">
                                            <div style="font-size:14px;"><?= Helper::e($subIcon) ?></div>
                                            <strong style="display:block; font-size:12px; margin:2px 0; color:var(--il-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= Helper::e($slot['subject_name']) ?>">
                                                <?= Helper::e($slot['subject_name']) ?>
                                            </strong>
                                            <div class="muted" style="font-size:10.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= Helper::e($slot['teacher_name']) ?>">
                                                <?= Helper::e($slot['teacher_name']) ?>
                                            </div>
                                            <?php if (!empty($slot['room_number'])): ?>
                                                <div style="font-size:9.5px; color:#64748B; margin-top:2px;">
                                                    📍 Rm <?= Helper::e($slot['room_number']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="padding:14px 4px; font-size:11px; color:var(--il-muted);">
                                            —
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<style>
@media print {
    .dashboard-header, nav, footer, .btn { display: none !important; }
    .only-print { display: block !important; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
}
</style>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

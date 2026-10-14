<?php
use App\Auth;
use App\Helper;
use App\Timetable;

Auth::requireRole('student');
$user = Auth::user();
$studentId = (int)$user['id'];

$todayRoutine = Timetable::getStudentSchedule($studentId);

$pageTitle = 'My Class Routine — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm">
    <div class="container-narrow">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">🗓️ DAILY SCHEDULE</span>
                <h1 style="margin:2px 0 0;">Today's Class Routine (<?= date('l, d M') ?>)</h1>
                <p class="muted">Your bell schedule and periods for today.</p>
            </div>
            <a href="/dashboard" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
            <?php if (empty($todayRoutine)): ?>
                <div style="padding:48px 24px; text-align:center; color:var(--il-muted);">
                    <div style="font-size:38px; margin-bottom:8px;">🏖️</div>
                    <h3>No scheduled periods today</h3>
                    <p class="muted">Check with your class teacher or explore lessons online.</p>
                    <a href="/lessons" class="btn btn-primary btn-sm">Study Lessons →</a>
                </div>
            <?php else: ?>
                <table class="table" style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--il-border); text-align:left; background:var(--surface-0); color:var(--il-muted); font-size:12px; text-transform:uppercase;">
                            <th style="padding:12px 18px;">Period</th>
                            <th style="padding:12px 18px;">Timing</th>
                            <th style="padding:12px 18px;">Subject</th>
                            <th style="padding:12px 18px;">Instructor</th>
                            <th style="padding:12px 18px;">Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todayRoutine as $r): ?>
                            <tr style="border-bottom:1px solid var(--il-border);">
                                <td style="padding:12px 18px;">
                                    <span class="badge" style="background:#1E293B; color:#fff; font-weight:700;">
                                        Period <?= (int)$r['period_number'] ?>
                                    </span>
                                </td>
                                <td style="padding:12px 18px; font-family:'JetBrains Mono', monospace; font-weight:600;">
                                    <?= Timetable::formatTime12($r['start_time']) ?> - <?= Timetable::formatTime12($r['end_time']) ?>
                                </td>
                                <td style="padding:12px 18px;">
                                    <strong style="color:var(--il-text);"><?= Helper::e($r['subject_name']) ?></strong>
                                </td>
                                <td style="padding:12px 18px; color:var(--il-muted);">
                                    <?= Helper::e($r['teacher_name']) ?>
                                </td>
                                <td style="padding:12px 18px;">
                                    <span class="badge" style="background:#F1F5F9; color:#475569;">
                                        <?= !empty($r['room_number']) ? 'Room ' . Helper::e($r['room_number']) : 'Main Classroom' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

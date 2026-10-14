<?php
use App\Auth;
use App\School;
use App\Analytics;
use App\Helper;
use App\Database;
use App\ParentSummary;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);
if (!$school) { Auth::logout(); Helper::redirect('/login'); }

$stats           = School::stats($schoolId);
$userSplit       = Analytics::schoolUserSplit($schoolId);
$attendanceTrend = Analytics::schoolAttendanceByMonth($schoolId, 6);
$participation   = Analytics::schoolParticipationByCategory($schoolId);

$db = Database::connect();
$pending = School::pendingUsers($schoolId);

$recent = $db->prepare("
    SELECT u.id, u.email, u.role, u.status, u.created_at,
           COALESCE(s.full_name, t.full_name) AS full_name,
           COALESCE(s.profile_image, t.profile_image) AS avatar
    FROM users u
    LEFT JOIN students s ON s.user_id = u.id
    LEFT JOIN teachers t ON t.user_id = u.id
    WHERE u.school_id = ? AND u.role IN ('student','teacher')
    ORDER BY u.created_at DESC LIMIT 5
");
$recent->execute([$schoolId]);
$recent = $recent->fetchAll();

$pageTitle = $school['name'] . ' — School Admin';

// ---- Prepare chart data ------------------------------------------------
$trendLabels = [];
$trendValues = [];
foreach ($attendanceTrend as $t) {
    $trendLabels[] = date('M y', strtotime($t['ym'] . '-01'));
    $trendValues[] = (int)$t['pct'];
}

$userSplitData = [
    (int)$userSplit['students_active'],
    (int)$userSplit['students_pending'],
    (int)$userSplit['teachers_active'],
    (int)$userSplit['teachers_pending'],
];

$catLabels = [];
$catValues = [];
foreach ($participation as $p) {
    $catLabels[] = ucfirst($p['category']);
    $catValues[] = (int)$p['cnt'];
}

require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>

        <!-- ============ HEADER ============ -->
        <div class="dashboard-header" style="align-items:center;">
            <div style="display:flex; align-items:center; gap:16px;">
                <?php if (!empty($school['crest_logo'])): ?>
                    <div class="school-header-crest-badge" style="width:64px; height:64px; border-radius:12px; background:#FFFFFF; border:1.5px solid var(--il-border); box-shadow:0 2px 6px rgba(0,0,0,0.08); display:flex; align-items:center; justify-content:center; padding:4px; overflow:hidden; flex-shrink:0;">
                        <img src="<?= htmlspecialchars($school['crest_logo']) ?>" alt="<?= Helper::e($school['name']) ?>" style="width:100%; height:100%; object-fit:contain;" onerror="this.closest('.school-header-crest-badge').style.display='none';">
                    </div>
                <?php endif; ?>
                <div class="dashboard-header-text">
                    <span class="kicker">🏫 SCHOOL ADMIN</span>
                    <h1 style="margin-top:2px; font-weight:800; letter-spacing:-0.02em;"><?= Helper::e($school['name']) ?></h1>
                    <p class="muted" style="margin-top:4px;">
                        <?php if ($school['board']): ?><?= Helper::e($school['board']) ?> · <?php endif; ?>
                        <?php if ($school['city']): ?><?= Helper::e($school['city']) ?><?= $school['state'] ? ', ' . Helper::e($school['state']) : '' ?><?php endif; ?>
                        <?php if (!empty($school['code'])): ?> · <code style="font-size:.8rem"><?= Helper::e($school['code']) ?></code><?php endif; ?>
                    </p>
                </div>
            </div>
            <span class="badge badge-success">Active</span>
        </div>

        <?php if ((int)$stats['pending'] > 0): ?>
            <div class="alert alert-warning">
                <strong><?= (int)$stats['pending'] ?> user(s)</strong> are waiting for your approval.
                <a href="/school/teachers" style="text-decoration:underline">Review teachers</a> ·
                <a href="/school/students" style="text-decoration:underline">Review students</a>
            </div>
        <?php endif; ?>

        <!-- ============ STAT TILES ============ -->
        <div class="grid grid-4">
            <div class="stat"><span class="ico">👩‍🎓</span><span class="n"><?= (int)$stats['students'] ?></span><span class="l">Students</span></div>
            <div class="stat" style="border-left-color:#2563EB"><span class="ico">👩‍🏫</span><span class="n"><?= (int)$stats['teachers'] ?></span><span class="l">Teachers</span></div>
            <div class="stat" style="border-left-color:#7C2D5C"><span class="ico">🎓</span><span class="n"><?= (int)$stats['batches'] ?></span><span class="l">Batches</span></div>
            <div class="stat" style="border-left-color:#F59E0B"><span class="ico">⏳</span><span class="n"><?= (int)$stats['pending'] ?></span><span class="l">Pending</span></div>
        </div>

        <!-- ============ CHARTS ROW 1 ============ -->
        <div class="grid grid-2 mt-3">
            <div class="chart-card">
                <h3>📈 Attendance trend</h3>
                <p class="chart-sub">Monthly attendance percentage over the last 6 months.</p>
                <div class="chart-wrap">
                    <canvas id="chart-attendance"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3>👥 User breakdown</h3>
                <p class="chart-sub">Active and pending users split by role.</p>
                <div class="chart-wrap chart-donut">
                    <canvas id="chart-users"></canvas>
                </div>
            </div>
        </div>

        <!-- ============ CHARTS ROW 2 ============ -->
        <div class="chart-card mt-3">
            <h3>🏆 Participation by category</h3>
            <p class="chart-sub">How many times students from your school registered for each activity category.</p>
            <div class="chart-wrap">
                <canvas id="chart-participation"></canvas>
            </div>
        </div>

        <!-- ============ WEEKLY PARENT SUMMARIES ============ -->
        <?php if (!empty($school['allow_weekly_summary'])): ?>
            <?php $weeklyEnabled = !empty($school['weekly_summary_enabled']); ?>
            <div class="card mt-3">
                <div class="flex between" style="align-items:flex-start;flex-wrap:wrap;gap:1rem">
                    <div style="flex:1; min-width:240px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <h3 style="margin:0;">📧 Weekly Parent Summaries</h3>
                            <span class="badge badge-<?= $weeklyEnabled ? 'success' : 'primary' ?>" style="font-size:.72rem;">
                                <?= $weeklyEnabled ? 'Active' : 'Paused' ?>
                            </span>
                        </div>
                        <p class="muted" style="font-size:.88rem; margin-top:.35rem; line-height:1.4;">
                            Send parents an overview of their child's attendance, certificates, tests, and upcoming activities.
                            Use the slider on the right to toggle weekly summaries <strong>Yes</strong> or <strong>No</strong> for your school.
                        </p>
                    </div>

                    <!-- Slide Yes / No Switch -->
                    <form method="post" action="/school/toggle-weekly-summary-handler.php" style="display:inline-flex; align-items:center; gap:12px; margin:0;" id="weeklySummaryToggleForm">
                        <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                        <span style="font-size:13px; font-weight:700; color:<?= $weeklyEnabled ? 'var(--il-success)' : 'var(--il-muted)' ?>;">
                            <?= $weeklyEnabled ? 'Enabled (Yes)' : 'Disabled (No)' ?>
                        </span>
                        <label class="il-switch" title="Slide Yes / No to enable or disable weekly parent summaries">
                            <input type="checkbox" name="weekly_summary_enabled" value="1" <?= $weeklyEnabled ? 'checked' : '' ?> onchange="document.getElementById('weeklySummaryToggleForm').submit();">
                            <span class="il-slider"></span>
                        </label>
                    </form>
                </div>

                <div class="divider"></div>

                <?php if ($weeklyEnabled): ?>
                    <form method="post" action="/school/send-parent-summaries-handler.php"
                          onsubmit="return confirm('Send weekly summaries to your school\'s parents now?\n\nParents already emailed this week will be skipped.')">
                        <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">

                        <div class="form-row" style="align-items:flex-end;">
                            <div class="form-group" style="flex:1;">
                                <label style="font-weight:600;">Week starting</label>
                                <input type="date" name="week_start"
                                       value="<?= Helper::e(ParentSummary::defaultWeekStart()) ?>">
                                <small>The 7 days starting from this date will be summarized.</small>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <button class="btn btn-primary">📤 Send parent summaries now</button>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div style="background:var(--surface-2); padding:12px 16px; border-radius:10px; border:1px dashed var(--il-border); display:flex; align-items:center; gap:10px;">
                        <span style="font-size:20px;">⏸️</span>
                        <div style="font-size:.86rem; color:var(--il-muted);">
                            <strong>Weekly summaries are currently turned OFF (No).</strong> Slide the toggle to <strong>Yes</strong> whenever you want to resume sending parent summaries.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ============ PENDING QUEUE ============ -->
        <div class="card mt-3">
            <div class="flex between">
                <h3>⏳ Pending approvals</h3>
                <span class="pill"><?= count($pending) ?></span>
            </div>
            <div class="divider"></div>

            <?php if (!$pending): ?>
                <p class="muted">Nothing pending. Your school is fully up to date 🎉</p>
            <?php else: ?>
                <?php foreach (array_slice($pending, 0, 5) as $p): ?>
                    <div class="kv" style="align-items:flex-start">
                        <div style="flex:1">
                            <div class="flex" style="align-items:center;gap:.5rem;flex-wrap:wrap">
                                <strong><?= Helper::e($p['full_name'] ?: '—') ?></strong>
                                <span class="badge badge-primary"><?= Helper::e($p['role']) ?></span>
                            </div>
                            <div class="muted" style="font-size:.85rem"><?= Helper::e($p['email']) ?></div>
                        </div>
                        <div class="flex">
                            <form method="post" action="/school/user-action-handler.php" style="display:inline">
                                <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                <input type="hidden" name="user_id" value="<?= (int)$p['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="post" action="/school/user-action-handler.php" style="display:inline">
                                <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                <input type="hidden" name="user_id" value="<?= (int)$p['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ============ RECENT SIGNUPS ============ -->
        <div class="card mt-3">
            <h3>🆕 Recent signups</h3>
            <div class="divider"></div>
            <?php if (!$recent): ?>
                <p class="muted">No users have signed up under your school yet.</p>
            <?php else: foreach ($recent as $r): ?>
                <div class="kv">
                    <div class="flex" style="gap:.65rem">
                        <?php if (!empty($r['avatar'])): ?>
                            <img class="avatar" src="<?= Helper::e($r['avatar']) ?>" alt="">
                        <?php else: ?>
                            <span class="avatar avatar-initial" data-role="<?= Helper::e($r['role']) ?>">
                                <?= Helper::e(mb_strtoupper(mb_substr($r['full_name'] ?: $r['email'], 0, 1))) ?>
                            </span>
                        <?php endif; ?>
                        <div>
                            <strong><?= Helper::e($r['full_name'] ?: '—') ?></strong>
                            <div class="muted" style="font-size:.82rem"><?= Helper::e($r['email']) ?> · <?= Helper::e($r['role']) ?></div>
                        </div>
                    </div>
                    <span class="badge badge-<?= $r['status'] === 'active' ? 'success' : ($r['status'] === 'pending' ? 'primary' : 'muted') ?>">
                        <?= Helper::e($r['status']) ?>
                    </span>
                </div>
            <?php endforeach; endif; ?>
        </div>

    </div>
</div>

<!-- ============================================================
     CHART.JS
     ============================================================ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js failed to load from CDN.');
        return;
    }

    const isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const textColor = isDark ? '#DCC8B8' : '#7A5C4A';
    const gridColor = isDark ? 'rgba(220,200,184,.10)' : 'rgba(122,92,74,.10)';
    const tooltipBg = '#2B1B12';
    const tooltipTxt = '#FFFFFF';

    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = textColor;
    Chart.defaults.animation.duration = 400;

    const commonTooltip = {
        backgroundColor: tooltipBg,
        titleColor: tooltipTxt,
        bodyColor: tooltipTxt,
        padding: 10,
        cornerRadius: 8,
        displayColors: false
    };

    // ---- Attendance line -----------------------------------------------
    (function () {
        const el = document.getElementById('chart-attendance');
        if (!el) return;

        new Chart(el, {
            type: 'line',
            data: {
                labels: <?= json_encode($trendLabels) ?>,
                datasets: [{
                    label: 'Attendance %',
                    data: <?= json_encode($trendValues) ?>,
                    borderColor: '#FF7A18',
                    backgroundColor: 'rgba(255,122,24,.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: isDark ? '#1A120C' : '#fff',
                    pointBorderColor: '#FF7A18',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: Object.assign({}, commonTooltip, {
                        callbacks: { label: ctx => ` ${ctx.parsed.y}%` }
                    })
                },
                scales: {
                    y: {
                        beginAtZero: true, max: 100,
                        ticks: { color: textColor, callback: v => v + '%' },
                        grid: { color: gridColor, drawBorder: false }
                    },
                    x: {
                        ticks: { color: textColor },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    })();

    // ---- User breakdown doughnut ---------------------------------------
    (function () {
        const el = document.getElementById('chart-users');
        if (!el) return;

        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: ['Active students', 'Pending students', 'Active teachers', 'Pending teachers'],
                datasets: [{
                    data: <?= json_encode($userSplitData) ?>,
                    backgroundColor: ['#16A34A', '#F59E0B', '#2563EB', '#7C2D5C'],
                    borderColor: isDark ? '#1A120C' : '#fff',
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            font: { size: 12, weight: '600' },
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8
                        }
                    },
                    tooltip: Object.assign({}, commonTooltip, {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed}`
                        }
                    })
                }
            }
        });
    })();

    // ---- Participation bar ---------------------------------------------
    (function () {
        const el = document.getElementById('chart-participation');
        if (!el) return;

        new Chart(el, {
            type: 'bar',
            data: {
                labels: <?= json_encode($catLabels) ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?= json_encode($catValues) ?>,
                    backgroundColor: '#FF7A18',
                    hoverBackgroundColor: '#FF9F45',
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: commonTooltip
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: textColor, precision: 0 },
                        grid: { color: gridColor, drawBorder: false }
                    },
                    x: {
                        ticks: { color: textColor },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    })();
})();
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
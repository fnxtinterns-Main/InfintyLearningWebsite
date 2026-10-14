<?php
use App\Auth;
use App\Analytics;
use App\Batch;
use App\Helper;

Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

$teacherUserId = $role === 'teacher' ? (int)Auth::user()['id'] : null;
$myBatches = Batch::all($teacherUserId);

$batchId = (int)($_GET['batch'] ?? 0);

if (!$batchId && !empty($myBatches)) {
    $batchId = (int)$myBatches[0]['id'];
}

$currentBatch = null;
if ($batchId) {
    foreach ($myBatches as $b) {
        if ((int)$b['id'] === $batchId) { $currentBatch = $b; break; }
    }
    if (!$currentBatch && $role === 'teacher') {
        $batchId = !empty($myBatches) ? (int)$myBatches[0]['id'] : 0;
        $currentBatch = $myBatches[0] ?? null;
    }
}

$attendanceSummary = $batchId ? Analytics::batchAttendanceSummary($batchId, 30) : null;
$studentPerf       = $batchId ? Analytics::batchStudentPerformance($batchId, 30) : [];
$monthlyTrend      = $batchId ? Analytics::batchAttendanceByMonth($batchId, 6) : [];

$pageTitle = 'Analytics — Infinity Learning';

// ---- Prepare chart data ------------------------------------------------
$trendLabels = [];
$trendValues = [];
foreach ($monthlyTrend as $t) {
    $trendLabels[] = date('M y', strtotime($t['ym'] . '-01'));
    $trendValues[] = (int)$t['pct'];
}

$studentLabels = [];
$studentValues = [];
foreach ($studentPerf as $s) {
    $name = $s['full_name'] ?: $s['email'];
    // Trim long names for axis labels
    if (mb_strlen($name) > 20) $name = mb_substr($name, 0, 18) . '…';
    $studentLabels[] = $name;
    $studentValues[] = (int)$s['present_pct'];
}

require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <div>

        <div class="dashboard-header">
            <div class="dashboard-header-text">
                <span class="kicker">📈 ANALYTICS</span>
                <h1>Batch analytics</h1>
                <p class="muted">Attendance and performance insights for your batches.</p>
            </div>
        </div>

        <?php if (!$myBatches): ?>
            <div class="card text-center mt-3">
                <p class="muted">You don't have any batches yet. <a href="/teacher/batch/new">Create one →</a></p>
            </div>
        <?php else: ?>

            <!-- ===== Batch selector ===== -->
            <div class="card mt-3" style="display:flex;flex-wrap:wrap;gap:.5rem;padding:.75rem">
                <span class="muted" style="font-size:.85rem;font-weight:700;align-self:center;padding:0 .5rem">Batch:</span>
                <?php foreach ($myBatches as $b): ?>
                    <a href="?batch=<?= (int)$b['id'] ?>"
                       class="btn <?= $batchId === (int)$b['id'] ? 'btn-primary' : 'btn-ghost' ?> btn-sm">
                        <?= Helper::e($b['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($currentBatch && $attendanceSummary): ?>

                <!-- ===== KPI tiles ===== -->
                <div class="grid grid-4 mt-3">
                    <div class="stat"><span class="ico">👩‍🎓</span><span class="n"><?= (int)$currentBatch['student_count'] ?></span><span class="l">Students</span></div>
                    <div class="stat"><span class="ico">✅</span><span class="n"><?= (int)$attendanceSummary['present_pct'] ?>%</span><span class="l">Attendance (30d)</span></div>
                    <div class="stat" style="border-left:4px solid #16A34A">
                        <span class="n"><?= (int)$attendanceSummary['present'] ?></span>
                        <span class="l">Present</span>
                    </div>
                    <div class="stat" style="border-left:4px solid #DC2626">
                        <span class="n"><?= (int)$attendanceSummary['absent'] ?></span>
                        <span class="l">Absent</span>
                    </div>
                </div>

                <!-- ===== Charts Row 1 ===== -->
                <div class="grid grid-2 mt-3">
                    <div class="chart-card">
                        <h3>📈 Attendance trend</h3>
                        <p class="chart-sub">Monthly percentage for the last 6 months.</p>
                        <div class="chart-wrap">
                            <canvas id="chart-batch-attendance"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h3>📊 Attendance split</h3>
                        <p class="chart-sub">Present / absent / late / excused over the last 30 days.</p>
                        <div class="chart-wrap chart-donut">
                            <canvas id="chart-batch-split"></canvas>
                        </div>
                    </div>
                </div>

                <!-- ===== Charts Row 2 ===== -->
                <div class="chart-card mt-3">
                    <h3>👩‍🎓 Per-student attendance</h3>
                    <p class="chart-sub">Present percentage per student over the last 30 days. Sorted lowest first so students needing attention appear first.</p>
                    <div class="chart-wrap chart-lg">
                        <canvas id="chart-students"></canvas>
                    </div>
                </div>

                <!-- ===== Per-student table ===== -->
                <div class="card mt-3">
                    <div class="flex between">
                        <h3>👩‍🎓 Student performance</h3>
                        <span class="pill"><?= count($studentPerf) ?> students</span>
                    </div>
                    <div class="divider"></div>

                    <?php if (!$studentPerf): ?>
                        <p class="muted">No data yet — mark some attendance first.</p>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table class="data">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student</th>
                                        <th>Sessions</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Late</th>
                                        <th>Attendance %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($studentPerf as $i => $s):
                                    $pct = (int)$s['present_pct'];
                                    $color = $pct >= 80 ? '#16A34A' : ($pct >= 60 ? '#F59E0B' : '#DC2626');
                                ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <div class="flex">
                                                <?php if (!empty($s['profile_image'])): ?>
                                                    <img class="avatar" src="<?= Helper::e($s['profile_image']) ?>" alt="">
                                                <?php else: ?>
                                                    <span class="avatar avatar-initial" data-role="student">
                                                        <?= Helper::e(mb_strtoupper(mb_substr($s['full_name'] ?: $s['email'], 0, 1))) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <div>
                                                    <a href="/student?id=<?= (int)$s['user_id'] ?>" style="color:inherit">
                                                        <strong><?= Helper::e($s['full_name'] ?: '—') ?></strong>
                                                    </a>
                                                    <div class="muted" style="font-size:.8rem"><?= Helper::e($s['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= (int)$s['total_sessions'] ?></td>
                                        <td><?= (int)$s['present_count'] ?></td>
                                        <td><?= (int)$s['absent_count'] ?></td>
                                        <td><?= (int)$s['late_count'] ?></td>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:.5rem">
                                                <div style="flex:1;height:8px;border-radius:99px;background:var(--surface-2);overflow:hidden">
                                                    <div style="height:100%;width:<?= $pct ?>%;background:<?= $color ?>"></div>
                                                </div>
                                                <strong style="font-size:.82rem;min-width:32px"><?= $pct ?>%</strong>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================
     CHART.JS
     ============================================================ -->
<?php if ($currentBatch && $attendanceSummary): ?>
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

    // ---- Batch attendance trend ----------------------------------------
    (function () {
        const el = document.getElementById('chart-batch-attendance');
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

    // ---- Split doughnut ------------------------------------------------
    (function () {
        const el = document.getElementById('chart-batch-split');
        if (!el) return;

        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late', 'Excused'],
                datasets: [{
                    data: [
                        <?= (int)$attendanceSummary['present'] ?>,
                        <?= (int)$attendanceSummary['absent'] ?>,
                        <?= (int)$attendanceSummary['late'] ?>,
                        <?= (int)$attendanceSummary['excused'] ?>
                    ],
                    backgroundColor: ['#16A34A', '#DC2626', '#F59E0B', '#2563EB'],
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

    // ---- Per-student bar ----------------------------------------------
    (function () {
        const el = document.getElementById('chart-students');
        if (!el) return;

        const values = <?= json_encode($studentValues) ?>;
        const colors = values.map(v => v >= 80 ? '#16A34A' : (v >= 60 ? '#F59E0B' : '#DC2626'));

        new Chart(el, {
            type: 'bar',
            data: {
                labels: <?= json_encode($studentLabels) ?>,
                datasets: [{
                    label: 'Attendance %',
                    data: values,
                    backgroundColor: colors,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: Object.assign({}, commonTooltip, {
                        callbacks: { label: ctx => ` ${ctx.parsed.y}% attendance` }
                    })
                },
                scales: {
                    y: {
                        beginAtZero: true, max: 100,
                        ticks: { color: textColor, callback: v => v + '%' },
                        grid: { color: gridColor, drawBorder: false }
                    },
                    x: {
                        ticks: {
                            color: textColor,
                            maxRotation: 45,
                            minRotation: 45,
                            autoSkip: false,
                            font: { size: 11 }
                        },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    })();
})();
</script>
<?php endif; ?>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
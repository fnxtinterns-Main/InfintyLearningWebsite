<?php
use App\Auth;
use App\Analytics;
use App\Helper;
use App\Database;

Auth::requireSuperAdmin();

$overview      = Analytics::platformOverview();
$scope         = Analytics::scopeBreakdown();
$signups       = Analytics::studentSignupsByMonth(6);
$attendancePct = Analytics::platformAttendancePct();
$topStudents   = Analytics::topParticipants(8);
$byCategory    = Analytics::participationByCategory();
$topBatches    = Analytics::topBatches(8);

$schoolRows = Database::connect()->query("
    SELECT
        s.id, s.name, s.code, s.city, s.state, s.status,
        (SELECT COUNT(*) FROM users u WHERE u.school_id = s.id AND u.role='student' AND u.status='active') AS students,
        (SELECT COUNT(*) FROM users u WHERE u.school_id = s.id AND u.role='teacher' AND u.status='active') AS teachers,
        (SELECT COUNT(*) FROM users u WHERE u.school_id = s.id AND u.status='pending') AS pending,
        (SELECT COUNT(*) FROM batches b WHERE b.school_id = s.id AND b.status='active') AS batches
    FROM schools s
    ORDER BY students DESC
")->fetchAll();

$pageTitle = 'Analytics — Admin';

// ---- Prepare chart data ------------------------------------------------

// Signups (line chart)
$signupLabels = [];
$signupValues = [];
foreach ($signups as $s) {
    $ym = $s['ym'];
    $label = date('M y', strtotime($ym . '-01'));
    $signupLabels[] = $label;
    $signupValues[] = (int)$s['cnt'];
}

// Scope breakdown (doughnut)
$scopeTotals = [
    'global' => (int)$scope['activities']['global'] + (int)$scope['lessons']['global'] + (int)$scope['announcements']['global'],
    'scoped' => (int)$scope['activities']['scoped'] + (int)$scope['lessons']['scoped'] + (int)$scope['announcements']['scoped'],
];

// Top schools (horizontal bar, top 8)
$topSchools = array_slice($schoolRows, 0, 8);
$schoolLabels = [];
$schoolStudents = [];
foreach ($topSchools as $s) {
    $schoolLabels[] = $s['name'];
    $schoolStudents[] = (int)$s['students'];
}

// Participation by category (bar)
$catLabels = [];
$catValues = [];
foreach ($byCategory as $c) {
    $catLabels[] = ucfirst($c['category']);
    $catValues[] = (int)$c['cnt'];
}

// Top batches (horizontal bar)
$batchLabels = [];
$batchValues = [];
foreach ($topBatches as $b) {
    $batchLabels[] = $b['name'];
    $batchValues[] = (int)$b['student_count'];
}

require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/admin/_sidebar.php'; ?>
    <div>
        <h1>Analytics</h1>
        <p class="muted">Platform-wide insights across users, schools, attendance, activities and batches.</p>

        <!-- ============================================================
             KPI TILES
             ============================================================ -->
        <div class="grid grid-4 mt-3">
            <div class="stat"><span class="ico">👩‍🎓</span><span class="n"><?= (int)$overview['students_active'] ?></span><span class="l">Active students</span></div>
            <div class="stat"><span class="ico">👩‍🏫</span><span class="n"><?= (int)$overview['teachers_total'] ?></span><span class="l">Teachers</span></div>
            <div class="stat"><span class="ico">🏫</span><span class="n"><?= (int)$overview['schools'] ?></span><span class="l">Schools</span></div>
            <div class="stat"><span class="ico">✅</span><span class="n"><?= (int)$attendancePct ?>%</span><span class="l">Attendance (30d)</span></div>
        </div>

        <div class="grid grid-4 mt-3">
            <div class="stat"><span class="ico">📚</span><span class="n"><?= (int)$overview['lessons_published'] ?></span><span class="l">Lessons</span></div>
            <div class="stat"><span class="ico">🏆</span><span class="n"><?= (int)$overview['activities_total'] ?></span><span class="l">Activities</span></div>
            <div class="stat"><span class="ico">✍️</span><span class="n"><?= (int)$overview['registrations'] ?></span><span class="l">Registrations</span></div>
            <div class="stat"><span class="ico">🗂️</span><span class="n"><?= (int)$overview['attendance_records'] ?></span><span class="l">Attendance rows</span></div>
        </div>

        <!-- ============================================================
             SIGNUPS LINE + SCOPE DOUGHNUT
             ============================================================ -->
        <div class="grid grid-2 mt-3">
            <div class="chart-card">
                <h3>📈 Student signups</h3>
                <p class="chart-sub">New registrations over the last 6 months.</p>
                <div class="chart-wrap">
                    <canvas id="chart-signups"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3>🌐 Content scope</h3>
                <p class="chart-sub">Global vs school-scoped content across activities, lessons and announcements.</p>
                <div class="chart-wrap chart-donut">
                    <canvas id="chart-scope"></canvas>
                </div>
            </div>
        </div>

        <!-- ============================================================
             CATEGORY BAR + BATCH BAR
             ============================================================ -->
        <div class="grid grid-2 mt-3">
            <div class="chart-card">
                <h3>🏆 Participation by category</h3>
                <p class="chart-sub">Activity registrations grouped by category.</p>
                <div class="chart-wrap">
                    <canvas id="chart-categories"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3>🎓 Top batches</h3>
                <p class="chart-sub">Active batches with the most enrolled students.</p>
                <div class="chart-wrap">
                    <canvas id="chart-batches"></canvas>
                </div>
            </div>
        </div>

        <!-- ============================================================
             TOP SCHOOLS BAR
             ============================================================ -->
        <div class="chart-card mt-3">
            <h3>🏫 Student distribution across schools</h3>
            <p class="chart-sub">Top 8 schools by active student count.</p>
            <div class="chart-wrap">
                <canvas id="chart-schools"></canvas>
            </div>
        </div>

        <!-- ============================================================
             PER-SCHOOL TABLE
             ============================================================ -->
        <div class="card mt-3">
            <div class="flex between">
                <h3>🏫 All schools</h3>
                <a href="/admin/schools" class="btn btn-ghost btn-sm">Manage</a>
            </div>
            <div class="divider"></div>

            <?php if (!$schoolRows): ?>
                <p class="muted">No schools registered yet.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>School</th>
                                <th>Students</th>
                                <th>Teachers</th>
                                <th>Batches</th>
                                <th>Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($schoolRows as $sr): ?>
                            <tr>
                                <td>
                                    <a href="/admin/users?school=<?= (int)$sr['id'] ?>" style="color:inherit">
                                        <strong><?= Helper::e($sr['name']) ?></strong>
                                    </a>
                                    <?php if ($sr['code']): ?>
                                        <div class="muted" style="font-size:.75rem"><?= Helper::e($sr['code']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= (int)$sr['students'] ?></td>
                                <td><?= (int)$sr['teachers'] ?></td>
                                <td><?= (int)$sr['batches'] ?></td>
                                <td>
                                    <?php if ((int)$sr['pending'] > 0): ?>
                                        <span class="badge badge-primary"><?= (int)$sr['pending'] ?></span>
                                    <?php else: ?>
                                        <span class="muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================================================
             TOP PARTICIPANTS
             ============================================================ -->
        <div class="card mt-3">
            <h3>🥇 Top participants</h3>
            <div class="divider"></div>
            <?php if (!$topStudents): ?>
                <p class="muted">No registrations yet.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>#</th><th>Student</th><th>Participations</th></tr></thead>
                        <tbody>
                        <?php foreach ($topStudents as $i => $s): ?>
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
                                            <a href="/student?id=<?= (int)$s['id'] ?>" style="color:inherit">
                                                <strong><?= Helper::e($s['full_name'] ?: '—') ?></strong>
                                            </a>
                                            <div class="muted" style="font-size:.8rem"><?= Helper::e($s['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-primary"><?= (int)$s['participation_count'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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

    // ---- Shared theme helpers -----------------------------------------
    const isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const textColor  = isDark ? '#DCC8B8' : '#7A5C4A';
    const gridColor  = isDark ? 'rgba(220,200,184,.10)' : 'rgba(122,92,74,.10)';
    const tooltipBg  = isDark ? '#2B1B12' : '#2B1B12';
    const tooltipTxt = '#FFFFFF';

    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = textColor;
    Chart.defaults.animation.duration = 400;

    const commonScales = (opts = {}) => ({
        y: Object.assign({
            beginAtZero: true,
            ticks: { color: textColor, precision: 0 },
            grid: { color: gridColor, drawBorder: false }
        }, opts.y || {}),
        x: Object.assign({
            ticks: { color: textColor },
            grid: { display: false, drawBorder: false }
        }, opts.x || {})
    });

    const commonTooltip = {
        backgroundColor: tooltipBg,
        titleColor: tooltipTxt,
        bodyColor: tooltipTxt,
        padding: 10,
        cornerRadius: 8,
        displayColors: false
    };

    // ---- Signups line chart -------------------------------------------
    (function () {
        const el = document.getElementById('chart-signups');
        if (!el) return;

        new Chart(el, {
            type: 'line',
            data: {
                labels: <?= json_encode($signupLabels) ?>,
                datasets: [{
                    label: 'Signups',
                    data: <?= json_encode($signupValues) ?>,
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
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y} student${ctx.parsed.y === 1 ? '' : 's'}`
                        }
                    })
                },
                scales: commonScales()
            }
        });
    })();

    // ---- Scope doughnut -----------------------------------------------
    (function () {
        const el = document.getElementById('chart-scope');
        if (!el) return;

        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: ['Global', 'School-scoped'],
                datasets: [{
                    data: [<?= (int)$scopeTotals['global'] ?>, <?= (int)$scopeTotals['scoped'] ?>],
                    backgroundColor: ['#2563EB', '#FF7A18'],
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
                            font: { size: 13, weight: '600' },
                            padding: 14,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: Object.assign({}, commonTooltip, {
                        callbacks: {
                            label: ctx => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0) || 1;
                                const pct = Math.round(ctx.parsed / total * 100);
                                return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                            }
                        }
                    })
                }
            }
        });
    })();

    // ---- Participation by category (bar) ------------------------------
    (function () {
        const el = document.getElementById('chart-categories');
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
                scales: commonScales()
            }
        });
    })();

    // ---- Top batches (horizontal bar) ---------------------------------
    (function () {
        const el = document.getElementById('chart-batches');
        if (!el) return;

        new Chart(el, {
            type: 'bar',
            data: {
                labels: <?= json_encode($batchLabels) ?>,
                datasets: [{
                    label: 'Students',
                    data: <?= json_encode($batchValues) ?>,
                    backgroundColor: '#7C2D5C',
                    hoverBackgroundColor: '#A0386B',
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 28
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: commonTooltip
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { color: textColor, precision: 0 },
                        grid: { color: gridColor, drawBorder: false }
                    },
                    y: {
                        ticks: { color: textColor },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    })();

    // ---- Top schools (horizontal bar) ---------------------------------
    (function () {
        const el = document.getElementById('chart-schools');
        if (!el) return;

        new Chart(el, {
            type: 'bar',
            data: {
                labels: <?= json_encode($schoolLabels) ?>,
                datasets: [{
                    label: 'Students',
                    data: <?= json_encode($schoolStudents) ?>,
                    backgroundColor: '#FF7A18',
                    hoverBackgroundColor: '#FF9F45',
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 26
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: commonTooltip
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { color: textColor, precision: 0 },
                        grid: { color: gridColor, drawBorder: false }
                    },
                    y: {
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
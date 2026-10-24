<?php
use App\Auth;
use App\Helper;
use App\AcademicAssessment;
use App\Batch;
use App\Lesson;
use App\School;

Auth::requireRole('school_admin', 'admin');
$user = Auth::user();
$schoolId = (int)($user['school_id'] ?? 0);
if (!$schoolId && Auth::isSuperAdmin() && !empty($_GET['school_id'])) {
    $schoolId = (int)$_GET['school_id'];
}
if (!$schoolId) {
    $schoolId = 1; // Default
}

$school = School::find($schoolId);
$batches = Batch::all();
$subjects = Lesson::subjects();
$examTypes = AcademicAssessment::getExamTypes();

$selectedType = $_GET['exam_type'] ?? '';
$selectedBatchId = !empty($_GET['batch_id']) ? (int)$_GET['batch_id'] : 0;
$selectedSubjectId = !empty($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

$filters = [
    'exam_type'  => $selectedType ?: null,
    'batch_id'   => $selectedBatchId ?: null,
    'subject_id' => $selectedSubjectId ?: null,
];

$analytics = AcademicAssessment::getSchoolAnalytics($schoolId, $filters);
$summary = $analytics['summary'] ?? [];
$classToppers = $analytics['class_toppers'] ?? [];
$classStats = $analytics['class_stats'] ?? [];
$subjectStats = $analytics['subject_stats'] ?? [];
$records = $analytics['records'] ?? [];

$pageTitle = 'Academic Results & Examination Analytics — School Admin';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div style="flex:1; min-width:0; padding:20px;">
        <!-- HEADER -->
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px; align-items:center;">
            <div>
                <span class="kicker">🏆 SCHOLASTIC OVERVIEW</span>
                <h1 style="margin:2px 0 0; font-size:26px; font-weight:800;">Academic Results &amp; Analytics</h1>
                <p class="muted">Live overview of class averages, class toppers, subject performance trends, and itemized marks across Unit Tests, Pen-Paper Tests, Mid-Term, and Final Exams.</p>
            </div>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <button onclick="exportResultsCSV()" class="btn btn-outline-secondary btn-sm" style="font-weight:700;">
                    📥 Export CSV
                </button>
                <button onclick="window.print()" class="btn btn-primary btn-sm" style="font-weight:700;">
                    🖨️ Print Results
                </button>
            </div>
        </div>

        <!-- 1. TOP KPI CARDS -->
        <div class="grid grid-4 mt-2" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:14px;">
            <div class="stat" style="border-left-color:#2563EB;">
                <span class="ico">📊</span>
                <span class="n"><?= $summary['overall_average'] ?? 0 ?>%</span>
                <span class="l">School Average Marks</span>
            </div>
            <div class="stat" style="border-left-color:#16A34A;">
                <span class="ico">✅</span>
                <span class="n"><?= $summary['pass_rate'] ?? 0 ?>%</span>
                <span class="l">Scholastic Pass Rate</span>
            </div>
            <div class="stat" style="border-left-color:#7C3AED;">
                <span class="ico">📝</span>
                <span class="n"><?= $summary['total_evaluations'] ?? 0 ?></span>
                <span class="l">Total Student Evaluations</span>
            </div>
            <div class="stat" style="border-left-color:#D97706;">
                <span class="ico">🏫</span>
                <span class="n"><?= count($classStats) ?> Classes / <?= count($subjectStats) ?> Subjects</span>
                <span class="l">Active Academic Disciplines</span>
            </div>
        </div>

        <!-- 2. CLASS TOPPERS FOR EACH CLASS -->
        <div class="card mt-3" style="border-radius:16px;">
            <div class="flex between" style="align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
                        <span>👑</span> Class Toppers &amp; Honor Roll
                    </h3>
                    <p class="muted" style="margin:2px 0 0; font-size:13px;">Top rankers with highest cumulative marks % computed across examinations.</p>
                </div>
                <span class="badge badge-warning" style="font-weight:700;">🏅 Academic Excellence</span>
            </div>
            <div class="divider"></div>

            <?php if (empty($classToppers)): ?>
                <div style="text-align:center; padding:28px 20px; background:var(--surface-1); border-radius:12px; border:1px dashed var(--il-border);">
                    <span style="font-size:32px;">🏆</span>
                    <h4 style="margin:6px 0 2px;">No Exam Records Yet</h4>
                    <p class="muted" style="margin:0; font-size:13px;">When teachers enter unit test or exam marks, class toppers for each class will automatically be calculated and celebrated here.</p>
                </div>
            <?php else: ?>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:14px;">
                    <?php foreach ($classToppers as $classLevel => $topGroup): 
                        $top = $topGroup['topper'];
                        $runner = $topGroup['runner_up'];
                        $third = $topGroup['third'];
                    ?>
                        <div style="background:linear-gradient(135deg, rgba(245,158,11,0.06), rgba(99,102,241,0.06)); border:1.5px solid rgba(245,158,11,0.3); border-radius:14px; padding:18px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <span class="badge badge-brand" style="font-size:12px; font-weight:800; background:#fef3c7; color:#92400e; border:1px solid #fde68a;">
                                    🏫 <?= Helper::e($classLevel) ?>
                                </span>
                                <span style="font-size:11px; font-weight:700; color:#b45309;">Rank #1 Topper</span>
                            </div>

                            <!-- Rank 1 Topper Hero -->
                            <div style="display:flex; align-items:center; gap:12px; padding:10px; background:#fff; border-radius:10px; border:1px solid rgba(245,158,11,0.25); box-shadow:0 2px 6px rgba(0,0,0,0.04);">
                                <div style="font-size:26px;">🥇</div>
                                <div style="flex:1; min-width:0;">
                                    <a href="/student?id=<?= (int)$top['student_id'] ?>" style="font-weight:800; font-size:15px; color:var(--il-text); text-decoration:none;" class="hover-underline">
                                        <?= Helper::e($top['name']) ?>
                                    </a>
                                    <div class="muted" style="font-size:11px;"><?= $top['tests'] ?> tests evaluated</div>
                                </div>
                                <div style="text-align:right;">
                                    <span class="badge badge-success" style="font-size:13px; font-weight:800; padding:4px 8px;">
                                        <?= $top['avg_pct'] ?>%
                                    </span>
                                </div>
                            </div>

                            <!-- 2nd and 3rd Runners Up -->
                            <?php if ($runner || $third): ?>
                                <div style="margin-top:10px; display:flex; flex-direction:column; gap:6px; font-size:12px;">
                                    <?php if ($runner): ?>
                                        <div style="display:flex; justify-content:space-between; align-items:center; padding:4px 8px; border-radius:6px; background:var(--surface-1);">
                                            <span>🥈 <strong><?= Helper::e($runner['name']) ?></strong></span>
                                            <span style="font-weight:700; color:#64748b;"><?= $runner['avg_pct'] ?>%</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($third): ?>
                                        <div style="display:flex; justify-content:space-between; align-items:center; padding:4px 8px; border-radius:6px; background:var(--surface-1);">
                                            <span>🥉 <strong><?= Helper::e($third['name']) ?></strong></span>
                                            <span style="font-weight:700; color:#64748b;"><?= $third['avg_pct'] ?>%</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3. CLASS AVERAGES & SUBJECT PERFORMANCE BREAKDOWN -->
        <div class="grid grid-2 mt-3">
            <!-- CLASS-WISE AVERAGE MARKS -->
            <div class="card" style="border-radius:16px;">
                <h3 style="margin:0 0 4px; font-size:17px; display:flex; align-items:center; gap:8px;">
                    <span>🏫</span> Average Marks of All Classes
                </h3>
                <p class="muted" style="font-size:12.5px; margin:0 0 12px;">Comparative performance across each class level.</p>
                <div class="divider"></div>

                <?php if (empty($classStats)): ?>
                    <p class="muted" style="text-align:center; padding:20px 0;">No class performance data recorded yet.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php foreach ($classStats as $cs): 
                            $pct = (float)$cs['avg_pct'];
                            $barColor = $pct >= 75 ? '#16A34A' : ($pct >= 50 ? '#2563EB' : '#D97706');
                        ?>
                            <div>
                                <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; margin-bottom:4px;">
                                    <span><?= Helper::e($cs['class']) ?></span>
                                    <span style="color:<?= $barColor ?>;"><?= $pct ?>% Avg (<?= $cs['count'] ?> tests)</span>
                                </div>
                                <div style="background:var(--surface-2); border-radius:999px; height:9px; overflow:hidden; border:1px solid var(--il-border);">
                                    <div style="background:<?= $barColor ?>; height:100%; width:<?= min(100, $pct) ?>%; border-radius:999px; transition:width 0.5s ease;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SUBJECT-WISE AVERAGE MARKS -->
            <div class="card" style="border-radius:16px;">
                <h3 style="margin:0 0 4px; font-size:17px; display:flex; align-items:center; gap:8px;">
                    <span>📚</span> Average Marks of Each Subject
                </h3>
                <p class="muted" style="font-size:12.5px; margin:0 0 12px;">Overall subject mastery across all tests conducted.</p>
                <div class="divider"></div>

                <?php if (empty($subjectStats)): ?>
                    <p class="muted" style="text-align:center; padding:20px 0;">No subject performance data recorded yet.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php foreach ($subjectStats as $ss): 
                            $pct = (float)$ss['avg_pct'];
                            $barColor = $pct >= 75 ? '#16A34A' : ($pct >= 50 ? '#7C3AED' : '#F59E0B');
                        ?>
                            <div>
                                <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; margin-bottom:4px;">
                                    <span><?= Helper::e($ss['subject']) ?></span>
                                    <span style="color:<?= $barColor ?>;"><?= $pct ?>% (<?= $ss['count'] ?> entries)</span>
                                </div>
                                <div style="background:var(--surface-2); border-radius:999px; height:9px; overflow:hidden; border:1px solid var(--il-border);">
                                    <div style="background:<?= $barColor ?>; height:100%; width:<?= min(100, $pct) ?>%; border-radius:999px; transition:width 0.5s ease;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. FILTER BAR -->
        <div class="card mt-3" style="padding:16px 20px; border-radius:14px;">
            <form method="get" action="/school/results" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:14px; align-items:flex-end;">
                <div>
                    <label style="font-size:12px; font-weight:700; display:block; margin-bottom:4px;">Exam Category</label>
                    <select name="exam_type" class="form-control" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="unit_test"  <?= $selectedType === 'unit_test'  ? 'selected' : '' ?>>📝 Unit Test (UT)</option>
                        <option value="pen_paper"  <?= $selectedType === 'pen_paper'  ? 'selected' : '' ?>>📄 Pen &amp; Paper Test (PPT)</option>
                        <option value="mid_term"   <?= $selectedType === 'mid_term'   ? 'selected' : '' ?>>📊 Mid-Term Exam</option>
                        <option value="final_term" <?= $selectedType === 'final_term' ? 'selected' : '' ?>>🎓 Final Term Exam</option>
                    </select>
                </div>

                <div>
                    <label style="font-size:12px; font-weight:700; display:block; margin-bottom:4px;">Batch / Cohort</label>
                    <select name="batch_id" class="form-control" onchange="this.form.submit()">
                        <option value="0">All Batches</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" <?= (int)$b['id'] === $selectedBatchId ? 'selected' : '' ?>>
                                <?= Helper::e($b['name']) ?> (<?= Helper::e($b['class_level'] ?? 'All') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="font-size:12px; font-weight:700; display:block; margin-bottom:4px;">Subject</label>
                    <select name="subject_id" class="form-control" onchange="this.form.submit()">
                        <option value="0">All Subjects</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= (int)$s['id'] ?>" <?= (int)$s['id'] === $selectedSubjectId ? 'selected' : '' ?>>
                                <?= Helper::e($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <a href="/school/results" class="btn btn-secondary btn-sm" style="display:block; text-align:center; height:38px; line-height:24px;">Reset Filters</a>
                </div>
            </form>
        </div>

        <!-- 5. DETAILED MARKS BREAKDOWN TABLE -->
        <div class="card mt-3" style="padding:0; border-radius:14px; overflow:hidden;">
            <div style="padding:16px 20px; background:var(--surface-2); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <h3 style="margin:0; font-size:16px;">Itemized Student Marks Register (<?= count($records) ?> Evaluations)</h3>
                    <p class="muted" style="margin:2px 0 0; font-size:12px;">Detailed scores, status, and quick links to student profiles and report cards.</p>
                </div>
                <div>
                    <input type="text" id="filterInput" placeholder="🔍 Quick search scholar name..." onkeyup="filterResultsTable()" class="form-control" style="font-size:12.5px; width:240px;">
                </div>
            </div>

            <div class="table-wrap" style="margin:0;">
                <table class="data" id="resultsTable" style="margin:0; font-size:13px;">
                    <thead>
                        <tr style="background:var(--surface-1);">
                            <th>Scholar Name</th>
                            <th>Class / Batch</th>
                            <th>Exam / Assessment Title</th>
                            <th>Category</th>
                            <th>Subject</th>
                            <th>Score / Max</th>
                            <th>Percentage</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="9" style="text-align:center; padding:32px;" class="muted">
                                    No student marks match the selected filters.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $r): 
                                $score = (float)$r['score_obtained'];
                                $max = (float)($r['total_marks'] ?: 1.0);
                                $pct = round(($score / $max) * 100, 1);
                                $passed = $score >= (float)$r['passing_marks'];
                                $typeKey = $r['assessment_type'];
                                $typeLabel = $examTypes[$typeKey] ?? ucfirst(str_replace('_',' ', $typeKey));
                            ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <div style="width:30px; height:30px; border-radius:50%; background:var(--surface-2); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:11px; border:1px solid var(--il-border); flex-shrink:0;">
                                                <?= strtoupper(substr($r['student_name'] ?: 'S', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <a href="/student?id=<?= (int)$r['student_user_id'] ?>" style="font-weight:700; color:var(--il-text); text-decoration:none;" class="hover-underline">
                                                    <?= Helper::e($r['student_name']) ?>
                                                </a>
                                                <div class="muted" style="font-size:11px;"><?= Helper::e($r['student_email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="pill" style="font-size:11px;"><?= Helper::e($r['class_level']) ?></span>
                                        <?php if (!empty($r['batch_name'])): ?>
                                            <div class="muted" style="font-size:10.5px;"><?= Helper::e($r['batch_name']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= Helper::e($r['assessment_title']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-brand" style="font-size:10.5px;">
                                            <?= Helper::e($typeLabel) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-weight:600;"><?= Helper::e($r['subject_name']) ?></span>
                                    </td>
                                    <td>
                                        <strong style="font-size:14px; color:<?= $passed ? '#16A34A' : '#DC2626' ?>;">
                                            <?= $r['score_obtained'] ?>
                                        </strong>
                                        <span class="muted" style="font-size:11px;">/ <?= $r['total_marks'] ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight:800; font-size:13px; color:<?= $passed ? '#16A34A' : '#DC2626' ?>;">
                                            <?= $pct ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($passed): ?>
                                            <span class="badge badge-success" style="font-size:10.5px;">✓ Passed</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger" style="font-size:10.5px;">Needs Improvement</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:inline-flex; gap:6px;">
                                            <a href="/student?id=<?= (int)$r['student_user_id'] ?>" class="btn btn-secondary btn-sm" title="View Student Development Profile" style="font-size:11px; padding:3px 8px;">
                                                Profile
                                            </a>
                                            <a href="/student/report-card?student_id=<?= (int)$r['student_user_id'] ?>" class="btn btn-primary btn-sm" title="View Full Report Card" style="font-size:11px; padding:3px 8px;">
                                                Report Card
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterResultsTable() {
    const input = document.getElementById("filterInput");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("resultsTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName("td")[0];
        if (td) {
            let txtValue = td.textContent || td.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

function exportResultsCSV() {
    const table = document.getElementById("resultsTable");
    let csv = [];
    for (let i = 0; i < table.rows.length; i++) {
        let row = [], cols = table.rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length - 1; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/(\s\s+)/g, " ").trim();
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(","));
    }
    const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
    const downloadLink = document.createElement("a");
    downloadLink.download = "School_Academic_Results_" + new Date().toISOString().slice(0,10) + ".csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

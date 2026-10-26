<?php
use App\Auth;
use App\Helper;
use App\Batch;
use App\AcademicAssessment;
use App\Lesson;
use App\Database;
use App\Security;
use App\Session;

Auth::requireRole('teacher');
$user = Auth::user();
$teacherId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

$batches = Batch::all($teacherId);
$selectedBatchId = !empty($_GET['batch_id']) ? (int)$_GET['batch_id'] : (!empty($batches[0]['id']) ? (int)$batches[0]['id'] : 0);
$selectedType = $_GET['type'] ?? 'unit_test';
$selectedAssessmentId = !empty($_GET['assessment_id']) ? (int)$_GET['assessment_id'] : 0;

$subjects = Lesson::subjects();
$examTypes = AcademicAssessment::getExamTypes();

// Find active batch
$currentBatch = null;
foreach ($batches as $b) {
    if ((int)$b['id'] === $selectedBatchId) {
        $currentBatch = $b;
        break;
    }
}

// Find existing assessments for this batch to populate switcher
$batchAssessments = [];
if ($selectedBatchId) {
    try {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT id, title, assessment_type, total_marks, passing_marks, created_at,
                   (SELECT COUNT(*) FROM assessment_submissions WHERE assessment_id = academic_assessments.id AND score_obtained IS NOT NULL) AS evaluated_count
            FROM academic_assessments
            WHERE batch_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$selectedBatchId]);
        $batchAssessments = $stmt->fetchAll();
    } catch (\Throwable $e) {}
}

// Default max & passing marks based on type
$defaultMax = 20.0;
$defaultPass = 8.0;
if ($selectedType === 'pen_paper') {
    $defaultMax = 25.0;
    $defaultPass = 10.0;
} elseif ($selectedType === 'mid_term' || $selectedType === 'final_term') {
    $defaultMax = 80.0;
    $defaultPass = 32.0;
}

$activeAssessment = null;
if ($selectedAssessmentId) {
    $activeAssessment = AcademicAssessment::find($selectedAssessmentId);
    if ($activeAssessment) {
        $selectedType = $activeAssessment['assessment_type'];
        $defaultMax = (float)$activeAssessment['total_marks'];
        $defaultPass = (float)$activeAssessment['passing_marks'];
    }
}

// Handle Form Submission (Save Marks)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batchId = (int)($_POST['batch_id'] ?? 0);
    $examType = trim($_POST['assessment_type'] ?? 'unit_test');
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $totalMarks = (float)($_POST['total_marks'] ?? 20.0);
    $passingMarks = (float)($_POST['passing_marks'] ?? 8.0);
    $examDate = !empty($_POST['due_date']) ? $_POST['due_date'] . ' 23:59:59' : date('Y-m-d 23:59:59');
    $assessmentId = (int)($_POST['assessment_id'] ?? 0);
    $marks = $_POST['marks'] ?? [];

    if (!$title) {
        $typeLabel = $examTypes[$examType] ?? 'Test';
        $title = $typeLabel . ' — ' . date('d M Y');
    }

    try {
        // If updating or creating assessment
        if ($assessmentId > 0) {
            $db = Database::connect();
            $db->prepare("
                UPDATE academic_assessments 
                SET title = ?, total_marks = ?, passing_marks = ?, due_date = ?, assessment_type = ?
                WHERE id = ? AND teacher_user_id = ?
            ")->execute([$title, $totalMarks, $passingMarks, $examDate, $examType, $assessmentId, $teacherId]);
        } else {
            // Create new assessment record
            $assessmentId = AcademicAssessment::create([
                'batch_id'         => $batchId,
                'class_level'      => $currentBatch['class_level'] ?? 'Grade 8',
                'subject_id'       => $subjectId ?: (!empty($subjects[0]['id']) ? (int)$subjects[0]['id'] : 1),
                'title'            => $title,
                'assessment_type'  => $examType,
                'total_marks'      => $totalMarks,
                'passing_marks'    => $passingMarks,
                'duration_minutes' => 60,
                'instructions'     => 'Direct marks recorded by instructor.',
                'questions'        => [],
                'due_date'         => $examDate,
                'status'           => 'published'
            ], $teacherId, $schoolId);
        }

        // Save batch student marks
        $saved = AcademicAssessment::saveBatchMarks($assessmentId, $marks);

        Session::flash('success', "✓ Marks successfully recorded for {$saved} students! Scores will now appear on their dashboard and report cards.");
        Helper::redirect("/teacher/marks?batch_id={$batchId}&assessment_id={$assessmentId}&type={$examType}");
    } catch (\Throwable $e) {
        Session::flash('error', "Could not save marks: " . $e->getMessage());
    }
}

// Fetch students for grid
$students = [];
if ($selectedBatchId) {
    $students = AcademicAssessment::getStudentsForMarksEntry($selectedBatchId, $selectedAssessmentId);
}

$pageTitle = 'Direct Marks Entry — Teacher Hub';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <div style="flex:1; min-width:0; padding:20px;">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px; align-items:center;">
            <div>
                <span class="kicker">✍️ ACADEMIC GRADING</span>
                <h1 style="margin:2px 0 0; font-size:26px; font-weight:800;">Direct Marks Entry Grid</h1>
                <p class="muted">Enter marks for Unit Tests, Pen &amp; Paper Class Tests, Mid-Term, and Final Exams. Marks flow dynamically to student profiles and school report cards.</p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="/teacher/assessment/new" class="btn btn-primary btn-sm">+ Create New Assessment / Quiz</a>
                <a href="/teacher/assessments" class="btn btn-outline-secondary btn-sm">📝 All Assessments</a>
            </div>
        </div>

        <?php if ($msg = Session::flash('success')): ?>
            <div class="alert alert-success" style="margin-bottom:18px;"><?= Helper::e($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = Session::flash('error')): ?>
            <div class="alert alert-danger" style="margin-bottom:18px;"><?= Helper::e($err) ?></div>
        <?php endif; ?>

        <!-- STEP 1: BATCH & EXAM SELECTOR -->
        <div class="card mb-3" style="padding:18px 22px; border-radius:14px;">
            <form method="get" action="/teacher/marks" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:16px; align-items:flex-end;">
                <div>
                    <label style="font-weight:700; font-size:12.5px; display:block; margin-bottom:5px;">1. Select Batch</label>
                    <select name="batch_id" class="form-control" onchange="this.form.submit()">
                        <?php if (empty($batches)): ?>
                            <option value="">No active batches found</option>
                        <?php else: ?>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= (int)$b['id'] ?>" <?= (int)$b['id'] === $selectedBatchId ? 'selected' : '' ?>>
                                    <?= Helper::e($b['name']) ?> (<?= Helper::e($b['class_level'] ?? 'General') ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label style="font-weight:700; font-size:12.5px; display:block; margin-bottom:5px;">2. Exam Category</label>
                    <select name="type" class="form-control" onchange="this.form.submit()">
                        <option value="unit_test"  <?= $selectedType === 'unit_test'  ? 'selected' : '' ?>>📝 Unit Test (UT)</option>
                        <option value="pen_paper"  <?= $selectedType === 'pen_paper'  ? 'selected' : '' ?>>📄 Pen &amp; Paper Test (PPT)</option>
                        <option value="mid_term"   <?= $selectedType === 'mid_term'   ? 'selected' : '' ?>>📊 Mid-Term Exam</option>
                        <option value="final_term" <?= $selectedType === 'final_term' ? 'selected' : '' ?>>🎓 Final Term Exam</option>
                    </select>
                </div>

                <?php if (!empty($batchAssessments)): ?>
                    <div>
                        <label style="font-weight:700; font-size:12.5px; display:block; margin-bottom:5px;">3. Existing Assessment</label>
                        <select name="assessment_id" class="form-control" onchange="this.form.submit()">
                            <option value="0">+ Create / Enter New Assessment Marks</option>
                            <?php foreach ($batchAssessments as $ba): ?>
                                <option value="<?= (int)$ba['id'] ?>" <?= (int)$ba['id'] === $selectedAssessmentId ? 'selected' : '' ?>>
                                    <?= Helper::e($ba['title']) ?> (<?= $ba['evaluated_count'] ?> marked)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- STEP 2: MARKS ENTRY GRID -->
        <?php if (!$selectedBatchId): ?>
            <div class="card text-center" style="padding:40px 20px;">
                <span style="font-size:36px;">🏫</span>
                <h3>No Batch Selected</h3>
                <p class="muted">Please select one of your active batches above to start entering marks.</p>
            </div>
        <?php elseif (empty($students)): ?>
            <div class="card text-center" style="padding:40px 20px;">
                <span style="font-size:36px;">👩‍🎓</span>
                <h3>No Students in this Batch</h3>
                <p class="muted">This batch has no active students enrolled. Add students to the batch first.</p>
                <a href="/teacher/batch?id=<?= $selectedBatchId ?>" class="btn btn-primary btn-sm mt-2">Manage Batch Roster</a>
            </div>
        <?php else: ?>
            <form method="post" action="/teacher/marks">
                <input type="hidden" name="batch_id" value="<?= $selectedBatchId ?>">
                <input type="hidden" name="assessment_id" value="<?= $selectedAssessmentId ?>">
                <input type="hidden" name="assessment_type" value="<?= Helper::e($selectedType) ?>">

                <!-- EXAM METADATA SPECIFICATION -->
                <div class="card mb-3" style="padding:18px 22px; border-radius:14px; background:var(--surface-1); border-left:4px solid var(--il-primary);">
                    <div style="font-size:12px; font-weight:800; text-transform:uppercase; color:var(--il-primary); letter-spacing:0.05em; margin-bottom:8px;">
                        Exam Configuration &amp; Details
                    </div>
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap:14px;">
                        <div>
                            <label style="font-size:12px; font-weight:700;">Subject</label>
                            <select name="subject_id" class="form-control" required style="margin-top:4px;">
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= (int)$s['id'] ?>" <?= (!empty($activeAssessment['subject_id']) && (int)$activeAssessment['subject_id'] === (int)$s['id']) ? 'selected' : '' ?>>
                                        <?= Helper::e($s['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="font-size:12px; font-weight:700;">Exam / Test Title</label>
                            <input type="text" name="title" class="form-control" style="margin-top:4px;"
                                   value="<?= Helper::e($activeAssessment['title'] ?? ($examTypes[$selectedType] ?? 'Test') . ' — ' . date('d M Y')) ?>" required>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:700;">Max Marks</label>
                            <input type="number" step="0.5" min="1" max="500" name="total_marks" class="form-control" style="margin-top:4px;"
                                   value="<?= $defaultMax ?>" required id="maxMarksField">
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:700;">Passing Marks</label>
                            <input type="number" step="0.5" min="0" max="500" name="passing_marks" class="form-control" style="margin-top:4px;"
                                   value="<?= $defaultPass ?>" required id="passMarksField">
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:700;">Exam Date</label>
                            <input type="date" name="due_date" class="form-control" style="margin-top:4px;"
                                   value="<?= !empty($activeAssessment['due_date']) ? date('Y-m-d', strtotime($activeAssessment['due_date'])) : date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <!-- STUDENTS ENTRY TABLE -->
                <div class="card" style="padding:0; border-radius:14px; overflow:hidden;">
                    <div style="padding:16px 20px; background:var(--surface-2); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <div>
                            <strong style="font-size:15px;">Scholar Roster (<?= count($students) ?> Students Enrolled)</strong>
                            <p class="muted" style="margin:2px 0 0; font-size:12px;">Type score for each scholar. Empty rows will be skipped.</p>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <button type="submit" class="btn btn-primary" style="font-weight:700; padding:8px 24px;">
                                💾 Save All Marks (1-Click)
                            </button>
                        </div>
                    </div>

                    <div class="table-wrap" style="margin:0;">
                        <table class="data" style="margin:0;">
                            <thead>
                                <tr style="background:var(--surface-1);">
                                    <th style="width:40px;">#</th>
                                    <th>Scholar Name</th>
                                    <th>Class / Roll</th>
                                    <th style="width:160px;">Marks Obtained</th>
                                    <th style="width:120px;">Percentage</th>
                                    <th>Teacher Remarks / Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $idx => $st): 
                                    $score = $st['score_obtained'] !== null ? (float)$st['score_obtained'] : '';
                                    $pct = ($score !== '' && $defaultMax > 0) ? round(($score / $defaultMax) * 100, 1) : null;
                                ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td>
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                <div style="width:34px; height:34px; border-radius:50%; background:var(--surface-2); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; border:1px solid var(--il-border); flex-shrink:0;">
                                                    <?= strtoupper(substr($st['full_name'] ?: $st['email'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <strong><?= Helper::e($st['full_name'] ?: 'Scholar') ?></strong>
                                                    <div class="muted" style="font-size:11px;"><?= Helper::e($st['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="pill" style="font-size:11px;"><?= Helper::e($st['current_class'] ?: 'Enrolled') ?></span>
                                        </td>
                                        <td>
                                            <div style="display:flex; align-items:center; gap:6px;">
                                                <input type="number" step="0.5" min="0" max="500" 
                                                       name="marks[<?= (int)$st['student_user_id'] ?>][score]" 
                                                       value="<?= $score !== '' ? $score : '' ?>" 
                                                       placeholder="—"
                                                       class="form-control score-input"
                                                       style="width:90px; font-weight:800; font-size:14px; text-align:center;"
                                                       oninput="updateStudentPct(this)">
                                                <span class="muted" style="font-size:12px;">/ <?= $defaultMax ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="pct-badge badge <?= ($pct !== null && $pct >= 40) ? 'badge-success' : ($pct !== null ? 'badge-danger' : 'badge-secondary') ?>" style="font-weight:700; font-size:11px;">
                                                <?= $pct !== null ? "{$pct}%" : 'Not Marked' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <input type="text" name="marks[<?= (int)$st['student_user_id'] ?>][feedback]" 
                                                   value="<?= Helper::e($st['teacher_feedback'] ?? '') ?>" 
                                                   placeholder="e.g. Excellent work, Well prepared..."
                                                   class="form-control" style="font-size:12px;">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="padding:16px 20px; background:var(--surface-1); border-top:1px solid var(--il-border); display:flex; justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary" style="font-weight:700; padding:10px 30px;">
                            💾 Save All Marks (1-Click)
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function updateStudentPct(input) {
    const row = input.closest('tr');
    const badge = row.querySelector('.pct-badge');
    const maxVal = parseFloat(document.getElementById('maxMarksField').value) || 1;
    const scoreVal = parseFloat(input.value);

    if (!isNaN(scoreVal) && scoreVal >= 0) {
        const pct = Math.round((scoreVal / maxVal) * 1000) / 10;
        badge.textContent = pct + '%';
        if (pct >= 40) {
            badge.className = 'pct-badge badge badge-success';
        } else {
            badge.className = 'pct-badge badge badge-danger';
        }
    } else {
        badge.textContent = 'Not Marked';
        badge.className = 'pct-badge badge badge-secondary';
    }
}
</script>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

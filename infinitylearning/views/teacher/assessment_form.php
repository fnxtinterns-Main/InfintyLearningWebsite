<?php
use App\Auth;
use App\Helper;
use App\QuestionBank;
use App\Batch;
use App\Database;

Auth::requireRole('teacher');
$user = Auth::user();
$teacherId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

$db = Database::connect();
$subjects = $db->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();
$batches = Batch::all($teacherId);

$availableQuestions = QuestionBank::list([
    'teacher_user_id' => $teacherId,
    'school_id' => $schoolId,
]);

$pageTitle = 'Create New Assessment — Teacher Hub';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <div>
        <div class="dashboard-header">
            <div>
                <a href="/teacher/assessments" class="btn btn-sm btn-outline-secondary" style="margin-bottom:6px;">← Back to Assessments</a>
                <h1 style="margin:0;">Create Assessment / Exam</h1>
                <p class="muted">Assemble homework or tests by selecting questions from your question bank.</p>
            </div>
        </div>

        <form method="post" action="/teacher/assessment-save-handler.php">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">

            <div class="grid grid-2" style="align-items:start; gap:24px;">
                <!-- LEFT: ASSESSMENT DETAILS -->
                <div class="card" style="padding:24px;">
                    <h3 style="margin-top:0; font-size:17px; margin-bottom:16px;">1. Assessment Configuration</h3>

                    <div style="margin-bottom:14px;">
                        <label class="form-label">Assessment Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Unit 3 Test: Linear Equations or Weekly Homework 4">
                    </div>

                    <div class="grid grid-2" style="margin-bottom:14px;">
                        <div>
                            <label class="form-label">Assessment Type *</label>
                            <select name="assessment_type" class="form-control" required>
                                <option value="homework">Homework Assignment</option>
                                <option value="unit_test">Unit Test</option>
                                <option value="mid_term">Mid-Term Examination</option>
                                <option value="final_term">Final-Term Examination</option>
                                <option value="practice_quiz">Practice Quiz</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Subject *</label>
                            <select name="subject_id" class="form-control" required>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= (int)$s['id'] ?>"><?= Helper::e($s['icon'] ?? '📚') ?> <?= Helper::e($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-2" style="margin-bottom:14px;">
                        <div>
                            <label class="form-label">Assign to Batch</label>
                            <select name="batch_id" class="form-control">
                                <option value="">All Students (Global in School)</option>
                                <?php foreach ($batches as $b): ?>
                                    <option value="<?= (int)$b['id'] ?>"><?= Helper::e($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Duration (Minutes, 0 = Untimed)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="45" min="0">
                        </div>
                    </div>

                    <div class="grid grid-2" style="margin-bottom:14px;">
                        <div>
                            <label class="form-label">Start Date & Time</label>
                            <input type="datetime-local" name="start_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                        <div>
                            <label class="form-label">Due Date & Time *</label>
                            <input type="datetime-local" name="due_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime('+7 days')) ?>" required>
                        </div>
                    </div>

                    <div class="grid grid-2" style="margin-bottom:14px;">
                        <div>
                            <label class="form-label">Total Marks</label>
                            <input type="number" step="0.5" name="total_marks" id="inputTotalMarks" class="form-control" value="20" required>
                        </div>
                        <div>
                            <label class="form-label">Passing Marks</label>
                            <input type="number" step="0.5" name="passing_marks" class="form-control" value="8" required>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Instructions for Students</label>
                        <textarea name="instructions" class="form-control" rows="3" placeholder="Enter guidelines: e.g. Attempt all questions, no calculators allowed, write rough work clearly..."></textarea>
                    </div>
                </div>

                <!-- RIGHT: QUESTION SELECTOR -->
                <div class="card" style="padding:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <h3 style="margin:0; font-size:17px;">2. Pick Questions (<span id="selectedCount">0</span> selected)</h3>
                        <a href="/teacher/questions" target="_blank" class="btn btn-xs btn-outline-primary">+ Add New Question</a>
                    </div>

                    <p class="muted" style="font-size:13px; margin:0 0 14px;">Check the questions below to include in this assessment:</p>

                    <?php if (empty($availableQuestions)): ?>
                        <div style="padding:32px 16px; text-align:center; color:var(--il-muted); background:var(--surface-0); border-radius:12px;">
                            No questions found in your Question Bank.
                            <br><a href="/teacher/questions" class="btn btn-primary btn-sm mt-2">Open Question Bank →</a>
                        </div>
                    <?php else: ?>
                        <div style="max-height:540px; overflow-y:auto; padding-right:6px; display:flex; flex-direction:column; gap:10px;">
                            <?php foreach ($availableQuestions as $q): ?>
                                <label style="display:flex; gap:12px; align-items:flex-start; padding:12px; background:var(--surface-0); border:1px solid var(--il-border); border-radius:10px; cursor:pointer;">
                                    <input type="checkbox" name="question_ids[]" value="<?= (int)$q['id'] ?>" data-marks="<?= (float)$q['marks'] ?>" onchange="updateSelectedQuestions()" style="margin-top:4px; width:18px; height:18px;">
                                    <div style="flex:1;">
                                        <div style="display:flex; gap:6px; align-items:center; margin-bottom:4px; flex-wrap:wrap;">
                                            <span class="badge" style="font-size:10px;"><?= Helper::e($q['subject_name'] ?? 'General') ?></span>
                                            <span class="badge badge-<?= $q['difficulty'] === 'easy' ? 'success' : 'primary' ?>" style="font-size:10px;"><?= ucfirst(Helper::e($q['difficulty'])) ?></span>
                                            <span class="badge" style="background:#FEF3C7; color:#92400E; font-size:10px; font-weight:700;"><?= (float)$q['marks'] ?>m</span>
                                            <span class="muted" style="font-size:11px;"><?= Helper::e($q['question_type']) ?></span>
                                        </div>
                                        <div style="font-size:13px; font-weight:600; color:var(--il-text); line-height:1.4;">
                                            <?= Helper::e(mb_strimwidth($q['question_text'], 0, 140, '...')) ?>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top:20px; border-top:1px solid var(--il-border); padding-top:16px; display:flex; justify-content:flex-end; gap:10px;">
                        <a href="/teacher/assessments" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Publish Assessment</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function updateSelectedQuestions() {
    const checks = document.querySelectorAll('input[name="question_ids[]"]:checked');
    document.getElementById('selectedCount').textContent = checks.length;
    let totalMarks = 0;
    checks.forEach(c => {
        totalMarks += parseFloat(c.dataset.marks || 1);
    });
    if (totalMarks > 0) {
        document.getElementById('inputTotalMarks').value = totalMarks;
    }
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

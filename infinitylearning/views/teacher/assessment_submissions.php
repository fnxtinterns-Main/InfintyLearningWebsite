<?php
use App\Auth;
use App\Helper;
use App\AcademicAssessment;
use App\Database;

$teacher = Auth::requireTeacher();
$assessmentId = (int)($_GET['id'] ?? 0);
$assessment = AcademicAssessment::find($assessmentId);

if (!$assessment) {
    http_response_code(404);
    exit('Assessment not found');
}

$submissions = AcademicAssessment::getSubmissions($assessmentId);

// Handle manual evaluation submission if posted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'grade_submission') {
    $submissionId = (int)($_POST['submission_id'] ?? 0);
    $score = (float)($_POST['score_obtained'] ?? 0);
    $feedback = trim($_POST['teacher_feedback'] ?? '');

    $db = Database::connect();
    $stmt = $db->prepare("
        UPDATE assessment_submissions
        SET score_obtained = ?,
            teacher_feedback = ?,
            is_evaluated = 1,
            evaluated_by = ?,
            evaluated_at = NOW(),
            status = 'evaluated'
        WHERE id = ? AND assessment_id = ?
    ");
    $stmt->execute([$score, $feedback, (int)Auth::id(), $submissionId, $assessmentId]);
    header("Location: /teacher/assessment/submissions?id={$assessmentId}&saved=1");
    exit;
}

$pageTitle = 'Submissions: ' . $assessment['title'] . ' — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <main class="admin-main">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <a href="/teacher/assessments" class="back-link">← All Assessments</a>
                <h1 style="margin:4px 0 0; font-size:24px;">📝 Submissions: <?= Helper::e($assessment['title']) ?></h1>
                <p style="color:var(--il-muted); font-size:14px; margin:4px 0 0;">
                    Type: <strong style="text-transform:capitalize;"><?= Helper::e(str_replace('_', ' ', $assessment['type'])) ?></strong> · Total Marks: <strong><?= (float)$assessment['total_marks'] ?></strong> · <?= count($submissions) ?> Submissions
                </p>
            </div>
        </div>

        <?php if (!empty($_GET['saved'])): ?>
            <div class="card mb-4" style="background:#DCFCE7; border-color:#86EFAC; color:#166534; padding:12px 16px; border-radius:10px;">
                ✅ <strong>Score and feedback saved successfully!</strong>
            </div>
        <?php endif; ?>

        <?php if (empty($submissions)): ?>
            <div class="card text-center py-5">
                <span style="font-size:42px;">⏳</span>
                <h3 style="margin:12px 0 6px;">No submissions yet</h3>
                <p class="muted" style="margin:0; font-size:14px;">Students have not submitted their attempts for this assessment yet.</p>
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:20px;">
                <?php foreach ($submissions as $idx => $sub):
                    $answers = !empty($sub['answers_json']) ? json_decode($sub['answers_json'], true) : [];
                    $questions = $assessment['questions'] ?? [];
                ?>
                    <div class="card" style="padding:24px; border-left: 4px solid <?= $sub['is_evaluated'] ? '#10b981' : '#f59e0b' ?>;">
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                            <div>
                                <h3 style="margin:0 0 4px; font-size:18px;">
                                    <?= Helper::e($sub['student_name'] ?: 'Student') ?>
                                    <?php if (!empty($sub['current_class'])): ?>
                                        <span class="pill" style="font-size:12px; margin-left:6px;">Class <?= Helper::e($sub['current_class']) ?></span>
                                    <?php endif; ?>
                                </h3>
                                <div class="muted" style="font-size:13px;">
                                    Submitted: <?= date('d M Y, H:i', strtotime($sub['submitted_at'])) ?>
                                    · Email: <?= Helper::e($sub['student_email']) ?>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:20px; font-weight:800; color:<?= $sub['is_evaluated'] ? '#10b981' : '#f59e0b' ?>;">
                                    <?= $sub['score_obtained'] !== null ? (float)$sub['score_obtained'] . ' / ' . (float)$assessment['total_marks'] : 'Pending Grade' ?>
                                </div>
                                <span class="badge <?= $sub['is_evaluated'] ? 'badge-success' : 'badge-warning' ?>" style="font-size:11px;">
                                    <?= $sub['is_evaluated'] ? 'Evaluated' : 'Needs Review' ?>
                                </span>
                            </div>
                        </div>

                        <!-- Toggle Student Responses Accordion -->
                        <details style="margin-top:16px; background:var(--surface-2); padding:12px 16px; border-radius:10px;">
                            <summary style="font-weight:600; cursor:pointer; color:var(--primary); outline:none;">
                                👁️ View Student Answers (<?= count($questions) ?> Questions)
                            </summary>
                            <div style="display:flex; flex-direction:column; gap:14px; margin-top:14px;">
                                <?php foreach ($questions as $qIdx => $q): 
                                    $qId = (string)($q['id'] ?? '');
                                    $ans = $answers[$qId] ?? '—';
                                ?>
                                    <div style="background:var(--surface); padding:12px; border-radius:8px; border:1px solid var(--border);">
                                        <div style="font-weight:600; font-size:14px; margin-bottom:6px;">
                                            Q<?= $qIdx + 1 ?>: <?= Helper::e($q['question_text']) ?>
                                            <span class="muted" style="font-weight:normal; font-size:12px;">(<?= (float)($q['marks'] ?? 1) ?> Marks)</span>
                                        </div>
                                        <div style="font-size:13px; color:var(--text); background:var(--surface-2); padding:8px 12px; border-radius:6px; margin-top:4px;">
                                            <strong>Student Response:</strong> <?= nl2br(Helper::e((string)$ans)) ?>
                                        </div>
                                        <?php if (!empty($q['correct_answer'])): ?>
                                            <div class="muted" style="font-size:12px; margin-top:4px;">
                                                <em>Model Answer / Key:</em> <?= Helper::e($q['correct_answer']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </details>

                        <!-- Grading / Feedback Form -->
                        <form method="POST" style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border); display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap;">
                            <input type="hidden" name="action" value="grade_submission">
                            <input type="hidden" name="submission_id" value="<?= (int)$sub['id'] ?>">

                            <div class="form-group" style="width:140px;">
                                <label class="form-label" style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">Score (Max <?= (float)$assessment['total_marks'] ?>)</label>
                                <input type="number" step="0.5" min="0" max="<?= (float)$assessment['total_marks'] ?>" name="score_obtained" class="input" value="<?= $sub['score_obtained'] !== null ? (float)$sub['score_obtained'] : '' ?>" required>
                            </div>

                            <div class="form-group" style="flex:1; min-width:240px;">
                                <label class="form-label" style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">Teacher Feedback / Remarks</label>
                                <input type="text" name="teacher_feedback" class="input" placeholder="e.g. Good derivation in section B. Pay attention to unit conversions." value="<?= Helper::e($sub['teacher_feedback'] ?? '') ?>">
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm" style="padding:10px 18px; font-weight:600;">
                                💾 Save Grade
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

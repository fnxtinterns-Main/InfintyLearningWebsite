<?php
use App\Auth;
use App\Helper;
use App\AcademicAssessment;
use App\Database;

Auth::requireRole('student');
$user = Auth::user();
$studentId = (int)$user['id'];

$assessmentId = (int)($_GET['id'] ?? 0);
$assessment = AcademicAssessment::find($assessmentId);
if (!$assessment) {
    Helper::redirect('/student/assessments');
}

$questions = $assessment['questions'] ?? [];

$db = Database::connect();
$subStmt = $db->prepare("SELECT * FROM assessment_submissions WHERE assessment_id = ? AND student_user_id = ? LIMIT 1");
$subStmt->execute([$assessmentId, $studentId]);
$existingSub = $subStmt->fetch();

$pageTitle = Helper::e($assessment['title']) . ' — Examination Room';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm">
    <div class="container-narrow">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:14px;">
            <div>
                <a href="/student/assessments" class="btn btn-sm btn-outline-secondary" style="margin-bottom:6px;">← Back to Assessments</a>
                <h1 style="margin:0; font-size:24px;"><?= Helper::e($assessment['title']) ?></h1>
                <div class="muted" style="font-size:13px; margin-top:2px;">
                    Subject: <strong><?= Helper::e($assessment['subject_name'] ?? 'General') ?></strong> •
                    Total Marks: <strong><?= (float)$assessment['total_marks'] ?> pts</strong> •
                    Questions: <strong><?= count($questions) ?></strong>
                </div>
            </div>

            <?php if (!empty($assessment['duration_minutes']) && !$existingSub): ?>
                <div style="background:#1E293B; color:#fff; padding:10px 18px; border-radius:12px; font-family:'JetBrains Mono', monospace; font-size:18px; font-weight:700; display:flex; align-items:center; gap:8px;">
                    ⏱️ <span id="assessmentCountdown">--:--</span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($existingSub): ?>
            <div class="card" style="padding:32px; text-align:center;">
                <div style="font-size:42px; margin-bottom:8px;">✅</div>
                <h2>You have already submitted this assessment</h2>
                <p class="muted">Submitted on <?= date('d M Y, H:i', strtotime($existingSub['submitted_at'])) ?></p>

                <?php if ($existingSub['is_evaluated']): ?>
                    <div style="margin:20px 0; background:var(--surface-0); padding:20px; border-radius:14px; display:inline-block;">
                        <div style="font-size:12px; text-transform:uppercase; color:var(--il-muted);">Your Score</div>
                        <div style="font-size:36px; font-weight:800; font-family:'JetBrains Mono', monospace; color:var(--il-primary);">
                            <?= (float)$existingSub['score_obtained'] ?> / <?= (float)$assessment['total_marks'] ?>
                        </div>
                        <?php if (!empty($existingSub['teacher_feedback'])): ?>
                            <div style="margin-top:10px; font-size:14px; color:var(--il-text);">
                                <strong>Teacher Feedback:</strong> <?= Helper::e($existingSub['teacher_feedback']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" style="display:inline-block; margin-top:14px;">
                        Your submission contains questions requiring teacher manual evaluation. Check back soon for your graded marks!
                    </div>
                <?php endif; ?>
                <br>
                <a href="/student/assessments" class="btn btn-primary mt-3">Back to Assessments</a>
            </div>
        <?php else: ?>
            <?php if (!empty($assessment['instructions'])): ?>
                <div class="alert alert-info" style="margin-bottom:20px;">
                    <strong>Instructions:</strong> <?= nl2br(Helper::e($assessment['instructions'])) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/student/assessment-submit-handler.php" id="assessmentForm">
                <input type="hidden" name="assessment_id" value="<?= (int)$assessment['id'] ?>">
                <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">

                <div style="display:flex; flex-direction:column; gap:18px;">
                    <?php foreach ($questions as $idx => $q): ?>
                        <?php
                            $qId = (string)($q['id'] ?? $idx);
                            $qType = $q['question_type'] ?? 'mcq';
                        ?>
                        <div class="card" style="padding:22px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span class="badge" style="background:#1E293B; color:#fff; font-weight:700;">
                                        Q<?= $idx + 1 ?>
                                    </span>
                                    <span class="badge badge-<?= $q['difficulty'] === 'easy' ? 'success' : 'primary' ?>">
                                        <?= ucfirst(Helper::e($q['difficulty'] ?? 'medium')) ?>
                                    </span>
                                </div>
                                <span class="badge" style="background:#FEF3C7; color:#92400E; font-weight:700;">
                                    <?= (float)($q['marks'] ?? 1.0) ?> Mark<?= (float)($q['marks'] ?? 1.0) > 1 ? 's' : '' ?>
                                </span>
                            </div>

                            <div style="font-size:16px; font-weight:600; color:var(--il-text); margin-bottom:16px; line-height:1.5;">
                                <?= nl2br(Helper::e($q['question_text'])) ?>
                            </div>

                            <?php if ($qType === 'mcq' && !empty($q['options_json'])): ?>
                                <?php $opts = json_decode($q['options_json'], true) ?: []; ?>
                                <div style="display:grid; grid-template-columns:1fr; gap:8px;">
                                    <?php foreach ($opts as $oi => $opt): ?>
                                        <label style="display:flex; align-items:center; gap:12px; padding:10px 14px; background:var(--surface-0); border:1px solid var(--il-border); border-radius:8px; cursor:pointer; font-size:14px;">
                                            <input type="radio" name="answers[<?= $qId ?>]" value="<?= Helper::e($opt) ?>" style="width:18px; height:18px;">
                                            <span><strong><?= chr(65 + $oi) ?>.</strong> <?= Helper::e($opt) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($qType === 'true_false'): ?>
                                <div style="display:flex; gap:14px;">
                                    <label style="flex:1; display:flex; align-items:center; gap:10px; padding:10px 16px; background:var(--surface-0); border:1px solid var(--il-border); border-radius:8px; cursor:pointer;">
                                        <input type="radio" name="answers[<?= $qId ?>]" value="True" style="width:18px; height:18px;">
                                        <strong>True</strong>
                                    </label>
                                    <label style="flex:1; display:flex; align-items:center; gap:10px; padding:10px 16px; background:var(--surface-0); border:1px solid var(--il-border); border-radius:8px; cursor:pointer;">
                                        <input type="radio" name="answers[<?= $qId ?>]" value="False" style="width:18px; height:18px;">
                                        <strong>False</strong>
                                    </label>
                                </div>
                            <?php else: ?>
                                <div>
                                    <textarea name="answers[<?= $qId ?>]" class="form-control" rows="3" placeholder="Type your step-by-step answer or calculation here..."></textarea>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top:24px; display:flex; justify-content:space-between; align-items:center; background:#fff; padding:18px 24px; border-radius:14px; border:1px solid var(--il-border);">
                    <div class="muted" style="font-size:13px;">Double check your responses before finalizing.</div>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Are you ready to submit your assessment?');">
                        Submit Final Answers 📤
                    </button>
                </div>
            </form>

            <?php if (!empty($assessment['duration_minutes'])): ?>
                <script>
                const durationMinutes = <?= (int)$assessment['duration_minutes'] ?>;
                let timeLeft = durationMinutes * 60;
                const timerEl = document.getElementById('assessmentCountdown');

                const timerInterval = setInterval(() => {
                    timeLeft--;
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        alert('Time is up! Your answers will now be submitted automatically.');
                        document.getElementById('assessmentForm').submit();
                        return;
                    }

                    const mins = Math.floor(timeLeft / 60);
                    const secs = timeLeft % 60;
                    timerEl.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                    if (timeLeft < 300) {
                        timerEl.style.color = '#EF4444';
                    }
                }, 1000);
                </script>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

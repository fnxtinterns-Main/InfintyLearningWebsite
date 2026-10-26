<?php
use App\Auth;
use App\Helper;
use App\AcademicAssessment;

Auth::requireRole('student');
$user = Auth::user();
$studentId = (int)$user['id'];

$assessments = AcademicAssessment::listForStudent($studentId);

$pageTitle = 'My Tests & Homework — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm">
    <div class="container-narrow" style="max-width:820px; margin:0 auto;">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">📝 MY ACADEMIC EVALUATIONS</span>
                <h1 style="margin:2px 0 0;">Tests & Homework Assignments</h1>
                <p class="muted">Complete assigned homework, practice unit tests, and review your scores and teacher feedback.</p>
            </div>
            <a href="/dashboard" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
        </div>

        <?php if ($msg = \App\Session::flash('success')): ?>
            <div class="alert alert-success"><?= Helper::e($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = \App\Session::flash('error')): ?>
            <div class="alert alert-danger"><?= Helper::e($err) ?></div>
        <?php endif; ?>

        <div class="card" style="padding:0; overflow:hidden;">
            <?php if (empty($assessments)): ?>
                <div style="padding:48px 24px; text-align:center; color:var(--il-muted);">
                    <div style="font-size:42px; margin-bottom:8px;">🎉</div>
                    <h3>All caught up!</h3>
                    <p style="margin:4px 0 16px;">You have no pending homework assignments or scheduled exams right now.</p>
                    <a href="/lessons" class="btn btn-primary btn-sm">Explore Lessons →</a>
                </div>
            <?php else: ?>
                <div style="padding:8px 0;">
                    <?php foreach ($assessments as $a): ?>
                        <?php
                            $isDone = !empty($a['submission_id']);
                            $isOverdue = (!empty($a['due_date']) && strtotime($a['due_date']) < time() && !$isDone);
                        ?>
                        <div style="padding:18px 24px; border-bottom:1px solid var(--il-border); display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
                            <div style="flex:1; min-width:260px;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
                                    <span class="badge" style="background:#F1F5F9; color:#475569; font-size:11px; text-transform:capitalize;">
                                        <?= str_replace('_', ' ', Helper::e($a['assessment_type'])) ?>
                                    </span>
                                    <span class="badge" style="background:#EFF6FF; color:#1E40AF; font-size:11px;">
                                        <?= Helper::e($a['subject_name'] ?? 'General') ?>
                                    </span>
                                    <?php if (!empty($a['duration_minutes'])): ?>
                                        <span class="badge badge-info" style="font-size:11px;">⏱️ <?= (int)$a['duration_minutes'] ?> mins</span>
                                    <?php endif; ?>
                                    <?php if ($isDone): ?>
                                        <span class="badge badge-success" style="font-size:11px;">✅ Submitted</span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="badge badge-danger" style="font-size:11px;">⚠️ Overdue</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary" style="font-size:11px;">Pending</span>
                                    <?php endif; ?>
                                </div>

                                <h3 style="margin:0 0 4px; font-size:17px; color:var(--il-text);">
                                    <?= Helper::e($a['title']) ?>
                                </h3>

                                <div class="muted" style="font-size:13px;">
                                    Total Marks: <strong><?= (float)$a['total_marks'] ?> pts</strong> •
                                    Due by: <strong><?= !empty($a['due_date']) ? date('d M Y, H:i', strtotime($a['due_date'])) : 'No deadline' ?></strong>
                                </div>
                            </div>

                            <div>
                                <?php if ($isDone): ?>
                                    <div style="text-align:right;">
                                        <div style="font-size:18px; font-weight:800; font-family:'JetBrains Mono', monospace; color:var(--il-primary);">
                                            <?= $a['is_evaluated'] ? ((float)$a['score_obtained'] . ' / ' . (float)$a['total_marks']) : 'Awaiting Grading' ?>
                                        </div>
                                        <div style="font-size:12px; color:var(--il-muted);">
                                            <?= $a['is_evaluated'] ? 'Final Score' : 'Submitted on ' . date('d M', strtotime($a['submitted_at'])) ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <a href="/student/assessment/take?id=<?= (int)$a['id'] ?>" class="btn btn-primary btn-sm">
                                        <?= $a['assessment_type'] === 'homework' ? 'Solve Homework ✍️' : 'Start Test ⏱️' ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

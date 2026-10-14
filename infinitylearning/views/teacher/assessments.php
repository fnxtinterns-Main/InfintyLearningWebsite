<?php
use App\Auth;
use App\Helper;
use App\AcademicAssessment;
use App\Batch;
use App\Database;

Auth::requireRole('teacher');
$user = Auth::user();
$teacherId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

$batches = Batch::all($teacherId);
$selectedBatchId = !empty($_GET['batch_id']) ? (int)$_GET['batch_id'] : 0;
$selectedType = $_GET['type'] ?? '';

$assessments = AcademicAssessment::listForTeacher($teacherId, [
    'batch_id' => $selectedBatchId ?: null,
    'assessment_type' => $selectedType ?: null,
]);

$pageTitle = 'Academic Assessments & Tests — Teacher Hub';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <div>
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">📝 TESTING & HOMEWORK</span>
                <h1 style="margin:2px 0 0;">Assessments & Examinations</h1>
                <p class="muted">Deploy homework, unit tests, mid-term, and final-term exams directly from your question bank.</p>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="/teacher/questions" class="btn btn-outline-secondary">📚 Question Bank</a>
                <a href="/teacher/assessment/new" class="btn btn-primary">+ Create New Assessment</a>
            </div>
        </div>

        <?php if ($msg = \App\Session::flash('success')): ?>
            <div class="alert alert-success"><?= Helper::e($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = \App\Session::flash('error')): ?>
            <div class="alert alert-danger"><?= Helper::e($err) ?></div>
        <?php endif; ?>

        <!-- FILTER BAR -->
        <div class="card" style="padding:14px 20px; margin-bottom:20px;">
            <form method="get" action="/teacher/assessments" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <div style="width:220px;">
                    <select name="type" class="form-control">
                        <option value="">All Assessment Types</option>
                        <option value="homework" <?= $selectedType === 'homework' ? 'selected' : '' ?>>Homework</option>
                        <option value="unit_test" <?= $selectedType === 'unit_test' ? 'selected' : '' ?>>Unit Test</option>
                        <option value="mid_term" <?= $selectedType === 'mid_term' ? 'selected' : '' ?>>Mid Term Exam</option>
                        <option value="final_term" <?= $selectedType === 'final_term' ? 'selected' : '' ?>>Final Term Exam</option>
                        <option value="practice_quiz" <?= $selectedType === 'practice_quiz' ? 'selected' : '' ?>>Practice Quiz</option>
                    </select>
                </div>
                <div style="width:200px;">
                    <select name="batch_id" class="form-control">
                        <option value="">All Batches</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" <?= $selectedBatchId === (int)$b['id'] ? 'selected' : '' ?>>
                                <?= Helper::e($b['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <?php if ($selectedBatchId || $selectedType): ?>
                    <a href="/teacher/assessments" class="btn btn-ghost">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- LIST -->
        <div class="card" style="padding:0; overflow:hidden;">
            <?php if (empty($assessments)): ?>
                <div style="padding:48px 24px; text-align:center; color:var(--il-muted);">
                    <div style="font-size:40px; margin-bottom:8px;">📋</div>
                    <h3>No assessments assigned yet</h3>
                    <p style="margin:4px 0 16px;">Create your first homework assignment or exam from the question bank.</p>
                    <a href="/teacher/assessment/new" class="btn btn-primary btn-sm">+ Create Assessment Now</a>
                </div>
            <?php else: ?>
                <table class="table" style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--il-border); text-align:left; background:var(--surface-0); color:var(--il-muted); font-size:12px; text-transform:uppercase;">
                            <th style="padding:12px 18px;">Type & Title</th>
                            <th style="padding:12px 18px;">Subject & Batch</th>
                            <th style="padding:12px 18px;">Marks</th>
                            <th style="padding:12px 18px;">Due Date</th>
                            <th style="padding:12px 18px;">Submissions</th>
                            <th style="padding:12px 18px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assessments as $a): ?>
                            <tr style="border-bottom:1px solid var(--il-border);">
                                <td style="padding:12px 18px;">
                                    <div style="font-weight:700; font-size:15px; color:var(--il-text);">
                                        <?= Helper::e($a['title']) ?>
                                    </div>
                                    <span class="badge" style="background:#F1F5F9; color:#475569; font-size:11px; text-transform:capitalize;">
                                        <?= str_replace('_', ' ', Helper::e($a['assessment_type'])) ?>
                                    </span>
                                    <?php if (!empty($a['duration_minutes'])): ?>
                                        <span class="badge badge-info" style="font-size:11px;">⏱️ <?= (int)$a['duration_minutes'] ?> mins</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px 18px;">
                                    <strong><?= Helper::e($a['subject_name'] ?? 'General') ?></strong>
                                    <div class="muted" style="font-size:12px;">Batch: <?= Helper::e($a['batch_name'] ?? 'All Batch Students') ?></div>
                                </td>
                                <td style="padding:12px 18px; font-family:'JetBrains Mono', monospace; font-weight:700;">
                                    <?= (float)$a['total_marks'] ?> pts
                                </td>
                                <td style="padding:12px 18px; font-size:13px; color:var(--il-muted);">
                                    <?= !empty($a['due_date']) ? date('d M, H:i', strtotime($a['due_date'])) : 'No deadline' ?>
                                </td>
                                <td style="padding:12px 18px;">
                                    <a href="/teacher/assessment/submissions?id=<?= (int)$a['id'] ?>" class="badge badge-success" style="font-size:12px; font-weight:700; text-decoration:none;">
                                        📥 <?= (int)$a['submissions_count'] ?> submitted
                                    </a>
                                </td>
                                <td style="padding:12px 18px; text-align:right;">
                                    <a href="/teacher/assessment/submissions?id=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline-primary" style="padding:3px 10px; font-size:12px;">
                                        View & Grade →
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

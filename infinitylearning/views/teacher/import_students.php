<?php
use App\Auth;
use App\Batch;
use App\Helper;
use App\Session;

Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

$batchId = (int)($_GET['batch_id'] ?? 0);
$batch = $batchId ? Batch::find($batchId) : null;

if ($batch && $role === 'teacher' && (int)$batch['teacher_id'] !== (int)Auth::user()['id']) {
    http_response_code(403); exit('You do not own this batch.');
}

// Teacher's batches (for the batch selector when no batch is preselected)
$teacherUserId = $role === 'teacher' ? (int)Auth::user()['id'] : null;
$myBatches = Batch::all($teacherUserId);

$pageTitle = 'Import Students — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section" style="max-width:820px">
    <a href="<?= $batch ? '/teacher/batch?id=' . (int)$batch['id'] : '/teacher/batches' ?>" class="muted">← Back</a>

    <div class="card mt-2">
        <h1>📥 Import students from CSV</h1>
        <p class="muted">
            Add many students at once. Each row creates a student account (if new) and enrolls them into the selected batch.
        </p>

        <div class="alert alert-info" style="margin-top:1rem">
            <strong>Before you upload:</strong>
            <ul style="margin:.4rem 0 0 1.2rem;padding:0">
                <li>Download the template below to see the exact columns required.</li>
                <li>Every student needs <strong>full_name</strong>, <strong>email</strong>, <strong>phone</strong>, and <strong>current_class</strong>.</li>
                <li>Phone numbers must include country code (e.g. +919876543210).</li>
                <li>If no password is provided, we'll auto-generate one and include it in the results file.</li>
                <li>Existing users (matched by email or phone) will simply be enrolled into the batch.</li>
            </ul>
        </div>

        <div class="flex mt-2">
            <a class="btn btn-secondary" href="/teacher/import-template.php">⬇ Download CSV template</a>
        </div>

        <div class="divider"></div>

        <form method="post" action="/teacher/import-students-handler.php" enctype="multipart/form-data" id="importForm">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">

            <div class="form-group">
                <label>Select batch *</label>
                <select name="batch_id" required>
                    <option value="">— choose a batch —</option>
                    <?php foreach ($myBatches as $b): ?>
                        <option value="<?= (int)$b['id'] ?>"
                            <?= $batch && (int)$batch['id'] === (int)$b['id'] ? 'selected' : '' ?>>
                            <?= Helper::e($b['name']) ?>
                            <?php if ($b['subject']): ?> · <?= Helper::e($b['subject']) ?><?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$myBatches): ?>
                    <small style="color:var(--il-danger)">You have no batches yet. <a href="/teacher/batch/new">Create one first →</a></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>CSV file *</label>
                <input type="file" name="csv" accept=".csv,text/csv" required>
                <small>Max 2MB. UTF-8 encoded.</small>
            </div>

            <div class="flex mt-2">
                <button class="btn btn-primary" type="submit" name="stage" value="preview" <?= !$myBatches ? 'disabled' : '' ?>>
                    📋 Preview import
                </button>
            </div>
        </form>
    </div>

    <?php
    // ----- Preview block (from previous submission) -----
    $preview = $_SESSION['_import_preview'] ?? null;
    if ($preview):
        unset($_SESSION['_import_preview']);
        $rows = $preview['rows'];
        $summary = $preview['summary'];
        $batchIdPreview = (int)$preview['batch_id'];
        $tempFile = $preview['temp_file'];
    ?>
        <div class="card mt-3">
            <h2>🔍 Preview</h2>
            <p class="muted">Review before committing. Nothing has been saved yet.</p>

            <div class="grid grid-4 mt-2">
                <div class="stat"><span class="n"><?= (int)$summary['total'] ?></span><span class="l">Total rows</span></div>
                <div class="stat" style="border-left:4px solid #16A34A"><span class="n"><?= (int)$summary['new'] ?></span><span class="l">New accounts</span></div>
                <div class="stat" style="border-left:4px solid #2563EB"><span class="n"><?= (int)$summary['existing'] ?></span><span class="l">Existing users</span></div>
                <div class="stat" style="border-left:4px solid #DC2626"><span class="n"><?= (int)$summary['errors'] ?></span><span class="l">Errors</span></div>
            </div>

            <div class="table-wrap mt-3">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Class</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int)$r['row'] ?></td>
                            <td><?= Helper::e($r['data']['full_name'] ?? '—') ?></td>
                            <td><?= Helper::e($r['data']['email'] ?? '—') ?></td>
                            <td><?= Helper::e($r['data']['phone'] ?? '—') ?></td>
                            <td><?= Helper::e($r['data']['current_class'] ?? '—') ?></td>
                            <td>
                                <?php if ($r['status'] === 'new'): ?>
                                    <span class="badge badge-success">New</span>
                                <?php elseif ($r['status'] === 'existing'): ?>
                                    <span class="badge badge-info">Existing</span>
                                <?php elseif ($r['status'] === 'already_in_batch'): ?>
                                    <span class="badge badge-muted">Already in batch</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Error</span>
                                    <div class="muted" style="font-size:.75rem"><?= Helper::e($r['error'] ?? '') ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <form method="post" action="/teacher/import-students-handler.php" class="mt-3">
                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                <input type="hidden" name="stage" value="commit">
                <input type="hidden" name="batch_id" value="<?= $batchIdPreview ?>">
                <input type="hidden" name="temp_file" value="<?= Helper::e($tempFile) ?>">
                <div class="flex">
                    <button class="btn btn-primary"<?= $summary['new'] + $summary['existing'] === 0 ? ' disabled' : '' ?>>
                        ✅ Commit import (<?= (int)($summary['new'] + $summary['existing']) ?> row(s))
                    </button>
                    <a class="btn btn-ghost" href="/teacher/batch/import?batch_id=<?= $batchIdPreview ?>">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
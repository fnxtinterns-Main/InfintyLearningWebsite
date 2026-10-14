<?php
use App\Auth;
use App\Helper;
use App\FeeManagement;
use App\Batch;
use App\Database;

Auth::requireRole('teacher');
$user = Auth::user();
$teacherId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

$batches = Batch::all($teacherId);
$selectedBatchId = !empty($_GET['batch_id']) ? (int)$_GET['batch_id'] : (int)($batches[0]['id'] ?? 0);

$fees = FeeManagement::list([
    'batch_id' => $selectedBatchId ?: null,
]);

$pageTitle = 'Batch Student Fees — Teacher Hub';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <div>
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">💳 TUITION & BATCH DUES</span>
                <h1 style="margin:2px 0 0;">Batch Student Fee Tracker</h1>
                <p class="muted">Check payment statuses for your batch students, send polite WhatsApp payment reminders, and record collections.</p>
            </div>
        </div>

        <?php if ($msg = \App\Session::flash('success')): ?>
            <div class="alert alert-success"><?= Helper::e($msg) ?></div>
        <?php endif; ?>

        <!-- BATCH SELECTOR -->
        <div class="card" style="padding:14px 20px; margin-bottom:20px;">
            <form method="get" action="/teacher/fees" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                <label style="font-weight:600; font-size:14px; margin:0;">Select Batch:</label>
                <select name="batch_id" class="form-control" style="width:240px;" onchange="this.form.submit()">
                    <?php foreach ($batches as $b): ?>
                        <option value="<?= (int)$b['id'] ?>" <?= $selectedBatchId === (int)$b['id'] ? 'selected' : '' ?>>
                            <?= Helper::e($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- TABLE -->
        <div class="card" style="padding:0; overflow:hidden;">
            <?php if (empty($fees)): ?>
                <div style="padding:48px 24px; text-align:center; color:var(--il-muted);">
                    <div style="font-size:38px; margin-bottom:8px;">💰</div>
                    <h3>No fee dues recorded for this batch yet</h3>
                    <p class="muted">Fees recorded by the school administration or tutor will appear here.</p>
                </div>
            <?php else: ?>
                <table class="table" style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--il-border); text-align:left; background:var(--surface-0); color:var(--il-muted); font-size:12px; text-transform:uppercase;">
                            <th style="padding:12px 18px;">Student</th>
                            <th style="padding:12px 18px;">Month & Type</th>
                            <th style="padding:12px 18px;">Due Amount</th>
                            <th style="padding:12px 18px;">Paid</th>
                            <th style="padding:12px 18px;">Status</th>
                            <th style="padding:12px 18px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fees as $f): ?>
                            <?php
                                $dueBalance = (float)$f['amount_due'] - (float)$f['discount'] - (float)$f['amount_paid'];
                                $whatsappMsg = urlencode("Hello! Gentle tuition fee reminder for {$f['student_name']} regarding {$f['fee_month']} dues of ₹" . number_format($dueBalance, 2) . ". Thank you!");
                                $waLink = !empty($f['student_phone']) ? "https://wa.me/" . preg_replace('/[^0-9]/', '', $f['student_phone']) . "?text={$whatsappMsg}" : null;
                            ?>
                            <tr style="border-bottom:1px solid var(--il-border);">
                                <td style="padding:12px 18px;">
                                    <strong><?= Helper::e($f['student_name']) ?></strong>
                                    <div class="muted" style="font-size:12px;"><?= Helper::e($f['student_phone']) ?></div>
                                </td>
                                <td style="padding:12px 18px;">
                                    <?= Helper::e($f['fee_month']) ?>
                                    <div class="muted" style="font-size:12px; text-transform:capitalize;"><?= Helper::e($f['fee_type']) ?></div>
                                </td>
                                <td style="padding:12px 18px; font-family:'JetBrains Mono', monospace; font-weight:700;">
                                    ₹<?= number_format((float)$f['amount_due'], 2) ?>
                                </td>
                                <td style="padding:12px 18px; font-family:'JetBrains Mono', monospace; color:#16A34A; font-weight:700;">
                                    ₹<?= number_format((float)$f['amount_paid'], 2) ?>
                                </td>
                                <td style="padding:12px 18px;">
                                    <span class="badge badge-<?= $f['status'] === 'paid' ? 'success' : ($f['status'] === 'overdue' ? 'danger' : 'primary') ?>" style="text-transform:uppercase; font-size:11px;">
                                        <?= Helper::e($f['status']) ?>
                                    </span>
                                </td>
                                <td style="padding:12px 18px; text-align:right;">
                                    <?php if ($f['status'] !== 'paid' && $waLink): ?>
                                        <a href="<?= $waLink ?>" target="_blank" class="btn btn-xs btn-outline-success" style="background:#DCFCE7; color:#166534;">
                                            WhatsApp Reminder 💬
                                        </a>
                                    <?php else: ?>
                                        <span class="badge badge-success">Paid</span>
                                    <?php endif; ?>
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

<?php
use App\Auth;
use App\Helper;
use App\FeeManagement;

Auth::requireRole('student');
$user = Auth::user();
$studentId = (int)$user['id'];

$fees = FeeManagement::list([
    'student_user_id' => $studentId,
]);

$summary = FeeManagement::getStudentSummary($studentId);

$pageTitle = 'My Fees & Receipts — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm">
    <div class="container-narrow">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">💳 STUDENT ACCOUNTS</span>
                <h1 style="margin:2px 0 0;">Fees & Payment Receipts</h1>
                <p class="muted">Review monthly tuition records, transaction reference numbers, and digital fee vouchers.</p>
            </div>
            <a href="/dashboard" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
        </div>

        <!-- STAT TILES -->
        <div class="grid grid-3" style="margin-bottom:20px;">
            <div class="stat">
                <span class="ico">📜</span>
                <span class="n" style="font-size:22px;">₹<?= number_format((float)$summary['total_due'], 2) ?></span>
                <span class="l">Total Billed</span>
            </div>
            <div class="stat" style="border-left-color:#16A34A;">
                <span class="ico">✅</span>
                <span class="n" style="font-size:22px; color:#16A34A;">₹<?= number_format((float)$summary['total_paid'], 2) ?></span>
                <span class="l">Paid Amount</span>
            </div>
            <div class="stat" style="border-left-color:#DC2626;">
                <span class="ico">⏳</span>
                <span class="n" style="font-size:22px; color:#DC2626;">₹<?= number_format((float)$summary['balance_pending'], 2) ?></span>
                <span class="l">Pending Balance</span>
            </div>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
            <?php if (empty($fees)): ?>
                <div style="padding:48px 24px; text-align:center; color:var(--il-muted);">
                    <div style="font-size:40px; margin-bottom:8px;">🧾</div>
                    <h3>No fee invoices registered</h3>
                    <p class="muted">All tuition and extracurricular dues are clear.</p>
                </div>
            <?php else: ?>
                <table class="table" style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--il-border); text-align:left; background:var(--surface-0); color:var(--il-muted); font-size:12px; text-transform:uppercase;">
                            <th style="padding:12px 18px;">Month & Type</th>
                            <th style="padding:12px 18px;">Due Amount</th>
                            <th style="padding:12px 18px;">Paid</th>
                            <th style="padding:12px 18px;">Status</th>
                            <th style="padding:12px 18px; text-align:right;">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fees as $f): ?>
                            <tr style="border-bottom:1px solid var(--il-border);">
                                <td style="padding:12px 18px;">
                                    <strong><?= Helper::e($f['fee_month']) ?> (<?= Helper::e($f['academic_year']) ?>)</strong>
                                    <div class="muted" style="font-size:12px; text-transform:capitalize;"><?= Helper::e($f['fee_type']) ?> Fee</div>
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
                                    <?php if (!empty($f['receipt_number']) || (float)$f['amount_paid'] > 0): ?>
                                        <div style="font-family:'JetBrains Mono', monospace; font-size:12px; font-weight:700; color:var(--il-primary);">
                                            <?= Helper::e($f['receipt_number'] ?: 'REC-' . $f['id']) ?>
                                        </div>
                                        <div style="font-size:11px; color:var(--il-muted); margin-bottom:4px;"><?= !empty($f['paid_at']) ? date('d M Y', strtotime($f['paid_at'])) : date('d M Y') ?></div>
                                        <a href="/student/fee-receipt?id=<?= (int)$f['id'] ?>" target="_blank" class="btn btn-xs btn-secondary" style="font-size:11px; padding:3px 8px;">
                                            🧾 View Receipt
                                        </a>
                                    <?php else: ?>
                                        <span class="muted" style="font-size:12px;">Payment Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

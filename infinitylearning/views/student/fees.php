<?php
use App\Auth;
use App\Helper;
use App\FeeManagement;
use App\School;

Auth::requireRole('student');
$user = Auth::user();
$studentId = (int)$user['id'];
$isAiverse = School::isAiverse((int)($user['school_id'] ?? 0));

if ($isAiverse) {
    $enrollments = \App\Aiverse::getStudentEnrollments($studentId);
    $summary = \App\Aiverse::getStudentAiverseFeeSummary($studentId);
    $fees = [];
    foreach ($enrollments as $en) {
        $fees[] = [
            'id' => $en['fee_id'] ?? 0,
            'course_id' => (int)$en['course_id'],
            'notes' => $en['course_title'],
            'fee_month' => date('F Y', strtotime($en['created_at'])),
            'academic_year' => '2026-2027',
            'fee_type' => 'Course Program',
            'amount_due' => $en['amount_due'] ?? $en['course_fee'],
            'amount_paid' => $en['amount_paid'] ?? 0,
            'status' => $en['payment_status'] ?? 'pending',
            'receipt_number' => $en['receipt_number'] ?? null,
            'paid_at' => $en['paid_at'] ?? null,
        ];
    }
} else {
    $fees = FeeManagement::list([
        'student_user_id' => $studentId,
    ]);
    $summary = FeeManagement::getStudentSummary($studentId);
}

$pageTitle = ($isAiverse ? 'My Course Fees' : 'My Fees & Receipts') . ' — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm">
    <div class="container-narrow">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker"><?= $isAiverse ? '🚀 AIVERSE COURSE ACCOUNTS' : '💳 STUDENT ACCOUNTS' ?></span>
                <h1 style="margin:2px 0 0;"><?= $isAiverse ? 'Course Fees & Payment Status' : 'Fees & Payment Receipts' ?></h1>
                <p class="muted"><?= $isAiverse ? 'Review enrolled AI course fees, amounts paid, pending balances, and digital fee vouchers.' : 'Review monthly tuition records, transaction reference numbers, and digital fee vouchers.' ?></p>
            </div>
            <a href="/dashboard" class="btn btn-outline-secondary btn-sm"><?= $isAiverse ? '← Back to AIverse Hub' : '← Back to Dashboard' ?></a>
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
                            <th style="padding:12px 18px;"><?= $isAiverse ? 'Course & Program' : 'Month & Type' ?></th>
                            <th style="padding:12px 18px;">Total Due</th>
                            <th style="padding:12px 18px;">Paid</th>
                            <th style="padding:12px 18px;">Status</th>
                            <th style="padding:12px 18px; text-align:right;">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fees as $f): ?>
                            <tr style="border-bottom:1px solid var(--il-border);">
                                <td style="padding:12px 18px;">
                                    <strong><?= !empty($f['notes']) ? Helper::e($f['notes']) : Helper::e($f['fee_month']) . ' (' . Helper::e($f['academic_year']) . ')' ?></strong>
                                    <div class="muted" style="font-size:12px; text-transform:capitalize;"><?= $isAiverse ? 'AI Program Enrollment' : Helper::e($f['fee_type']) . ' Fee' ?></div>
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
                                        <span class="muted" style="font-size:12px; display:block; margin-bottom:4px;">Payment Pending</span>
                                    <?php endif; ?>
                                    <?php if ($isAiverse && !empty($f['course_id'])): ?>
                                        <form method="POST" action="/aiverse/unenroll" style="margin-top:6px;" onsubmit="return confirm('Are you sure you want to drop <?= Helper::e(addslashes($f['notes'])) ?>? Your enrollment will be cancelled.');">
                                            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                            <input type="hidden" name="course_id" value="<?= (int)$f['course_id'] ?>">
                                            <input type="hidden" name="redirect_to" value="/student/fees">
                                            <button type="submit" class="btn btn-xs btn-outline-danger" style="font-size:10.5px; padding:2px 6px; color:#DC2626; border-color:#FECACA;" title="Drop / Unenroll from Course">
                                                Drop Course ✕
                                            </button>
                                        </form>
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

<?php
declare(strict_types=1);

use App\Auth;
use App\FeeManagement;
use App\Helper;

Auth::requireLogin();
$user = Auth::user();
$feeId = (int)($_GET['id'] ?? 0);

if (!$feeId) {
    Helper::redirect('/dashboard');
}

$fee = FeeManagement::getFee($feeId);
if (!$fee) {
    http_response_code(404);
    exit('Fee record not found.');
}

// Security: students can only view their own receipts; school admins only their school
if (Auth::role() === 'student' && (int)$fee['student_user_id'] !== (int)$user['id']) {
    http_response_code(403);
    exit('Unauthorized access.');
}
if (Auth::isSchoolAdmin() && !empty($user['school_id']) && (int)$fee['school_id'] !== (int)$user['school_id']) {
    http_response_code(403);
    exit('Unauthorized access.');
}

$pageTitle = 'Receipt #' . ($fee['receipt_number'] ?: 'FEE-' . $fee['id']) . ' — Infinity Learning';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Helper::e($pageTitle) ?></title>
<link rel="icon" href="/assets/img/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Fraunces:wght@600;700&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: #F4F1EA;
    color: #231913;
    padding: 30px 16px;
    display: flex;
    justify-content: center;
}
.no-print-bar {
    max-width: 820px;
    width: 100%;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition: all 0.15s ease;
}
.btn-primary { background: #FF7A18; color: #fff; }
.btn-primary:hover { background: #E66508; }
.btn-secondary { background: #fff; color: #333; border: 1px solid #D5C7B8; }
.btn-secondary:hover { background: #FAF7F2; }

/* The Receipt Card */
.receipt-card {
    max-width: 820px;
    width: 100%;
    background: #fff;
    border-radius: 16px;
    padding: 40px 48px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    border: 1px solid #E7DFD5;
    position: relative;
    overflow: hidden;
}
.receipt-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 2px solid #F0E8DC;
    padding-bottom: 24px;
    margin-bottom: 28px;
}
.school-info { display: flex; gap: 16px; align-items: center; }
.school-logo { width: 64px; height: 64px; border-radius: 10px; object-fit: contain; }
.school-logo-fallback { width: 64px; height: 64px; border-radius: 10px; background: #FF7A18; color:#fff; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 800; }
.school-title { font-family: 'Fraunces', serif; font-size: 22px; font-weight: 700; color: #1E120B; }
.school-meta { font-size: 12.5px; color: #6E5C4F; margin-top: 3px; line-height: 1.4; }

.receipt-meta { text-align: right; }
.receipt-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 4px 10px;
    border-radius: 6px;
    background: #DCFCE7;
    color: #15803D;
    margin-bottom: 6px;
}
.receipt-title { font-size: 20px; font-weight: 800; color: #1E120B; }
.receipt-number { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: #6E5C4F; font-weight: 700; }

.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px; }
.info-block { background: #FAF7F2; border-radius: 10px; padding: 14px 18px; border: 1px solid #EFE8DE; }
.info-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #8A7667; margin-bottom: 4px; }
.info-value { font-size: 14.5px; font-weight: 700; color: #231913; }
.info-sub { font-size: 12.5px; color: #6E5C4F; margin-top: 2px; }

/* Table */
table.receipt-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
table.receipt-table th { background: #F5EFE6; color: #5B483B; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 10px 14px; text-align: left; }
table.receipt-table td { padding: 14px; border-bottom: 1px solid #F0E8DC; font-size: 13.5px; color: #2B1B12; }
table.receipt-table th.tar, table.receipt-table td.tar { text-align: right; }

.total-section { display: flex; justify-content: flex-end; margin-bottom: 30px; }
.total-box { width: 300px; }
.total-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13.5px; color: #5B483B; }
.total-row.grand-total { border-top: 2px solid #231913; padding-top: 10px; font-size: 17px; font-weight: 800; color: #1E120B; }

.receipt-footer { display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px dashed #D5C7B8; padding-top: 24px; font-size: 12px; color: #6E5C4F; }
.stamp-box {
    border: 2px solid #16A34A;
    color: #16A34A;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    display: inline-block;
    transform: rotate(-6deg);
}
.sig-line { width: 160px; border-top: 1px solid #333; text-align: center; padding-top: 4px; font-size: 11px; font-weight: 700; }

@media print {
    body { background: #fff; padding: 0; }
    .no-print-bar { display: none !important; }
    .receipt-card { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
}
</style>
</head>
<body>

<div style="width:100%; max-width:820px;">
    <div class="no-print-bar">
        <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
        <div style="display:flex; gap:10px;">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print / Download PDF</button>
        </div>
    </div>

    <div class="receipt-card">
        <!-- HEADER -->
        <div class="receipt-header">
            <div class="school-info">
                <?php if (!empty($fee['crest_logo'])): ?>
                    <img src="<?= Helper::e($fee['crest_logo']) ?>" class="school-logo" alt="">
                <?php else: ?>
                    <div class="school-logo-fallback">🏛️</div>
                <?php endif; ?>
                <div>
                    <div class="school-title"><?= Helper::e($fee['school_name'] ?: 'Infinity Learning Partner School') ?></div>
                    <div class="school-meta">
                        <?= Helper::e($fee['school_address'] ?: 'Academic Campus · Department of Accounts & Fees') ?><br>
                        Contact: <?= Helper::e($fee['school_phone'] ?: '+91 98765 43210') ?> · <?= Helper::e($fee['school_email'] ?: 'support@infinitylearning.net') ?>
                    </div>
                </div>
            </div>

            <div class="receipt-meta">
                <span class="receipt-badge"><?= strtoupper(Helper::e($fee['status'])) ?></span>
                <div class="receipt-title">Fee Receipt</div>
                <div class="receipt-number"><?= Helper::e($fee['receipt_number'] ?: 'REC-2026-' . str_pad((string)$fee['id'], 5, '0', STR_PAD_LEFT)) ?></div>
            </div>
        </div>

        <!-- STUDENT & PAYMENT INFO -->
        <div class="grid-2">
            <div class="info-block">
                <div class="info-label">Student Details</div>
                <div class="info-value"><?= Helper::e($fee['student_name'] ?: 'Student User') ?></div>
                <div class="info-sub">Class / Grade: <?= Helper::e($fee['current_class'] ?: 'Standard') ?></div>
                <div class="info-sub">Student ID / Email: <?= Helper::e($fee['student_email']) ?></div>
            </div>

            <div class="info-block">
                <div class="info-label">Payment Particulars</div>
                <div class="info-value">Mode: <?= strtoupper(Helper::e($fee['payment_mode'] ?: 'UPI / Bank Transfer')) ?></div>
                <div class="info-sub">Academic Session: <?= Helper::e($fee['academic_year'] ?: '2026-2027') ?></div>
                <div class="info-sub">Payment Date: <?= $fee['paid_at'] ? date('d F Y · h:i A', strtotime($fee['paid_at'])) : date('d F Y') ?></div>
            </div>
        </div>

        <!-- BREAKDOWN TABLE -->
        <table class="receipt-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Fee Month</th>
                    <th class="tar">Amount Due</th>
                    <th class="tar">Discount</th>
                    <th class="tar">Net Paid</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong><?= ucfirst(Helper::e($fee['fee_type'])) ?> Fee</strong>
                        <?php if (!empty($fee['batch_name'])): ?>
                            <div style="font-size:12px; color:#8A7667;">Batch: <?= Helper::e($fee['batch_name']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($fee['notes'])): ?>
                            <div style="font-size:12px; color:#8A7667;">Note: <?= Helper::e($fee['notes']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= Helper::e($fee['fee_month']) ?></td>
                    <td class="tar">₹<?= number_format((float)$fee['amount_due'], 2) ?></td>
                    <td class="tar">₹<?= number_format((float)$fee['discount'], 2) ?></td>
                    <td class="tar" style="font-weight:700; color:#15803D;">₹<?= number_format((float)$fee['amount_paid'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <!-- TOTAL BOX -->
        <div class="total-section">
            <div class="total-box">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>₹<?= number_format((float)$fee['amount_due'], 2) ?></span>
                </div>
                <?php if ((float)$fee['discount'] > 0): ?>
                    <div class="total-row">
                        <span>Concession / Discount:</span>
                        <span>-₹<?= number_format((float)$fee['discount'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="total-row grand-total">
                    <span>Total Amount Paid:</span>
                    <span style="color:#15803D;">₹<?= number_format((float)$fee['amount_paid'], 2) ?></span>
                </div>
                <div class="total-row" style="font-size:12px; margin-top:4px;">
                    <span>Remaining Balance:</span>
                    <span>₹<?= number_format(max(0.0, (float)$fee['amount_due'] - (float)$fee['discount'] - (float)$fee['amount_paid']), 2) ?></span>
                </div>
            </div>
        </div>

        <!-- FOOTER & SIGNATURE -->
        <div class="receipt-footer">
            <div>
                <div class="stamp-box">PAID & VERIFIED</div>
                <div style="margin-top:6px; font-size:11px;">System generated valid electronic receipt · Infinity Learning EduOS</div>
            </div>

            <div style="text-align:center;">
                <div class="sig-line">Authorized Signatory / Bursar</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

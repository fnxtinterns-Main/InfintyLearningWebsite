<?php
use App\Auth;
use App\Helper;
use App\FeeManagement;
use App\School;
use App\Database;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);
$isAiverse = School::isAiverse($schoolId);
$aiverseCourses = $isAiverse ? \App\Aiverse::getCourses('all') : [];

$db = Database::connect();
$students = $db->query("
    SELECT u.id, s.full_name, s.current_class, s.city, s.district, s.state, u.phone, u.email
    FROM users u
    JOIN students s ON s.user_id = u.id
    WHERE u.school_id = {$schoolId} AND u.status = 'active'
    ORDER BY s.full_name ASC
")->fetchAll();

$batches = $db->query("SELECT * FROM batches WHERE school_id = {$schoolId} ORDER BY name ASC")->fetchAll();

$selectedMonth = $_GET['month'] ?? date('F');
$selectedStatus = $_GET['status'] ?? '';
$selectedBatchId = !empty($_GET['batch_id']) ? (int)$_GET['batch_id'] : 0;

$fees = FeeManagement::list([
    'school_id' => $schoolId,
    'fee_month' => $selectedMonth ?: null,
    'status' => $selectedStatus ?: null,
    'batch_id' => $selectedBatchId ?: null,
]);

$totalDue = 0;
$totalCollected = 0;
$pendingCount = 0;
foreach ($fees as $f) {
    $totalDue += (float)$f['amount_due'];
    $totalCollected += (float)$f['amount_paid'];
    if (in_array($f['status'], ['pending','partial','overdue'])) {
        $pendingCount++;
    }
}

$pageTitle = ($isAiverse ? 'Course Fee Management — ' : 'Student Fee Management — ') . ($school['name'] ?? 'Infinity Learning');
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker"><?= $isAiverse ? '🤖 AIVERSE COURSE REVENUE & BILLING' : '💳 ACCOUNTS & TUITION' ?></span>
                <h1 style="margin:2px 0 0;"><?= $isAiverse ? 'AI Course Fees & Payments' : 'Student Fee Management' ?></h1>
                <p class="muted">
                    <?= $isAiverse 
                        ? 'Track AI course enrollments, verify scholar payments, and issue official digital receipts.' 
                        : 'Track monthly tuition, generate digital fee receipts, and send 1-click WhatsApp reminders to parents.' ?>
                </p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openNewFeeModal()">
                <?= $isAiverse ? '+ Enroll Scholar / Assign Fee' : '+ Record / Assign Fee' ?>
            </button>
        </div>

        <?php if ($msg = \App\Session::flash('success')): ?>
            <div class="alert alert-success"><?= Helper::e($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = \App\Session::flash('error')): ?>
            <div class="alert alert-danger"><?= Helper::e($err) ?></div>
        <?php endif; ?>

        <!-- STAT TILES -->
        <div class="grid grid-3" style="margin-bottom:20px;">
            <div class="stat">
                <span class="ico">💰</span>
                <span class="n" style="font-size:22px;">₹<?= number_format($totalDue, 2) ?></span>
                <span class="l">Total Dues (<?= Helper::e($selectedMonth) ?>)</span>
            </div>
            <div class="stat" style="border-left-color:#16A34A;">
                <span class="ico">✅</span>
                <span class="n" style="font-size:22px; color:#16A34A;">₹<?= number_format($totalCollected, 2) ?></span>
                <span class="l">Collected</span>
            </div>
            <div class="stat" style="border-left-color:#DC2626;">
                <span class="ico">⏳</span>
                <span class="n" style="font-size:22px; color:#DC2626;"><?= $pendingCount ?></span>
                <span class="l">Pending Students</span>
            </div>
        </div>

        <!-- FILTER BAR -->
        <div class="card" style="padding:14px 20px; margin-bottom:20px;">
            <form method="get" action="/school/fees" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <div style="width:160px;">
                    <select name="month" class="form-control">
                        <?php foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $m): ?>
                            <option value="<?= $m ?>" <?= $selectedMonth === $m ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="width:160px;">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="paid" <?= $selectedStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="partial" <?= $selectedStatus === 'partial' ? 'selected' : '' ?>>Partial</option>
                        <option value="overdue" <?= $selectedStatus === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>
                <div style="width:180px;">
                    <select name="batch_id" class="form-control">
                        <option value="">All Batches</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" <?= $selectedBatchId === (int)$b['id'] ? 'selected' : '' ?>><?= Helper::e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <?php if ($selectedStatus || $selectedBatchId): ?>
                    <a href="/school/fees?month=<?= urlencode($selectedMonth) ?>" class="btn btn-ghost">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABLE -->
        <div class="card" style="padding:0; overflow:hidden;">
            <?php if (empty($fees)): ?>
                <div style="padding:48px 24px; text-align:center; color:var(--il-muted);">
                    <div style="font-size:38px; margin-bottom:8px;">💳</div>
                    <h3>No fee records for <?= Helper::e($selectedMonth) ?></h3>
                    <p style="margin:4px 0 16px;">Assign fee amounts to students for this month.</p>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openNewFeeModal()">+ Record Fee Now</button>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:14px;">
                        <thead>
                            <tr style="border-bottom:2px solid var(--il-border); text-align:left; background:var(--surface-0); color:var(--il-muted); font-size:12px; text-transform:uppercase;">
                                <th style="padding:12px 18px;"><?= $isAiverse ? 'Scholar' : 'Student' ?></th>
                                <th style="padding:12px 18px;"><?= $isAiverse ? 'District & Location' : 'Class & Batch' ?></th>
                                <th style="padding:12px 18px;"><?= $isAiverse ? 'Course Program' : 'Type & Month' ?></th>
                                <th style="padding:12px 18px;">Amount Due</th>
                                <th style="padding:12px 18px;">Paid</th>
                                <th style="padding:12px 18px;">Status</th>
                                <th style="padding:12px 18px; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fees as $f): ?>
                                <?php
                                    $dueBalance = (float)$f['amount_due'] - (float)$f['discount'] - (float)$f['amount_paid'];
                                    $progTitle = !empty($f['notes']) ? $f['notes'] : ($isAiverse ? 'AI Specialization Track' : $f['fee_month']);
                                    $whatsappMsg = urlencode("Hello! This is a reminder from {$school['name']} regarding {$f['student_name']}'s course fee for {$progTitle}. Pending amount: ₹" . number_format($dueBalance, 2) . ". Kindly complete the payment.");
                                    $waLink = !empty($f['student_phone']) ? "https://wa.me/" . preg_replace('/[^0-9]/', '', $f['student_phone']) . "?text={$whatsappMsg}" : null;
                                ?>
                                <tr style="border-bottom:1px solid var(--il-border);">
                                    <td style="padding:12px 18px;">
                                        <strong><?= Helper::e($f['student_name']) ?></strong>
                                        <div class="muted" style="font-size:12px;"><?= Helper::e($f['student_phone'] ?: $f['student_email']) ?></div>
                                    </td>
                                    <td style="padding:12px 18px;">
                                        <?php if ($isAiverse): ?>
                                            <div><?= Helper::e($f['city'] ?? 'Scholar') ?></div>
                                            <div class="muted" style="font-size:12px;"><?= Helper::e(($f['district'] ?? '') . (!empty($f['state']) ? ', ' . $f['state'] : '')) ?></div>
                                        <?php else: ?>
                                            <?= Helper::e($f['current_class'] ?: 'Class') ?>
                                            <div class="muted" style="font-size:12px;"><?= Helper::e($f['batch_name'] ?: 'General') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:12px 18px;">
                                        <strong><?= Helper::e($progTitle) ?></strong>
                                        <div class="muted" style="font-size:12px;"><?= Helper::e($f['fee_month']) ?></div>
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
                                        <?php if (!empty($f['receipt_number'])): ?>
                                            <div style="font-size:11px; font-family:'JetBrains Mono', monospace; color:var(--il-muted);"><?= Helper::e($f['receipt_number']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:12px 18px; text-align:right;">
                                        <div style="display:flex; justify-content:flex-end; gap:6px; align-items:center;">
                                            <?php if ($f['status'] !== 'paid'): ?>
                                                <button type="button" class="btn btn-xs btn-primary" onclick="openPaymentModal(<?= (int)$f['id'] ?>, '<?= Helper::e($f['student_name']) ?>', <?= $dueBalance ?>)">
                                                    Record Pay 💵
                                                </button>
                                                <?php if ($waLink): ?>
                                                    <a href="<?= $waLink ?>" target="_blank" class="btn btn-xs btn-outline-success" style="background:#DCFCE7; color:#166534; border-color:#86EFAC;">
                                                        WhatsApp 📱
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if ((float)$f['amount_paid'] > 0 || $f['status'] === 'paid'): ?>
                                                <a href="/school/fee-receipt?id=<?= (int)$f['id'] ?>" target="_blank" class="btn btn-xs btn-secondary" title="View & Print Official Receipt" style="font-weight:700;">
                                                    🧾 Receipt
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($isAiverse): ?>
                                                <form method="POST" action="/school/courses/unenroll" style="margin:0; display:inline;" onsubmit="return confirm('Remove scholar <?= Helper::e(addslashes($f['student_name'])) ?> from this course enrollment?');">
                                                    <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                                    <input type="hidden" name="fee_id" value="<?= (int)$f['id'] ?>">
                                                    <input type="hidden" name="student_user_id" value="<?= (int)$f['student_user_id'] ?>">
                                                    <input type="hidden" name="redirect_to" value="/school/fees">
                                                    <button type="submit" class="btn btn-xs btn-outline-danger" style="font-size:10.5px; padding:3px 6px; color:#DC2626; border-color:#FECACA;" title="Remove Scholar from Course">
                                                        Drop ✕
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL TO RECORD / ASSIGN FEE -->
<div id="newFeeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(5px);">
    <div style="background:#fff; border-radius:18px; max-width:540px; width:100%; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h3 style="margin:0; font-size:19px;"><?= $isAiverse ? 'Enroll Scholar in AI Course / Assign Fee' : 'Assign Fee to Student' ?></h3>
            <button type="button" class="btn btn-sm btn-ghost" onclick="closeNewFeeModal()">&times;</button>
        </div>

        <form method="post" action="/school/fee-save-handler.php">
            <input type="hidden" name="action" value="assign">
            <input type="hidden" name="school_id" value="<?= $schoolId ?>">
            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">

            <div style="margin-bottom:14px;">
                <label class="form-label">Select <?= $isAiverse ? 'Scholar' : 'Student' ?> *</label>
                <select name="student_user_id" class="form-control" required>
                    <?php foreach ($students as $st): ?>
                        <option value="<?= (int)$st['id'] ?>">
                            <?= Helper::e($st['full_name']) ?> (<?= Helper::e($isAiverse ? ($st['city'] ?? $st['phone']) : $st['current_class']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($isAiverse): ?>
                <div style="margin-bottom:14px;">
                    <label class="form-label">Select AI Course Track *</label>
                    <select name="course_select" id="aiverseCourseSelect" class="form-control" onchange="onCourseSelectChange(this)" required>
                        <option value="">-- Choose AI Course --</option>
                        <?php foreach ($aiverseCourses as $ac): ?>
                            <option value="<?= (int)$ac['id'] ?>" data-fee="<?= (float)$ac['fee'] ?>" data-title="<?= Helper::e($ac['title']) ?>">
                                <?= Helper::e($ac['title']) ?> (₹<?= number_format((float)$ac['fee'], 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="notes" id="aiverseCourseNotes" value="AI Course Enrollment">
                    <input type="hidden" name="fee_type" value="activity">
                    <input type="hidden" name="fee_month" value="<?= date('F Y') ?>">
                </div>
            <?php else: ?>
                <div class="grid grid-2" style="margin-bottom:14px;">
                    <div>
                        <label class="form-label">Fee Month *</label>
                        <select name="fee_month" class="form-control" required>
                            <?php foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $m): ?>
                                <option value="<?= $m ?>" <?= date('F') === $m ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Fee Type</label>
                        <select name="fee_type" class="form-control">
                            <option value="tuition">Tuition Fee</option>
                            <option value="chess_academy">Chess Academy Fee</option>
                            <option value="admission">Admission Fee</option>
                            <option value="transport">Transport Fee</option>
                            <option value="exam">Exam Fee</option>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-2" style="margin-bottom:14px;">
                <div>
                    <label class="form-label">Amount Due (₹) *</label>
                    <input type="number" step="0.5" name="amount_due" id="feeAmountDueInput" class="form-control" value="<?= $isAiverse ? '4999' : '2500' ?>" required>
                </div>
                <div>
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeNewFeeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Fee Due</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TO RECORD PAYMENT -->
<div id="paymentModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(5px);">
    <div style="background:#fff; border-radius:18px; max-width:480px; width:100%; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.3);">
        <h3 style="margin-top:0; font-size:19px;">Record Fee Payment</h3>
        <p class="muted" id="payStudentText" style="margin-bottom:16px;">Student: </p>

        <form method="post" action="/school/fee-save-handler.php">
            <input type="hidden" name="action" value="pay">
            <input type="hidden" name="fee_id" id="payFeeId">
            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">

            <div style="margin-bottom:14px;">
                <label class="form-label">Amount Paid (₹) *</label>
                <input type="number" step="0.5" name="amount_paid" id="payAmountInput" class="form-control" required>
            </div>

            <div style="margin-bottom:14px;">
                <label class="form-label">Payment Mode *</label>
                <select name="payment_mode" class="form-control" required>
                    <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                    <option value="card">Debit / Credit Card</option>
                    <option value="cheque">Cheque</option>
                </select>
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label">Notes / Transaction Reference</label>
                <input type="text" name="notes" class="form-control" placeholder="e.g. UPI Ref #439201948">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm & Issue Receipt</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewFeeModal() { document.getElementById('newFeeModal').style.display = 'flex'; }
function closeNewFeeModal() { document.getElementById('newFeeModal').style.display = 'none'; }
function onCourseSelectChange(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.fee) {
        document.getElementById('feeAmountDueInput').value = opt.dataset.fee;
        var noteInput = document.getElementById('aiverseCourseNotes');
        if (noteInput) {
            noteInput.value = 'Course Enrollment: ' + opt.dataset.title;
        }
    }
}
function openPaymentModal(id, name, due) {
    document.getElementById('payFeeId').value = id;
    document.getElementById('payStudentText').textContent = 'Student: ' + name + ' • Balance Due: ₹' + due.toFixed(2);
    document.getElementById('payAmountInput').value = due;
    document.getElementById('paymentModal').style.display = 'flex';
}
function closePaymentModal() { document.getElementById('paymentModal').style.display = 'none'; }
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

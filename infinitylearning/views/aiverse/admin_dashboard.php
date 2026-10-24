<?php
declare(strict_types=1);

use App\Auth;
use App\Aiverse;
use App\Helper;
use App\School;
use App\Database;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);

$db = Database::connect();

// 1. Stats
$allCourses = Aiverse::getCourses('all');
$publishedCoursesCount = count(array_filter($allCourses, fn($c) => $c['status'] === 'published'));

// Total Aiverse Scholars
$scholarCount = (int)$db->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE school_id = {$schoolId} AND role = 'student'
")->fetchColumn();

// Total Fee Collections & Pending Balances
$feeStats = $db->query("
    SELECT 
        COALESCE(SUM(amount_paid), 0) AS total_collected,
        COALESCE(SUM(CASE WHEN status IN ('pending','partial','overdue') THEN (amount_due - discount - amount_paid) ELSE 0 END), 0) AS total_pending
    FROM student_fees
    WHERE school_id = {$schoolId}
")->fetch();

$totalCollected = (float)($feeStats['total_collected'] ?? 0);
$totalPending = (float)($feeStats['total_pending'] ?? 0);

// Recent Enrollments / Fees
$recentEnrollments = Aiverse::getAllEnrollments();
$recentEnrollments = array_slice($recentEnrollments, 0, 8);

$pageTitle = 'AIVERSE Operations Console — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div>

        <!-- HEADER -->
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px; align-items:center;">
            <div>
                <span class="kicker">🤖 AIVERSE ACADEMY · OPERATIONS</span>
                <h1 style="margin:2px 0 0; font-weight:800;">AIVERSE Operations Console</h1>
                <p class="muted" style="margin:2px 0 0;">
                    Manage Artificial Intelligence courses, track scholar enrollments, and supervise course revenue.
                </p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="/school/courses" class="btn btn-primary btn-sm">+ Publish AI Course</a>
                <a href="/school/fees" class="btn btn-secondary btn-sm">💳 Track Payments</a>
                <a href="/school/students" class="btn btn-outline-secondary btn-sm">👩‍🎓 Scholars</a>
            </div>
        </div>

        <!-- STAT TILES -->
        <div class="grid grid-4" style="gap:16px; margin-bottom:24px;">
            <div class="stat" style="background:var(--surface-1); border-radius:14px; padding:18px; border-left:4px solid var(--il-primary);">
                <span class="ico" style="font-size:22px;">🤖</span>
                <span class="n" style="font-size:26px; font-weight:800;"><?= $publishedCoursesCount ?></span>
                <span class="l" style="font-size:11.5px; text-transform:uppercase; letter-spacing:0.04em;">Published Courses</span>
            </div>
            <div class="stat" style="background:var(--surface-1); border-radius:14px; padding:18px; border-left:4px solid #3B82F6;">
                <span class="ico" style="font-size:22px;">👩‍🎓</span>
                <span class="n" style="font-size:26px; font-weight:800; color:#3B82F6;"><?= $scholarCount ?></span>
                <span class="l" style="font-size:11.5px; text-transform:uppercase; letter-spacing:0.04em;">Registered Scholars</span>
            </div>
            <div class="stat" style="background:var(--surface-1); border-radius:14px; padding:18px; border-left:4px solid #16A34A;">
                <span class="ico" style="font-size:22px;">💰</span>
                <span class="n" style="font-size:24px; font-family:'JetBrains Mono', monospace; font-weight:800; color:#16A34A;">₹<?= number_format($totalCollected, 2) ?></span>
                <span class="l" style="font-size:11.5px; text-transform:uppercase; letter-spacing:0.04em;">Total Course Collections</span>
            </div>
            <div class="stat" style="background:var(--surface-1); border-radius:14px; padding:18px; border-left:4px solid #DC2626;">
                <span class="ico" style="font-size:22px;">⏳</span>
                <span class="n" style="font-size:24px; font-family:'JetBrains Mono', monospace; font-weight:800; color:#DC2626;">₹<?= number_format($totalPending, 2) ?></span>
                <span class="l" style="font-size:11.5px; text-transform:uppercase; letter-spacing:0.04em;">Pending Fee Balance</span>
            </div>
        </div>

        <!-- RECENT ENROLLMENTS & COURSE TRACKS -->
        <div class="grid grid-2" style="gap:20px; margin-bottom:28px;">
            
            <!-- RECENT ENROLLMENTS -->
            <div class="card" style="border:1.5px solid var(--il-border); border-radius:16px; padding:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="margin:0; font-size:1.15rem;">Recent Scholar Enrollments</h3>
                    <a href="/school/fees" class="btn btn-xs btn-outline-secondary">View All →</a>
                </div>

                <?php if (empty($recentEnrollments)): ?>
                    <div style="text-align:center; padding:32px 16px; color:var(--il-muted);">
                        <div style="font-size:32px; margin-bottom:8px;">🎓</div>
                        <p style="margin:0; font-size:13.5px;">No enrollments recorded yet.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="table" style="width:100%; border-collapse:collapse; font-size:13px; margin:0;">
                            <thead>
                                <tr style="border-bottom:1px solid var(--il-border); text-align:left; color:var(--il-muted); font-size:11px; text-transform:uppercase;">
                                    <th style="padding:8px 10px;">Scholar</th>
                                    <th style="padding:8px 10px;">Course</th>
                                    <th style="padding:8px 10px;">Status</th>
                                    <th style="padding:8px 10px; text-align:right;">Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentEnrollments as $re): ?>
                                    <tr style="border-bottom:1px solid var(--il-border);">
                                        <td style="padding:10px;">
                                            <strong><?= Helper::e($re['student_name'] ?: 'Scholar') ?></strong>
                                            <div class="muted" style="font-size:11px;"><?= Helper::e($re['student_phone'] ?: $re['student_email']) ?></div>
                                        </td>
                                        <td style="padding:10px;">
                                            <div><?= Helper::e($re['course_title']) ?></div>
                                            <span class="muted" style="font-size:11px;">₹<?= number_format((float)($re['amount_due'] ?? $re['course_fee']), 2) ?></span>
                                        </td>
                                        <td style="padding:10px;">
                                            <span class="badge badge-<?= $re['payment_status'] === 'paid' ? 'success' : ($re['payment_status'] === 'partial' ? 'primary' : 'warning') ?>" style="text-transform:uppercase; font-size:10px;">
                                                <?= Helper::e($re['payment_status'] ?? 'pending') ?>
                                            </span>
                                        </td>
                                        <td style="padding:10px; text-align:right;">
                                            <?php if (!empty($re['fee_id']) && ((float)($re['amount_paid'] ?? 0) > 0 || !empty($re['receipt_number']))): ?>
                                                <a href="/school/fee-receipt?id=<?= (int)$re['fee_id'] ?>" target="_blank" class="btn btn-xs btn-secondary" style="font-size:11px; padding:2px 6px;">
                                                    🧾 View
                                                </a>
                                            <?php else: ?>
                                                <span class="muted" style="font-size:11px;">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ACTIVE AI COURSES -->
            <div class="card" style="border:1.5px solid var(--il-border); border-radius:16px; padding:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="margin:0; font-size:1.15rem;">Active Course Catalog</h3>
                    <a href="/school/courses" class="btn btn-xs btn-outline-secondary">Manage Courses →</a>
                </div>

                <div style="display:flex; flex-direction:column; gap:12px;">
                    <?php foreach (array_slice($allCourses, 0, 5) as $ac): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:var(--surface-1); border-radius:10px; border:1px solid var(--il-border);">
                            <div>
                                <div style="font-weight:700; font-size:13.5px;"><?= Helper::e($ac['title']) ?></div>
                                <div class="muted" style="font-size:11.5px;">⏱️ <?= Helper::e($ac['duration']) ?> · 📊 <?= Helper::e($ac['level']) ?></div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-family:'JetBrains Mono', monospace; font-weight:800; color:var(--il-primary); font-size:13.5px;">
                                    ₹<?= number_format((float)$ac['fee'], 2) ?>
                                </div>
                                <span class="badge badge-<?= $ac['status'] === 'published' ? 'success' : 'warning' ?>" style="font-size:10px; text-transform:uppercase;">
                                    <?= Helper::e($ac['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

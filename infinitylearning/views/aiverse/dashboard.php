<?php
use App\Auth;
use App\Helper;
use App\Aiverse;
use App\Session;

Auth::requireRole('student');
$user = Auth::user();
$studentId = (int)$user['id'];

$student = [];
try {
    $student = Auth::findStudentByUserId($studentId) ?: [];
} catch (\Throwable $e) {}

// Aiverse Courses & Student Enrollments
$allCourses = Aiverse::getCourses('published');
$enrollments = Aiverse::getStudentEnrollments($studentId);
$summary = Aiverse::getStudentAiverseFeeSummary($studentId);
$activeBanners = Aiverse::getBanners(true);
$allBundles = Aiverse::getBundles('published');

$enrolledCourseIds = array_map('intval', array_column($enrollments, 'course_id'));

// Build lookup map for courses and active BOGOs
$courseLookup = [];
$bogoMap = [];
foreach ($allCourses as $c) {
    $cid = (int)$c['id'];
    $courseLookup[$cid] = $c;
    $bogo = Aiverse::getActiveBogoForCourse($cid);
    if ($bogo && !empty($bogo['bogo_free_course_id'])) {
        $bogoMap[$cid] = $bogo;
    }
}

$pageTitle = 'AIverse Hub — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';

$displayName = $student['full_name'] ?? 'Scholar';
?>
<section class="section-sm">
    <div class="container-narrow">

        <?php if ($msg = Session::flash('success')): ?>
            <div class="alert alert-success" style="margin-bottom:20px; font-weight:600; font-size:14px; border-radius:12px; border-left:5px solid #16A34A; box-shadow:0 4px 12px rgba(22,163,74,0.12);">
                <?= Helper::e($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg = Session::flash('info')): ?>
            <div class="alert alert-info" style="margin-bottom:20px; font-weight:600; font-size:14px; border-radius:12px; border-left:5px solid var(--il-primary);">
                <?= Helper::e($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg = Session::flash('error')): ?>
            <div class="alert alert-danger" style="margin-bottom:20px; font-weight:600; font-size:14px; border-radius:12px; border-left:5px solid #DC2626;">
                <?= Helper::e($msg) ?>
            </div>
        <?php endif; ?>

        <!-- PROMOTIONAL HERO BANNERS CAROUSEL / HIGHLIGHT WIDGET -->
        <?php if (!empty($activeBanners)): ?>
            <div class="banner-carousel-wrap" style="margin-bottom:28px; position:relative;">
                <div id="bannerCarousel" style="position:relative; border-radius:18px; overflow:hidden; box-shadow:0 12px 36px rgba(0,0,0,0.15);">
                    <?php foreach ($activeBanners as $idx => $bn): ?>
                        <?php $bg = $bn['bg_gradient'] ?: 'linear-gradient(135deg, #1e1b4b 0%, #31104b 50%, #0f172a 100%)'; ?>
                        <div class="banner-slide" data-slide-index="<?= $idx ?>" style="display:<?= $idx === 0 ? 'block' : 'none' ?>; background:<?= Helper::e($bg) ?>; padding:32px 36px; color:#FFFFFF; position:relative; overflow:hidden;">
                            
                            <!-- Ambient glowing effect -->
                            <div style="position:absolute; top:-40px; right:-40px; width:220px; height:220px; background:radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%); border-radius:50%; pointer-events:none;"></div>
                            
                            <div style="position:relative; z-index:2; max-width:800px;">
                                <div style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.18); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.3); padding:4px 12px; border-radius:999px; font-size:11px; font-weight:800; letter-spacing:0.05em; text-transform:uppercase; margin-bottom:12px;">
                                    <?= Helper::e($bn['badge'] ?: 'Special Offer') ?>
                                </div>
                                <h2 style="font-size:1.85rem; font-weight:800; line-height:1.25; margin:0 0 10px; color:#FFFFFF; text-shadow:0 2px 10px rgba(0,0,0,0.35);">
                                    <?= Helper::e($bn['title']) ?>
                                </h2>
                                <?php if (!empty($bn['subtitle'])): ?>
                                    <p style="font-size:0.98rem; line-height:1.55; color:rgba(255,255,255,0.9); margin:0 0 20px;">
                                        <?= Helper::e($bn['subtitle']) ?>
                                    </p>
                                <?php endif; ?>
                                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                                    <a href="<?= Helper::e($bn['cta_link'] ?: '#bundles') ?>" class="btn" style="background:#FFFFFF; color:#0f172a; font-weight:800; font-size:13.5px; padding:9px 22px; border-radius:10px; border:none; box-shadow:0 4px 16px rgba(0,0,0,0.25);">
                                        <?= Helper::e($bn['cta_text'] ?: 'Explore Offers →') ?>
                                    </a>
                                    <a href="#courses" class="btn btn-sm" style="background:rgba(255,255,255,0.15); color:#FFFFFF; border:1px solid rgba(255,255,255,0.3); backdrop-filter:blur(4px); font-size:12.5px;">
                                        Browse All Courses
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- CAROUSEL CONTROLS (IF MULTIPLE BANNERS) -->
                <?php if (count($activeBanners) > 1): ?>
                    <div style="display:flex; justify-content:center; gap:8px; margin-top:12px;">
                        <?php foreach ($activeBanners as $idx => $bn): ?>
                            <button type="button" class="carousel-dot" onclick="switchBannerSlide(<?= $idx ?>)" style="width:10px; height:10px; border-radius:50%; border:none; background:<?= $idx === 0 ? 'var(--il-primary)' : 'var(--il-border)' ?>; cursor:pointer; padding:0; transition:all 0.2s;" aria-label="Slide <?= $idx + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- AIVERSE HERO HEADER -->
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px; margin-bottom:28px;">
            <div>
                <div style="display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg, rgba(99,102,241,0.15), rgba(168,85,247,0.15)); border:1.5px solid rgba(139,92,246,0.35); padding:4px 12px; border-radius:999px; margin-bottom:8px;">
                    <span style="font-size:14px;">🚀</span>
                    <span style="font-size:12px; font-weight:800; letter-spacing:0.04em; color:var(--il-primary);">AIVERSE ACADEMY</span>
                </div>
                <h1 style="margin:4px 0 6px; font-size:2rem;">Welcome, <?= Helper::e($displayName) ?>!</h1>
                <p class="muted" style="margin:0; font-size:0.95rem;">
                    Your dedicated portal for Artificial Intelligence, Autonomous Agents, Model Context Protocols (MCP) &amp; Machine Learning programs.
                </p>
            </div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <a href="#bundles" class="btn btn-outline-primary btn-sm">📦 View Bundles &amp; Offers</a>
                <a href="#courses" class="btn btn-primary btn-sm">Explore Courses</a>
                <a href="/profile" class="btn btn-outline-secondary btn-sm">My Profile</a>
            </div>
        </div>

        <!-- FEES & PAYMENT TRACKER (COURSE SPECIFIC) -->
        <div class="card" style="margin-bottom:28px; padding:24px; border:1.5px solid var(--il-border); border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.04);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px; border-bottom:1px solid var(--il-border); padding-bottom:14px;">
                <div>
                    <span class="kicker" style="color:var(--il-primary); font-size:11px; font-weight:800; letter-spacing:0.05em;">💳 COURSE ACCOUNT &amp; INVOICES</span>
                    <h2 style="margin:2px 0 0; font-size:1.35rem;">Fee &amp; Payment Summary</h2>
                </div>
                <a href="/student/fees" class="btn btn-xs btn-outline-secondary">Detailed Payment History →</a>
            </div>

            <!-- COURSE BREAKDOWN & FEE TRACKER -->
            <?php if (empty($enrollments)): ?>
                <div style="text-align:center; padding:36px 20px; background:var(--surface-0); border-radius:12px; border:1px dashed var(--il-border);">
                    <div style="font-size:36px; margin-bottom:10px;">🎓</div>
                    <h3 style="font-size:1.15rem; margin-bottom:6px;">No Active Course Enrollments Yet</h3>
                    <p class="muted" style="max-width:520px; margin:0 auto 16px; font-size:0.9rem;">
                        You have not enrolled in any course yet. Your fee balance is currently <strong>₹0.00</strong>. Take advantage of our course bundles and special offers below to enroll!
                    </p>
                    <div style="display:flex; justify-content:center; gap:10px;">
                        <a href="#bundles" class="btn btn-outline-primary btn-sm">📦 View 10% Discount Bundles</a>
                        <a href="#courses" class="btn btn-primary btn-sm">Browse All Tracks ↓</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- STAT TILES -->
                <div class="grid grid-3" style="gap:16px; margin-bottom:24px;">
                    <div class="stat" style="background:var(--surface-1); border-radius:12px; padding:18px; border-left:4px solid var(--il-primary);">
                        <span class="ico" style="font-size:22px;">📜</span>
                        <span class="n" style="font-size:24px; font-family:'JetBrains Mono', monospace; font-weight:800;">₹<?= number_format((float)$summary['total_due'], 2) ?></span>
                        <span class="l" style="font-size:12px; text-transform:uppercase; letter-spacing:0.04em;">Total Course Fee</span>
                    </div>
                    <div class="stat" style="background:var(--surface-1); border-radius:12px; padding:18px; border-left:4px solid #16A34A;">
                        <span class="ico" style="font-size:22px;">✅</span>
                        <span class="n" style="font-size:24px; font-family:'JetBrains Mono', monospace; font-weight:800; color:#16A34A;">₹<?= number_format((float)$summary['total_paid'], 2) ?></span>
                        <span class="l" style="font-size:12px; text-transform:uppercase; letter-spacing:0.04em;">Amount Paid</span>
                    </div>
                    <div class="stat" style="background:var(--surface-1); border-radius:12px; padding:18px; border-left:4px solid <?= (float)$summary['balance_pending'] > 0 ? '#DC2626' : '#16A34A' ?>;">
                        <span class="ico" style="font-size:22px;"><?= (float)$summary['balance_pending'] > 0 ? '⏳' : '🎉' ?></span>
                        <span class="n" style="font-size:24px; font-family:'JetBrains Mono', monospace; font-weight:800; color:<?= (float)$summary['balance_pending'] > 0 ? '#DC2626' : '#16A34A' ?>;">
                            ₹<?= number_format((float)$summary['balance_pending'], 2) ?>
                        </span>
                        <span class="l" style="font-size:12px; text-transform:uppercase; letter-spacing:0.04em;">Pending Balance</span>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:13.5px;">
                        <thead>
                            <tr style="border-bottom:2px solid var(--il-border); text-align:left; background:var(--surface-0); color:var(--il-muted); font-size:11.5px; text-transform:uppercase;">
                                <th style="padding:10px 14px;">Course Program</th>
                                <th style="padding:10px 14px;">Fee / Promo</th>
                                <th style="padding:10px 14px;">Paid</th>
                                <th style="padding:10px 14px;">Pending</th>
                                <th style="padding:10px 14px;">Payment Status</th>
                                <th style="padding:10px 14px; text-align:right;">Actions &amp; Receipts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $en): ?>
                                <?php 
                                    $due = (float)($en['amount_due'] ?? $en['course_fee']);
                                    $paid = (float)($en['amount_paid'] ?? 0);
                                    $discount = (float)($en['discount'] ?? 0);
                                    $pending = max(0.0, $due - $paid);
                                    $status = $en['payment_status'] ?? ($paid >= $due && $due > 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending'));
                                    $isFreePromo = ($due <= 0.0 && $discount > 0);
                                ?>
                                <tr style="border-bottom:1px solid var(--il-border);">
                                    <td style="padding:12px 14px;">
                                        <strong><?= Helper::e($en['course_title']) ?></strong>
                                        <?php if ($isFreePromo): ?>
                                            <span class="badge" style="background:rgba(22,163,74,0.15); color:#16A34A; font-size:10px; margin-left:6px; font-weight:700;">🎁 100% FREE PROMO</span>
                                        <?php elseif ($discount > 0): ?>
                                            <span class="badge" style="background:rgba(99,102,241,0.15); color:var(--il-primary); font-size:10px; margin-left:6px; font-weight:700;">PROMO DISCOUNT APPLIED</span>
                                        <?php endif; ?>
                                        <div class="muted" style="font-size:11.5px;">Enrolled on <?= date('d M Y', strtotime($en['created_at'])) ?> · <?= Helper::e($en['duration'] ?? '8 Weeks') ?></div>
                                    </td>
                                    <td style="padding:12px 14px; font-family:'JetBrains Mono', monospace; font-weight:700;">
                                        <?php if ($isFreePromo): ?>
                                            <span style="color:#16A34A;">₹0.00</span>
                                            <span class="muted" style="font-size:10.5px; text-decoration:line-through; display:block;">₹<?= number_format((float)$en['course_fee'], 2) ?></span>
                                        <?php else: ?>
                                            ₹<?= number_format($due, 2) ?>
                                            <?php if ($discount > 0): ?>
                                                <div style="font-size:10.5px; color:#16A34A; font-weight:600;">-₹<?= number_format($discount, 2) ?> saved</div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:12px 14px; font-family:'JetBrains Mono', monospace; color:#16A34A; font-weight:700;">
                                        ₹<?= number_format($paid, 2) ?>
                                    </td>
                                    <td style="padding:12px 14px; font-family:'JetBrains Mono', monospace; color:<?= $pending > 0 ? '#DC2626' : '#16A34A' ?>; font-weight:700;">
                                        ₹<?= number_format($pending, 2) ?>
                                    </td>
                                    <td style="padding:12px 14px;">
                                        <span class="badge badge-<?= ($status === 'paid' || $isFreePromo) ? 'success' : ($status === 'partial' ? 'primary' : 'warning') ?>" style="text-transform:uppercase; font-size:10.5px;">
                                            <?= $isFreePromo ? 'PAID (PROMO)' : Helper::e($status) ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 14px; text-align:right;">
                                        <div style="display:flex; justify-content:flex-end; align-items:center; gap:8px;">
                                            <?php if (!empty($en['fee_id']) && ($paid > 0 || $isFreePromo || !empty($en['receipt_number']))): ?>
                                                <a href="/student/fee-receipt?id=<?= (int)$en['fee_id'] ?>" target="_blank" class="btn btn-xs btn-secondary" style="font-size:11px; padding:3px 8px;">
                                                    🧾 Receipt
                                                </a>
                                            <?php endif; ?>
                                            <form method="POST" action="/aiverse/unenroll" style="margin:0; display:inline;" onsubmit="return confirm('Are you sure you want to drop <?= Helper::e(addslashes($en['course_title'])) ?>? Your enrollment will be cancelled.');">
                                                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                                <input type="hidden" name="course_id" value="<?= (int)$en['course_id'] ?>">
                                                <input type="hidden" name="redirect_to" value="/dashboard">
                                                <button type="submit" class="btn btn-xs btn-outline-danger" style="font-size:11px; padding:3px 8px; color:#DC2626; border-color:#FECACA;" title="Drop / Unenroll from this course">
                                                    Drop Course ✕
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- SPECIAL COURSE BUNDLES & PROMOTIONS (5-10% OFF & BOGO) -->
        <?php if (!empty($allBundles)): ?>
            <div id="bundles" style="margin-bottom:36px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
                    <div>
                        <div style="display:inline-flex; align-items:center; gap:6px; background:rgba(22,163,74,0.12); color:#16A34A; border:1px solid rgba(22,163,74,0.3); padding:3px 10px; border-radius:999px; font-size:11px; font-weight:800; letter-spacing:0.04em; text-transform:uppercase; margin-bottom:4px;">
                            🎁 MASSIVE COMBO SAVINGS
                        </div>
                        <h2 style="margin:2px 0 0; font-size:1.5rem;">Curriculum Packages &amp; Offers</h2>
                        <p class="muted" style="margin:2px 0 0; font-size:0.9rem;">Bundle multiple tracks for 5%–10% instant discount or claim Buy-One-Get-One (BOGO) learning packages.</p>
                    </div>
                    <span class="badge badge-brand" style="font-size:12px; padding:5px 12px;"><?= count($allBundles) ?> Special Offers Active</span>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:20px;">
                    <?php foreach ($allBundles as $b): ?>
                        <?php
                            $cids = json_decode($b['course_ids'] ?? '[]', true) ?: [];
                            $origPrice = (float)$b['original_price'];
                            $bPrice = (float)$b['bundle_price'];
                            $savings = max(0.0, $origPrice - $bPrice);
                            $isBogo = !empty($b['is_bogo']);
                            
                            // Check if student is already enrolled in all courses of this bundle
                            $allEnrolled = true;
                            foreach ($cids as $cid) {
                                if (!in_array((int)$cid, $enrolledCourseIds, true)) {
                                    $allEnrolled = false;
                                    break;
                                }
                            }
                        ?>
                        <div class="card" style="border:1.5px solid <?= $isBogo ? 'rgba(168,85,247,0.45)' : 'rgba(22,163,74,0.4)' ?>; border-radius:16px; padding:24px; display:flex; flex-direction:column; justify-content:space-between; position:relative; overflow:hidden; background:linear-gradient(180deg, var(--surface-0) 0%, var(--surface-1) 100%); box-shadow:0 6px 24px rgba(0,0,0,0.04);">
                            
                            <!-- Highlight badge -->
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                                <span class="badge" style="font-size:11px; font-weight:800; background:<?= $isBogo ? 'linear-gradient(135deg, #9333EA, #7E22CE)' : 'linear-gradient(135deg, #16A34A, #15803D)' ?>; color:#FFFFFF; padding:4px 12px; border-radius:999px;">
                                    <?= Helper::e($b['badge'] ?: ($isBogo ? '🎁 BOGO OFFER' : 'SAVE 10%')) ?>
                                </span>
                                <?php if ($savings > 0): ?>
                                    <span style="font-size:12px; font-weight:800; color:#16A34A; background:rgba(22,163,74,0.12); padding:3px 8px; border-radius:6px;">
                                        Save ₹<?= number_format($savings, 0) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div>
                                <h3 style="font-size:1.25rem; margin:0 0 6px; font-weight:800;"><?= Helper::e($b['title']) ?></h3>
                                <?php if (!empty($b['tagline'])): ?>
                                    <p class="muted" style="font-size:0.88rem; line-height:1.45; margin-bottom:14px;">
                                        <?= Helper::e($b['tagline']) ?>
                                    </p>
                                <?php endif; ?>

                                <!-- COURSES INCLUDED LIST -->
                                <div style="background:var(--surface-0); border:1px solid var(--il-border); border-radius:10px; padding:12px; margin-bottom:18px;">
                                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--il-muted); margin-bottom:8px;">
                                        <?= $isBogo ? 'BOGO Package Breakdown' : 'Included Tracks in this Bundle' ?>
                                    </div>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <?php foreach ($cids as $cid): ?>
                                            <?php if (isset($courseLookup[(int)$cid])): ?>
                                                <?php 
                                                    $cItem = $courseLookup[(int)$cid];
                                                    $isFreeItem = ($isBogo && (int)$b['bogo_free_course_id'] === (int)$cid);
                                                    $alreadyHas = in_array((int)$cid, $enrolledCourseIds, true);
                                                ?>
                                                <div style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px;">
                                                    <span style="display:flex; align-items:center; gap:6px;">
                                                        <span><?= $isFreeItem ? '🎁' : '✅' ?></span>
                                                        <strong style="color:<?= $isFreeItem ? '#16A34A' : 'inherit' ?>;"><?= Helper::e($cItem['title']) ?></strong>
                                                        <?php if ($alreadyHas): ?>
                                                            <span class="badge badge-success" style="font-size:9.5px; padding:1px 6px;">Already Enrolled</span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span style="font-family:'JetBrains Mono', monospace; font-size:12px; font-weight:700; color:<?= $isFreeItem ? '#16A34A' : 'var(--il-muted)' ?>;">
                                                        <?= $isFreeItem ? 'FREE' : '₹' . number_format((float)$cItem['fee'], 0) ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- PRICING & ENROLL -->
                            <div style="border-top:1px solid var(--il-border); padding-top:16px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:12px;">
                                    <div>
                                        <div class="muted" style="font-size:11px;">Total Bundle Price</div>
                                        <div style="display:flex; align-items:baseline; gap:8px;">
                                            <span style="font-size:1.45rem; font-family:'JetBrains Mono', monospace; font-weight:800; color:#16A34A;">
                                                ₹<?= number_format($bPrice, 2) ?>
                                            </span>
                                            <?php if ($origPrice > $bPrice): ?>
                                                <span class="muted" style="font-size:0.9rem; font-family:'JetBrains Mono', monospace; text-decoration:line-through;">
                                                    ₹<?= number_format($origPrice, 2) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($b['discount_pct'] > 0): ?>
                                        <span class="badge" style="background:rgba(22,163,74,0.15); color:#16A34A; font-weight:800; font-size:11.5px;">
                                            <?= Helper::e($b['discount_pct']) ?>% OFF
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($allEnrolled): ?>
                                    <button type="button" class="btn btn-success btn-sm btn-block" style="width:100%; font-weight:700;" disabled>
                                        ✅ You Own All Included Tracks
                                    </button>
                                <?php else: ?>
                                    <form method="POST" action="/aiverse/enroll-bundle" style="margin:0;" onsubmit="return confirm('Enroll in <?= Helper::e(addslashes($b['title'])) ?> for ₹<?= number_format($bPrice, 2) ?>? Your bundle discount will be applied.');">
                                        <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                        <input type="hidden" name="bundle_id" value="<?= (int)$b['id'] ?>">
                                        <button type="submit" class="btn btn-primary btn-sm btn-block" style="width:100%; font-weight:800; background:<?= $isBogo ? 'linear-gradient(135deg, #7E22CE, #9333EA)' : '' ?>; border:none; padding:10px;">
                                            <?= $isBogo ? '🎁 Claim BOGO Bundle Offer →' : 'Enroll in Bundle & Save 10% →' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- AI COURSES CATALOG & SPECIALIZATIONS -->
        <div id="courses" style="margin-bottom:36px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                <div>
                    <span class="kicker" style="color:var(--il-primary); font-size:11px; font-weight:800; letter-spacing:0.05em;">🤖 CURRICULUM &amp; SPECIALIZATIONS</span>
                    <h2 style="margin:2px 0 0; font-size:1.5rem;">AIverse Course Tracks</h2>
                    <p class="muted" style="margin:2px 0 0; font-size:0.9rem;">Hands-on Artificial Intelligence programs, Model Context Protocol (MCP) tooling &amp; multi-agent systems.</p>
                </div>
                <span class="badge badge-brand" style="font-size:12px; padding:5px 12px;"><?= count($allCourses) ?> Specialized Tracks</span>
            </div>

            <!-- CATEGORY FILTER PILLS -->
            <div style="display:flex; gap:8px; overflow-x:auto; padding-bottom:12px; margin-bottom:16px;">
                <button type="button" class="btn btn-xs cat-filter-btn active" onclick="filterCourses('all', this)" style="border-radius:999px; padding:6px 14px; font-weight:700;">All Tracks (<?= count($allCourses) ?>)</button>
                <button type="button" class="btn btn-xs cat-filter-btn btn-outline-secondary" onclick="filterCourses('Generative AI', this)" style="border-radius:999px; padding:6px 14px;">Generative AI</button>
                <button type="button" class="btn btn-xs cat-filter-btn btn-outline-secondary" onclick="filterCourses('Agentic Systems', this)" style="border-radius:999px; padding:6px 14px;">Agentic Systems &amp; Swarms</button>
                <button type="button" class="btn btn-xs cat-filter-btn btn-outline-secondary" onclick="filterCourses('Model Context Protocols', this)" style="border-radius:999px; padding:6px 14px;">MCP &amp; Tooling</button>
                <button type="button" class="btn btn-xs cat-filter-btn btn-outline-secondary" onclick="filterCourses('Cloud AI & Enterprise', this)" style="border-radius:999px; padding:6px 14px;">Cloud &amp; Azure</button>
                <button type="button" class="btn btn-xs cat-filter-btn btn-outline-secondary" onclick="filterCourses('Programming & Data Science', this)" style="border-radius:999px; padding:6px 14px;">Python &amp; Data</button>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:20px;" id="coursesGrid">
                <?php foreach ($allCourses as $c): ?>
                    <?php 
                        $cid = (int)$c['id'];
                        $isEnrolled = in_array($cid, $enrolledCourseIds, true);
                        $hasBogo = isset($bogoMap[$cid]);
                        $bogoData = $hasBogo ? $bogoMap[$cid] : null;
                        $freeCourseTitle = ($hasBogo && isset($courseLookup[(int)$bogoData['bogo_free_course_id']])) 
                            ? $courseLookup[(int)$bogoData['bogo_free_course_id']]['title'] 
                            : null;

                        $iconMap = [
                            'generative' => '🧠',
                            'python' => '🐍',
                            'agent' => '🤖',
                            'protocol' => '🔌',
                            'mcp' => '🔌',
                            'azure' => '☁️',
                            'foundry' => '☁️',
                            'llmops' => '⚙️',
                            'vision' => '👁️',
                            'deep' => '👁️',
                            'full-stack' => '⚡',
                            'master' => '🏆'
                        ];
                        $icon = '🚀';
                        foreach ($iconMap as $k => $ic) {
                            if (stripos($c['title'], $k) !== false || stripos($c['slug'], $k) !== false) {
                                $icon = $ic;
                                break;
                            }
                        }
                    ?>
                    <div class="card course-card" data-category="<?= Helper::e($c['category']) ?>" style="border:1.5px solid var(--il-border); border-radius:14px; display:flex; flex-direction:column; justify-content:space-between; padding:22px; transition:transform 0.2s, box-shadow 0.2s;">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                                <span style="font-size:28px;"><?= $icon ?></span>
                                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                                    <span class="badge badge-<?= $isEnrolled ? 'success' : 'primary' ?>" style="font-size:10.5px; text-transform:uppercase;">
                                        <?= $isEnrolled ? 'Enrolled' : Helper::e($c['badge'] ?: 'Featured Track') ?>
                                    </span>
                                    <?php if ($hasBogo && !$isEnrolled): ?>
                                        <span class="badge" style="background:rgba(22,163,74,0.15); color:#16A34A; border:1px solid #86EFAC; font-size:10px; font-weight:800;">
                                            🎁 BOGO: FREE BONUS
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <h3 style="font-size:1.2rem; margin:0 0 8px;"><?= Helper::e($c['title']) ?></h3>
                            <p class="muted" style="font-size:0.88rem; line-height:1.5; margin-bottom:14px;">
                                <?= Helper::e($c['tagline'] ?: $c['overview']) ?>
                            </p>
                            
                            <?php if ($hasBogo && !$isEnrolled && $freeCourseTitle): ?>
                                <div style="background:rgba(22,163,74,0.08); border:1px dashed #86EFAC; border-radius:8px; padding:8px 10px; font-size:11.5px; margin-bottom:14px; color:#15803D;">
                                    <strong>🎁 BOGO Offer:</strong> Enroll in this track &amp; receive <strong><?= Helper::e($freeCourseTitle) ?></strong> completely FREE!
                                </div>
                            <?php endif; ?>

                            <div style="font-size:12px; display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px;">
                                <span class="badge" style="background:var(--surface-2);">⏱️ <?= Helper::e($c['duration']) ?></span>
                                <span class="badge" style="background:var(--surface-2);">📊 <?= Helper::e($c['level']) ?></span>
                                <span class="badge" style="background:var(--surface-2);">🎓 Certificate</span>
                            </div>
                        </div>

                        <div style="border-top:1px solid var(--il-border); padding-top:14px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <div>
                                    <div class="muted" style="font-size:11px;">Program Fee</div>
                                    <div style="font-family:'JetBrains Mono', monospace; font-weight:800; font-size:1.15rem; color:var(--il-primary);">
                                        ₹<?= number_format((float)$c['fee'], 2) ?>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openCourseDetails(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>, <?= $isEnrolled ? 'true' : 'false' ?>, <?= $hasBogo ? htmlspecialchars(json_encode($bogoData), ENT_QUOTES, 'UTF-8') : 'null' ?>)">
                                    🔍 Syllabus &amp; Details
                                </button>
                            </div>
                            <?php if ($isEnrolled): ?>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" class="btn btn-success btn-sm" style="flex:1; font-weight:700;" onclick="openCourseDetails(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>, true, null)">
                                        ✅ Enrolled
                                    </button>
                                    <form method="POST" action="/aiverse/unenroll" style="margin:0;" onsubmit="return confirm('Are you sure you want to unenroll from <?= Helper::e(addslashes($c['title'])) ?>?');">
                                        <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                        <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>">
                                        <input type="hidden" name="redirect_to" value="/dashboard">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="color:#DC2626; border-color:#FECACA; padding:6px 10px; font-size:11px;" title="Drop / Unenroll">
                                            Drop ✕
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <button type="button" class="btn btn-primary btn-sm btn-block" style="width:100%; font-weight:700;" onclick="openCourseDetails(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>, false, <?= $hasBogo ? htmlspecialchars(json_encode($bogoData), ENT_QUOTES, 'UTF-8') : 'null' ?>)">
                                    <?= $hasBogo ? 'Enroll Now (Includes Free Companion Track) →' : 'Enroll Now →' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PROFILE / CONTACT CARD -->
        <div class="card" style="padding:22px; border:1.5px solid var(--il-border); border-radius:14px; margin-bottom:28px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="margin:0; font-size:1.15rem;">Scholar Information</h3>
                <a href="/profile" class="btn btn-xs btn-outline-secondary">Edit Information</a>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; font-size:13.5px;">
                <div>
                    <span class="muted" style="display:block; font-size:11.5px; text-transform:uppercase;">Full Name</span>
                    <strong><?= Helper::e($student['full_name'] ?? '—') ?></strong>
                </div>
                <div>
                    <span class="muted" style="display:block; font-size:11.5px; text-transform:uppercase;">Email Address</span>
                    <strong><?= Helper::e($user['email'] ?? '—') ?></strong>
                </div>
                <div>
                    <span class="muted" style="display:block; font-size:11.5px; text-transform:uppercase;">Phone Number</span>
                    <strong><?= Helper::e($user['phone'] ?? '—') ?></strong>
                </div>
                <div>
                    <span class="muted" style="display:block; font-size:11.5px; text-transform:uppercase;">District</span>
                    <strong><?= Helper::e($student['district'] ?? '—') ?></strong>
                </div>
                <div>
                    <span class="muted" style="display:block; font-size:11.5px; text-transform:uppercase;">City &amp; State</span>
                    <strong><?= Helper::e(($student['city'] ?? '') . (!empty($student['state']) ? ', ' . $student['state'] : '')) ?></strong>
                </div>
                <div>
                    <span class="muted" style="display:block; font-size:11.5px; text-transform:uppercase;">Pin Code</span>
                    <strong><?= Helper::e($student['pincode'] ?? '—') ?></strong>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- COMPREHENSIVE COURSE DETAILS & ENROLL MODAL -->
<div id="courseModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:16px; overflow-y:auto; backdrop-filter:blur(4px);">
    <div style="background:var(--surface-0); border:1px solid var(--il-border); border-radius:18px; max-width:680px; width:100%; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px rgba(0,0,0,0.25); position:relative; padding:28px;">
        
        <button type="button" onclick="closeCourseModal()" style="position:absolute; top:20px; right:20px; background:var(--surface-2); border:none; width:34px; height:34px; border-radius:50%; font-size:18px; cursor:pointer; color:var(--il-text); display:flex; align-items:center; justify-content:center;">&times;</button>

        <div style="display:inline-block; padding:4px 10px; background:var(--surface-2); border-radius:6px; font-size:11px; font-weight:800; text-transform:uppercase; color:var(--il-primary); margin-bottom:8px;" id="mBadge">FEATURED TRACK</div>
        <h2 id="mTitle" style="font-size:1.6rem; margin:0 0 6px;">Course Title</h2>
        <p class="muted" id="mTagline" style="font-size:0.95rem; margin-bottom:18px; line-height:1.4;">Tagline</p>

        <!-- BOGO PERK CALLOUT IN MODAL -->
        <div id="mBogoCallout" style="display:none; background:rgba(22,163,74,0.1); border:1.5px dashed #86EFAC; border-radius:12px; padding:14px 16px; margin-bottom:20px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:22px;">🎁</span>
                <div>
                    <strong style="color:#15803D; font-size:13.5px; display:block;">Special Buy-One-Get-One (BOGO) Offer Active!</strong>
                    <span style="font-size:12.5px; color:var(--il-text);" id="mBogoText">Enrolling in this track will automatically grant you a free companion course!</span>
                </div>
            </div>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:22px; padding:12px 16px; background:var(--surface-1); border-radius:10px;">
            <div style="font-size:12.5px;">⏱️ <strong>Duration:</strong> <span id="mDuration">8 Weeks</span></div>
            <div style="font-size:12.5px;">📊 <strong>Level:</strong> <span id="mLevel">Beginner</span></div>
            <div style="font-size:12.5px;">🏷️ <strong>Category:</strong> <span id="mCategory">AI</span></div>
            <div style="font-size:12.5px; margin-left:auto;">💳 <strong>Fee:</strong> <span id="mFee" style="color:var(--il-primary); font-weight:800; font-family:'JetBrains Mono', monospace;">₹0.00</span></div>
        </div>

        <div style="margin-bottom:20px;">
            <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--il-primary); margin-bottom:6px;">📖 Course Overview</h4>
            <p id="mOverview" style="font-size:0.92rem; line-height:1.6; margin:0; color:var(--il-text);">Overview text</p>
        </div>

        <div style="margin-bottom:20px;">
            <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--il-primary); margin-bottom:6px;">🎯 What This Course Provides (Key Outcomes)</h4>
            <div id="mOutcomes" style="background:var(--surface-1); border-radius:10px; padding:14px 16px; font-size:0.9rem; line-height:1.6; white-space:pre-line;"></div>
        </div>

        <div style="margin-bottom:20px;">
            <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--il-primary); margin-bottom:6px;">📚 Curriculum &amp; Modules</h4>
            <div id="mCurriculum" style="background:var(--surface-1); border-radius:10px; padding:14px 16px; font-size:0.9rem; line-height:1.6; white-space:pre-line;"></div>
        </div>

        <div style="margin-bottom:20px;">
            <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--il-primary); margin-bottom:6px;">🎁 What's Included</h4>
            <div id="mIncludes" style="background:var(--surface-1); border-radius:10px; padding:14px 16px; font-size:0.9rem; line-height:1.6; white-space:pre-line;"></div>
        </div>

        <div style="margin-bottom:24px;">
            <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--il-primary); margin-bottom:6px;">⚡ Prerequisites</h4>
            <p id="mPrerequisites" style="font-size:0.9rem; margin:0; color:var(--il-muted);"></p>
        </div>

        <!-- ACTION FORM -->
        <div style="border-top:1.5px solid var(--il-border); padding-top:20px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <span class="muted" style="font-size:11px; display:block;">One-time Course Fee</span>
                <span id="mFeeAction" style="font-size:1.4rem; font-weight:800; font-family:'JetBrains Mono', monospace; color:var(--il-primary);">₹0.00</span>
            </div>
            
            <div id="mActionArea">
                <form method="POST" action="/aiverse/enroll" style="margin:0;">
                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                    <input type="hidden" name="course_id" id="mCourseIdInput" value="">
                    <button type="submit" class="btn btn-primary" style="padding:10px 24px; font-weight:800; font-size:14px;">
                        Confirm &amp; Enroll in Course →
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function switchBannerSlide(idx) {
    const slides = document.querySelectorAll('.banner-slide');
    const dots = document.querySelectorAll('.carousel-dot');
    slides.forEach((s, i) => {
        s.style.display = i === idx ? 'block' : 'none';
    });
    dots.forEach((d, i) => {
        d.style.background = i === idx ? 'var(--il-primary)' : 'var(--il-border)';
    });
}

function filterCourses(category, btn) {
    document.querySelectorAll('.cat-filter-btn').forEach(b => {
        b.classList.remove('active');
        b.classList.add('btn-outline-secondary');
    });
    btn.classList.add('active');
    btn.classList.remove('btn-outline-secondary');

    const cards = document.querySelectorAll('.course-card');
    cards.forEach(card => {
        if (category === 'all' || card.getAttribute('data-category') === category) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function openCourseDetails(course, isEnrolled, bogoData) {
    document.getElementById('mBadge').innerText = course.badge || 'FEATURED TRACK';
    document.getElementById('mTitle').innerText = course.title || '';
    document.getElementById('mTagline').innerText = course.tagline || course.overview || '';
    document.getElementById('mDuration').innerText = course.duration || '8 Weeks';
    document.getElementById('mLevel').innerText = course.level || 'All Levels';
    document.getElementById('mCategory').innerText = course.category || 'Artificial Intelligence';
    
    var feeFormatted = '₹' + parseFloat(course.fee).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('mFee').innerText = feeFormatted;
    document.getElementById('mFeeAction').innerText = feeFormatted;

    document.getElementById('mOverview').innerText = course.overview || 'Comprehensive course designed by industry AI experts.';
    document.getElementById('mOutcomes').innerText = course.outcomes || '• Hands-on AI engineering and model implementation\n• Portfolio-ready projects\n• Capstone presentation and certificate';
    document.getElementById('mCurriculum').innerText = course.curriculum || '• Module 1: Core Principles\n• Module 2: Applied Tooling & Architectures\n• Module 3: Hands-on Projects\n• Module 4: Deployment & Capstone';
    document.getElementById('mIncludes').innerText = course.includes || '• Live mentor-led sessions\n• Verified Industry Certificate\n• 24/7 AI learning assistant\n• Source code & notebooks access';
    document.getElementById('mPrerequisites').innerText = course.prerequisites || 'Basic computer knowledge and willingness to learn.';

    const bogoBox = document.getElementById('mBogoCallout');
    if (bogoData && !isEnrolled) {
        bogoBox.style.display = 'block';
        document.getElementById('mBogoText').innerText = bogoData.tagline || 'Enroll in this course today and receive a companion course 100% FREE!';
    } else {
        bogoBox.style.display = 'none';
    }

    const actionArea = document.getElementById('mActionArea');
    if (isEnrolled) {
        actionArea.innerHTML = `
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span class="badge badge-success" style="font-size:13px; padding:8px 16px;">✅ You are Enrolled</span>
                <form method="POST" action="/aiverse/unenroll" style="margin:0;" onsubmit="return confirm('Are you sure you want to unenroll from this course? Your enrollment and pending fees will be cancelled.');">
                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                    <input type="hidden" name="course_id" value="${course.id}">
                    <input type="hidden" name="redirect_to" value="/dashboard">
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="font-weight:700; color:#DC2626; border-color:#FECACA; padding:7px 14px;">
                        Drop / Unenroll from Course ✕
                    </button>
                </form>
            </div>
        `;
    } else {
        actionArea.innerHTML = `
            <form method="POST" action="/aiverse/enroll" style="margin:0;">
                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                <input type="hidden" name="course_id" value="${course.id}">
                <button type="submit" class="btn btn-primary" style="padding:10px 24px; font-weight:800; font-size:14px;">
                    ${bogoData ? 'Confirm & Claim Course + Free Perk →' : 'Confirm &amp; Enroll in Course →'}
                </button>
            </form>
        `;
    }

    var modal = document.getElementById('courseModal');
    modal.style.display = 'flex';
}

function closeCourseModal() {
    document.getElementById('courseModal').style.display = 'none';
}

window.addEventListener('click', function(e) {
    var modal = document.getElementById('courseModal');
    if (e.target === modal) {
        closeCourseModal();
    }
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

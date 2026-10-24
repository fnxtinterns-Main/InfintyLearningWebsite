<?php
declare(strict_types=1);

use App\Auth;
use App\Aiverse;
use App\Helper;
use App\School;
use App\Session;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);

$allBundles = Aiverse::getBundles('all');
$allCourses = Aiverse::getCourses('all');

$courseMap = [];
foreach ($allCourses as $c) {
    $courseMap[(int)$c['id']] = $c;
}

$pageTitle = 'Course Bundles & Special Offers — ' . ($school['name'] ?? 'AIVERSE');
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div style="min-width:0; width:100%;">
        <!-- AIVERSE ADMIN SUB-NAVIGATION TABS -->
        <div style="display:flex; gap:8px; border-bottom:1.5px solid var(--il-border); margin-bottom:20px; padding-bottom:10px; overflow-x:auto;">
            <a href="/school/courses" class="btn btn-sm btn-outline-secondary" style="border-radius:999px; font-weight:700;">🤖 AI Courses &amp; Curriculum</a>
            <a href="/school/bundles" class="btn btn-sm btn-primary" style="border-radius:999px; font-weight:700;">📦 Course Bundles &amp; Discounts</a>
            <a href="/school/banners" class="btn btn-sm btn-outline-secondary" style="border-radius:999px; font-weight:700;">📢 Promo Banners</a>
        </div>

        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">📦 CURRICULUM PACKAGES &amp; OFFERS</span>
                <h1 style="margin:2px 0 0;">Course Bundles &amp; Discounts</h1>
                <p class="muted">Create discounted curriculum bundles (5%–10% off) and Buy-One-Get-One (BOGO) promotions for students.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openBundleModal(null)">+ Create New Bundle / Offer</button>
        </div>

        <?php if ($msg = Session::flash('success')): ?>
            <div class="alert alert-success" style="margin-bottom:20px; font-weight:600; font-size:14px; border-radius:10px;">
                <?= Helper::e($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg = Session::flash('info')): ?>
            <div class="alert alert-info" style="margin-bottom:20px; font-weight:600; font-size:14px; border-radius:10px;">
                <?= Helper::e($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg = Session::flash('error')): ?>
            <div class="alert alert-danger" style="margin-bottom:20px; font-weight:600; font-size:14px; border-radius:10px;">
                <?= Helper::e($msg) ?>
            </div>
        <?php endif; ?>

        <!-- STATS BAR -->
        <div class="grid grid-3" style="gap:16px; margin-bottom:24px;">
            <div class="stat" style="background:var(--surface-1); border-radius:12px; padding:18px; border-left:4px solid var(--il-primary);">
                <span class="ico" style="font-size:22px;">📦</span>
                <span class="n" style="font-size:24px; font-weight:800; font-family:'JetBrains Mono', monospace;"><?= count($allBundles) ?></span>
                <span class="l" style="font-size:12px; text-transform:uppercase; letter-spacing:0.04em;">Total Bundles &amp; Offers</span>
            </div>
            <div class="stat" style="background:var(--surface-1); border-radius:12px; padding:18px; border-left:4px solid #16A34A;">
                <span class="ico" style="font-size:22px;">🎁</span>
                <span class="n" style="font-size:24px; font-weight:800; font-family:'JetBrains Mono', monospace; color:#16A34A;">
                    <?= count(array_filter($allBundles, fn($b) => !empty($b['is_bogo']))) ?>
                </span>
                <span class="l" style="font-size:12px; text-transform:uppercase; letter-spacing:0.04em;">Active BOGO Deals</span>
            </div>
            <div class="stat" style="background:var(--surface-1); border-radius:12px; padding:18px; border-left:4px solid #9333EA;">
                <span class="ico" style="font-size:22px;">🏷️</span>
                <span class="n" style="font-size:24px; font-weight:800; font-family:'JetBrains Mono', monospace; color:#9333EA;">5% – 10%</span>
                <span class="l" style="font-size:12px; text-transform:uppercase; letter-spacing:0.04em;">Combo Discount Range</span>
            </div>
        </div>

        <!-- BUNDLES TABLE -->
        <div class="card" style="border:1.5px solid var(--il-border); border-radius:16px; overflow:hidden; padding:0; margin-bottom:28px;">
            <div style="padding:16px 20px; border-bottom:1px solid var(--il-border); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:1.15rem;">All Promotional Bundles</h3>
                <span class="badge badge-brand"><?= count($allBundles) ?> Active Packages</span>
            </div>

            <div style="overflow-x:auto;">
                <table class="table" style="width:100%; border-collapse:collapse; font-size:13px; margin:0;">
                    <thead>
                        <tr style="background:var(--surface-0); border-bottom:2px solid var(--il-border); text-align:left; font-size:11px; text-transform:uppercase; color:var(--il-muted);">
                            <th style="padding:12px 14px;">Bundle / Offer Title</th>
                            <th style="padding:12px 14px;">Courses Included</th>
                            <th style="padding:12px 14px;">Original Price</th>
                            <th style="padding:12px 14px;">Bundle Price</th>
                            <th style="padding:12px 14px;">Discount Badge</th>
                            <th style="padding:12px 14px;">Status</th>
                            <th style="padding:12px 14px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allBundles)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:40px 20px; color:var(--il-muted);">
                                    No course bundles created yet. Click "+ Create New Bundle / Offer" to get started!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allBundles as $b): ?>
                                <?php
                                    $cids = json_decode($b['course_ids'] ?? '[]', true) ?: [];
                                    $origPrice = (float)$b['original_price'];
                                    $bPrice = (float)$b['bundle_price'];
                                    $isBogo = !empty($b['is_bogo']);
                                ?>
                                <tr style="border-bottom:1px solid var(--il-border);">
                                    <td style="padding:12px 14px; min-width:220px;">
                                        <div style="font-weight:700; font-size:14px; color:var(--il-text);">
                                            <?= Helper::e($b['title']) ?>
                                        </div>
                                        <?php if (!empty($b['tagline'])): ?>
                                            <div class="muted" style="font-size:11.5px; margin-top:2px;">
                                                <?= Helper::e($b['tagline']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:12px 14px; max-width:260px;">
                                        <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                            <?php foreach ($cids as $cid): ?>
                                                <?php if (isset($courseMap[(int)$cid])): ?>
                                                    <?php $isFree = ($isBogo && (int)$b['bogo_free_course_id'] === (int)$cid); ?>
                                                    <span class="badge" style="font-size:10.5px; background:<?= $isFree ? 'rgba(22,163,74,0.15)' : 'var(--surface-2)' ?>; color:<?= $isFree ? '#16A34A' : 'inherit' ?>; border:1px solid <?= $isFree ? '#86EFAC' : 'transparent' ?>;">
                                                        <?= $isFree ? '🎁 Free: ' : '• ' ?><?= Helper::e($courseMap[(int)$cid]['title']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td style="padding:12px 14px; font-family:'JetBrains Mono', monospace; color:var(--il-muted); text-decoration:line-through;">
                                        ₹<?= number_format($origPrice, 2) ?>
                                    </td>
                                    <td style="padding:12px 14px; font-family:'JetBrains Mono', monospace; font-weight:800; font-size:14px; color:#16A34A;">
                                        ₹<?= number_format($bPrice, 2) ?>
                                    </td>
                                    <td style="padding:12px 14px;">
                                        <span class="badge" style="background:<?= $isBogo ? 'rgba(168,85,247,0.15)' : 'rgba(22,163,74,0.15)' ?>; color:<?= $isBogo ? '#9333EA' : '#16A34A' ?>; font-weight:700;">
                                            <?= Helper::e($b['badge'] ?: ($isBogo ? 'BOGO' : $b['discount_pct'] . '% OFF')) ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 14px;">
                                        <span class="badge badge-<?= $b['status'] === 'published' ? 'success' : 'secondary' ?>" style="text-transform:capitalize;">
                                            <?= Helper::e($b['status']) ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 14px; text-align:right;">
                                        <div style="display:inline-flex; gap:6px;">
                                            <button type="button" class="btn btn-xs btn-outline-primary" onclick="openBundleModal(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>)">
                                                ✏️ Edit
                                            </button>
                                            <form method="POST" action="/school/bundles/delete" style="display:inline; margin:0;" onsubmit="return confirm('Delete this bundle permanently?');">
                                                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                                <input type="hidden" name="bundle_id" value="<?= (int)$b['id'] ?>">
                                                <button type="submit" class="btn btn-xs btn-outline-danger" style="color:#DC2626;">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: ADD / EDIT BUNDLE -->
<div id="bundleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="width:100%; max-width:680px; max-height:90vh; overflow-y:auto; border-radius:18px; padding:24px; background:var(--surface-0); border:1.5px solid var(--il-border); box-shadow:0 12px 40px rgba(0,0,0,0.25);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid var(--il-border); padding-bottom:12px;">
            <h3 id="modalTitle" style="margin:0; font-size:1.3rem;">Create Course Bundle</h3>
            <button type="button" onclick="closeBundleModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--il-muted);">&times;</button>
        </div>

        <form method="POST" action="/school/bundles/save" id="bundleForm">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <input type="hidden" name="bundle_id" id="f_bundle_id" value="">

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700; font-size:12px; text-transform:uppercase;">Bundle / Promotion Title *</label>
                <input type="text" name="title" id="f_title" class="form-control" placeholder="e.g. Complete GenAI & Agentic AI Master Bundle" required style="width:100%;">
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
                <div class="form-group">
                    <label style="font-weight:700; font-size:12px; text-transform:uppercase;">URL Slug (auto-generated if empty)</label>
                    <input type="text" name="slug" id="f_slug" class="form-control" placeholder="complete-genai-master-bundle">
                </div>
                <div class="form-group">
                    <label style="font-weight:700; font-size:12px; text-transform:uppercase;">Promotional Badge</label>
                    <input type="text" name="badge" id="f_badge" class="form-control" placeholder="e.g. Save 10% · Best Value">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700; font-size:12px; text-transform:uppercase;">Tagline / Subtitle</label>
                <input type="text" name="tagline" id="f_tagline" class="form-control" placeholder="e.g. All 4 Core Tracks: Prompting, MCP, Swarms & Azure AI" style="width:100%;">
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700; font-size:12px; text-transform:uppercase;">Description</label>
                <textarea name="description" id="f_description" class="form-control" rows="2" placeholder="Brief marketing overview of this bundle..."></textarea>
            </div>

            <!-- BOGO TOGGLE -->
            <div style="background:var(--surface-1); border:1px solid var(--il-border); border-radius:10px; padding:14px; margin-bottom:16px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; margin:0;">
                    <input type="checkbox" name="is_bogo" id="f_is_bogo" value="1" onchange="toggleBogoMode()">
                    <span>🎁 Is this a "Buy-One-Get-One" (BOGO) Offer? (Buy Course A -> Get Course B Free)</span>
                </label>

                <div id="bogoFields" style="display:none; margin-top:12px; padding-top:12px; border-top:1px dashed var(--il-border);">
                    <div class="grid grid-2" style="gap:12px;">
                        <div>
                            <label style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--il-primary);">Primary Course (Student Pays)</label>
                            <select name="bogo_primary_course_id" id="f_bogo_primary" class="form-control" onchange="recalculateBogo()">
                                <option value="">-- Select Primary Track --</option>
                                <?php foreach ($allCourses as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>" data-fee="<?= (float)$c['fee'] ?>">
                                        <?= Helper::e($c['title']) ?> (₹<?= number_format((float)$c['fee'], 0) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:700; text-transform:uppercase; color:#16A34A;">Free Course (Auto-Granted at ₹0)</label>
                            <select name="bogo_free_course_id" id="f_bogo_free" class="form-control" onchange="recalculateBogo()">
                                <option value="">-- Select Free Companion Track --</option>
                                <?php foreach ($allCourses as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>" data-fee="<?= (float)$c['fee'] ?>">
                                        <?= Helper::e($c['title']) ?> (₹<?= number_format((float)$c['fee'], 0) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MULTI-COURSE SELECTION FOR COMBO BUNDLE -->
            <div id="comboCourseSection" class="form-group" style="margin-bottom:16px;">
                <label style="font-weight:700; font-size:12px; text-transform:uppercase; margin-bottom:8px; display:block;">Select Courses Included in this Combo Bundle *</label>
                <div style="background:var(--surface-1); border:1px solid var(--il-border); border-radius:10px; padding:12px; max-height:160px; overflow-y:auto; display:flex; flex-direction:column; gap:8px;">
                    <?php foreach ($allCourses as $c): ?>
                        <label style="display:flex; align-items:center; justify-content:space-between; font-size:12.5px; margin:0; cursor:pointer;">
                            <span style="display:flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="course_ids[]" value="<?= (int)$c['id'] ?>" class="course-cb" data-fee="<?= (float)$c['fee'] ?>" onchange="recalculateCombo()">
                                <span><?= Helper::e($c['title']) ?></span>
                            </span>
                            <span style="font-family:'JetBrains Mono', monospace; font-weight:700; color:var(--il-primary);">₹<?= number_format((float)$c['fee'], 0) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- PRICING & DISCOUNT CALCULATOR -->
            <div class="grid grid-3" style="gap:14px; margin-bottom:16px; background:var(--surface-1); padding:14px; border-radius:10px; border:1px solid var(--il-border);">
                <div class="form-group">
                    <label style="font-weight:700; font-size:11px; text-transform:uppercase;">Sum of Track Fees (₹)</label>
                    <input type="number" step="0.01" name="original_price" id="f_original_price" class="form-control" readonly style="font-family:'JetBrains Mono', monospace; font-weight:700;">
                </div>
                <div class="form-group">
                    <label style="font-weight:700; font-size:11px; text-transform:uppercase;">Discount %</label>
                    <input type="number" step="0.1" min="0" max="100" name="discount_pct" id="f_discount_pct" class="form-control" value="10" oninput="applyDiscountPct()" style="font-family:'JetBrains Mono', monospace; font-weight:700;">
                </div>
                <div class="form-group">
                    <label style="font-weight:700; font-size:11px; text-transform:uppercase; color:#16A34A;">Bundle Price (₹) *</label>
                    <input type="number" step="0.01" name="bundle_price" id="f_bundle_price" class="form-control" required style="font-family:'JetBrains Mono', monospace; font-weight:800; color:#16A34A;" oninput="applyBundlePrice()">
                </div>
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:20px;">
                <div class="form-group">
                    <label style="font-weight:700; font-size:12px; text-transform:uppercase;">Status</label>
                    <select name="status" id="f_status" class="form-control">
                        <option value="published">Published (Visible to Scholars)</option>
                        <option value="draft">Draft (Hidden)</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-weight:700; font-size:12px; text-transform:uppercase;">Display Order</label>
                    <input type="number" name="display_order" id="f_display_order" class="form-control" value="0">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--il-border); padding-top:16px;">
                <button type="button" class="btn btn-outline-secondary" onclick="closeBundleModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveBundleBtn">Save &amp; Publish Bundle</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBundleModal(data) {
    const modal = document.getElementById('bundleModal');
    const form = document.getElementById('bundleForm');
    form.reset();

    // uncheck all course checkboxes
    document.querySelectorAll('.course-cb').forEach(cb => cb.checked = false);

    if (data) {
        document.getElementById('modalTitle').textContent = 'Edit Course Bundle';
        document.getElementById('f_bundle_id').value = data.id || '';
        document.getElementById('f_title').value = data.title || '';
        document.getElementById('f_slug').value = data.slug || '';
        document.getElementById('f_badge').value = data.badge || '';
        document.getElementById('f_tagline').value = data.tagline || '';
        document.getElementById('f_description').value = data.description || '';
        document.getElementById('f_original_price').value = data.original_price || '0.00';
        document.getElementById('f_bundle_price').value = data.bundle_price || '0.00';
        document.getElementById('f_discount_pct').value = data.discount_pct || '10';
        document.getElementById('f_status').value = data.status || 'published';
        document.getElementById('f_display_order').value = data.display_order || '0';

        const isBogo = parseInt(data.is_bogo, 10) === 1;
        document.getElementById('f_is_bogo').checked = isBogo;
        document.getElementById('f_bogo_primary').value = data.bogo_primary_course_id || '';
        document.getElementById('f_bogo_free').value = data.bogo_free_course_id || '';

        // Select checkboxes
        let cids = [];
        try {
            cids = JSON.parse(data.course_ids || '[]');
        } catch (e) {}
        cids.forEach(cid => {
            const cb = document.querySelector(`.course-cb[value="${cid}"]`);
            if (cb) cb.checked = true;
        });

        toggleBogoMode();
    } else {
        document.getElementById('modalTitle').textContent = 'Create New Course Bundle';
        document.getElementById('f_bundle_id').value = '';
        document.getElementById('f_is_bogo').checked = false;
        document.getElementById('f_discount_pct').value = '10';
        toggleBogoMode();
    }

    modal.style.display = 'flex';
}

function closeBundleModal() {
    document.getElementById('bundleModal').style.display = 'none';
}

function toggleBogoMode() {
    const isBogo = document.getElementById('f_is_bogo').checked;
    const bogoFields = document.getElementById('bogoFields');
    const comboSection = document.getElementById('comboCourseSection');
    if (isBogo) {
        bogoFields.style.display = 'block';
        comboSection.style.display = 'none';
        recalculateBogo();
    } else {
        bogoFields.style.display = 'none';
        comboSection.style.display = 'block';
        recalculateCombo();
    }
}

function recalculateBogo() {
    const pSel = document.getElementById('f_bogo_primary');
    const fSel = document.getElementById('f_bogo_free');

    const pOpt = pSel.options[pSel.selectedIndex];
    const fOpt = fSel.options[fSel.selectedIndex];

    const pFee = pOpt ? parseFloat(pOpt.getAttribute('data-fee') || '0') : 0;
    const fFee = fOpt ? parseFloat(fOpt.getAttribute('data-fee') || '0') : 0;

    const sum = pFee + fFee;
    document.getElementById('f_original_price').value = sum.toFixed(2);
    document.getElementById('f_bundle_price').value = pFee.toFixed(2); // Scholar only pays for primary!

    if (sum > 0) {
        const disc = ((fFee / sum) * 100).toFixed(1);
        document.getElementById('f_discount_pct').value = disc;
        if (!document.getElementById('f_badge').value || document.getElementById('f_badge').value.includes('BOGO') || document.getElementById('f_badge').value.includes('Save')) {
            document.getElementById('f_badge').value = '🎁 BUY 1 GET 1 FREE';
        }
    }
}

function recalculateCombo() {
    let sum = 0;
    document.querySelectorAll('.course-cb:checked').forEach(cb => {
        sum += parseFloat(cb.getAttribute('data-fee') || '0');
    });

    document.getElementById('f_original_price').value = sum.toFixed(2);
    applyDiscountPct();
}

function applyDiscountPct() {
    const orig = parseFloat(document.getElementById('f_original_price').value || '0');
    const pct = parseFloat(document.getElementById('f_discount_pct').value || '0');
    if (orig > 0) {
        const finalPrice = Math.round(orig * (1.0 - (pct / 100.0)));
        document.getElementById('f_bundle_price').value = finalPrice.toFixed(2);
        if (!document.getElementById('f_badge').value || document.getElementById('f_badge').value.includes('Save') || document.getElementById('f_badge').value.includes('%')) {
            document.getElementById('f_badge').value = `Save ${pct}% · Combo`;
        }
    }
}

function applyBundlePrice() {
    const orig = parseFloat(document.getElementById('f_original_price').value || '0');
    const bPrice = parseFloat(document.getElementById('f_bundle_price').value || '0');
    if (orig > 0 && bPrice <= orig) {
        const pct = (((orig - bPrice) / orig) * 100).toFixed(1);
        document.getElementById('f_discount_pct').value = pct;
        document.getElementById('f_badge').value = `Save ${pct}% · Special`;
    }
}
</script>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

<?php
declare(strict_types=1);

use App\Auth;
use App\Aiverse;
use App\Helper;
use App\School;
use App\Session;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);

$allBanners = Aiverse::getBanners(false);

$pageTitle = 'Promo Banners & Student Announcements — ' . ($school['name'] ?? 'AIVERSE');
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div style="min-width:0; width:100%;">
        <!-- AIVERSE ADMIN SUB-NAVIGATION TABS -->
        <div style="display:flex; gap:8px; border-bottom:1.5px solid var(--il-border); margin-bottom:20px; padding-bottom:10px; overflow-x:auto;">
            <a href="/school/courses" class="btn btn-sm btn-outline-secondary" style="border-radius:999px; font-weight:700;">🤖 AI Courses &amp; Curriculum</a>
            <a href="/school/bundles" class="btn btn-sm btn-outline-secondary" style="border-radius:999px; font-weight:700;">📦 Course Bundles &amp; Discounts</a>
            <a href="/school/banners" class="btn btn-sm btn-primary" style="border-radius:999px; font-weight:700;">📢 Promo Banners</a>
        </div>

        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">📢 STUDENT DASHBOARD BANNERS</span>
                <h1 style="margin:2px 0 0;">Promotional Banners</h1>
                <p class="muted">Add, customize, and toggle prominent promotional banners displayed at the top of students' dashboards.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openBannerModal(null)">+ Add New Banner</button>
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

        <!-- BANNER LIVE PREVIEWS & LIST -->
        <div style="display:flex; flex-direction:column; gap:20px; margin-bottom:32px;">
            <?php if (empty($allBanners)): ?>
                <div class="card" style="text-align:center; padding:48px 20px; border-radius:16px; border:1.5px dashed var(--il-border);">
                    <div style="font-size:36px; margin-bottom:10px;">📢</div>
                    <h3 style="margin:0 0 8px;">No Banners Created Yet</h3>
                    <p class="muted" style="max-width:480px; margin:0 auto 16px;">Create your first promo banner to announce bundles, discounts, and new AI course offerings to all scholars.</p>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openBannerModal(null)">+ Add Promo Banner</button>
                </div>
            <?php else: ?>
                <?php foreach ($allBanners as $bn): ?>
                    <?php 
                        $isActive = !empty($bn['is_active']);
                        $bg = $bn['bg_gradient'] ?: 'linear-gradient(135deg, #1e1b4b 0%, #31104b 50%, #0f172a 100%)';
                    ?>
                    <div class="card" style="border:1.5px solid var(--il-border); border-radius:16px; padding:0; overflow:hidden; opacity:<?= $isActive ? '1' : '0.65' ?>; transition:opacity 0.2s;">
                        <!-- BANNER PREVIEW -->
                        <div style="background:<?= Helper::e($bg) ?>; padding:24px 28px; color:#FFFFFF; position:relative; overflow:hidden;">
                            <div style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.18); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.3); padding:3px 10px; border-radius:999px; font-size:11px; font-weight:800; letter-spacing:0.04em; text-transform:uppercase; margin-bottom:10px;">
                                <?= Helper::e($bn['badge'] ?: 'Special Offer') ?>
                            </div>
                            <h3 style="margin:0 0 8px; font-size:1.45rem; font-weight:800; color:#FFFFFF; text-shadow:0 2px 8px rgba(0,0,0,0.4);">
                                <?= Helper::e($bn['title']) ?>
                            </h3>
                            <?php if (!empty($bn['subtitle'])): ?>
                                <p style="margin:0 0 16px; font-size:0.95rem; color:rgba(255,255,255,0.85); max-width:760px; line-height:1.5;">
                                    <?= Helper::e($bn['subtitle']) ?>
                                </p>
                            <?php endif; ?>
                            <div>
                                <a href="<?= Helper::e($bn['cta_link'] ?: '#bundles') ?>" class="btn btn-sm" style="background:#FFFFFF; color:#0f172a; font-weight:700; border:none; box-shadow:0 4px 14px rgba(0,0,0,0.25);">
                                    <?= Helper::e($bn['cta_text'] ?: 'Explore Offers →') ?>
                                </a>
                            </div>
                        </div>

                        <!-- BANNER CONTROLS BAR -->
                        <div style="padding:12px 20px; background:var(--surface-0); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; border-top:1px solid var(--il-border);">
                            <div style="display:flex; align-items:center; gap:12px; font-size:12.5px;">
                                <span class="badge badge-<?= $isActive ? 'success' : 'secondary' ?>">
                                    <?= $isActive ? '● Live on Dashboard' : '○ Inactive' ?>
                                </span>
                                <span class="muted">Display Order: <strong><?= (int)$bn['display_order'] ?></strong></span>
                                <span class="muted">Link: <code><?= Helper::e($bn['cta_link']) ?></code></span>
                            </div>

                            <div style="display:flex; align-items:center; gap:8px;">
                                <form method="POST" action="/school/banners/toggle" style="margin:0; display:inline;">
                                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                    <input type="hidden" name="banner_id" value="<?= (int)$bn['id'] ?>">
                                    <input type="hidden" name="is_active" value="<?= $isActive ? '0' : '1' ?>">
                                    <button type="submit" class="btn btn-xs <?= $isActive ? 'btn-outline-secondary' : 'btn-outline-success' ?>">
                                        <?= $isActive ? 'Turn Off' : 'Activate' ?>
                                    </button>
                                </form>

                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="openBannerModal(<?= htmlspecialchars(json_encode($bn), ENT_QUOTES, 'UTF-8') ?>)">
                                    ✏️ Edit
                                </button>

                                <form method="POST" action="/school/banners/delete" style="margin:0; display:inline;" onsubmit="return confirm('Delete this banner?');">
                                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                    <input type="hidden" name="banner_id" value="<?= (int)$bn['id'] ?>">
                                    <button type="submit" class="btn btn-xs btn-outline-danger" style="color:#DC2626;">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL: ADD / EDIT BANNER -->
<div id="bannerModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="width:100%; max-width:640px; max-height:90vh; overflow-y:auto; border-radius:18px; padding:24px; background:var(--surface-0); border:1.5px solid var(--il-border); box-shadow:0 12px 40px rgba(0,0,0,0.25);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid var(--il-border); padding-bottom:12px;">
            <h3 id="bannerModalTitle" style="margin:0; font-size:1.3rem;">Create Promo Banner</h3>
            <button type="button" onclick="closeBannerModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--il-muted);">&times;</button>
        </div>

        <form method="POST" action="/school/banners/save" id="bannerForm">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <input type="hidden" name="banner_id" id="f_bn_id" value="">

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700; font-size:12px; text-transform:uppercase;">Banner Headline *</label>
                <input type="text" name="title" id="f_bn_title" class="form-control" placeholder="e.g. 🎁 Mega Launch: Buy GenAI Track, Get Python for AI 100% FREE!" required style="width:100%;">
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700; font-size:12px; text-transform:uppercase;">Subtitle / Offer Details</label>
                <textarea name="subtitle" id="f_bn_subtitle" class="form-control" rows="2" placeholder="e.g. Enroll in the Master Track today and receive the complete Python foundation course at zero cost!"></textarea>
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
                <div class="form-group">
                    <label style="font-weight:700; font-size:12px; text-transform:uppercase;">Badge Text</label>
                    <input type="text" name="badge" id="f_bn_badge" class="form-control" placeholder="e.g. ⚡ LIMITED TIME BOGO">
                </div>
                <div class="form-group">
                    <label style="font-weight:700; font-size:12px; text-transform:uppercase;">Display Order</label>
                    <input type="number" name="display_order" id="f_bn_display_order" class="form-control" value="0">
                </div>
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
                <div class="form-group">
                    <label style="font-weight:700; font-size:12px; text-transform:uppercase;">CTA Button Label</label>
                    <input type="text" name="cta_text" id="f_bn_cta_text" class="form-control" placeholder="e.g. Claim Free Course Offer →">
                </div>
                <div class="form-group">
                    <label style="font-weight:700; font-size:12px; text-transform:uppercase;">CTA Target Link</label>
                    <input type="text" name="cta_link" id="f_bn_cta_link" class="form-control" placeholder="e.g. #bundles or /student/fees">
                </div>
            </div>

            <!-- GRADIENT SELECTION -->
            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-weight:700; font-size:12px; text-transform:uppercase; margin-bottom:8px; display:block;">Gradient Theme</label>
                <select id="gradientPreset" class="form-control" onchange="onSelectGradientPreset(this.value)" style="margin-bottom:8px;">
                    <option value="linear-gradient(135deg, #1e1b4b 0%, #31104b 50%, #0f172a 100%)">🌌 Cosmic Violet (Indigo / Purple / Slate)</option>
                    <option value="linear-gradient(135deg, #064e3b 0%, #0f172a 60%, #1e1b4b 100%)">🌿 Emerald Matrix (Teal / Deep Slate / Violet)</option>
                    <option value="linear-gradient(135deg, #0f172a 0%, #115e59 50%, #312e81 100%)">⚡ Cyberpunk Cyan (Navy / Cyan / Indigo)</option>
                    <option value="linear-gradient(135deg, #7c2d12 0%, #431407 60%, #0f172a 100%)">🔥 Solar Sunset (Amber / Rust / Dark)</option>
                    <option value="linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #090d16 100%)">🌑 Midnight Charcoal</option>
                    <option value="custom">⚙️ Custom CSS Gradient</option>
                </select>
                <input type="text" name="bg_gradient" id="f_bn_bg_gradient" class="form-control" value="linear-gradient(135deg, #1e1b4b 0%, #31104b 50%, #0f172a 100%)" placeholder="CSS gradient string" style="font-family:'JetBrains Mono', monospace; font-size:12px;">
            </div>

            <!-- BANNER STATUS -->
            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700;">
                    <input type="checkbox" name="is_active" id="f_bn_is_active" value="1" checked>
                    <span>Activate this banner immediately on scholar dashboards</span>
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--il-border); padding-top:16px;">
                <button type="button" class="btn btn-outline-secondary" onclick="closeBannerModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save &amp; Publish Banner</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBannerModal(data) {
    const modal = document.getElementById('bannerModal');
    const form = document.getElementById('bannerForm');
    form.reset();

    if (data) {
        document.getElementById('bannerModalTitle').textContent = 'Edit Promo Banner';
        document.getElementById('f_bn_id').value = data.id || '';
        document.getElementById('f_bn_title').value = data.title || '';
        document.getElementById('f_bn_subtitle').value = data.subtitle || '';
        document.getElementById('f_bn_badge').value = data.badge || '';
        document.getElementById('f_bn_display_order').value = data.display_order || '0';
        document.getElementById('f_bn_cta_text').value = data.cta_text || '';
        document.getElementById('f_bn_cta_link').value = data.cta_link || '#bundles';
        document.getElementById('f_bn_bg_gradient').value = data.bg_gradient || 'linear-gradient(135deg, #1e1b4b 0%, #31104b 50%, #0f172a 100%)';
        document.getElementById('f_bn_is_active').checked = parseInt(data.is_active, 10) === 1;
    } else {
        document.getElementById('bannerModalTitle').textContent = 'Create Promo Banner';
        document.getElementById('f_bn_id').value = '';
        document.getElementById('f_bn_badge').value = '⚡ SPECIAL OFFER';
        document.getElementById('f_bn_cta_text').value = 'Explore Bundles →';
        document.getElementById('f_bn_cta_link').value = '#bundles';
        document.getElementById('f_bn_bg_gradient').value = 'linear-gradient(135deg, #1e1b4b 0%, #31104b 50%, #0f172a 100%)';
        document.getElementById('f_bn_is_active').checked = true;
    }

    modal.style.display = 'flex';
}

function closeBannerModal() {
    document.getElementById('bannerModal').style.display = 'none';
}

function onSelectGradientPreset(val) {
    if (val !== 'custom') {
        document.getElementById('f_bn_bg_gradient').value = val;
    }
}
</script>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

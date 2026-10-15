<?php
use App\Auth;
use App\Helper;
use App\School;
use App\Database;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);

$db = Database::connect();
// Ensure columns exist on schools table
try {
    $db->exec("
        ALTER TABLE `schools`
          ADD COLUMN IF NOT EXISTS `brand_primary_color` varchar(20) DEFAULT '#0A192F',
          ADD COLUMN IF NOT EXISTS `brand_secondary_color` varchar(20) DEFAULT '#BE9420',
          ADD COLUMN IF NOT EXISTS `brand_tagline` varchar(255) DEFAULT NULL,
          ADD COLUMN IF NOT EXISTS `crest_logo` varchar(500) DEFAULT NULL,
          ADD COLUMN IF NOT EXISTS `principal_signature` varchar(500) DEFAULT NULL,
          ADD COLUMN IF NOT EXISTS `official_seal` varchar(500) DEFAULT NULL;
    ");
} catch (\Throwable $e) {}

$stmt = $db->prepare("SELECT * FROM schools WHERE id = ? LIMIT 1");
$stmt->execute([$schoolId]);
$schoolData = $stmt->fetch() ?: $school;

$primaryColor = $schoolData['brand_primary_color'] ?: '#0A192F';
$secondaryColor = $schoolData['brand_secondary_color'] ?: '#BE9420';
$tagline = $schoolData['brand_tagline'] ?: '';
$crestLogo = $schoolData['crest_logo'] ?: '';
$signature = $schoolData['principal_signature'] ?: '';
$seal = $schoolData['official_seal'] ?: '';

$pageTitle = 'White-Label Branding & Identity — ' . $school['name'];
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <main class="admin-main">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <a href="/school" class="back-link">← Admin</a>
                <h1 style="margin:4px 0 0; font-size:24px;">🏛️ White-Label Branding & Portal Identity</h1>
                <p style="color:var(--il-muted); font-size:14px; margin:4px 0 0;">
                    Customize student portals, report cards, fee receipts, and dashboards with your institution's colors, crest, and seal.
                </p>
            </div>
        </div>

        <?php if (!empty($_GET['saved'])): ?>
            <div class="card mb-4" style="background:#DCFCE7; border-color:#86EFAC; color:#166534; padding:12px 16px; border-radius:10px;">
                ✅ <strong>Branding updated successfully!</strong> Custom brand colors and crest have been applied across your school portal.
            </div>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns: 1fr 380px; gap:24px; align-items:start;">
            <!-- Branding Configuration Form -->
            <div class="card" style="padding:24px;">
                <h3 style="margin-top:0; margin-bottom:16px; font-size:18px;">🎨 Brand Assets & Theme Colors</h3>

                <form action="/school/branding-save-handler.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                    <input type="hidden" name="school_id" value="<?= $schoolId ?>">

                    <!-- Colors -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight:600; display:block; margin-bottom:6px;">Primary Brand Color</label>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="color" id="primary-color-picker" name="brand_primary_color" value="<?= Helper::e($primaryColor) ?>" style="width:44px; height:40px; border-radius:8px; border:1px solid var(--border); cursor:pointer;">
                                <input type="text" id="primary-color-text" class="input" value="<?= Helper::e($primaryColor) ?>" style="flex:1;">
                            </div>
                            <span class="muted" style="font-size:12px;">Used on portal headers, primary action buttons, and active tabs.</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight:600; display:block; margin-bottom:6px;">Secondary / Accent Color</label>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="color" id="secondary-color-picker" name="brand_secondary_color" value="<?= Helper::e($secondaryColor) ?>" style="width:44px; height:40px; border-radius:8px; border:1px solid var(--border); cursor:pointer;">
                                <input type="text" id="secondary-color-text" class="input" value="<?= Helper::e($secondaryColor) ?>" style="flex:1;">
                            </div>
                            <span class="muted" style="font-size:12px;">Used on badges, highlights, and report card medals.</span>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- Tagline & Identity -->
                    <div class="form-group" style="margin-bottom:20px;">
                        <label class="form-label" style="font-weight:600; display:block; margin-bottom:6px;">Official Motto or Tagline</label>
                        <input type="text" name="brand_tagline" id="tagline-input" class="input" placeholder="e.g. Inspiring Excellence Since 1994" value="<?= Helper::e($tagline) ?>">
                        <span class="muted" style="font-size:12px;">Appears beneath your school name on official transcripts and fee receipts.</span>
                    </div>

                    <div class="divider"></div>

                    <!-- Logos and Assets -->
                    <div style="display:flex; flex-direction:column; gap:20px; margin-bottom:24px;">
                        <!-- 1. Crest / Logo -->
                        <div class="card p-3" style="background:var(--surface-2); border:1px solid var(--border); border-radius:10px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                <div>
                                    <label class="form-label" style="font-weight:700; font-size:14px; margin-bottom:2px; display:block;">
                                        1. School Crest / Logo
                                    </label>
                                    <span class="muted" style="font-size:12px;">Appears on top navbar, certificates, and student reports. (PNG, JPG, SVG, WebP)</span>
                                </div>
                                <div id="crest-preview-badge" style="width:48px; height:48px; border-radius:8px; background:var(--surface); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                    <?php if ($crestLogo): ?>
                                        <img src="<?= Helper::e($crestLogo) ?>" style="width:100%; height:100%; object-fit:contain;">
                                    <?php else: ?>
                                        <span style="font-size:20px;">🏫</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div>
                                    <label class="muted" style="font-size:11px; font-weight:600; display:block; margin-bottom:3px;">📁 UPLOAD FROM DEVICE</label>
                                    <input type="file" name="crest_file" id="crest-file-input" accept="image/*" class="input" style="padding:6px 10px; width:100%; font-size:13px;">
                                </div>
                                <div>
                                    <label class="muted" style="font-size:11px; font-weight:600; display:block; margin-bottom:3px;">🔗 OR PASTE IMAGE URL</label>
                                    <input type="text" name="crest_logo" id="crest-input" class="input" placeholder="https://example.com/logo.png or /uploads/..." value="<?= Helper::e($crestLogo) ?>" style="font-size:13px;">
                                </div>
                            </div>
                        </div>

                        <!-- 2. Institutional Seal -->
                        <div class="card p-3" style="background:var(--surface-2); border:1px solid var(--border); border-radius:10px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                <div>
                                    <label class="form-label" style="font-weight:700; font-size:14px; margin-bottom:2px; display:block;">
                                        2. Official Institutional Seal
                                    </label>
                                    <span class="muted" style="font-size:12px;">Watermarked onto official PDF fee receipts and academic certificates.</span>
                                </div>
                                <div id="seal-preview-badge" style="width:48px; height:48px; border-radius:8px; background:var(--surface); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                    <?php if ($seal): ?>
                                        <img src="<?= Helper::e($seal) ?>" style="width:100%; height:100%; object-fit:contain;">
                                    <?php else: ?>
                                        <span style="font-size:20px;">📜</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div>
                                    <label class="muted" style="font-size:11px; font-weight:600; display:block; margin-bottom:3px;">📁 UPLOAD FROM DEVICE</label>
                                    <input type="file" name="seal_file" id="seal-file-input" accept="image/*" class="input" style="padding:6px 10px; width:100%; font-size:13px;">
                                </div>
                                <div>
                                    <label class="muted" style="font-size:11px; font-weight:600; display:block; margin-bottom:3px;">🔗 OR PASTE SEAL URL</label>
                                    <input type="text" name="official_seal" id="seal-input" class="input" placeholder="https://example.com/seal.png or /uploads/..." value="<?= Helper::e($seal) ?>" style="font-size:13px;">
                                </div>
                            </div>
                        </div>

                        <!-- 3. Digital Signature -->
                        <div class="card p-3" style="background:var(--surface-2); border:1px solid var(--border); border-radius:10px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                <div>
                                    <label class="form-label" style="font-weight:700; font-size:14px; margin-bottom:2px; display:block;">
                                        3. Principal / Authority Digital Signature
                                    </label>
                                    <span class="muted" style="font-size:12px;">Embossed on final term marksheets, transcripts, and completion certificates.</span>
                                </div>
                                <div id="sig-preview-badge" style="width:48px; height:48px; border-radius:8px; background:var(--surface); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                    <?php if ($signature): ?>
                                        <img src="<?= Helper::e($signature) ?>" style="width:100%; height:100%; object-fit:contain;">
                                    <?php else: ?>
                                        <span style="font-size:20px;">✍️</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div>
                                    <label class="muted" style="font-size:11px; font-weight:600; display:block; margin-bottom:3px;">📁 UPLOAD FROM DEVICE</label>
                                    <input type="file" name="signature_file" id="signature-file-input" accept="image/*" class="input" style="padding:6px 10px; width:100%; font-size:13px;">
                                </div>
                                <div>
                                    <label class="muted" style="font-size:11px; font-weight:600; display:block; margin-bottom:3px;">🔗 OR PASTE SIGNATURE URL</label>
                                    <input type="text" name="principal_signature" id="signature-input" class="input" placeholder="https://example.com/signature.png or /uploads/..." value="<?= Helper::e($signature) ?>" style="font-size:13px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="btn btn-primary" style="padding:10px 24px; font-weight:600;">
                            💾 Save & Apply Branding
                        </button>
                    </div>
                </form>
            </div>

            <!-- Live White-Label Mockup Preview -->
            <div class="card" style="padding:20px; position:sticky; top:20px;">
                <h4 style="margin:0 0 12px; font-size:16px;">👁️ Live Preview</h4>
                <p class="muted" style="font-size:12px; margin-bottom:16px;">This is how your students and teachers will see your portal navigation.</p>

                <!-- Navbar preview -->
                <div id="mock-navbar" style="background:<?= Helper::e($primaryColor) ?>; border-radius:10px; padding:12px 16px; color:#fff; display:flex; align-items:center; justify-content:space-between; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div id="mock-crest" style="width:28px; height:28px; border-radius:6px; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; font-size:14px; overflow:hidden;">
                            <?php if ($crestLogo): ?>
                                <img src="<?= Helper::e($crestLogo) ?>" style="width:100%; height:100%; object-fit:contain;">
                            <?php else: ?>
                                🏫
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:13px; line-height:1.2;"><?= Helper::e($school['name']) ?></div>
                            <div id="mock-tagline" style="font-size:10px; opacity:0.8;"><?= Helper::e($tagline ?: 'Portal Identity') ?></div>
                        </div>
                    </div>
                    <span id="mock-badge" style="background:<?= Helper::e($secondaryColor) ?>; color:#fff; font-size:10px; font-weight:700; padding:3px 8px; border-radius:12px;">
                        ACADEMICS
                    </span>
                </div>

                <!-- Card preview -->
                <div class="card mt-3" style="padding:16px; background:var(--surface-2); border-radius:10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:12px; font-weight:600; color:var(--text);">Fee Receipt & Grade Card</span>
                        <span id="mock-accent-dot" style="width:10px; height:10px; border-radius:50%; background:<?= Helper::e($secondaryColor) ?>;"></span>
                    </div>
                    <div style="margin-top:10px; font-size:11px; color:var(--il-muted);">
                        Official documents issued will contain your school crest and customized primary headers.
                    </div>
                    <button type="button" id="mock-btn" class="btn btn-sm btn-block mt-3" style="background:<?= Helper::e($primaryColor) ?>; color:#fff; border:none; font-size:12px;">
                        Sample Action Button
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pPicker = document.getElementById('primary-color-picker');
    const pText = document.getElementById('primary-color-text');
    const sPicker = document.getElementById('secondary-color-picker');
    const sText = document.getElementById('secondary-color-text');
    const taglineInp = document.getElementById('tagline-input');
    const crestInp = document.getElementById('crest-input');

    const mockNav = document.getElementById('mock-navbar');
    const mockBadge = document.getElementById('mock-badge');
    const mockTagline = document.getElementById('mock-tagline');
    const mockBtn = document.getElementById('mock-btn');
    const mockDot = document.getElementById('mock-accent-dot');
    const mockCrest = document.getElementById('mock-crest');

    pPicker.addEventListener('input', function() {
        pText.value = this.value;
        mockNav.style.background = this.value;
        mockBtn.style.background = this.value;
    });
    pText.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            pPicker.value = this.value;
            mockNav.style.background = this.value;
            mockBtn.style.background = this.value;
        }
    });

    sPicker.addEventListener('input', function() {
        sText.value = this.value;
        mockBadge.style.background = this.value;
        mockDot.style.background = this.value;
    });
    sText.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            sPicker.value = this.value;
            mockBadge.style.background = this.value;
            mockDot.style.background = this.value;
        }
    });

    taglineInp.addEventListener('input', function() {
        mockTagline.textContent = this.value || 'Portal Identity';
    });

    crestInp.addEventListener('input', function() {
        if (this.value) {
            mockCrest.innerHTML = `<img src="${this.value}" style="width:100%; height:100%; object-fit:contain;" onerror="this.parentElement.innerHTML='🏫'">`;
            document.getElementById('crest-preview-badge').innerHTML = `<img src="${this.value}" style="width:100%; height:100%; object-fit:contain;" onerror="this.parentElement.innerHTML='🏫'">`;
        } else {
            mockCrest.innerHTML = '🏫';
            document.getElementById('crest-preview-badge').innerHTML = '🏫';
        }
    });

    // Local file input listeners
    function bindFilePreview(fileInputId, previewBadgeId, extraCallback) {
        const fileInput = document.getElementById(fileInputId);
        const badge = document.getElementById(previewBadgeId);
        if (!fileInput || !badge) return;

        fileInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    badge.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:contain;">`;
                    if (extraCallback) extraCallback(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    bindFilePreview('crest-file-input', 'crest-preview-badge', function(dataUrl) {
        mockCrest.innerHTML = `<img src="${dataUrl}" style="width:100%; height:100%; object-fit:contain;">`;
    });
    bindFilePreview('seal-file-input', 'seal-preview-badge');
    bindFilePreview('signature-file-input', 'sig-preview-badge');
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

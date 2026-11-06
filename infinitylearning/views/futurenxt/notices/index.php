<?php
declare(strict_types=1);

use App\FutureNxt\Auth;
use App\FutureNxt\Notice;

Auth::requireLogin();
$me = Auth::user();
$pageTitle = 'Company Notices & Announcements';
$activePage = 'notices';

$catFilter = trim((string)($_GET['category'] ?? ''));
$filters = [];
if (!empty($catFilter)) {
    $filters['category'] = $catFilter;
}
if (!empty($_GET['q'])) {
    $filters['q'] = trim($_GET['q']);
}

$notices = Notice::list($filters);

$error = $_GET['error'] ?? $_SESSION['fnx_error'] ?? null;
$success = $_GET['success'] ?? $_SESSION['fnx_success'] ?? null;
unset($_SESSION['fnx_error'], $_SESSION['fnx_success']);

require __DIR__ . '/../layouts/header.php';
?>

<div class="fnx-container">
    <!-- Header Banner -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="margin:0 0 4px; font-size:24px; font-weight:800; color:var(--fnx-navy);">Notice Board &amp; Announcements</h1>
            <p style="margin:0; font-size:13.5px; color:var(--fnx-muted);">
                Official communications, holiday schedules, corporate events, and operational circulars.
            </p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <?php if (Auth::isAdminOrExec()): ?>
                <button type="button" class="fnx-btn fnx-btn-orange" onclick="fnxOpenModal('broadcastNoticeModal')">
                    <span>+</span> Broadcast Notice / Event
                </button>
            <?php endif; ?>
            <a href="/futurenxt/dashboard" class="fnx-btn fnx-btn-outline">
                ← Dashboard
            </a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="fnx-alert fnx-alert-danger" style="margin-bottom:20px;">
            <span>⚠️</span> <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="fnx-alert fnx-alert-success" style="margin-bottom:20px;">
            <span>✓</span> <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <!-- Category Filters -->
    <div class="fnx-card" style="padding:14px 20px; margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="/futurenxt/notices" class="fnx-btn <?= empty($catFilter) ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 14px; font-size:12.5px;">
                    All (<?= count($notices) ?>)
                </a>
                <a href="/futurenxt/notices?category=holiday" class="fnx-btn <?= $catFilter === 'holiday' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 14px; font-size:12.5px;">
                    🏖️ Holidays
                </a>
                <a href="/futurenxt/notices?category=event" class="fnx-btn <?= $catFilter === 'event' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 14px; font-size:12.5px;">
                    🎉 Important Events
                </a>
                <a href="/futurenxt/notices?category=urgent" class="fnx-btn <?= $catFilter === 'urgent' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 14px; font-size:12.5px;">
                    🚨 Urgent
                </a>
                <a href="/futurenxt/notices?category=policy" class="fnx-btn <?= $catFilter === 'policy' ? 'fnx-btn-dark' : 'fnx-btn-outline' ?>" style="padding:6px 14px; font-size:12.5px;">
                    📜 Policies
                </a>
            </div>

            <form method="GET" action="/futurenxt/notices" style="display:flex; gap:8px; align-items:center;">
                <?php if (!empty($catFilter)): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($catFilter) ?>">
                <?php endif; ?>
                <input type="text" name="q" class="fnx-input" placeholder="Search notices..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="padding:6px 12px; font-size:12.5px; width:220px;">
                <button type="submit" class="fnx-btn fnx-btn-outline" style="padding:6px 12px; font-size:12.5px;">Search</button>
            </form>
        </div>
    </div>

    <!-- Notices Feed -->
    <?php if (empty($notices)): ?>
        <div class="fnx-card" style="text-align:center; padding:48px 20px;">
            <span style="font-size:36px; display:block; margin-bottom:12px;">📢</span>
            <strong style="font-size:16px; color:#111827;">No notices posted yet</strong>
            <p style="margin:6px 0 0; font-size:13.5px; color:#6B7280;">
                All official notices, holiday lists, and events will appear here.
            </p>
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:16px; margin-bottom:32px;">
            <?php foreach ($notices as $n): 
                $isPinned = !empty($n['is_pinned']);
                $cat = $n['category'];
                $badgeStyle = match($cat) {
                    'holiday' => 'background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0;',
                    'event'   => 'background:#EFF6FF; color:#1E40AF; border:1px solid #BFDBFE;',
                    'urgent'  => 'background:#FEF2F2; color:#991B1B; border:1px solid #FECACA;',
                    'policy'  => 'background:#FDF4FF; color:#86198F; border:1px solid #F5D0FE;',
                    default   => 'background:#FFF7ED; color:#9A3412; border:1px solid #FED7AA;'
                };
                $icon = match($cat) {
                    'holiday' => '🏖️ Holiday Notice',
                    'event'   => '🎉 Important Event',
                    'urgent'  => '🚨 Urgent Announcement',
                    'policy'  => '📜 Policy Update',
                    default   => '📢 Company Notice'
                };
            ?>
                <div class="fnx-card" style="position:relative; border-left:4px solid <?= $isPinned ? 'var(--fnx-orange)' : ($cat === 'urgent' ? '#EF4444' : ($cat === 'holiday' ? '#10B981' : '#3B82F6')) ?>; box-shadow:0 2px 10px rgba(0,0,0,0.03);">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:12px;">
                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <span class="fnx-badge" style="<?= $badgeStyle ?> font-weight:800; font-size:12px; padding:4px 10px;">
                                <?= $icon ?>
                            </span>
                            <?php if ($isPinned): ?>
                                <span class="fnx-badge fnx-badge-orange" style="font-size:11px;">
                                    📌 Pinned
                                </span>
                            <?php endif; ?>
                            <span style="font-size:12px; color:#6B7280;">
                                Effective Date: <strong style="color:#111827;"><?= date('l, d F Y', strtotime($n['notice_date'])) ?></strong>
                            </span>
                        </div>

                        <?php if (Auth::isAdminOrExec()): ?>
                            <div style="display:flex; gap:6px;">
                                <form method="POST" action="/futurenxt-handler.php?action=pin_notice" style="display:inline;">
                                    <input type="hidden" name="notice_id" value="<?= (int)$n['id'] ?>">
                                    <input type="hidden" name="pin" value="<?= $isPinned ? '0' : '1' ?>">
                                    <input type="hidden" name="redirect_url" value="/futurenxt/notices">
                                    <button type="submit" class="fnx-btn fnx-btn-outline" style="padding:4px 9px; font-size:11.5px;">
                                        <?= $isPinned ? 'Unpin' : '📌 Pin' ?>
                                    </button>
                                </form>
                                <form method="POST" action="/futurenxt-handler.php?action=delete_notice" style="display:inline;" onsubmit="return confirm('Archive this notice?');">
                                    <input type="hidden" name="notice_id" value="<?= (int)$n['id'] ?>">
                                    <input type="hidden" name="redirect_url" value="/futurenxt/notices">
                                    <button type="submit" class="fnx-btn fnx-btn-outline" style="padding:4px 9px; font-size:11.5px; color:#EF4444; border-color:#FCA5A5;">
                                        Archive
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h2 style="margin:0 0 10px; font-size:18px; font-weight:800; color:#111827;">
                        <?= htmlspecialchars($n['title']) ?>
                    </h2>

                    <div style="font-size:14px; color:#374151; line-height:1.6; white-space:pre-wrap; margin-bottom:14px; background:#FAFAFA; border:1px solid #F3F4F6; padding:14px 18px; border-radius:10px;">
                        <?= htmlspecialchars($n['content']) ?>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#6B7280; flex-wrap:wrap; gap:8px;">
                        <span>
                            Audience: <strong style="color:#111827; text-transform:uppercase;"><?= htmlspecialchars($n['target_audience']) ?></strong>
                        </span>
                        <span>
                            Posted by <strong><?= htmlspecialchars($n['author_name'] ?? 'Executive Leadership') ?></strong> (<?= htmlspecialchars($n['author_code'] ?? 'FNX') ?>) on <?= date('d M Y, h:i A', strtotime($n['created_at'])) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (Auth::isAdminOrExec()): ?>
<!-- Modal: Broadcast Notice / Event / Holiday -->
<div id="broadcastNoticeModal" class="fnx-modal-backdrop" style="display:none;">
    <div class="fnx-modal-dialog" style="max-width:620px;">
        <div class="fnx-modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:#111827;">📢 Broadcast Notice or Event</h3>
                <p style="margin:2px 0 0; font-size:12px; color:#4B5563;">Publish company-wide alerts, holiday schedules, and events.</p>
            </div>
            <button type="button" class="fnx-modal-close" onclick="fnxCloseModal('broadcastNoticeModal')">&times;</button>
        </div>

        <form method="POST" action="/futurenxt-handler.php?action=create_notice">
            <input type="hidden" name="redirect_url" value="/futurenxt/notices">
            <div class="fnx-modal-body">
                <div style="margin-bottom:14px;">
                    <label class="fnx-form-label">Notice Title *</label>
                    <input type="text" name="title" class="fnx-input" placeholder="e.g. Diwali Holiday Announcement / Annual Tech Symposium 2026" required>
                </div>

                <div class="fnx-grid-2" style="margin-bottom:14px;">
                    <div>
                        <label class="fnx-form-label">Category *</label>
                        <select name="category" class="fnx-select" required>
                            <option value="holiday">🏖️ Holiday Notice</option>
                            <option value="event">🎉 Important Event</option>
                            <option value="notice" selected>📢 Company Notice</option>
                            <option value="urgent">🚨 Urgent Announcement</option>
                            <option value="policy">📜 Policy Update</option>
                        </select>
                    </div>
                    <div>
                        <label class="fnx-form-label">Target Audience *</label>
                        <select name="target_audience" class="fnx-select" required>
                            <option value="all" selected>All Personnel (Everyone)</option>
                            <option value="employees">Employees &amp; Interns</option>
                            <option value="interns">Interns Only</option>
                            <option value="managers">Managers Only</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="fnx-form-label">Effective Notice Date *</label>
                    <input type="date" name="notice_date" class="fnx-input" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="fnx-form-label">Notice Content &amp; Details *</label>
                    <textarea name="content" class="fnx-textarea" rows="5" placeholder="Detail the schedule, venue, instructions, holiday dates, and guidelines..." required style="resize:vertical; line-height:1.5;"></textarea>
                </div>

                <div style="display:flex; flex-direction:column; gap:8px; padding:12px 14px; background:#FFF9F4; border:1px solid var(--fnx-border); border-radius:8px; margin-bottom:14px;">
                    <label style="display:inline-flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:#111827; cursor:pointer;">
                        <input type="checkbox" name="is_pinned" value="1" checked style="accent-color:var(--fnx-orange);">
                        📌 Pin to Top of Notice Board &amp; Dashboard
                    </label>
                    <label style="display:inline-flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:#111827; cursor:pointer;">
                        <input type="checkbox" name="send_email_notification" value="1" checked style="accent-color:var(--fnx-orange);">
                        📧 Send Instant Email Notification to All Target Personnel
                    </label>
                </div>
            </div>

            <div class="fnx-modal-footer">
                <button type="button" class="fnx-btn fnx-btn-outline" onclick="fnxCloseModal('broadcastNoticeModal')">Cancel</button>
                <button type="submit" class="fnx-btn fnx-btn-orange">✓ Publish &amp; Broadcast →</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

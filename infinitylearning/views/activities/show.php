<?php
use App\Activity;
use App\Auth;
use App\Helper;
use App\Session;

$slug = $_GET['slug'] ?? '';
$activity = Activity::findBySlug($slug);
if (!$activity) { http_response_code(404); require VIEW_PATH . '/404.php'; return; }

$user = Auth::user();
if (!Activity::canView($activity, $user)) {
    http_response_code(403);
    exit('This activity is private to a school you are not a member of.');
}

$registered = false;
if ($user) {
    $stmt = \App\Database::connect()->prepare(
        "SELECT id FROM activity_registrations WHERE activity_id=? AND user_id=?"
    );
    $stmt->execute([$activity['id'], $user['id']]);
    $registered = (bool)$stmt->fetch();
}

$pageTitle = $activity['title'] . ' — Infinity Learning';
$cover = $activity['cover_image'] ?: '/assets/img/placeholder-activity.png';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm">
    <div class="container">

        <a href="/activities" class="back-link">← All activities</a>

        <!-- ============ HERO IMAGE ============ -->
        <div class="detail-hero">
            <img src="<?= Helper::e($cover) ?>" alt="">

            <div class="detail-hero-gradient"></div>

            <div class="detail-hero-body">
                <div class="detail-hero-badges">
                    <span class="badge badge-primary"><?= ucfirst($activity['category']) ?></span>
                    <?php if (empty($activity['school_id'])): ?>
                        <span class="badge badge-info">🌐 Global event</span>
                    <?php elseif (!empty($activity['school_name'])): ?>
                        <span class="badge badge-brand">🏫 <?= Helper::e($activity['school_name']) ?></span>
                    <?php endif; ?>
                    <?php if ($activity['featured']): ?>
                        <span class="badge badge-success">⭐ Featured</span>
                    <?php endif; ?>
                </div>

                <h1><?= Helper::e($activity['title']) ?></h1>
                <p class="detail-hero-sub"><?= Helper::e($activity['short_description'] ?? '') ?></p>

                <div class="detail-hero-meta">
                    <?php if ($activity['event_date']): ?>
                        <span>📅 <?= date('d M Y', strtotime($activity['event_date'])) ?></span>
                    <?php endif; ?>
                    <?php if ($activity['venue']): ?>
                        <span>📍 <?= Helper::e($activity['venue']) ?></span>
                    <?php endif; ?>
                    <?php if (($activity['fee'] ?? 0) > 0): ?>
                        <span>💰 ₹<?= number_format((float)$activity['fee'], 0) ?></span>
                    <?php else: ?>
                        <span>🎟 Free</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ============ BODY + SIDEBAR ============ -->
        <div class="detail-grid">

            <div class="detail-main">
                <div class="card">
                    <h2 class="detail-h2">About this activity</h2>
                    <div class="detail-content"><?= nl2br(Helper::e($activity['description'] ?? '')) ?></div>

                    <?php if (!empty($activity['eligibility'])): ?>
                        <div class="detail-info-block">
                            <div class="detail-info-icon">🎯</div>
                            <div>
                                <strong>Eligibility</strong>
                                <p><?= Helper::e($activity['eligibility']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($activity['tags'])): ?>
                        <div class="detail-tags">
                            <?php foreach (explode(',', $activity['tags']) as $tag): ?>
                                <span class="pill"><?= Helper::e(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="detail-side">
                <div class="card">
                    <h3>📋 Event details</h3>
                    <div class="divider"></div>

                    <?php
                    $rows = [
                        'Date'          => $activity['event_date'] ? date('d M Y', strtotime($activity['event_date'])) : '—',
                        'Time'          => trim(($activity['start_time'] ? substr($activity['start_time'], 0, 5) : '') .
                                                ($activity['end_time']   ? ' – ' . substr($activity['end_time'], 0, 5) : '')) ?: '—',
                        'Venue'         => $activity['venue'] ?: '—',
                        'Organizer'     => $activity['organizer'] ?: '—',
                        'Reg. deadline' => $activity['registration_deadline'] ? date('d M Y', strtotime($activity['registration_deadline'])) : '—',
                    ];
                    foreach ($rows as $k => $v): ?>
                        <div class="kv"><span><?= $k ?></span><span><?= Helper::e((string)$v) ?></span></div>
                    <?php endforeach; ?>

                    <?php if ($activity['contact_email'] || $activity['contact_phone']): ?>
                        <div class="divider"></div>
                        <h4 style="font-size:.85rem;color:var(--il-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem">Contact</h4>
                        <?php if ($activity['contact_email']): ?>
                            <div class="kv"><span>Email</span><span><?= Helper::e($activity['contact_email']) ?></span></div>
                        <?php endif; ?>
                        <?php if ($activity['contact_phone']): ?>
                            <div class="kv"><span>Phone</span><span><?= Helper::e($activity['contact_phone']) ?></span></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="card mt-3">
                    <?php if (!$user): ?>
                        <a class="btn btn-primary btn-block btn-lg" href="/login">Login to register →</a>
                    <?php elseif ($registered): ?>
                        <div class="alert alert-success" style="margin-bottom:0">
                            ✅ You're registered for this activity.
                        </div>
                    <?php else: ?>
                        <form method="post" action="/activity-register-handler.php">
                            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                            <input type="hidden" name="activity_id" value="<?= (int)$activity['id'] ?>">
                            <textarea name="notes" placeholder="Any notes (optional)"
                                      style="width:100%;padding:.7rem;border:1.5px solid var(--il-border);border-radius:var(--r-md);margin-bottom:.75rem;font-family:inherit;font-size:.9rem;min-height:70px"></textarea>
                            <button class="btn btn-primary btn-block btn-lg">🎯 Register now</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($activity['registration_url']): ?>
                        <a class="btn btn-secondary btn-block mt-2"
                           href="<?= Helper::e($activity['registration_url']) ?>"
                           target="_blank" rel="noopener">External registration →</a>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
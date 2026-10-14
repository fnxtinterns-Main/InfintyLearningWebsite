<?php
use App\Auth;
use App\Counseling;
use App\Helper;
use App\Session;

Auth::requireLogin();
if (Auth::role() !== 'student') {
    http_response_code(403);
    exit('For students only.');
}

$user = Auth::user();
$requests = Counseling::forStudent((int)$user['id']);

$types = Counseling::types();

// Counts
$counts = ['pending'=>0,'assigned'=>0,'in_progress'=>0,'completed'=>0,'cancelled'=>0];
foreach ($requests as $r) {
    if (isset($counts[$r['status']])) $counts[$r['status']]++;
}

$pageTitle = 'My Counseling — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section">
    <div class="flex between" style="align-items:flex-start;flex-wrap:wrap;gap:1rem">
        <div>
            <span class="kicker">🧭 MY COUNSELING</span>
            <h1 style="margin-bottom:.35rem">My Counseling Requests</h1>
            <p class="muted">Track every request you've submitted and read your counselor's replies.</p>
        </div>
        <a href="/counseling/apply" class="btn btn-primary">+ New request</a>
    </div>

    <!-- ============ STAT TILES ============ -->
    <div class="grid grid-4 mt-3">
        <div class="stat"><span class="ico">⏳</span><span class="n"><?= (int)$counts['pending'] ?></span><span class="l">Pending</span></div>
        <div class="stat" style="border-left-color:#2563EB"><span class="ico">👩‍🏫</span><span class="n"><?= (int)($counts['assigned'] + $counts['in_progress']) ?></span><span class="l">In progress</span></div>
        <div class="stat" style="border-left-color:#16A34A"><span class="ico">✅</span><span class="n"><?= (int)$counts['completed'] ?></span><span class="l">Completed</span></div>
        <div class="stat"><span class="ico">🚫</span><span class="n"><?= (int)$counts['cancelled'] ?></span><span class="l">Cancelled</span></div>
    </div>

    <!-- ============ LIST ============ -->
    <?php if (!$requests): ?>
        <div class="card text-center mt-3">
            <img src="/assets/img/oldman.png" alt=""
                 style="max-height:110px;margin-bottom:.75rem;opacity:.85"
                 onerror="this.style.display='none'">
            <h3 style="margin-bottom:.35rem">No counseling requests yet</h3>
            <p class="muted" style="max-width:440px;margin:0 auto 1rem">
                Whether you need help with academics, career choices, admissions, or understanding yourself better —
                our counselors are here.
            </p>
            <a href="/counseling/apply" class="btn btn-primary">Start your first request →</a>
        </div>
    <?php else: ?>
        <div class="grid grid-2 mt-3">
            <?php foreach ($requests as $r):
                $t = $types[$r['counseling_type']] ?? ['label'=>$r['counseling_type'],'icon'=>'📋','color'=>'#999'];

                $statusLabel = match ($r['status']) {
                    'pending'     => ['⏳ Awaiting assignment', 'primary'],
                    'assigned'    => ['👩‍🏫 Counselor assigned', 'info'],
                    'in_progress' => ['🔄 In progress', 'primary'],
                    'completed'   => ['✅ Completed', 'success'],
                    'cancelled'   => ['🚫 Cancelled', 'muted'],
                    default       => [ucfirst($r['status']), 'muted'],
                };
            ?>
                <div class="card" style="border-top:4px solid <?= Helper::e($t['color']) ?>">
                    <div class="flex between" style="align-items:flex-start;gap:.5rem;flex-wrap:wrap">
                        <span class="badge" style="background:<?= Helper::e($t['color']) ?>22;color:<?= Helper::e($t['color']) ?>">
                            <?= $t['icon'] ?> <?= Helper::e($t['label']) ?>
                        </span>
                        <span class="badge badge-<?= $statusLabel[1] ?>">
                            <?= $statusLabel[0] ?>
                        </span>
                    </div>

                    <p class="muted" style="font-size:.82rem;margin-top:.5rem">
                        Submitted <?= date('d M Y, H:i', strtotime($r['created_at'])) ?>
                        <?php if (!empty($r['school_name'])): ?> · <?= Helper::e($r['school_name']) ?><?php endif; ?>
                    </p>

                    <!-- Counselor info -->
                    <?php if (!empty($r['teacher_name'])): ?>
                        <div class="counsel-card-section">
                            <div class="muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800;margin-bottom:.25rem">
                                Counselor
                            </div>
                            <div class="flex" style="gap:.55rem;align-items:center">
                                <span class="avatar avatar-initial" data-role="teacher">
                                    <?= Helper::e(mb_strtoupper(mb_substr($r['teacher_name'], 0, 1))) ?>
                                </span>
                                <div>
                                    <strong><?= Helper::e($r['teacher_name']) ?></strong>
                                    <?php if (!empty($r['assigned_at'])): ?>
                                        <div class="muted" style="font-size:.78rem">
                                            Assigned <?= date('d M Y', strtotime($r['assigned_at'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="counsel-card-section muted">
                            <div style="font-size:.85rem">
                                Your school admin hasn't assigned a counselor yet.
                                You'll be notified as soon as they do.
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Your ratings (compact view) -->
                    <div class="counsel-card-section">
                        <div class="muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800;margin-bottom:.35rem">
                            Your self-ratings
                        </div>
                        <div class="counsel-ratings">
                            <?php
                            $labels = [
                                'rating_concentration' => 'Concentration',
                                'rating_cognitive'     => 'Cognitive',
                                'rating_comprehension' => 'Comprehension',
                                'rating_creativity'    => 'Creativity',
                                'rating_confidence'    => 'Confidence',
                                'rating_communication' => 'Communication',
                            ];
                            foreach ($labels as $key => $label):
                                $val = (int)$r[$key];
                                $pct = (int)round($val / 6 * 100);
                            ?>
                                <div class="counsel-rating-item">
                                    <span class="counsel-rating-label"><?= $label ?></span>
                                    <div class="counsel-rating-bar">
                                        <div style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <span class="counsel-rating-num"><?= $val ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Reason -->
                    <?php if (!empty($r['reason'])): ?>
                        <div class="counsel-card-section">
                            <div class="muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800;margin-bottom:.25rem">
                                Your question
                            </div>
                            <div style="font-size:.88rem;line-height:1.6">
                                <?= nl2br(Helper::e(mb_substr($r['reason'], 0, 300))) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Teacher reply -->
                    <?php if ($r['status'] === 'completed' && !empty($r['teacher_notes'])): ?>
                        <div class="counsel-card-section counsel-reply">
                            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800;margin-bottom:.35rem;color:#B85000">
                                ✉️ Counselor's reply
                            </div>
                            <div style="font-size:.9rem;line-height:1.7">
                                <?= nl2br(Helper::e($r['teacher_notes'])) ?>
                            </div>
                            <?php if (!empty($r['completed_at'])): ?>
                                <div class="muted" style="font-size:.78rem;margin-top:.5rem">
                                    Completed <?= date('d M Y, H:i', strtotime($r['completed_at'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($r['status'] === 'in_progress'): ?>
                        <div class="alert alert-info" style="font-size:.85rem;padding:.6rem .8rem;margin-top:.75rem">
                            🔄 Your counselor is preparing the session. Notes will appear here when complete.
                        </div>
                    <?php elseif ($r['status'] === 'cancelled'): ?>
                        <div class="alert alert-warning" style="font-size:.85rem;padding:.6rem .8rem;margin-top:.75rem">
                            This request was cancelled. Feel free to submit a new one.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
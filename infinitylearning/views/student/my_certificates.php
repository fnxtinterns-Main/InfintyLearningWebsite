<?php
use App\Auth;
use App\Certificate;
use App\Helper;

Auth::requireLogin();
if (Auth::role() !== 'student') { http_response_code(403); exit('For students only.'); }

$user = Auth::user();
$certs = Certificate::listForStudent((int)$user['id']);

$counts = [
    'total'       => count($certs),
    'winner'      => 0,
    'runner_up'   => 0,
    'participant' => 0,
];
foreach ($certs as $c) {
    $t = $c['certificate_type'] ?? 'participant';
    if (isset($counts[$t])) $counts[$t]++;
}

$pageTitle = 'My Certificates — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section">
    <div class="flex between" style="align-items:flex-start">
        <div>
            <h1>🏅 My Certificates</h1>
            <p class="muted">Every certificate you've earned on Infinity Learning.</p>
        </div>
        <a href="/dashboard" class="btn btn-secondary btn-sm">← Dashboard</a>
    </div>

    <!-- Counts -->
    <div class="grid grid-4 mt-3">
        <div class="stat"><span class="ico">📜</span><span class="n"><?= (int)$counts['total'] ?></span><span class="l">Total</span></div>
        <div class="stat" style="border-left:4px solid #16A34A"><span class="ico">🏆</span><span class="n"><?= (int)$counts['winner'] ?></span><span class="l">Winner</span></div>
        <div class="stat" style="border-left:4px solid #2563EB"><span class="ico">🥈</span><span class="n"><?= (int)$counts['runner_up'] ?></span><span class="l">Runner-up</span></div>
        <div class="stat" style="border-left:4px solid #FF7A18"><span class="ico">🎓</span><span class="n"><?= (int)$counts['participant'] ?></span><span class="l">Participation</span></div>
    </div>

    <?php if (!$certs): ?>
        <div class="card text-center mt-3">
            <img src="/assets/img/oldman.png" alt=""
                 style="max-height:120px;margin-bottom:.75rem;opacity:.85"
                 onerror="this.style.display='none'">
            <p class="muted">
                You haven't earned any certificates yet.<br>
                Participate in activities and competitions to earn them!
            </p>
            <a href="/activities" class="btn btn-primary mt-2">Browse activities →</a>
        </div>
    <?php else: ?>
        <div class="grid grid-3 mt-3">
            <?php foreach ($certs as $c):
                $meta = Certificate::typeMeta($c['certificate_type'] ?? 'participant');
                $meta2 = json_decode($c['metadata'] ?? '{}', true) ?: [];
            ?>
                <div class="card card-hover" style="border-top:4px solid var(--il-primary);padding:1.25rem">
                    <div class="flex between" style="align-items:flex-start;gap:.5rem">
                        <span class="badge badge-<?= $meta['badge'] ?>"><?= $meta['label'] ?></span>
                        <span class="muted" style="font-size:.78rem"><?= date('d M Y', strtotime($c['issued_at'])) ?></span>
                    </div>

                    <h3 class="mt-2" style="margin-bottom:.35rem"><?= Helper::e($c['title']) ?></h3>

                    <?php if (!empty($meta2['event_title'])): ?>
                        <p class="muted" style="font-size:.88rem;margin-bottom:.5rem">
                            For: <strong><?= Helper::e($meta2['event_title']) ?></strong>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($meta2['rank'])): ?>
                        <p class="muted" style="font-size:.85rem">
                            🏅 <?= Helper::e($meta2['rank']) ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($meta2['school_name'])): ?>
                        <p class="muted" style="font-size:.82rem">
                            🏫 <?= Helper::e($meta2['school_name']) ?>
                        </p>
                    <?php endif; ?>

                    <div class="flex mt-2">
                        <a class="btn btn-primary btn-sm" href="/cert-download.php?id=<?= (int)$c['id'] ?>">
                            ⬇ Download PDF
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
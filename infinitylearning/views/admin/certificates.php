<?php
use App\Auth;
use App\Certificate;
use App\Helper;
use App\Database;

Auth::requireLogin();
if (Auth::role() !== 'admin') { http_response_code(403); exit('Forbidden'); }

$isSuper = Auth::isSuperAdmin();
$scopeSchoolId = $isSuper ? -1 : Auth::schoolId();

$type = $_GET['type'] ?? '';
$q    = trim($_GET['q'] ?? '');

$certs = Certificate::listForAdmin($scopeSchoolId, $type ?: null, $q ?: null, 300);

$counts = ['total' => count($certs), 'winner' => 0, 'runner_up' => 0, 'participant' => 0];
foreach ($certs as $c) {
    $t = $c['certificate_type'] ?? 'participant';
    if (isset($counts[$t])) $counts[$t]++;
}

$pageTitle = 'Certificates — ' . ($isSuper ? 'Admin' : 'School Admin');
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php
    if ($isSuper) require VIEW_PATH . '/admin/_sidebar.php';
    else         require VIEW_PATH . '/school/_sidebar.php';
    ?>
    <div>
        <div class="flex between" style="align-items:flex-start;flex-wrap:wrap;gap:.5rem">
            <div>
                <h1>Certificates</h1>
                <p class="muted">
                    <?php if ($isSuper): ?>
                        Every certificate issued across all schools.
                    <?php else: ?>
                        Certificates earned by your school's students.
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="grid grid-4 mt-3">
            <div class="stat"><span class="ico">📜</span><span class="n"><?= (int)$counts['total'] ?></span><span class="l">Total</span></div>
            <div class="stat" style="border-left:4px solid #16A34A"><span class="ico">🏆</span><span class="n"><?= (int)$counts['winner'] ?></span><span class="l">Winner</span></div>
            <div class="stat" style="border-left:4px solid #2563EB"><span class="ico">🥈</span><span class="n"><?= (int)$counts['runner_up'] ?></span><span class="l">Runner-up</span></div>
            <div class="stat" style="border-left:4px solid #FF7A18"><span class="ico">🎓</span><span class="n"><?= (int)$counts['participant'] ?></span><span class="l">Participation</span></div>
        </div>

        <div class="filters mt-3">
            <a href="?type=" class="<?= $type === '' ? 'active' : '' ?>">All types</a>
            <a href="?type=winner" class="<?= $type === 'winner' ? 'active' : '' ?>">🏆 Winner</a>
            <a href="?type=runner_up" class="<?= $type === 'runner_up' ? 'active' : '' ?>">🥈 Runner-up</a>
            <a href="?type=participant" class="<?= $type === 'participant' ? 'active' : '' ?>">🎓 Participation</a>
        </div>

        <form method="get" class="flex mb-3" style="max-width:420px">
            <?php if ($type): ?><input type="hidden" name="type" value="<?= Helper::e($type) ?>"><?php endif; ?>
            <input type="text" name="q" placeholder="Search by student, email or title…"
                   value="<?= Helper::e($q) ?>"
                   style="flex:1;padding:.65rem .9rem;border:1.5px solid var(--il-border);border-radius:10px">
            <button class="btn btn-primary">Search</button>
            <?php if ($type || $q): ?>
                <a href="/<?= $isSuper ? 'admin' : 'school' ?>/certificates" class="btn btn-ghost">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (!$certs): ?>
            <div class="card text-center mt-3">
                <p class="muted">No certificates found matching your filters.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <?php if ($isSuper): ?><th>School</th><?php endif; ?>
                            <th>Certificate</th>
                            <th>Type</th>
                            <th>Issued</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($certs as $c):
                        $meta = Certificate::typeMeta($c['certificate_type'] ?? 'participant');
                        $meta2 = json_decode($c['metadata'] ?? '{}', true) ?: [];
                    ?>
                        <tr>
                            <td><?= (int)$c['id'] ?></td>
                            <td>
                                <div class="flex">
                                    <?php if (!empty($c['profile_image'])): ?>
                                        <img class="avatar" src="<?= Helper::e($c['profile_image']) ?>" alt="">
                                    <?php else: ?>
                                        <span class="avatar avatar-initial" data-role="student">
                                            <?= Helper::e(mb_strtoupper(mb_substr($c['student_name'] ?: $c['student_email'], 0, 1))) ?>
                                        </span>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= Helper::e($c['student_name'] ?: '—') ?></strong>
                                        <div class="muted" style="font-size:.78rem"><?= Helper::e($c['student_email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <?php if ($isSuper): ?>
                                <td><?= Helper::e($c['school_name'] ?: '—') ?></td>
                            <?php endif; ?>
                            <td>
                                <strong><?= Helper::e($c['title']) ?></strong>
                                <?php if (!empty($meta2['event_title'])): ?>
                                    <div class="muted" style="font-size:.78rem"><?= Helper::e($meta2['event_title']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($meta2['rank'])): ?>
                                    <div class="muted" style="font-size:.78rem">🏅 <?= Helper::e($meta2['rank']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-<?= $meta['badge'] ?>"><?= $meta['label'] ?></span></td>
                            <td><?= date('d M Y', strtotime($c['issued_at'])) ?></td>
                            <td>
                                <a class="btn btn-ghost btn-sm" href="/cert-download.php?id=<?= (int)$c['id'] ?>">
                                    ⬇ Download
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
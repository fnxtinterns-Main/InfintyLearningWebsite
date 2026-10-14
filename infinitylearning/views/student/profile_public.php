<?php
use App\Auth;
use App\Helper;
use App\StudentProfile;
use App\Database;

Auth::requireLogin();

$viewer      = Auth::user();
$viewerRole  = Auth::role();
$studentId   = (int)($_GET['id'] ?? 0);
if (!$studentId) { Helper::redirect('/dashboard'); }

// Access control
$canView = false;
if ($viewerRole === 'admin') {
    $canView = true;
} elseif ($viewerRole === 'student' && (int)$viewer['id'] === $studentId) {
    $canView = true;
} elseif ($viewerRole === 'teacher') {
    // Only if the student is in one of the teacher's batches
    $db = Database::connect();
    $q = $db->prepare("
        SELECT 1 FROM batch_students bs
        JOIN batches b ON b.id = bs.batch_id
        WHERE bs.student_user_id = ? AND b.teacher_id = ?
        LIMIT 1
    ");
    $q->execute([$studentId, (int)$viewer['id']]);
    $canView = (bool)$q->fetch();
}
if (!$canView) {
    http_response_code(403);
    exit('You do not have access to this profile.');
}

$p = StudentProfile::build($studentId);
if (!$p) { http_response_code(404); require VIEW_PATH . '/404.php'; return; }

$isOwnProfile = ((int)$viewer['id'] === $studentId);

$pageTitle = ($p['full_name'] ?: 'Student') . ' — Development Profile';
require VIEW_PATH . '/layouts/header.php';

$achievementCounts = StudentProfile::achievementCounts($p['achievements']);
$initial = mb_strtoupper(mb_substr($p['full_name'] ?: $p['email'], 0, 1));
?>

<section class="section">
    <?php if (!$isOwnProfile): ?>
        <a href="<?= $viewerRole === 'teacher' ? '/teacher/batches' : '/admin/users' ?>" class="muted">← Back</a>
    <?php endif; ?>

    <!-- ================================================================
         HEADER
         ================================================================ -->
    <div class="card mt-2">
        <div class="flex" style="gap:1.5rem;align-items:flex-start;flex-wrap:wrap">
            <?php if (!empty($p['profile_image'])): ?>
                <img src="<?= Helper::e($p['profile_image']) ?>" alt=""
                     style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--il-primary-soft)">
            <?php else: ?>
                <span class="avatar avatar-initial" data-role="student"
                      style="width:96px;height:96px;font-size:2rem">
                    <?= Helper::e($initial) ?>
                </span>
            <?php endif; ?>

            <div style="flex:1;min-width:220px">
                <h1 style="margin-bottom:.25rem"><?= Helper::e($p['full_name'] ?: 'Unnamed student') ?></h1>
                <p class="muted">
                    <?php if ($p['current_class']): ?><?= Helper::e($p['current_class']) ?> · <?php endif; ?>
                    <?php if ($p['school_name']):   ?><?= Helper::e($p['school_name'])   ?><?php endif; ?>
                    <?php if ($p['school_board']):  ?> (<?= Helper::e($p['school_board']) ?>)<?php endif; ?>
                </p>
                <div class="flex mt-1" style="flex-wrap:wrap;gap:.5rem">
                    <?php if ($p['gender']): ?>
                        <span class="pill"><?= Helper::e(ucfirst(str_replace('_',' ',$p['gender']))) ?></span>
                    <?php endif; ?>
                    <?php if ($p['age']): ?>
                        <span class="pill">Age <?= (int)$p['age'] ?></span>
                    <?php endif; ?>
                    <?php if ($p['city']): ?>
                        <span class="pill"><?= Helper::e($p['city']) ?><?= $p['state'] ? ', '.Helper::e($p['state']) : '' ?></span>
                    <?php endif; ?>
                    <?php if ($p['academic_year']): ?>
                        <span class="pill">AY <?= Helper::e($p['academic_year']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="flex mt-2" style="gap:8px; flex-wrap:wrap; align-items:center;">
                    <?php if ($isOwnProfile): ?>
                        <a href="/profile" class="btn btn-secondary btn-sm">Edit my profile</a>
                    <?php endif; ?>
                    <a href="/student/doubts?student_id=<?= (int)$studentId ?>" class="btn btn-primary btn-sm" style="font-weight:700;">
                        💬 Doubt Portal
                    </a>
                    <a href="/parent-pass?student_id=<?= (int)$studentId ?>" class="btn btn-outline-primary btn-sm">
                        📱 Parent Progress Pass
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================
         TOP STATS
         ================================================================ -->
    <div class="grid grid-5 mt-3" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="stat">
            <span class="ico">✅</span>
            <span class="n"><?= (int)$p['attendance']['present_pct'] ?>%</span>
            <span class="l">Attendance</span>
        </div>
        <div class="stat">
            <span class="ico">📚</span>
            <span class="n"><?= (int)$p['lessons']['completed'] ?></span>
            <span class="l">Lessons completed</span>
        </div>
        <div class="stat">
            <span class="ico">🏫</span>
            <span class="n"><?= count($p['batches']) ?></span>
            <span class="l">Batches</span>
        </div>
        <div class="stat">
            <span class="ico">💬</span>
            <span class="n"><?= (int)($p['doubts_stats']['total'] ?? 0) ?></span>
            <span class="l">Doubts Asked (<?= (int)($p['doubts_stats']['answered'] ?? 0) ?> ans)</span>
        </div>
        <div class="stat">
            <span class="ico">🏆</span>
            <span class="n"><?= count($p['achievements']) + count($p['certificates']) ?></span>
            <span class="l">Achievements</span>
        </div>
    </div>

    <!-- ================================================================
         DEVELOPMENT SNAPSHOT (doc §14)
         ================================================================ -->
    <div class="card mt-3">
        <h3>🌱 Development Snapshot</h3>
        <p class="muted" style="font-size:.88rem">Participation across key growth areas.</p>
        <div class="divider"></div>

        <?php
        $areas = [
            'academic'        => ['📘','Academic',         '#FF7A18'],
            'sports'          => ['⚽','Sports',           '#16A34A'],
            'creative'        => ['🎨','Creative',         '#A855F7'],
            'communication'   => ['💬','Communication',    '#2563EB'],
            'problem_solving' => ['🧩','Problem-solving',  '#F59E0B'],
        ];
        $maxCount = 1;
        foreach ($areas as $k => $_) {
            $maxCount = max($maxCount, count($p['activity_profiles'][$k] ?? []));
        }
        ?>

        <?php foreach ($areas as $key => [$icon, $label, $color]):
            $count = count($p['activity_profiles'][$key] ?? []);
            $pct = (int)round($count / $maxCount * 100);
        ?>
            <div style="margin-bottom:.85rem">
                <div class="flex between" style="margin-bottom:.25rem">
                    <span style="font-weight:700;font-size:.9rem"><?= $icon ?> <?= $label ?></span>
                    <span class="muted" style="font-size:.82rem"><?= $count ?> participation<?= $count === 1 ? '' : 's' ?></span>
                </div>
                <div style="height:10px;border-radius:99px;background:var(--il-surface-2);overflow:hidden">
                    <div style="height:100%;width:<?= $pct ?>%;background:<?= $color ?>;transition:width .5s"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ================================================================
         TWO-COLUMN: ACADEMIC + PARENT
         ================================================================ -->
    <div class="grid grid-2 mt-3">
        <div class="card">
            <h3>📘 Academic</h3>
            <div class="divider"></div>
            <div class="kv"><span>School</span><span><?= Helper::e($p['school_name'] ?: '—') ?></span></div>
            <div class="kv"><span>Board</span><span><?= Helper::e($p['school_board'] ?: '—') ?></span></div>
            <div class="kv"><span>Class</span><span><?= Helper::e($p['current_class'] ?: '—') ?></span></div>
            <div class="kv"><span>Academic year</span><span><?= Helper::e($p['academic_year'] ?: '—') ?></span></div>
            <div class="kv"><span>Interests</span><span><?= Helper::e($p['interests'] ?: '—') ?></span></div>
            <div class="kv"><span>Lessons started</span><span><?= (int)$p['lessons']['started'] ?></span></div>
            <div class="kv"><span>Lessons completed</span><span><?= (int)$p['lessons']['completed'] ?></span></div>
        </div>

        <div class="card">
            <h3>👨‍👩‍👧 Family</h3>
            <div class="divider"></div>
            <?php if (!$p['parents']): ?>
                <p class="muted">No parent info on file.</p>
            <?php else: foreach ($p['parents'] as $par): ?>
                <div class="kv"><span>Name</span><span><?= Helper::e($par['full_name']) ?></span></div>
                <div class="kv"><span>Relationship</span><span><?= Helper::e(ucfirst($par['relationship'])) ?></span></div>
                <div class="kv"><span>Phone</span><span><?= Helper::e($par['phone']) ?></span></div>
                <div class="kv"><span>Email</span><span><?= Helper::e($par['email'] ?: '—') ?></span></div>
                <div class="kv"><span>Occupation</span><span><?= Helper::e($par['occupation'] ?: '—') ?></span></div>
                <div class="kv"><span>City</span><span><?= Helper::e($par['city'] ?: '—') ?></span></div>
                <?php if (count($p['parents']) > 1): ?><div class="divider"></div><?php endif; ?>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- ================================================================
         BATCHES
         ================================================================ -->
    <div class="card mt-3">
        <h3>🏫 Batches</h3>
        <div class="divider"></div>
        <?php if (!$p['batches']): ?>
            <p class="muted">No batches yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Batch</th><th>Subject</th><th>Class</th><th>Teacher</th><th>Joined</th></tr></thead>
                    <tbody>
                    <?php foreach ($p['batches'] as $b): ?>
                        <tr>
                            <td><strong><?= Helper::e($b['name']) ?></strong></td>
                            <td><?= Helper::e($b['subject'] ?: '—') ?></td>
                            <td><?= Helper::e($b['class_level'] ?: '—') ?></td>
                            <td><?= Helper::e($b['teacher_name'] ?: '—') ?></td>
                            <td><?= date('d M Y', strtotime($b['joined_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================================================================
         STUDENT DOUBT & Q&A PORTAL
         ================================================================ -->
    <div class="card mt-3">
        <div class="flex between" style="align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h3 style="margin:0;">💬 Student Doubt & Q&A Portal</h3>
                <p class="muted" style="font-size:.88rem; margin:2px 0 0;">Questions asked across lessons and verified teacher explanations.</p>
            </div>
            <div style="display:flex; gap:8px;">
                <a href="/student/doubts?student_id=<?= (int)$studentId ?>" class="btn btn-outline-primary btn-sm">
                    Open Full Doubt Portal →
                </a>
            </div>
        </div>
        <div class="divider"></div>

        <?php
        $doubtsStats = $p['doubts_stats'] ?? ['total'=>0,'answered'=>0,'open'=>0];
        $recentDoubts = $p['recent_doubts'] ?? [];
        ?>

        <div class="grid grid-3 mb-3">
            <div class="stat">
                <span class="ico">❓</span>
                <span class="n"><?= $doubtsStats['total'] ?></span>
                <span class="l">Total Asked</span>
            </div>
            <div class="stat" style="border-left-color:#10b981;">
                <span class="ico">✅</span>
                <span class="n" style="color:#10b981;"><?= $doubtsStats['answered'] ?></span>
                <span class="l">Answered by Teachers</span>
            </div>
            <div class="stat" style="border-left-color:#f59e0b;">
                <span class="ico">⏳</span>
                <span class="n" style="color:#f59e0b;"><?= $doubtsStats['open'] ?></span>
                <span class="l">Awaiting Response</span>
            </div>
        </div>

        <?php if (empty($recentDoubts)): ?>
            <p class="muted" style="text-align:center; padding:16px 0;">No doubts recorded yet. Students can ask questions directly from any lesson or via the Doubt Portal.</p>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php foreach (array_slice($recentDoubts, 0, 5) as $d): ?>
                    <div style="padding:12px 14px; border-radius:10px; background:var(--surface-1); border:1px solid var(--il-border); border-left:4px solid <?= $d['status'] === 'answered' ? '#10b981' : '#f59e0b' ?>;">
                        <div class="flex between" style="align-items:flex-start; gap:10px; flex-wrap:wrap;">
                            <div>
                                <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px; flex-wrap:wrap;">
                                    <?php if (!empty($d['subject_name'])): ?>
                                        <span class="badge badge-brand" style="font-size:10px;"><?= Helper::e($d['subject_name']) ?></span>
                                    <?php endif; ?>
                                    <span class="badge badge-primary" style="font-size:10px;">📖 <?= Helper::e($d['lesson_title']) ?></span>
                                    <span class="muted" style="font-size:11px;">· <?= date('d M Y', strtotime($d['created_at'])) ?></span>
                                </div>
                                <strong style="font-size:14px; color:var(--il-text);"><?= Helper::e($d['title']) ?></strong>
                                <p class="muted" style="font-size:12px; margin:4px 0 0; line-height:1.4;">
                                    <?= Helper::e(mb_substr($d['body'], 0, 160)) ?><?= mb_strlen($d['body']) > 160 ? '...' : '' ?>
                                </p>
                            </div>
                            <div>
                                <span class="badge <?= $d['status'] === 'answered' ? 'badge-success' : 'badge-warning' ?>" style="font-size:11px;">
                                    <?= $d['status'] === 'answered' ? '✅ Answered' : '⏳ Pending' ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($d['replies'])): ?>
                            <div style="margin-top:10px; padding-top:8px; border-top:1px dashed var(--il-border); font-size:12px; color:var(--il-text);">
                                <span style="font-weight:700; color:#10b981;">💬 Latest Answer by <?= Helper::e($d['replies'][count($d['replies'])-1]['author_name']) ?>:</span>
                                <?= Helper::e(mb_substr($d['replies'][count($d['replies'])-1]['reply_text'], 0, 140)) ?>...
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================================================================
         ATTENDANCE
         ================================================================ -->
    <div class="card mt-3">
        <h3>✅ Attendance</h3>
        <div class="divider"></div>
        <?php if ($p['attendance']['total'] === 0): ?>
            <p class="muted">No attendance recorded yet.</p>
        <?php else:
            $att = $p['attendance']['raw'];
            $tot = $p['attendance']['total'];
            $pct = fn($n) => (int)round($n / $tot * 100);
        ?>
            <div class="grid grid-4">
                <div class="stat" style="border-left:4px solid #16A34A">
                    <span class="n"><?= $att['present'] ?></span>
                    <span class="l">Present (<?= $pct($att['present']) ?>%)</span>
                </div>
                <div class="stat" style="border-left:4px solid #DC2626">
                    <span class="n"><?= $att['absent'] ?></span>
                    <span class="l">Absent (<?= $pct($att['absent']) ?>%)</span>
                </div>
                <div class="stat" style="border-left:4px solid #F59E0B">
                    <span class="n"><?= $att['late'] ?></span>
                    <span class="l">Late (<?= $pct($att['late']) ?>%)</span>
                </div>
                <div class="stat" style="border-left:4px solid #2563EB">
                    <span class="n"><?= $att['excused'] ?></span>
                    <span class="l">Excused (<?= $pct($att['excused']) ?>%)</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================================================================
         ACTIVITY TIMELINE
         ================================================================ -->
    <div class="card mt-3">
        <h3>🏆 Activity Participation</h3>
        <p class="muted" style="font-size:.88rem">Every event this student has signed up for, newest first.</p>
        <div class="divider"></div>

        <?php if (!$p['activities']): ?>
            <p class="muted">No activities yet.</p>
        <?php else: ?>
            <?php foreach ($p['activities'] as $a):
                $cover = $a['cover_image'] ?: '/assets/img/placeholder-activity.png';
                $isWinner = !empty($a['result_position']) && (int)$a['result_position'] <= 3;
            ?>
                <div class="flex" style="gap:1rem;padding:.85rem 0;border-bottom:1px dashed var(--il-border);align-items:flex-start">
                    <img src="<?= Helper::e($cover) ?>" alt=""
                         style="width:72px;height:72px;border-radius:10px;object-fit:cover;flex-shrink:0">
                    <div style="flex:1">
                        <div class="flex" style="gap:.5rem;align-items:center">
                            <strong><?= Helper::e($a['title']) ?></strong>
                            <span class="badge badge-primary"><?= Helper::e(ucfirst($a['category'])) ?></span>
                            <?php if ($isWinner): ?>
                                <span class="badge badge-success">🏅 Rank <?= (int)$a['result_position'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="muted" style="font-size:.85rem;margin-top:.15rem">
                            <?php if ($a['event_date']): ?>📅 <?= date('d M Y', strtotime($a['event_date'])) ?> · <?php endif; ?>
                            Status: <?= Helper::e(ucfirst(str_replace('_',' ',$a['participation_status']))) ?>
                            <?php if (!empty($a['result_score'])): ?>
                                · Score: <?= Helper::e((string)$a['result_score']) ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($a['short_description'])): ?>
                            <div class="muted" style="font-size:.85rem;margin-top:.15rem">
                                <?= Helper::e(mb_substr($a['short_description'], 0, 140)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <a href="/activity?slug=<?= urlencode($a['slug']) ?>" class="btn btn-ghost btn-sm">View</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ================================================================
         ACHIEVEMENTS
         ================================================================ -->
    <div class="card mt-3">
        <h3>🏅 Achievements</h3>
        <div class="divider"></div>

        <?php if (!$p['achievements']): ?>
            <p class="muted">No achievements recorded yet.</p>
        <?php else: ?>
            <div class="grid grid-3">
                <?php foreach ($p['achievements'] as $ac): ?>
                    <div class="card" style="border-left:4px solid var(--il-primary);padding:1rem">
                        <div class="flex between" style="align-items:flex-start">
                            <strong><?= Helper::e($ac['title']) ?></strong>
                            <span class="badge badge-primary"><?= Helper::e(str_replace('_',' ',$ac['category'])) ?></span>
                        </div>
                        <?php if (!empty($ac['description'])): ?>
                            <p class="muted" style="font-size:.85rem;margin-top:.35rem"><?= Helper::e($ac['description']) ?></p>
                        <?php endif; ?>
                        <div class="muted" style="font-size:.78rem;margin-top:.35rem">
                            <?= date('d M Y', strtotime($ac['earned_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================================================================
         CERTIFICATES
         ================================================================ -->
    <div class="card mt-3">
        <h3>📜 Certificates</h3>
        <div class="divider"></div>

        <?php if (!$p['certificates']): ?>
            <p class="muted">No certificates issued yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Title</th><th>Type</th><th>Issued</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($p['certificates'] as $c): ?>
                        <tr>
                            <td><strong><?= Helper::e($c['title']) ?></strong></td>
                            <td><span class="pill"><?= Helper::e($c['type']) ?></span></td>
                            <td><?= date('d M Y', strtotime($c['issued_at'])) ?></td>
                            <td>
                                <?php if (!empty($c['certificate_url'])): ?>
                                    <a class="btn btn-ghost btn-sm" href="<?= Helper::e($c['certificate_url']) ?>" target="_blank">Download</a>
                                <?php else: ?>
                                    <span class="muted" style="font-size:.8rem">Not available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
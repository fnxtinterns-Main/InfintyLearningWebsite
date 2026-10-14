<?php
use App\Auth;
use App\Helper;
use App\Activity;
use App\Batch;
use App\Attendance;
use App\Lesson;
use App\Announcement;
use App\Database;
use App\Chess\ChessRating;

$user = Auth::user();
$student = [];
try {
    $student = Auth::findStudentByUserId((int)$user['id']) ?: [];
} catch (\Throwable $e) {}

$chessProfile = [
    'tier' => 'Bronze',
    'current_rating' => 1200,
    'calibration_completed' => true,
    'in_promotion_battle' => false,
    'wins' => 0,
    'games_played' => 0
];
try {
    $chessProfile = ChessRating::getOrCreate((int)$user['id'], Auth::schoolId());
} catch (\Throwable $e) {}

$batches = [];
try {
    $batches = Batch::forStudent((int)$user['id']) ?: [];
} catch (\Throwable $e) {}

$att = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
try {
    $att = Attendance::summaryForStudent((int)$user['id']) ?: $att;
} catch (\Throwable $e) {}

$lessons = [];
try {
    $lessons = Lesson::listForViewer(['limit' => 4]) ?: [];
} catch (\Throwable $e) {}

$upcoming = [];
try {
    $upcoming = Activity::upcoming(3, Auth::schoolId()) ?: [];
} catch (\Throwable $e) {}

$ann = [];
try {
    $ann = Announcement::latestForViewer('students', 3) ?: [];
} catch (\Throwable $e) {}

$parent = null;
try {
    $db = Database::connect();
    $parentStmt = $db->prepare("
        SELECT p.* FROM parents p
        JOIN student_parents sp ON sp.parent_id = p.id
        WHERE sp.student_user_id = ? AND sp.is_primary = 1
        LIMIT 1
    ");
    $parentStmt->execute([(int)$user['id']]);
    $parent = $parentStmt->fetch() ?: null;
} catch (\Throwable $e) {}

$upcomingAssignments = [];
try {
    $upcomingAssignments = \App\ActivityAssignment::upcomingForStudent((int)$user['id'], 5);
} catch (\Throwable $e) {}

$gamification = [
    'current_streak' => 0,
    'longest_streak' => 0,
    'active_days'    => 0,
    'has_today'      => false,
    'total_xp'       => 0,
    'level'          => 1,
    'cur_level_base' => 0,
    'next_level_xp'  => 100,
    'level_progress' => 0,
    'rank_title'     => 'Novice'
];
try {
    $gamification = \App\StudentProfile::calculateStreakAndXP((int)$user['id']);
} catch (\Throwable $e) {}

$doubtStats = ['total' => 0, 'answered' => 0, 'open' => 0];
try {
    $doubtStats = \App\DoubtFeed::getStudentStats((int)$user['id']);
} catch (\Throwable $e) {}

$pageTitle = 'Dashboard — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';

$totalAtt = array_sum($att) ?: 1;
$presentPct = (int)round(($att['present'] ?? 0) / $totalAtt * 100);
$absentPct  = (int)round(($att['absent'] ?? 0)  / $totalAtt * 100);
$latePct    = (int)round(($att['late'] ?? 0)    / $totalAtt * 100);
$excusedPct = (int)round(($att['excused'] ?? 0) / $totalAtt * 100);
?>
<section class="section-sm">
    <div class="container-narrow" style="max-width:none">

        <!-- ============ WELCOME HEADER ============ -->
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px; align-items:center;">
            <div style="display:flex; align-items:center; gap:16px;">
                <?php if (!empty($schoolBranding['crest_logo'])): ?>
                    <div class="school-header-crest-badge" style="width:64px; height:64px; border-radius:12px; background:#FFFFFF; border:1.5px solid var(--il-border); box-shadow:0 2px 6px rgba(0,0,0,0.08); display:flex; align-items:center; justify-content:center; padding:4px; overflow:hidden; flex-shrink:0;">
                        <img src="<?= htmlspecialchars($schoolBranding['crest_logo']) ?>" alt="<?= Helper::e($schoolBranding['name'] ?? 'School') ?>" style="width:100%; height:100%; object-fit:contain;" onerror="this.closest('.school-header-crest-badge').style.display='none';">
                    </div>
                <?php endif; ?>
                <div class="dashboard-header-text">
                    <span class="kicker">👋 WELCOME BACK</span>
                    <h1 style="margin-top:2px; font-weight:800; letter-spacing:-0.02em;"><?= Helper::e($student['full_name'] ?? 'Student') ?></h1>
                    <p class="muted" style="margin-top:4px;"><?= Helper::e(!empty($student['current_class']) ? $student['current_class'] : 'Your learning snapshot') ?><?= !empty($schoolBranding['name']) ? ' · ' . Helper::e($schoolBranding['name']) : (!empty($student['school_name']) ? ' · ' . Helper::e($student['school_name']) : '') ?></p>
                </div>
            </div>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <a href="/student/doubts" class="btn btn-primary btn-sm" style="font-weight:700;">💬 Doubt Portal</a>
                <a href="/parent-pass" class="btn btn-outline-primary btn-sm" title="Share or view student progress card">📱 Parent Progress Pass</a>
                <a href="/profile" class="btn btn-secondary btn-sm">Edit profile</a>
            </div>
        </div>

        <!-- ============ STUDY STREAK & XP ENGINE BANNER ============ -->
        <div class="card mt-2" style="background:linear-gradient(135deg, rgba(99,102,241,0.08), rgba(245,158,11,0.08)); border:1px solid rgba(245,158,11,0.3); border-radius:14px; padding:16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div style="font-size:36px; line-height:1;">🔥</div>
                    <div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:18px; font-weight:800; color:var(--il-text);">
                                <?= $gamification['current_streak'] ?> Day Study Streak!
                            </span>
                            <?php if ($gamification['has_today']): ?>
                                <span class="badge badge-success" style="font-size:11px; padding:3px 8px;">✓ Active Today</span>
                            <?php else: ?>
                                <span class="badge badge-warning" style="font-size:11px; padding:3px 8px;">⏳ Learn today to keep streak!</span>
                            <?php endif; ?>
                        </div>
                        <div class="muted" style="font-size:12px; margin-top:3px;">
                            Best Streak: <strong><?= $gamification['longest_streak'] ?> days</strong> · <strong><?= $gamification['active_days'] ?></strong> active days logged
                        </div>
                    </div>
                </div>

                <!-- XP & LEVEL -->
                <div style="min-width:240px; flex:1; max-width:340px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:13px; font-weight:700; margin-bottom:6px;">
                        <span>Level <?= $gamification['level'] ?> <span class="badge badge-primary" style="font-size:11px; font-weight:600;"><?= $gamification['rank_title'] ?></span></span>
                        <span style="color:#f59e0b;">⚡ <?= $gamification['total_xp'] ?> XP</span>
                    </div>
                    <div style="background:var(--surface-2); border-radius:999px; height:8px; overflow:hidden; border:1px solid var(--il-border);">
                        <div style="background:linear-gradient(90deg, #f59e0b, #6366f1); height:100%; width:<?= $gamification['level_progress'] ?>%; border-radius:999px;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--il-muted); margin-top:4px;">
                        <span><?= $gamification['cur_level_base'] ?> XP</span>
                        <span><?= $gamification['level_progress'] ?>% to Level <?= $gamification['level'] + 1 ?></span>
                        <span><?= $gamification['next_level_xp'] ?> XP</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ STAT TILES ============ -->
        <div class="grid grid-5 mt-3" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));">
            <div class="stat">
                <span class="ico">🏫</span>
                <span class="n"><?= count($batches) ?></span>
                <span class="l">Batches</span>
            </div>
            <div class="stat" style="border-left-color:#16A34A">
                <span class="ico">✅</span>
                <span class="n"><?= $presentPct ?>%</span>
                <span class="l">Attendance</span>
            </div>
            <div class="stat" style="border-left-color:#2563EB">
                <span class="ico">📚</span>
                <span class="n"><?= count($lessons) ?></span>
                <span class="l">Lessons</span>
            </div>
            <div class="stat" style="border-left-color:#6366f1">
                <span class="ico">💬</span>
                <span class="n"><?= (int)$doubtStats['total'] ?></span>
                <span class="l">Doubts (<?= (int)$doubtStats['answered'] ?> ans)</span>
            </div>
            <div class="stat" style="border-left-color:#7C2D5C">
                <span class="ico">🏆</span>
                <span class="n"><?= count($upcoming) ?></span>
                <span class="l">Events</span>
            </div>
        </div>

        <!-- ============ RISING ROOKS CHESS WIDGET ============ -->
        <div class="card mt-3" style="background:linear-gradient(135deg, #0A192F 0%, #122A5A 100%); color:#fff; border:none; padding:20px 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <img src="/assets/img/chess/chess_logo.jpg" alt="Rising Rooks" style="width:60px; height:60px; border-radius:14px; object-fit:cover; border:2px solid #BE9420;">
                    <div>
                        <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:#FCD34D; font-weight:700;">Rising Rooks Chess Academy</div>
                        <h3 style="color:#fff; margin:2px 0 4px; font-size:18px;">
                            Tier: <?= ucfirst(Helper::e($chessProfile['tier'])) ?> • <?= (int)$chessProfile['current_rating'] ?> Elo
                        </h3>
                        <p style="color:#94A3B8; font-size:12px; margin:0;">
                            <?php if (!$chessProfile['calibration_completed']): ?>
                                🎯 Placement match <?= min(5, (int)$chessProfile['calibration_matches_played'] + 1) ?> of 5 waiting!
                            <?php elseif ($chessProfile['in_promotion_battle']): ?>
                                ⚔️ Promotion Battle in progress (<?= (int)$chessProfile['promotion_matches_played'] ?>/3 matches played)
                            <?php else: ?>
                                <?= (int)$chessProfile['wins'] ?> wins • <?= (int)$chessProfile['games_played'] ?> matches played
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div style="display:flex; gap:10px;">
                    <a href="/chess/play" class="btn btn-sm btn-primary" style="background:#FF7A18; border-color:#FF7A18; font-weight:700;">
                        Play Chess ♟️
                    </a>
                    <a href="/chess" class="btn btn-sm btn-outline-light" style="border-color:rgba(255,255,255,0.4); color:#fff;">
                        Open Hub →
                    </a>
                </div>
            </div>
        </div>

        <!-- ============ NEXT UP ASSIGNMENTS ============ -->
        <?php if ($upcomingAssignments): ?>
            <div class="card mt-3">
                <div class="flex between">
                    <h3>🎯 Next up</h3>
                    <span class="pill"><?= count($upcomingAssignments) ?> upcoming</span>
                </div>
                <p class="muted" style="font-size:.88rem">Activities your teacher scheduled for your batch.</p>
                <div class="divider"></div>

                <?php foreach ($upcomingAssignments as $a):
                    $cover = $a['activity_cover'] ?: '/assets/img/placeholder-activity.png'; ?>
                    <div class="flex" style="gap:1rem;padding:.85rem 0;border-bottom:1px dashed var(--il-border);align-items:flex-start">
                        <img src="<?= Helper::e($cover) ?>" alt=""
                             style="width:64px;height:64px;border-radius:10px;object-fit:cover;flex-shrink:0">
                        <div style="flex:1">
                            <div class="flex" style="gap:.5rem;align-items:center;flex-wrap:wrap">
                                <strong><?= Helper::e($a['activity_title']) ?></strong>
                                <span class="badge badge-primary"><?= Helper::e(ucfirst($a['activity_category'])) ?></span>
                            </div>
                            <div class="muted" style="font-size:.85rem;margin-top:.15rem">
                                <?php if ($a['scheduled_date']): ?>
                                    📅 <?= date('d M Y', strtotime($a['scheduled_date'])) ?>
                                    <?php if ($a['scheduled_time']): ?> · <?= substr($a['scheduled_time'], 0, 5) ?><?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($a['instructions'])): ?>
                                <div class="muted" style="font-size:.85rem;margin-top:.35rem;padding:.5rem .65rem;background:var(--surface-2);border-radius:8px">
                                    📝 <?= Helper::e($a['instructions']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="/activity?slug=<?= urlencode($a['activity_slug']) ?>" class="btn btn-ghost btn-sm">Open</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ============ ANNOUNCEMENTS ============ -->
        <?php if ($ann): ?>
            <div class="card mt-3">
                <h3>📣 Announcements</h3>
                <div class="divider"></div>
                <?php foreach ($ann as $a): ?>
                    <div class="kv">
                        <div>
                            <div class="flex" style="gap:.4rem;align-items:center;flex-wrap:wrap">
                                <strong><?= Helper::e($a['title']) ?></strong>
                                <?php if (!empty($a['school_name'])): ?>
                                    <span class="badge badge-brand"><?= Helper::e($a['school_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-info">Global</span>
                                <?php endif; ?>
                            </div>
                            <div class="muted" style="font-size:.85rem"><?= Helper::e($a['body']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ============ TWO COLUMNS ============ -->
        <div class="grid grid-2 mt-3">
            <div class="card">
                <div class="flex between" style="align-items:center;">
                    <h3 style="margin:0;">👤 Your Profile</h3>
                    <div style="display:flex; gap:6px;">
                        <a href="/student/doubts" class="btn btn-outline-primary btn-sm" style="font-size:11px; padding:3px 8px; font-weight:600;">💬 Doubt Portal</a>
                        <a href="/student?id=<?= (int)$user['id'] ?>" class="btn btn-ghost btn-sm" style="font-size:11px; padding:3px 8px;">Full profile →</a>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="kv"><span>Email</span><span><?= Helper::e($user['email']) ?></span></div>
                <div class="kv"><span>Phone</span><span><?= Helper::e(Helper::displayPhone($user['phone'])) ?></span></div>
                <div class="kv"><span>Class</span><span><?= Helper::e(!empty($student['current_class']) ? $student['current_class'] : '—') ?></span></div>
                <div class="kv"><span>Board</span><span><?= Helper::e(!empty($student['school_board']) ? $student['school_board'] : '—') ?></span></div>
                <div class="kv"><span>Academic year</span><span><?= Helper::e(!empty($student['academic_year']) ? $student['academic_year'] : '—') ?></span></div>
                <div class="kv"><span>Interests</span><span><?= Helper::e(!empty($student['interests']) ? $student['interests'] : '—') ?></span></div>
            </div>

            <div class="card">
                <h3>👨‍👩‍👧 Parent / Guardian</h3>
                <div class="divider"></div>
                <?php if (!$parent): ?>
                    <p class="muted">No parent info on file. <a href="/profile">Add it here</a>.</p>
                <?php else: ?>
                    <div class="kv"><span>Name</span><span><?= Helper::e($parent['full_name']) ?></span></div>
                    <div class="kv"><span>Relationship</span><span><?= Helper::e(ucfirst($parent['relationship'])) ?></span></div>
                    <div class="kv"><span>Phone</span><span><?= Helper::e(Helper::displayPhone($parent['phone'])) ?></span></div>
                    <div class="kv"><span>Email</span><span><?= Helper::e($parent['email'] ?: '—') ?></span></div>
                    <div class="kv"><span>Occupation</span><span><?= Helper::e($parent['occupation'] ?: '—') ?></span></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============ ATTENDANCE ============ -->
        <div class="card mt-3">
            <h3>📊 Attendance Breakdown</h3>
            <div class="divider"></div>
            <?php if ($totalAtt === 1 && array_sum($att) === 0): ?>
                <p class="muted">No attendance recorded yet.</p>
            <?php else: ?>
                <div class="grid grid-4">
                    <div class="stat" style="border-left-color:#16A34A"><span class="n"><?= $att['present'] ?></span><span class="l">Present (<?= $presentPct ?>%)</span></div>
                    <div class="stat" style="border-left-color:#DC2626"><span class="n"><?= $att['absent'] ?></span><span class="l">Absent (<?= $absentPct ?>%)</span></div>
                    <div class="stat" style="border-left-color:#F59E0B"><span class="n"><?= $att['late'] ?></span><span class="l">Late (<?= $latePct ?>%)</span></div>
                    <div class="stat" style="border-left-color:#2563EB"><span class="n"><?= $att['excused'] ?></span><span class="l">Excused (<?= $excusedPct ?>%)</span></div>
                </div>
                <div style="margin-top:1rem;height:12px;border-radius:99px;overflow:hidden;background:var(--surface-2);display:flex">
                    <div style="background:#16A34A;width:<?= $presentPct ?>%"></div>
                    <div style="background:#F59E0B;width:<?= $latePct ?>%"></div>
                    <div style="background:#2563EB;width:<?= $excusedPct ?>%"></div>
                    <div style="background:#DC2626;width:<?= $absentPct ?>%"></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============ BATCHES + LESSONS ============ -->
        <div class="grid grid-2 mt-3">
            <div class="card">
                <h3>🏫 Your Batches</h3>
                <div class="divider"></div>
                <?php if (!$batches): ?>
                    <p class="muted">No batches yet — ask your teacher to add you.</p>
                <?php else: foreach ($batches as $b): ?>
                    <div class="kv"><span><?= Helper::e($b['name']) ?></span><span class="pill"><?= Helper::e($b['subject'] ?: 'General') ?></span></div>
                <?php endforeach; endif; ?>
            </div>

            <div class="card">
                <div class="flex between">
                    <h3>📚 Continue Learning</h3>
                    <a href="/lessons" class="btn btn-ghost btn-sm">All lessons</a>
                </div>
                <div class="divider"></div>
                <?php if (!$lessons): ?>
                    <p class="muted">No lessons published yet.</p>
                <?php else: foreach ($lessons as $l): ?>
                    <a class="kv" href="/lesson?id=<?= (int)$l['id'] ?>" style="text-decoration:none;color:inherit">
                        <span><?= Helper::e($l['title']) ?></span>
                        <span class="pill"><?= Helper::e($l['subject_name']) ?></span>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- ============ UPCOMING ACTIVITIES ============ -->
        <?php if ($upcoming): ?>
            <div class="mt-3">
                <h3 class="mb-2">🏆 Upcoming Activities</h3>
                <div class="grid grid-3">
                    <?php foreach ($upcoming as $a):
                        $cover = $a['cover_image'] ?: '/assets/img/placeholder-activity.png'; ?>
                        <a class="activity-card" href="/activity?slug=<?= urlencode($a['slug']) ?>">
                            <img class="activity-cover" src="<?= Helper::e($cover) ?>" alt="">
                            <div class="activity-body">
                                <span class="badge badge-primary"><?= ucfirst($a['category']) ?></span>
                                <h3><?= Helper::e($a['title']) ?></h3>
                                <div class="activity-meta">
                                    <?php if ($a['event_date']): ?><span>📅 <?= date('d M', strtotime($a['event_date'])) ?></span><?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
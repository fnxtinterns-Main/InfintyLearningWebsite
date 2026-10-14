<?php
use App\Auth;
use App\Helper;
use App\Batch;
use App\Announcement;
use App\Lesson;
use App\Database;

$user = Auth::user();
$teacher = Auth::findTeacherByUserId((int)$user['id']);
$batches = Batch::all((int)$user['id']);
$ann = Announcement::latestForViewer('teachers', 3);

$studentCount = 0;
foreach ($batches as $b) {
    $studentCount += (int)($b['student_count'] ?? 0);
}

$lessonsCount = 0;
$lessonsDraft = 0;
try {
    $db = Database::connect();
    $lessonsCount = (int)$db->query("SELECT COUNT(*) FROM lessons WHERE created_by = " . (int)$user['id'])->fetchColumn();
    $lessonsDraft = (int)$db->query("SELECT COUNT(*) FROM lessons WHERE created_by = " . (int)$user['id'] . " AND published = 0")->fetchColumn();
} catch (\Throwable $e) {}

$pageTitle = 'Teacher Dashboard — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <div>

        <!-- ============ HEADER ============ -->
        <div class="dashboard-header" style="align-items:center;">
            <div style="display:flex; align-items:center; gap:16px;">
                <?php if (!empty($schoolBranding['crest_logo'])): ?>
                    <div class="school-header-crest-badge" style="width:64px; height:64px; border-radius:12px; background:#FFFFFF; border:1.5px solid var(--il-border); box-shadow:0 2px 6px rgba(0,0,0,0.08); display:flex; align-items:center; justify-content:center; padding:4px; overflow:hidden; flex-shrink:0;">
                        <img src="<?= htmlspecialchars($schoolBranding['crest_logo']) ?>" alt="<?= Helper::e($schoolBranding['name'] ?? 'School') ?>" style="width:100%; height:100%; object-fit:contain;" onerror="this.closest('.school-header-crest-badge').style.display='none';">
                    </div>
                <?php endif; ?>
                <div class="dashboard-header-text">
                    <span class="kicker">👋 WELCOME BACK</span>
                    <h1 style="margin-top:2px; font-weight:800; letter-spacing:-0.02em;"><?= Helper::e($teacher['full_name'] ?? 'Teacher') ?></h1>
                    <p class="muted" style="margin-top:4px;">
                        Status:
                        <span class="badge badge-<?= $user['status'] === 'active' ? 'success' : 'primary' ?>">
                            <?= Helper::e($user['status']) ?>
                        </span>
                        <?php if (!empty($schoolBranding['name'])): ?>
                            · <span style="font-weight:600;"><?= Helper::e($schoolBranding['name']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <a href="/profile" class="btn btn-secondary btn-sm">Edit profile</a>
        </div>

        <?php if ($user['status'] !== 'active'): ?>
            <div class="alert alert-warning">
                Your teacher account is awaiting school admin approval.
            </div>
        <?php endif; ?>

        <!-- ============ STAT TILES ============ -->
        <div class="grid grid-4">
            <div class="stat">
                <span class="ico">🏫</span>
                <span class="n"><?= count($batches) ?></span>
                <span class="l">Batches</span>
            </div>
            <div class="stat" style="border-left-color:#2563EB">
                <span class="ico">👩‍🎓</span>
                <span class="n"><?= $studentCount ?></span>
                <span class="l">Students</span>
            </div>
            <div class="stat" style="border-left-color:#7C2D5C">
                <span class="ico">📚</span>
                <span class="n"><?= $lessonsCount ?></span>
                <span class="l">Lessons</span>
            </div>
            <div class="stat" style="border-left-color:#F59E0B">
                <span class="ico">📝</span>
                <span class="n"><?= $lessonsDraft ?></span>
                <span class="l">Drafts</span>
            </div>
        </div>

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

        <!-- ============ QUICK ACTIONS ============ -->
        <h3 class="mt-3 mb-2">Quick actions</h3>
        <div class="grid grid-4">
            <a href="/teacher/batches" class="card card-hover" style="text-decoration:none;color:inherit">
                <h3>🏫 My Batches</h3>
                <p class="muted">Create and manage batches.</p>
                <span class="badge badge-primary mt-2" style="margin-top:.6rem">Open →</span>
            </a>
            <a href="/teacher/batch/new" class="card card-hover" style="text-decoration:none;color:inherit">
                <h3>➕ New Batch</h3>
                <p class="muted">Start a new batch.</p>
                <span class="badge badge-primary mt-2" style="margin-top:.6rem">Create →</span>
            </a>
            <a href="/teacher/lessons" class="card card-hover" style="text-decoration:none;color:inherit">
                <h3>📚 My Lessons</h3>
                <p class="muted"><?= $lessonsCount ?> lesson<?= $lessonsCount === 1 ? '' : 's' ?> uploaded</p>
                <span class="badge badge-primary mt-2" style="margin-top:.6rem">Manage →</span>
            </a>
            <a href="/teacher/counseling" class="card card-hover" style="text-decoration:none;color:inherit">
                <h3>🧭 Counseling</h3>
                <p class="muted">Assigned counseling requests.</p>
                <span class="badge badge-primary mt-2" style="margin-top:.6rem">Open →</span>
            </a>
        </div>

        <div class="grid grid-3 mt-2">
            <a href="/teacher/assign-activity" class="card card-hover" style="text-decoration:none;color:inherit">
                <h3>🎯 Assign Activity</h3>
                <p class="muted">Schedule for a batch.</p>
                <span class="badge badge-primary mt-2" style="margin-top:.6rem">Assign →</span>
            </a>
            <a href="/teacher/assignments" class="card card-hover" style="text-decoration:none;color:inherit">
                <h3>📋 My Assignments</h3>
                <p class="muted">See what you've scheduled.</p>
                <span class="badge badge-primary mt-2" style="margin-top:.6rem">View →</span>
            </a>
            <a href="/teacher/activity_results" class="card card-hover" style="text-decoration:none;color:inherit">
                <h3>🏅 Record Results</h3>
                <p class="muted">Award winners, auto-certify.</p>
                <span class="badge badge-primary mt-2" style="margin-top:.6rem">Record →</span>
            </a>
        </div>

        <!-- ============ BATCHES TABLE ============ -->
        <?php if ($batches): ?>
            <div class="card mt-3">
                <div class="flex between">
                    <h3>🏫 Your Batches</h3>
                    <a href="/teacher/batches" class="btn btn-ghost btn-sm">Manage all</a>
                </div>
                <div class="divider"></div>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Batch</th>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Students</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($batches as $b): ?>
                            <tr>
                                <td><?= (int)$b['id'] ?></td>
                                <td><strong><?= Helper::e($b['name']) ?></strong></td>
                                <td><?= Helper::e($b['subject'] ?: '—') ?></td>
                                <td><?= Helper::e($b['class_level'] ?: '—') ?></td>
                                <td><?= (int)$b['student_count'] ?></td>
                                <td>
                                    <div class="flex">
                                        <a class="btn btn-ghost btn-sm" href="/teacher/batch?id=<?= (int)$b['id'] ?>">Open</a>
                                        <a class="btn btn-primary btn-sm" href="/teacher/batch/attendance?id=<?= (int)$b['id'] ?>">Attendance</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
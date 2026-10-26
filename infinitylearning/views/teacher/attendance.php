<?php
use App\Auth;
use App\Batch;
use App\Attendance;
use App\Helper;
use App\Session;
use App\Database;

Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

$me = Auth::user();
$userId = (int)$me['id'];
$teacherProfile = Auth::findTeacherByUserId($userId);
$isClassTeacher = !empty($teacherProfile['class_teacher_grade']);
$classGrade     = $teacherProfile['class_teacher_grade'] ?? '';
$classSection   = $teacherProfile['class_teacher_section'] ?? '';

$batchId = (int)($_GET['id'] ?? 0);
$date    = $_GET['date'] ?? date('Y-m-d');
$mode    = $_GET['mode'] ?? ($batchId > 0 ? 'batch' : ($isClassTeacher ? 'class' : 'batch'));

$batch = null;
$members = [];
$existing = [];
$title = 'Attendance';
$subtitle = '';
$backUrl = '/teacher/batches';

if ($mode === 'class' && $isClassTeacher) {
    $schoolId = !empty($me['school_id']) ? (int)$me['school_id'] : 0;
    $db = Database::connect();
    
    // Query students in this grade
    $sql = "
        SELECT u.id AS user_id, u.email, s.full_name, s.current_class, s.roll_number, s.registration_number
        FROM users u
        JOIN students s ON s.user_id = u.id
        WHERE u.school_id = ? AND u.role = 'student' AND u.status = 'active'
          AND (s.current_class = ? OR s.current_class LIKE ?)
        ORDER BY CAST(s.roll_number AS UNSIGNED) ASC, s.roll_number ASC, s.full_name ASC
    ";
    $st = $db->prepare($sql);
    $st->execute([$schoolId, $classGrade, "%$classGrade%"]);
    $members = $st->fetchAll();

    // Query existing attendance for date
    if ($members) {
        $memberIds = array_column($members, 'user_id');
        $inClause = implode(',', array_fill(0, count($memberIds), '?'));
        $extSt = $db->prepare("
            SELECT student_user_id, status FROM attendance
            WHERE attendance_date = ? AND student_user_id IN ($inClause)
        ");
        $extSt->execute(array_merge([$date], $memberIds));
        foreach ($extSt->fetchAll() as $row) {
            $existing[(int)$row['student_user_id']] = $row;
        }
    }

    $title = "Class Attendance — $classGrade" . ($classSection ? " ($classSection)" : '');
    $subtitle = "Official Class Teacher Register for $classGrade" . ($classSection ? " - Section $classSection" : '');
    $backUrl = '/teacher/dashboard';
} else {
    $mode = 'batch';
    if ($batchId > 0) {
        $batch = Batch::find($batchId);
    } else {
        // Fallback to teacher's first batch if available
        $myBatches = Batch::all($userId);
        if ($myBatches) {
            $batch = $myBatches[0];
            $batchId = (int)$batch['id'];
        }
    }

    if (!$batch) {
        if ($isClassTeacher) {
            Helper::redirect('/teacher/attendance?mode=class');
        } else {
            Session::flash('error', 'No active batch found to take attendance.');
            Helper::redirect('/teacher/batches');
        }
    }

    if ($role === 'teacher' && (int)$batch['teacher_id'] !== $userId) {
        http_response_code(403); exit('You do not own this batch.');
    }

    $members = Batch::members($batchId);
    $existing = Attendance::forBatchDate($batchId, $date);
    $title = 'Batch Attendance — ' . $batch['name'];
    $subtitle = $batch['name'] . ' · ' . ($batch['subject'] ?: 'General');
    $backUrl = '/teacher/batch?id=' . (int)$batch['id'];
}

$pageTitle = $title . ' — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section" style="max-width:960px; margin:0 auto;">
    <a href="<?= htmlspecialchars($backUrl) ?>" class="muted">← Back</a>

    <!-- Attendance Header Card -->
    <div class="card mt-2" style="border-radius:16px;">
        <div class="flex between" style="align-items:flex-start; flex-wrap:wrap; gap:12px;">
            <div>
                <div class="flex" style="gap:8px; align-items:center;">
                    <h1 style="margin:0; font-size:24px;"><?= Helper::e($title) ?></h1>
                    <?php if ($mode === 'class'): ?>
                        <span class="badge badge-primary">Class Teacher Register</span>
                    <?php else: ?>
                        <span class="badge badge-info">Subject Batch</span>
                    <?php endif; ?>
                </div>
                <p class="muted" style="margin:4px 0 0;"><?= Helper::e($subtitle) ?></p>
            </div>

            <!-- Date Selector Form -->
            <form method="get" action="<?= $mode === 'class' ? '/teacher/attendance' : '/teacher/batch/attendance' ?>" class="flex" style="gap:8px; align-items:center;">
                <?php if ($mode === 'batch'): ?>
                    <input type="hidden" name="id" value="<?= (int)$batchId ?>">
                <?php else: ?>
                    <input type="hidden" name="mode" value="class">
                <?php endif; ?>
                <input type="date" name="date" value="<?= Helper::e($date) ?>"
                       style="padding:.45rem .75rem; border:1.5px solid var(--il-border); border-radius:10px; font-weight:600;">
                <button class="btn btn-secondary btn-sm">Load Date</button>
            </form>
        </div>

        <!-- Mode Switcher if teacher is both a Class Teacher and has Batches -->
        <?php if ($isClassTeacher): ?>
            <div class="divider"></div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="/teacher/attendance?mode=class&date=<?= urlencode($date) ?>"
                   class="btn btn-sm <?= $mode === 'class' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    ⭐ My Class (<?= Helper::e($classGrade) ?>)
                </a>
                <a href="/teacher/batches" class="btn btn-sm <?= $mode === 'batch' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    🏫 My Subject Batches
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$members): ?>
        <div class="card mt-3 text-center" style="padding:40px 20px; border-radius:16px;">
            <div style="font-size:2.5rem; margin-bottom:10px;">📋</div>
            <h3>No students found</h3>
            <p class="muted">
                <?= $mode === 'class' 
                    ? "No registered active students found in $classGrade. Students will appear here once approved by School Administration."
                    : "No students enrolled in this batch yet." ?>
            </p>
        </div>
    <?php else: ?>
        <form method="post" action="/teacher/attendance-save-handler.php" class="card mt-3" style="border-radius:16px; padding:24px;">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <input type="hidden" name="mode" value="<?= Helper::e($mode) ?>">
            <input type="hidden" name="batch_id" value="<?= (int)$batchId ?>">
            <input type="hidden" name="date" value="<?= Helper::e($date) ?>">

            <div class="flex between mb-2" style="align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <h3 style="margin:0;">📅 <?= date('l, d F Y', strtotime($date)) ?></h3>
                    <span class="muted" style="font-size:13px;"><?= count($members) ?> students enrolled</span>
                </div>
                <div class="flex" style="gap:8px;">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="markAll('present')" style="font-weight:700; color:#16A34A;">
                        ✅ Mark All Present
                    </button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="markAll('absent')" style="font-weight:700; color:#DC2626;">
                        ✕ Mark All Absent
                    </button>
                </div>
            </div>
            <div class="divider"></div>

            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <?php if ($mode === 'class'): ?>
                                <th style="width:80px;">Roll No</th>
                            <?php endif; ?>
                            <th>Student</th>
                            <?php if ($mode === 'class'): ?>
                                <th>Reg No</th>
                            <?php endif; ?>
                            <th style="text-align:center; color:#16A34A;">Present</th>
                            <th style="text-align:center; color:#DC2626;">Absent</th>
                            <th style="text-align:center; color:#F59E0B;">Late</th>
                            <th style="text-align:center; color:#2563EB;">Excused</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($members as $i => $m):
                        $sid = (int)$m['user_id'];
                        $current = $existing[$sid]['status'] ?? '';
                    ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <?php if ($mode === 'class'): ?>
                                <td>
                                    <strong><?= Helper::e(!empty($m['roll_number']) ? $m['roll_number'] : '—') ?></strong>
                                </td>
                            <?php endif; ?>
                            <td>
                                <strong><?= Helper::e($m['full_name'] ?: '—') ?></strong>
                                <div class="muted" style="font-size:.8rem"><?= Helper::e($m['email']) ?></div>
                            </td>
                            <?php if ($mode === 'class'): ?>
                                <td>
                                    <span class="muted" style="font-size:12px;"><?= Helper::e($m['registration_number'] ?: '—') ?></span>
                                </td>
                            <?php endif; ?>
                            <?php foreach (['present','absent','late','excused'] as $s): ?>
                                <td style="text-align:center;">
                                    <label style="cursor:pointer; display:inline-block; padding:4px 8px;">
                                        <input type="radio" name="status[<?= $sid ?>]" value="<?= $s ?>"
                                               <?= $current === $s ? 'checked' : '' ?> required
                                               style="cursor:pointer; width:16px; height:16px;">
                                    </label>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex mt-3" style="justify-content:flex-end;">
                <button type="submit" class="btn btn-primary" style="font-weight:700; padding:10px 24px;">
                    💾 Save Attendance Record
                </button>
            </div>
        </form>
    <?php endif; ?>
</section>

<script>
function markAll(status) {
    document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(r => r.checked = true);
}
</script>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
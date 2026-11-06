<?php
use App\Auth;
use App\Helper;
use App\Database;
use App\Batch;
use App\AcademicAssessment;
use App\Attendance;

Auth::requireRole('teacher', 'admin');
$user = Auth::user();
$userId = (int)$user['id'];
$schoolId = Auth::schoolId();

$db = Database::connect();

// Fetch batches taught by this teacher
$myBatches = Batch::all($userId);
$batchIds = array_column($myBatches, 'id');

$selectedBatchId = !empty($_GET['batch_id']) ? (int)$_GET['batch_id'] : 0;
$search = trim($_GET['search'] ?? '');

// Query authorized students: students in this teacher's batches or school
$students = [];
try {
    $whereClauses = ["u.role = 'student'", "u.status = 'active'"];
    $params = [];

    if ($selectedBatchId > 0 && in_array($selectedBatchId, $batchIds, true)) {
        $whereClauses[] = "EXISTS (SELECT 1 FROM batch_students bs WHERE bs.student_user_id = u.id AND bs.batch_id = ?)";
        $params[] = $selectedBatchId;
    } elseif (!empty($batchIds)) {
        $inB = implode(',', array_fill(0, count($batchIds), '?'));
        if ($schoolId) {
            $whereClauses[] = "(u.school_id = ? OR EXISTS (SELECT 1 FROM batch_students bs WHERE bs.student_user_id = u.id AND bs.batch_id IN ($inB)))";
            $params[] = $schoolId;
            $params = array_merge($params, $batchIds);
        } else {
            $whereClauses[] = "EXISTS (SELECT 1 FROM batch_students bs WHERE bs.student_user_id = u.id AND bs.batch_id IN ($inB))";
            $params = array_merge($params, $batchIds);
        }
    } elseif ($schoolId) {
        $whereClauses[] = "u.school_id = ?";
        $params[] = $schoolId;
    }

    if ($search !== '') {
        $whereClauses[] = "(s.full_name LIKE ? OR s.registration_number LIKE ? OR s.roll_number LIKE ? OR u.email LIKE ?)";
        $like = "%$search%";
        $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    }

    $sql = "
        SELECT u.id AS student_user_id, u.email, s.full_name, s.current_class, s.roll_number, s.registration_number, s.profile_image
        FROM users u
        JOIN students s ON s.user_id = u.id
        WHERE " . implode(' AND ', $whereClauses) . "
        ORDER BY s.current_class ASC, CAST(s.roll_number AS UNSIGNED) ASC, s.full_name ASC
        LIMIT 150
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
} catch (\Throwable $e) {
    error_log('Teacher reports student fetch error: ' . $e->getMessage());
}

$pageTitle = 'Student Academic Reports — Teacher Portal';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <main class="admin-main">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <a href="/teacher" class="back-link">← Teacher Hub</a>
                <h1 style="margin:4px 0 0; font-size:24px;">📊 Student Reports &amp; Academic Records</h1>
                <p style="color:var(--il-muted); font-size:14px; margin:4px 0 0;">
                    Access authorized student performance sheets, attendance summaries, and official report cards.
                </p>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <span class="badge" style="background:rgba(16,185,129,0.12); color:#10b981; font-weight:700; padding:6px 12px; border-radius:8px;">
                    🔒 Authorized Faculty Access
                </span>
            </div>
        </div>

        <!-- FILTER & SEARCH BAR -->
        <div class="card mt-3" style="padding:16px 20px; border-radius:14px;">
            <form method="get" action="/teacher/reports" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <div style="flex:1; min-width:220px;">
                    <input type="text" name="search" class="form-control" placeholder="Search student name, Reg ID, or Roll No..." value="<?= Helper::e($search) ?>">
                </div>
                <div style="width:240px;">
                    <select name="batch_id" class="form-control">
                        <option value="">All My Batches &amp; Classes</option>
                        <?php foreach ($myBatches as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" <?= $selectedBatchId === (int)$b['id'] ? 'selected' : '' ?>>
                                🏫 <?= Helper::e($b['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="font-weight:700; padding:8px 20px;">Filter</button>
                <?php if ($search || $selectedBatchId): ?>
                    <a href="/teacher/reports" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- STUDENTS REPORT TABLE -->
        <div class="card mt-3" style="padding:0; overflow:hidden; border-radius:14px;">
            <div style="padding:16px 20px; border-bottom:1px solid var(--il-border); background:var(--surface-0); display:flex; justify-content:space-between; align-items:center;">
                <strong>Enrolled Scholars (<?= count($students) ?>)</strong>
                <span class="muted" style="font-size:12px;">Authorized according to your assigned batches &amp; school</span>
            </div>

            <?php if (empty($students)): ?>
                <div style="text-align:center; padding:48px 20px; color:var(--il-muted);">
                    <div style="font-size:40px; margin-bottom:8px;">👩‍🎓</div>
                    <h4>No student records found</h4>
                    <p style="font-size:13px; margin:4px 0 0;">Ensure students are registered in your school or enrolled in your batches.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Student Details</th>
                                <th>Reg. ID</th>
                                <th>Class &amp; Roll</th>
                                <th style="text-align:center;">Attendance</th>
                                <th style="text-align:center;">Exam Avg</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $idx => $st): 
                                $sid = (int)$st['student_user_id'];
                                $attSummary = Attendance::summaryForStudent($sid);
                                $totalAtt = array_sum($attSummary) ?: 1;
                                $attPct = (int)round(($attSummary['present'] ?? 0) / $totalAtt * 100);

                                $examSummary = ['total_tests' => 0, 'average_pct' => 0];
                                try {
                                    $eh = AcademicAssessment::getStudentExamHistory($sid);
                                    $examSummary = $eh['summary'] ?? $examSummary;
                                } catch (\Throwable $e) {}
                            ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <span class="avatar avatar-initial" data-role="student" style="width:36px; height:36px; font-size:13px; border-radius:50%; background:#2563EB; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700;">
                                                <?= mb_strtoupper(mb_substr($st['full_name'] ?: 'S', 0, 1)) ?>
                                            </span>
                                            <div>
                                                <strong><?= Helper::e($st['full_name'] ?: 'Unnamed Scholar') ?></strong>
                                                <div class="muted" style="font-size:12px;"><?= Helper::e($st['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code style="font-weight:700; color:var(--il-primary); font-size:12.5px;">
                                            <?= Helper::e(!empty($st['registration_number']) ? $st['registration_number'] : ('REG-' . $sid)) ?>
                                        </code>
                                    </td>
                                    <td>
                                        <div><strong>Class <?= Helper::e($st['current_class'] ?: 'N/A') ?></strong></div>
                                        <?php if (!empty($st['roll_number'])): ?>
                                            <span class="muted" style="font-size:12px;">Roll: <?= Helper::e($st['roll_number']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="badge badge-<?= $attPct >= 75 ? 'success' : ($attPct >= 50 ? 'primary' : 'danger') ?>" style="font-weight:700;">
                                            <?= $attPct ?>%
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if ($examSummary['total_tests'] > 0): ?>
                                            <span class="badge" style="background:#e0e7ff; color:#3730a3; font-weight:800;">
                                                <?= $examSummary['average_pct'] ?>% (<?= $examSummary['total_tests'] ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="muted" style="font-size:12px;">No tests yet</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:inline-flex; gap:6px;">
                                            <a href="/student/report-card?student_id=<?= $sid ?>" target="_blank" class="btn btn-primary btn-xs" style="font-weight:700;">
                                                📄 View Report Card
                                            </a>
                                            <a href="/student?id=<?= $sid ?>" class="btn btn-secondary btn-xs">
                                                Profile →
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>

<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth, Batch, Attendance, Helper, Audit};

Session::start();
Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/teacher/batches');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/teacher/batches');
}

$batchId = (int)($_POST['batch_id'] ?? 0);
$mode    = $_POST['mode'] ?? ($batchId > 0 ? 'batch' : 'class');
$date    = $_POST['date'] ?? date('Y-m-d');
$statuses = $_POST['status'] ?? [];

if ($mode === 'class') {
    $me = Auth::user();
    $teacher = Auth::findTeacherByUserId((int)$me['id']);
    if (empty($teacher['class_teacher_grade']) && $role !== 'admin') {
        Session::flash('error', 'You are not assigned as a Class Teacher.');
        Helper::redirect('/teacher/batches');
    }
    if (!is_array($statuses) || !$statuses) {
        Session::flash('error', 'No attendance submitted.');
        Helper::redirect('/teacher/attendance?date=' . urlencode($date));
    }
    $allowed = ['present','absent','late','excused'];
    $by = (int)$me['id'];
    $saved = 0;
    foreach ($statuses as $studentId => $status) {
        $studentId = (int)$studentId;
        if ($studentId <= 0 || !in_array($status, $allowed, true)) continue;
        Attendance::mark(null, $studentId, $date, $status, $by);
        $saved++;
    }
    Audit::log('attendance.class_marked', 'class', 0, ['date'=>$date, 'count'=>$saved, 'grade'=>$teacher['class_teacher_grade'] ?? '']);
    Session::flash('success', "Class attendance saved for $saved student(s) on " . date('d M Y', strtotime($date)) . '.');
    Helper::redirect('/teacher/attendance?date=' . urlencode($date));
}

$batch = Batch::find($batchId);
if (!$batch) { Session::flash('error', 'Batch not found.'); Helper::redirect('/teacher/batches'); }
if ($role === 'teacher' && (int)$batch['teacher_id'] !== (int)Auth::user()['id']) {
    Session::flash('error', 'You do not own this batch.');
    Helper::redirect('/teacher/batches');
}

if (!is_array($statuses) || !$statuses) {
    Session::flash('error', 'No attendance to save.');
    Helper::redirect('/teacher/batch/attendance?id=' . $batchId . '&date=' . urlencode($date));
}

$allowed = ['present','absent','late','excused'];
$by = (int)Auth::user()['id'];
$saved = 0;

foreach ($statuses as $studentId => $status) {
    $studentId = (int)$studentId;
    if ($studentId <= 0 || !in_array($status, $allowed, true)) continue;
    Attendance::mark($batchId, $studentId, $date, $status, $by);
    $saved++;
}

Audit::log('attendance.marked', 'batch', $batchId, ['date'=>$date, 'count'=>$saved]);
Session::flash('success', "Attendance saved for $saved student(s) on " . date('d M Y', strtotime($date)) . '.');
Helper::redirect('/teacher/batch/attendance?id=' . $batchId . '&date=' . urlencode($date));
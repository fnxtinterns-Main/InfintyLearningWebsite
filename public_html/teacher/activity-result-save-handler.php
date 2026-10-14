<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Activity, Batch, Helper};

Session::start();
Auth::requireLogin();
if (!in_array(Auth::role(), ['teacher','admin'], true)) {
    http_response_code(403); exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/teacher/assignments');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/teacher/assignments');
}

$activityId = (int)($_POST['activity_id'] ?? 0);
$batchId    = (int)($_POST['batch_id'] ?? 0);
$results    = $_POST['result'] ?? [];

if (!$activityId || !$batchId || !is_array($results)) {
    Session::flash('error', 'Nothing to save.');
    Helper::redirect('/teacher/activity_results?activity_id=' . $activityId . '&batch_id=' . $batchId);
}

$activity = Activity::find($activityId);
$batch    = Batch::find($batchId);
if (!$activity || !$batch) {
    Session::flash('error', 'Activity or batch not found.');
    Helper::redirect('/teacher/assignments');
}

// Ownership check for teachers
if (Auth::role() === 'teacher' && (int)$batch['teacher_id'] !== (int)Auth::user()['id']) {
    Session::flash('error', 'You do not own this batch.');
    Helper::redirect('/teacher/assignments');
}

$me = Auth::user();
$saved = 0;
$certs = 0;
$skipped = 0;

foreach ($results as $studentId => $row) {
    $studentId = (int)$studentId;
    $type      = $row['type']     ?? '';
    $position  = !empty($row['position']) ? (int)$row['position'] : null;
    $score     = isset($row['score']) && $row['score'] !== '' ? (float)$row['score'] : null;
    $remarks   = trim($row['remarks'] ?? '') ?: null;

    if ($studentId <= 0) { $skipped++; continue; }
    if (!in_array($type, ['winner','runner_up','participant'], true)) { $skipped++; continue; }

    try {
        $result = Activity::recordResult(
            activityId:    $activityId,
            studentUserId: $studentId,
            type:          $type,
            score:         $score,
            position:      $position,
            remarks:       $remarks,
            recordedBy:    (int)$me['id']
        );
        $saved++;
        if (!empty($result['certificate'])) $certs++;
    } catch (\Throwable $e) {
        error_log('Save result failed for student ' . $studentId . ': ' . $e->getMessage());
        $skipped++;
    }
}

Session::flash(
    'success',
    "Saved: $saved student result(s). Generated: $certs certificate(s). Skipped: $skipped."
);
Helper::redirect('/teacher/assignments');
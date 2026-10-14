<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth, Batch, Activity, ActivityAssignment, Helper, Database};

Session::start();
Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/teacher/assignments');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/teacher/assignments');
}

$id = (int)($_POST['id'] ?? 0);
$me = Auth::user();

if ($id) {
    // ---- Edit existing ----
    $existing = ActivityAssignment::find($id);
    if (!$existing) { Session::flash('error','Assignment not found.'); Helper::redirect('/teacher/assignments'); }
    if ($role === 'teacher' && (int)$existing['assigned_by'] !== (int)$me['id']) {
        Session::flash('error','You cannot edit this assignment.');
        Helper::redirect('/teacher/assignments');
    }

    ActivityAssignment::update($id, [
        'scheduled_date'   => $_POST['scheduled_date']   ?? '',
        'scheduled_time'   => $_POST['scheduled_time']   ?? '',
        'duration_minutes' => $_POST['duration_minutes'] ?? '',
        'instructions'     => $_POST['instructions']     ?? '',
        'due_date'         => $_POST['due_date']         ?? '',
        'status'           => $_POST['status']           ?? 'scheduled',
    ]);
    Session::flash('success', 'Assignment updated.');
    Helper::redirect('/teacher/assignments');
}

// ---- Create new ----
$batchId = (int)($_POST['batch_id'] ?? 0);
$activityId = (int)($_POST['activity_id'] ?? 0);

if (!$batchId || !$activityId) {
    Session::flash('error', 'Please select a batch and an activity.');
    Helper::redirect('/teacher/assign-activity');
}

$batch = Batch::find($batchId);
$activity = Activity::find($activityId);

if (!$batch)    { Session::flash('error','Batch not found.');    Helper::redirect('/teacher/assign-activity'); }
if (!$activity) { Session::flash('error','Activity not found.'); Helper::redirect('/teacher/assign-activity'); }

if ($role === 'teacher' && (int)$batch['teacher_id'] !== (int)$me['id']) {
    Session::flash('error', 'You do not own this batch.');
    Helper::redirect('/teacher/assign-activity');
}

$newId = ActivityAssignment::assign([
    'batch_id'         => $batchId,
    'activity_id'      => $activityId,
    'scheduled_date'   => $_POST['scheduled_date']   ?? '',
    'scheduled_time'   => $_POST['scheduled_time']   ?? '',
    'duration_minutes' => $_POST['duration_minutes'] ?? '',
    'instructions'     => $_POST['instructions']     ?? '',
    'due_date'         => $_POST['due_date']         ?? '',
    'status'           => 'scheduled',
], (int)$me['id']);

Session::flash('success', 'Activity assigned to ' . $batch['name'] . '. Students will see it on their dashboard.');
Helper::redirect('/teacher/assignments');
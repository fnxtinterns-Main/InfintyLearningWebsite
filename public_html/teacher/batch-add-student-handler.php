<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth, Batch, Helper};

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
$studentId = (int)($_POST['student_user_id'] ?? 0);

$batch = Batch::find($batchId);
if (!$batch) { Session::flash('error', 'Batch not found.'); Helper::redirect('/teacher/batches'); }

if ($role === 'teacher' && (int)$batch['teacher_id'] !== (int)Auth::user()['id']) {
    Session::flash('error', 'You do not own this batch.');
    Helper::redirect('/teacher/batches');
}

if (Batch::addStudent($batchId, $studentId)) {
    Session::flash('success', 'Student added to batch.');
} else {
    Session::flash('error', 'Student already in batch (or error).');
}
Helper::redirect('/teacher/batch?id=' . $batchId . '&q=' . urlencode($_POST['q'] ?? ''));
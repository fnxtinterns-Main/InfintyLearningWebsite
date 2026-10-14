<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Lesson, Helper};

Session::start();
Auth::requireLogin();
if (Auth::role() !== 'teacher') { http_response_code(403); exit('Forbidden'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/teacher/lessons');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/teacher/lessons');
}

$id = (int)($_POST['id'] ?? 0);
$me = Auth::user();

if ($id <= 0) { Session::flash('error', 'Invalid lesson.'); Helper::redirect('/teacher/lessons'); }

$lesson = Lesson::find($id);
if (!$lesson) { Session::flash('error', 'Lesson not found.'); Helper::redirect('/teacher/lessons'); }

if ((int)$lesson['created_by'] !== (int)$me['id']) {
    Session::flash('error', 'You can only delete your own lessons.');
    Helper::redirect('/teacher/lessons');
}

// Delete the actual files first (best-effort)
$attachments = $lesson['attachments_array'] ?? [];
foreach ($attachments as $a) {
    if (!empty($a['path'])) {
        $fs = PUBLIC_PATH . $a['path'];
        if (is_file($fs)) @unlink($fs);
    }
}
if (!empty($lesson['attachment'])) {
    $fs = PUBLIC_PATH . $lesson['attachment'];
    if (is_file($fs)) @unlink($fs);
}

// Then delete the DB row
Lesson::delete($id);

Session::flash('success', 'Lesson deleted.');
Helper::redirect('/teacher/lessons');
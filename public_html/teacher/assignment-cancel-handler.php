<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth, ActivityAssignment, Helper};

Session::start();
Auth::requireLogin();
if (!in_array(Auth::role(), ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/teacher/assignments');
}

$id = (int)($_POST['id'] ?? 0);
$me = Auth::user();
$a = ActivityAssignment::find($id);
if (!$a) { Session::flash('error','Assignment not found.'); Helper::redirect('/teacher/assignments'); }
if (Auth::role() === 'teacher' && (int)$a['assigned_by'] !== (int)$me['id']) {
    Session::flash('error','You cannot modify this assignment.');
    Helper::redirect('/teacher/assignments');
}

ActivityAssignment::cancel($id);
Session::flash('success', 'Assignment cancelled.');
Helper::redirect('/teacher/assignments');
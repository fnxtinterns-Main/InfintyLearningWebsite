<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, CurriculumCloner, Helper};

Session::start();
Auth::requireLogin();
if (!in_array(Auth::role(), ['teacher', 'admin'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/teacher/lessons');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid token.');
    Helper::redirect('/teacher/lessons');
}

$user = Auth::user();
$userId = (int)$user['id'];
$packId = $_POST['pack_id'] ?? '';
$batchId = (int)($_POST['batch_id'] ?? 0);

if (!$packId || $batchId <= 0) {
    Session::flash('error', 'Please choose a curriculum pack and a target batch.');
    Helper::redirect(Auth::role() === 'teacher' ? '/teacher/lessons' : '/school/lessons');
}

try {
    $res = CurriculumCloner::cloneToBatch($packId, $batchId, $userId);
    Session::flash('success', "Cloned {$res['cloned_count']} lessons and quizzes from {$res['pack_title']} into your batch!");
} catch (\Throwable $e) {
    error_log('Curriculum clone error: ' . $e->getMessage());
    Session::flash('error', 'Cloning failed: ' . $e->getMessage());
}

Helper::redirect(Auth::role() === 'teacher' ? '/teacher/lessons' : '/school/lessons');

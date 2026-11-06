<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Lesson, Helper};

header('Content-Type: application/json');

Session::start();
if (!Auth::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$lessonId = (int)($input['lesson_id'] ?? 0);
$userId = (int)Auth::id();

if ($lessonId <= 0 || $userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid lesson parameters']);
    exit;
}

$percent = isset($input['progress_percent']) ? (int)$input['progress_percent'] : 50;
$status = !empty($input['status']) ? trim($input['status']) : null;
$score = isset($input['score']) ? (int)$input['score'] : null;

try {
    $progress = Lesson::updateProgressRate($lessonId, $userId, $percent, $status, $score);
    echo json_encode(['success' => true, 'progress' => $progress]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

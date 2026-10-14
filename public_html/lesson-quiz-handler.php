<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, LessonQuiz, Helper};

Session::start();
header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in to submit the quiz.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$csrf = $data['csrf'] ?? null;
if (!Session::verifyCsrf($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid security token. Please refresh.']);
    exit;
}

$lessonId = (int)($data['lesson_id'] ?? 0);
$answers = $data['answers'] ?? [];

if ($lessonId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid lesson id.']);
    exit;
}

$user = Auth::user();
$studentId = (int)$user['id'];

try {
    $result = LessonQuiz::submitAttempt($lessonId, $studentId, $answers);
    echo json_encode(array_merge(['success' => true], $result));
} catch (\Throwable $e) {
    error_log('Lesson quiz submission error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

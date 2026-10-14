<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

if (!defined('APP_ROOT')) {
    define('APP_ROOT', $appRoot);
}
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, QuestionBank, Helper};

Session::start();
Auth::requireRole('teacher', 'admin');

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
        exit;
    }
    Helper::redirect('/teacher/questions');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    if ($isAjax) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token. Please refresh the page.']);
        exit;
    }
    Session::flash('error', 'Invalid request token. Please refresh the page.');
    Helper::redirect('/teacher/questions');
}

$user = Auth::user();
$isAdmin = in_array($user['role'] ?? '', ['admin'], true);
$teacherId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

// Allow admin to upload questions on behalf of teachers
if ($isAdmin && !empty($_POST['on_behalf_teacher_id'])) {
    $onBehalfId = (int)$_POST['on_behalf_teacher_id'];
    if ($onBehalfId > 0) {
        $teacherId = $onBehalfId;
    }
}

// Handle image upload if a file was submitted
if (!empty($_FILES['question_image']['tmp_name'])) {
    try {
        $img = \App\Uploader::image($_FILES['question_image'], 'questions');
        if ($img) {
            $_POST['image_url'] = $img;
        }
    } catch (\Throwable $e) {
        error_log("Question image upload error: " . $e->getMessage());
    }
}

$action = $_POST['action'] ?? 'create';

try {
    if ($action === 'create') {
        $newId = QuestionBank::create($_POST, $teacherId, $schoolId);
        if ($isAjax) {
            echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Question successfully saved to Question Bank.']);
            exit;
        }
        Session::flash('success', 'Question successfully saved to Question Bank.');
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            throw new \InvalidArgumentException('Invalid question ID provided.');
        }
        QuestionBank::update($id, $_POST, $teacherId);
        if ($isAjax) {
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Question updated successfully.']);
            exit;
        }
        Session::flash('success', 'Question updated successfully.');
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            throw new \InvalidArgumentException('Invalid question ID provided.');
        }
        $deleted = QuestionBank::delete($id, $teacherId);
        if ($isAjax) {
            echo json_encode(['success' => true, 'deleted' => $deleted, 'message' => 'Question deleted from bank.']);
            exit;
        }
        Session::flash('success', 'Question deleted from bank.');
    }
} catch (\Throwable $e) {
    error_log('Question save/delete error: ' . $e->getMessage());
    if ($isAjax) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        exit;
    }
    Session::flash('error', 'Error: ' . $e->getMessage());
}

Helper::redirect('/teacher/questions');

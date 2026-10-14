<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, DoubtFeed, Uploader, Helper};

Session::start();
Auth::requireLogin();

$isAjax = (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || isset($_POST['ajax'])
    || (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'empty');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
    Helper::redirect('/lessons');
}

$user = Auth::user();
$userId = (int)$user['id'];
$role = Auth::role();
$action = $_POST['action'] ?? 'ask';
$lessonId = (int)($_POST['lesson_id'] ?? 0);

try {
    if ($action === 'ask') {
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        if ($title === '' || $body === '') {
            if ($isAjax) {
                echo json_encode(['success' => false, 'error' => 'Please provide a title and explanation for your doubt.']);
                exit;
            }
            Session::flash('error', 'Please provide a title and explanation for your doubt.');
            Helper::redirect("/lesson?id={$lessonId}");
        }

        $attachUrl = null;
        if (!empty($_FILES['attachment']['tmp_name'])) {
            $attachUrl = Uploader::image($_FILES['attachment'], 'doubts');
        }

        $schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : (Auth::schoolId() ?: null);
        $batchId = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;

        $newId = DoubtFeed::askDoubt($lessonId, $userId, $title, $body, $attachUrl, $schoolId, $batchId);
        
        if ($isAjax) {
            echo json_encode(['success' => true, 'doubt_id' => $newId]);
            exit;
        }
        Session::flash('success', 'Your doubt has been posted! Teachers and peers will answer soon.');
    } elseif ($action === 'reply') {
        $doubtId = (int)($_POST['doubt_id'] ?? 0);
        $replyText = trim($_POST['reply_text'] ?? '');
        if ($replyText === '') {
            if ($isAjax) {
                echo json_encode(['success' => false, 'error' => 'Reply cannot be blank.']);
                exit;
            }
        } else {
            $repId = DoubtFeed::addReply($doubtId, $userId, $role, $replyText);
            if ($isAjax) {
                echo json_encode(['success' => true, 'reply_id' => $repId]);
                exit;
            }
            Session::flash('success', 'Reply posted successfully.');
        }
    }
} catch (\Throwable $e) {
    error_log('Doubt handler error: ' . $e->getMessage());
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
    Session::flash('error', 'Error: ' . $e->getMessage());
}

$redirect = !empty($_POST['redirect']) ? $_POST['redirect'] : "/lesson?id={$lessonId}";
Helper::redirect($redirect);

<?php
declare(strict_types=1);

define('APP_ROOT', __DIR__ . '/../infinitylearning');
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\Session;
use App\Auth;
use App\Helper;
use App\Chess\ChessLive;

Session::start();
Auth::requireLogin();

$user = Auth::user();
$userId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : 0;
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'create_session':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Session::verifyCsrf($_POST['csrf'] ?? null)) {
                Session::flash('error', 'Invalid request.');
                Helper::redirect('/chess/live');
            }

            $title = trim($_POST['title'] ?? 'Live Chess Session');
            $type = $_POST['session_type'] ?? '1-on-1';
            $studentId = !empty($_POST['student_user_id']) ? (int)$_POST['student_user_id'] : null;

            $session = ChessLive::createSession(
                $userId,
                $schoolId,
                $title,
                $type,
                $studentId
            );

            Session::flash('success', 'Live session created successfully!');
            Helper::redirect('/chess/live?session_id=' . $session['id']);
            break;

        case 'sync_move':
            header('Content-Type: application/json');
            $sessionId = (int)($_POST['session_id'] ?? 0);
            $fen = trim($_POST['fen'] ?? '');
            $pgn = trim($_POST['pgn'] ?? '');

            if ($sessionId > 0 && $fen) {
                ChessLive::syncMoveAndState($sessionId, $fen, $pgn);
                Helper::json(['success' => true]);
            }
            Helper::json(['success' => false, 'error' => 'Invalid params'], 400);
            break;

        case 'get_state':
            header('Content-Type: application/json');
            $sessionId = (int)($_GET['session_id'] ?? 0);
            $session = ChessLive::getSession($sessionId);
            Helper::json(['success' => true, 'session' => $session]);
            break;

        case 'end_session':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Session::verifyCsrf($_POST['csrf'] ?? null)) {
                Session::flash('error', 'Invalid request.');
                Helper::redirect('/chess/live');
            }
            $sessionId = (int)($_POST['session_id'] ?? 0);
            ChessLive::endSession($sessionId);
            Session::flash('success', 'Live session ended.');
            Helper::redirect('/chess/live');
            break;

        default:
            Helper::redirect('/chess/live');
    }
} catch (\Throwable $e) {
    if (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json') {
        Helper::json(['success' => false, 'error' => $e->getMessage()], 500);
    } else {
        Session::flash('error', $e->getMessage());
        Helper::redirect('/chess/live');
    }
}

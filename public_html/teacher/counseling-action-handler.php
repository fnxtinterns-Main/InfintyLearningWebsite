<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';
define('APP_ROOT', $appRoot);

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Counseling, Notification, Helper, Database};

Session::start();
Auth::requireLogin();
if (Auth::role() !== 'teacher') {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/teacher/counseling');
}
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/teacher/counseling');
}

$me = Auth::user();
$requestId = (int)($_POST['request_id'] ?? 0);
$action    = $_POST['action'] ?? '';

if (!$requestId || !in_array($action, ['start','complete','cancel'], true)) {
    Session::flash('error', 'Invalid action.');
    Helper::redirect('/teacher/counseling');
}

$request = Counseling::find($requestId);
if (!$request) {
    Session::flash('error', 'Request not found.');
    Helper::redirect('/teacher/counseling');
}

// Ownership — teacher must be the assigned one
if ((int)$request['assigned_teacher_id'] !== (int)$me['id']) {
    Session::flash('error', 'This request is not assigned to you.');
    Helper::redirect('/teacher/counseling');
}

try {
    switch ($action) {

        case 'start':
            Counseling::markInProgress($requestId, (int)$me['id']);
            Session::flash('success', 'Marked as in progress.');
            break;

        case 'complete':
            $notes = trim($_POST['notes'] ?? '');
            if ($notes === '') {
                Session::flash('error', 'Please add notes before completing.');
                Helper::redirect('/teacher/counseling-detail?id=' . $requestId);
            }
            Counseling::complete($requestId, $notes, (int)$me['id']);

            // Notify student
            Notification::push(
                (int)$request['student_user_id'],
                'counseling.completed',
                '✅ Your counseling session is complete',
                'Your counselor has shared notes from your session.',
                '/counseling'
            );

            // Notify school admin
            if (!empty($request['school_id'])) {
                $db = Database::connect();
                $stmt = $db->prepare("SELECT admin_user_id FROM schools WHERE id=? LIMIT 1");
                $stmt->execute([$request['school_id']]);
                $adminId = (int)($stmt->fetchColumn() ?: 0);
                if ($adminId) {
                    Notification::push(
                        $adminId,
                        'counseling.completed',
                        'Counseling session completed',
                        $request['full_name'] . ' — ' . Counseling::typeLabel($request['counseling_type']) . ' session finished.',
                        '/school/counseling-detail?id=' . $requestId
                    );
                }
            }

            Session::flash('success', 'Session marked as completed. Notes shared.');
            break;

        case 'cancel':
            Counseling::cancel($requestId, (int)$me['id']);

            if (!empty($request['school_id'])) {
                $db = Database::connect();
                $stmt = $db->prepare("SELECT admin_user_id FROM schools WHERE id=? LIMIT 1");
                $stmt->execute([$request['school_id']]);
                $adminId = (int)($stmt->fetchColumn() ?: 0);
                if ($adminId) {
                    Notification::push(
                        $adminId,
                        'counseling.cancelled',
                        'Counseling request cancelled',
                        'A counseling request was cancelled by the teacher.',
                        '/school/counseling'
                    );
                }
            }

            Session::flash('warning', 'Request cancelled.');
            break;
    }
} catch (\Throwable $e) {
    error_log('Teacher counseling action failed: ' . $e->getMessage());
    Session::flash('error', 'Action failed: ' . $e->getMessage());
}

Helper::redirect('/teacher/counseling');
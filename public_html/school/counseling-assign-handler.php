<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';
define('APP_ROOT', $appRoot);

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Counseling, Notification, Mailer, Helper, Database};

Session::start();
$schoolId = Auth::requireSchoolAdmin();
$me = Auth::user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/school/counseling');
}
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/school/counseling');
}

$requestId = (int)($_POST['request_id'] ?? 0);
$teacherUserId = (int)($_POST['teacher_user_id'] ?? 0);

if (!$requestId || !$teacherUserId) {
    Session::flash('error', 'Please choose a teacher.');
    Helper::redirect('/school/counseling?id=' . $requestId);
}

$request = Counseling::find($requestId);
if (!$request) {
    Session::flash('error', 'Request not found.');
    Helper::redirect('/school/counseling');
}
if ((int)$request['school_id'] !== (int)$schoolId) {
    Session::flash('error', 'This request is not from your school.');
    Helper::redirect('/school/counseling');
}

// Verify teacher belongs to this school
$db = Database::connect();
$check = $db->prepare("SELECT id, email FROM users WHERE id=? AND school_id=? AND role='teacher' AND status='active' LIMIT 1");
$check->execute([$teacherUserId, $schoolId]);
$teacher = $check->fetch();
if (!$teacher) {
    Session::flash('error', 'Selected teacher is not an active teacher in your school.');
    Helper::redirect('/school/counseling?id=' . $requestId);
}

// Assign
try {
    Counseling::assignTeacher($requestId, $teacherUserId, (int)$me['id']);

    // Notify the teacher
    Notification::push(
        $teacherUserId,
        'counseling.assigned',
        '🧭 New counseling request assigned to you',
        'A ' . Counseling::typeLabel($request['counseling_type']) . ' counseling request has been assigned to you.',
        '/teacher/counseling-detail?id=' . $requestId
    );

    // Optional: send email to teacher
    try {
        Mailer::send(
            $teacher['email'],
            'New counseling request assigned',
            '<p>Hi,</p>'
            . '<p>A new <strong>' . htmlspecialchars(Counseling::typeLabel($request['counseling_type'])) . '</strong> counseling request from '
            . '<strong>' . htmlspecialchars($request['full_name']) . '</strong> has been assigned to you.</p>'
            . '<p><a href="https://infintylearning.net/teacher/counseling?id=' . $requestId . '">Open the request →</a></p>'
        );
    } catch (\Throwable $e) {
        error_log('Teacher counseling email failed: ' . $e->getMessage());
    }

    Session::flash('success', 'Teacher assigned. They will be notified and can start the session.');
} catch (\Throwable $e) {
    error_log('Assign counseling failed: ' . $e->getMessage());
    Session::flash('error', 'Could not assign teacher: ' . $e->getMessage());
}

Helper::redirect('/school/counseling?id=' . $requestId);
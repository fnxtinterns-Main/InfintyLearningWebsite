<?php
declare(strict_types=1);

use App\Auth;
use App\Aiverse;
use App\Helper;
use App\Session;

Session::start();
Auth::requireSchoolAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/school/courses');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid security token.');
    Helper::redirect('/school/courses');
}

$studentUserId = (int)($_POST['student_user_id'] ?? 0);
$courseId = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;
$feeId = !empty($_POST['fee_id']) ? (int)$_POST['fee_id'] : null;

if (!$studentUserId && !$feeId) {
    Session::flash('error', 'Missing scholar or fee identifier.');
    Helper::redirect('/school/courses');
}

try {
    $courseTitle = 'Course';
    if ($courseId) {
        $course = Aiverse::getCourse($courseId);
        $courseTitle = $course['title'] ?? 'Course';
    }
    
    $success = Aiverse::unenrollStudent($studentUserId, $courseId, $feeId);
    if ($success) {
        Session::flash('success', 'Scholar successfully removed from ' . $courseTitle . ' and their course fee record was updated.');
    } else {
        Session::flash('error', 'Scholar was not enrolled or fee was already cleared.');
    }
} catch (\Throwable $e) {
    error_log('Admin unenroll error: ' . $e->getMessage());
    Session::flash('error', 'Failed to remove scholar: ' . $e->getMessage());
}

$redirect = $_POST['redirect_to'] ?? '/school/courses';
Helper::redirect($redirect);

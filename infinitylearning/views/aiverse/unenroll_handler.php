<?php
declare(strict_types=1);

use App\Auth;
use App\Aiverse;
use App\Helper;
use App\Session;

Session::start();
Auth::requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/dashboard');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Security token expired. Please try again.');
    Helper::redirect('/dashboard');
}

$user = Auth::user();
$studentId = (int)$user['id'];
$courseId = (int)($_POST['course_id'] ?? 0);

if (!$courseId) {
    Session::flash('error', 'Invalid course selected.');
    Helper::redirect('/dashboard');
}

try {
    $course = Aiverse::getCourse($courseId);
    $courseTitle = $course['title'] ?? 'AI Course';
    
    $success = Aiverse::unenrollStudent($studentId, $courseId);
    if ($success) {
        Session::flash('info', 'You have been successfully unenrolled from "' . $courseTitle . '". Your course account has been updated.');
    } else {
        Session::flash('error', 'You were not enrolled in this course.');
    }
} catch (\Throwable $e) {
    error_log('Student unenroll error: ' . $e->getMessage());
    Session::flash('error', 'Failed to unenroll: ' . $e->getMessage());
}

$redirect = $_POST['redirect_to'] ?? '/dashboard';
Helper::redirect($redirect);

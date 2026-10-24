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
    if (!$course) {
        Session::flash('error', 'Selected course does not exist.');
        Helper::redirect('/dashboard');
    }

    if (Aiverse::isStudentEnrolled($studentId, $courseId)) {
        Session::flash('info', 'You are already enrolled in "' . $course['title'] . '".');
        Helper::redirect('/dashboard');
    }

    $enrollment = Aiverse::enrollStudent($studentId, $courseId);
    if (!empty($enrollment['bogo_granted'])) {
        $freeTitle = $enrollment['bogo_granted']['course_title'] ?? 'Bonus Companion Course';
        Session::flash('success', '🎉 Congratulations! You have enrolled in "' . $course['title'] . '". 🎁 BOGO BONUS: You also received "' . $freeTitle . '" 100% FREE!');
    } else {
        Session::flash('success', '🎉 Congratulations! You have successfully enrolled in "' . $course['title'] . '". Your course account has been initialized.');
    }
} catch (\Throwable $e) {
    error_log('Aiverse enrollment error: ' . $e->getMessage());
    Session::flash('error', 'Unable to complete enrollment: ' . $e->getMessage());
}

Helper::redirect('/dashboard');

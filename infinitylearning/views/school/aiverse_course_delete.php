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

$courseId = (int)($_POST['course_id'] ?? 0);

if ($courseId > 0) {
    try {
        Aiverse::deleteCourse($courseId);
        Session::flash('success', 'Course deleted successfully.');
    } catch (\Throwable $e) {
        error_log('Aiverse course delete error: ' . $e->getMessage());
        Session::flash('error', 'Failed to delete course: ' . $e->getMessage());
    }
}

Helper::redirect('/school/courses');

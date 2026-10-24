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

$courseId = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;

try {
    $id = Aiverse::saveCourse($_POST, $courseId);
    Session::flash('success', $courseId ? 'AI Course updated successfully.' : 'New AI Course published successfully!');
} catch (\Throwable $e) {
    error_log('Aiverse course save error: ' . $e->getMessage());
    Session::flash('error', 'Failed to save course: ' . $e->getMessage());
}

Helper::redirect('/school/courses');

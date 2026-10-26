<?php
declare(strict_types=1);

// ---- Path to app folder (one level up from public_html) ----
define('APP_ROOT', __DIR__ . '/../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

// ---- Error display: OFF in production ----
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

use App\Session;
use App\Helper;

// ---- Set timezone from config ----
$config = require APP_ROOT . '/config/config.php';
date_default_timezone_set($config['app']['timezone']);

Session::start();

// ---- Parse URI safely (fixes the rtrim() null error) ----
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($requestUri, PHP_URL_PATH) ?: '/';
$uri = rtrim($uri, '/') ?: '/';

$routes = [
       // Public
    '/'           => '/views/home.php',
    '/about'      => '/views/about.php',
    '/contact'    => '/views/contact.php',
    '/privacy'    => '/views/privacy.php',
    '/terms'      => '/views/terms.php',
    '/activities' => '/views/activities/index.php',
    '/activity'   => '/views/activities/show.php',
    '/lessons'    => '/views/lessons/index.php',
    '/lesson'     => '/views/lessons/show.php',
    '/counseling'         => '/views/counseling/landing.php',
    '/counseling/apply'   => '/views/counseling/apply.php',

    // Auth
    '/register'   => '/views/auth/register.php',
    '/verify-otp' => '/views/auth/verify_otp.php',
    '/login'      => '/views/auth/login.php',
    '/logout'     => '/views/auth/logout_redirect.php',
    '/forgot'     => '/views/auth/forgot_password.php',
    '/reset'      => '/views/auth/reset_password.php',

    // Logged in
    '/dashboard'        => '/views/dashboard_router.php',
    '/profile'          => '/views/profile/edit.php',
    '/student'          => '/views/student/profile_public.php',
    '/pending-approval' => '/views/pending_approval.php',
    '/my-certificates'  => '/views/student/my_certificates.php',
    '/my-counseling'      => '/views/student/my_counseling.php',

    // Chess Academy (Rising Rooks)
    '/chess'                        => '/views/chess/hub.php',
    '/chess/play'                   => '/views/chess/play.php',
    '/chess/bots'                   => '/views/chess/bots.php',
    '/chess/history'                => '/views/chess/history.php',
    '/chess/puzzles'                => '/views/chess/puzzles.php',
    '/chess/study'                  => '/views/chess/study.php',
    '/chess/live'                   => '/views/chess/live.php',

    // School admin panel
    '/school'                       => '/views/school/dashboard.php',
    '/school/chess'                 => '/views/school/chess.php',
    '/school/teachers'              => '/views/school/teachers.php',
    '/school/students'              => '/views/school/students.php',
    '/school/batches'               => '/views/school/batches.php',
    '/school/activities'            => '/views/school/activities.php',
    '/school/activities/new'        => '/views/admin/activity_form.php',
    '/school/lessons'               => '/views/school/lessons.php',
    '/school/lessons/new'           => '/views/admin/lesson_form.php',
    '/school/announcements'         => '/views/school/announcements.php',
    '/school/certificates'          => '/views/admin/certificates.php',
    '/school/counseling'         => '/views/school/counseling.php',
    '/school/counseling-detail'  => '/views/school/counseling_detail.php',
    '/school/fees'               => '/views/school/fees.php',
    '/school/courses'            => '/views/school/aiverse_courses.php',
    '/school/courses/save'       => '/views/school/aiverse_course_save.php',
    '/school/courses/delete'     => '/views/school/aiverse_course_delete.php',
    '/school/courses/unenroll'   => '/views/school/aiverse_course_unenroll.php',
    '/school/bundles'            => '/views/school/aiverse_bundles.php',
    '/school/bundles/save'       => '/views/school/aiverse_bundle_save.php',
    '/school/bundles/delete'     => '/views/school/aiverse_bundle_delete.php',
    '/school/banners'            => '/views/school/aiverse_banners.php',
    '/school/banners/save'       => '/views/school/aiverse_banner_save.php',
    '/school/banners/delete'     => '/views/school/aiverse_banner_delete.php',
    '/school/banners/toggle'     => '/views/school/aiverse_banner_toggle.php',
    '/school/timetable'          => '/views/school/timetable.php',
    '/school/branding'           => '/views/school/branding.php',
    '/school/results'            => '/views/school/results.php',

    // AIverse
    '/aiverse/enroll'            => '/views/aiverse/enroll_handler.php',
    '/aiverse/enroll-bundle'     => '/views/aiverse/enroll_bundle_handler.php',
    '/aiverse/unenroll'          => '/views/aiverse/unenroll_handler.php',

    // Teacher
    '/teacher'                      => '/views/teacher/dashboard.php',
    '/teacher/dashboard'            => '/views/teacher/dashboard.php',
    '/teacher/chess'                => '/views/teacher/chess.php',
    '/teacher/batches'              => '/views/teacher/batches.php',
    '/teacher/batch'                => '/views/teacher/batch_view.php',
    '/teacher/batch/new'            => '/views/teacher/batch_form.php',
    '/teacher/batch/edit'           => '/views/teacher/batch_form.php',
    '/teacher/attendance'           => '/views/teacher/attendance.php',
    '/teacher/batch/attendance'     => '/views/teacher/attendance.php',
    '/teacher/batch/import'         => '/views/teacher/import_students.php',
    '/teacher/questions'            => '/views/teacher/question_bank.php',
    '/teacher/assessments'          => '/views/teacher/assessments.php',
    '/teacher/marks'                => '/views/teacher/marks_entry.php',
    '/teacher/assessment/new'       => '/views/teacher/assessment_form.php',
    '/teacher/assessment/submissions' => '/views/teacher/assessment_submissions.php',
    '/teacher/fees'                 => '/views/teacher/fees.php',
    '/teacher/timetable'            => '/views/teacher/timetable.php',
    '/teacher/doubts'               => '/views/teacher/doubts.php',
    '/teacher/assignments'          => '/views/teacher/assignments.php',
    '/teacher/assign-activity'      => '/views/teacher/assign_activity.php',
    '/teacher/assign-activity/edit' => '/views/teacher/assign_activity.php',
    '/teacher/analytics'            => '/views/teacher/analytics.php',
    '/teacher/activity_results'     => '/views/teacher/activity_results.php',
    '/teacher/lessons'              => '/views/teacher/lessons.php',
    '/teacher/lessons/new'          => '/views/admin/lesson_form.php',
    '/teacher/lessons/edit'         => '/views/admin/lesson_form.php',
    '/teacher/counseling'         => '/views/teacher/counseling.php',
    '/teacher/counseling-detail'  => '/views/teacher/counseling_detail.php',

    // Student & Live Extras
    '/student/dashboard'            => '/views/student/dashboard.php',
    '/student/assessments'          => '/views/student/assessments.php',
    '/student/assessment/take'      => '/views/student/assessment_take.php',
    '/student/fees'                 => '/views/student/fees.php',
    '/student/timetable'            => '/views/student/timetable.php',
    '/student/doubts'               => '/views/student/doubts.php',
    '/student/doubt-portal'         => '/views/student/doubts.php',
    '/student/report-card'          => '/views/school/report_card.php',
    '/student/fee-receipt'          => '/views/school/fee_receipt.php',
    '/student/parent-pass'          => '/views/student/parent_pass.php',
    '/parent-pass'                  => '/views/student/parent_pass.php',
    '/school/fee-receipt'           => '/views/school/fee_receipt.php',
    '/school/report-card'           => '/views/school/report_card.php',
    '/teacher/question-paper'       => '/views/teacher/question_paper.php',
    '/school/question-paper'        => '/views/teacher/question_paper.php',
    '/whiteboard'                   => '/views/whiteboard.php',
    '/live/whiteboard'              => '/views/whiteboard.php',

    // Super admin
    '/admin'                        => '/views/admin/dashboard.php',
    '/admin/chess'                  => '/views/admin/chess.php',
    '/admin/analytics'              => '/views/admin/analytics.php',
    '/admin/schools'                => '/views/admin/schools.php',
    '/admin/teachers'               => '/views/admin/teachers.php',
    '/admin/teacher-view'           => '/views/admin/teacher_view.php',
    '/admin/users'                  => '/views/admin/users.php',
    '/admin/student-view'           => '/views/admin/student_view.php',
    '/admin/batches'                => '/views/admin/batches.php',
    '/admin/activities'             => '/views/admin/activities.php',
    '/admin/activities/new'         => '/views/admin/activity_form.php',
    '/admin/activities/edit'        => '/views/admin/activity_form.php',
    '/admin/announcements'          => '/views/admin/announcements.php',
    '/admin/lessons'                => '/views/admin/lessons.php',
    '/admin/lessons/new'            => '/views/admin/lesson_form.php',
    '/admin/certificates'           => '/views/admin/certificates.php',
    '/admin/questions'              => '/views/teacher/question_bank.php',
    '/school/questions'             => '/views/teacher/question_bank.php',
    '/admin/question-paper'         => '/views/teacher/question_paper.php',
];

// ---- Serve real files (assets, handler scripts) directly ----
$directFile = __DIR__ . $uri;
if ($uri !== '/' && is_file($directFile)) {
    return false;
}

// ---- Route to view file ----
if (isset($routes[$uri]) && file_exists(APP_ROOT . $routes[$uri])) {
    require APP_ROOT . $routes[$uri];
} else {
    http_response_code(404);
    if (file_exists(APP_ROOT . '/views/404.php')) {
        require APP_ROOT . '/views/404.php';
    } else {
        echo '404 Not Found';
    }
}
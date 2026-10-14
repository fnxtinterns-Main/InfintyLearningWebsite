<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Helper, School};

Session::start();
$schoolId = Auth::requireSchoolAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/school');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid token. Please refresh and try again.');
    Helper::redirect('/school');
}

$school = School::find($schoolId);
if (!$school || empty($school['allow_weekly_summary'])) {
    Session::flash('error', 'Weekly Parent Summary is not enabled for your school by the Super Admin.');
    Helper::redirect('/school');
}

$enabled = isset($_POST['weekly_summary_enabled']) && (string)$_POST['weekly_summary_enabled'] === '1';

School::setWeeklySummaryToggle($schoolId, $enabled);

if ($enabled) {
    Session::flash('success', 'Weekly parent summaries enabled for your school.');
} else {
    Session::flash('warning', 'Weekly parent summaries paused for your school.');
}

Helper::redirect('/school');

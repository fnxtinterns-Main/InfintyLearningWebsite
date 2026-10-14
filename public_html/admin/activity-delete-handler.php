<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Activity, Helper};

Session::start();
Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/admin/activities');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/admin/activities');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    Session::flash('error', 'Invalid activity.');
    Helper::redirect('/admin/activities');
}

try {
    $activity = Activity::find($id);
    if (!$activity) {
        Session::flash('error', 'Activity not found.');
        Helper::redirect('/admin/activities');
    }

    // School admin can only delete their own school's activities
    if (Auth::isSchoolAdmin() && !empty($activity['school_id']) && (int)$activity['school_id'] !== (int)Auth::schoolId()) {
        Session::flash('error', 'You cannot delete this activity.');
        Helper::redirect('/admin/activities');
    }

    // Clean up cover image file if exists
    if (!empty($activity['cover_image'])) {
        $imgFile = PUBLIC_PATH . $activity['cover_image'];
        if (is_file($imgFile)) {
            @unlink($imgFile);
        }
    }

    Activity::delete($id);
    Session::flash('success', 'Activity deleted successfully.');
} catch (\Throwable $e) {
    error_log('Activity deletion failed: ' . $e->getMessage());
    Session::flash('error', 'Failed to delete activity: ' . $e->getMessage());
}

Helper::redirect('/admin/activities');
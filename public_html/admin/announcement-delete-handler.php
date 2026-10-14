<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth, Announcement, Helper, Database};

Session::start();
Auth::requireLogin();
if (Auth::role() !== 'admin') { http_response_code(403); exit('Forbidden'); }
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/admin/announcements');
}

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    // School admin can only delete their own school's announcements
    if (Auth::isSchoolAdmin()) {
        $stmt = Database::connect()->prepare("SELECT school_id FROM announcements WHERE id=? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row || (int)$row['school_id'] !== (int)Auth::schoolId()) {
            Session::flash('error', 'You cannot delete this announcement.');
            Helper::redirect(Auth::isSuperAdmin() ? '/admin/announcements' : '/school/announcements');
        }
    }
    Announcement::delete($id);
    Session::flash('success', 'Announcement deleted.');
}

Helper::redirect(Auth::isSuperAdmin() ? '/admin/announcements' : '/school/announcements');
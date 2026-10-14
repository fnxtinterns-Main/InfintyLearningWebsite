<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth, Announcement, Notification, Helper, Database};

Session::start();
Auth::requireLogin();
if (Auth::role() !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/admin/announcements');
}

$me       = Auth::user();
$isSuper  = Auth::isSuperAdmin();

$title    = trim($_POST['title'] ?? 'Untitled');
$body     = trim($_POST['body']  ?? '');
$audience = $_POST['audience']   ?? 'all';
$pinned   = !empty($_POST['pinned']);

if ($title === '' || $body === '') {
    Session::flash('error', 'Title and message are required.');
    Helper::redirect('/admin/announcements');
}

// Determine scope
$schoolId = null;
if (!$isSuper) {
    $schoolId = (int)$me['school_id'];
} else {
    if (!empty($_POST['school_id'])) {
        $schoolId = (int)$_POST['school_id'];
    }
}

Announcement::create($title, $body, $audience, $pinned, (int)$me['id'], $schoolId);

// ============================================================
// Send notifications
// ============================================================
$db = Database::connect();
$notifBody = mb_substr($body, 0, 120);
$link = '/dashboard';

// Build the audience SQL fragment
$audienceWhere = "u.status='active'";
if ($audience === 'students') $audienceWhere .= " AND u.role='student'";
elseif ($audience === 'teachers') $audienceWhere .= " AND u.role='teacher'";
elseif ($audience === 'admins') $audienceWhere .= " AND u.role='admin'";
else $audienceWhere .= " AND u.role IN ('student','teacher','admin')";

// Build the scope fragment
$scopeWhere = '';
$params = [];
if (!empty($schoolId)) {
    // Scoped → only that school's users (super admin or global admins)
    $scopeWhere = " AND u.school_id = ?";
    $params[] = $schoolId;
} else {
    // Global → all users
    $scopeWhere = " AND 1=1";
}

$sql = "SELECT u.id FROM users u WHERE $audienceWhere $scopeWhere";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$userIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

if ($userIds) {
    Notification::pushMany($userIds, 'announcement', $title, $notifBody, $link);
}

Session::flash('success', 'Announcement posted. ' . count($userIds) . ' user(s) notified.');
Helper::redirect('/admin/announcements');
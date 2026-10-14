<?php
use App\Auth;
use App\Helper;

Auth::requireLogin();

// Pending users can't reach the dashboard.
$user = Auth::user();
if (($user['status'] ?? '') === 'pending') {
    Helper::redirect('/pending-approval');
}
if (($user['status'] ?? '') === 'rejected') {
    Auth::logout();
    Helper::redirect('/login');
}

$role = Auth::role();

switch ($role) {
    case 'student': require VIEW_PATH . '/student/dashboard.php'; break;
    case 'teacher': require VIEW_PATH . '/teacher/dashboard.php'; break;
    case 'parent':  Helper::redirect('/parent-pass'); break;
    case 'admin':
        // Super admin (no school) → /admin; school admin → /school (sub-task E)
        Helper::redirect(empty($user['school_id']) ? '/admin' : '/school');
        break;
    default:
        Helper::redirect('/login');
}
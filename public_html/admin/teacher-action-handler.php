<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';
define('APP_ROOT', $appRoot);

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Admin, Notification, Mailer, Helper, School, Database};

Session::start();
Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/admin/teachers');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/admin/teachers');
}

$userId = (int)($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';
$me = Auth::user();
$db = Database::connect();

try {
    switch ($action) {
        case 'approve':
            Admin::approveTeacher($userId, (int)$me['id']);

            // Fetch teacher info for welcome email
            $stmt = $db->prepare("
                SELECT u.id, u.email, u.role, u.school_id,
                       t.full_name AS teacher_name
                FROM users u
                LEFT JOIN teachers t ON t.user_id = u.id
                WHERE u.id = ? LIMIT 1
            ");
            $stmt->execute([$userId]);
            $teacher = $stmt->fetch();

            if ($teacher) {
                $schoolName = null;
                if (!empty($teacher['school_id'])) {
                    $s = School::find((int)$teacher['school_id']);
                    $schoolName = $s['name'] ?? null;
                }

                // Send welcome email
                try {
                    Mailer::send(
                        $teacher['email'],
                        '🎉 Your Infinity Learning teacher account is approved',
                        Mailer::approvalEmail(
                            $teacher['teacher_name'] ?: 'Teacher',
                            $teacher['email'],
                            'teacher',
                            $schoolName
                        )
                    );
                } catch (\Throwable $e) {
                    error_log('Teacher approval email failed: ' . $e->getMessage());
                }

                Notification::push(
                    $userId,
                    'teacher.approved',
                    'Your teacher account is approved 🎉',
                    'You can now log in and create batches, upload lessons, and assign activities.',
                    '/dashboard'
                );
            }

            Session::flash('success', 'Teacher approved. Welcome email sent.');
            break;

        case 'reject':
            Admin::rejectTeacher($userId, 'Not specified');
            Session::flash('success', 'Teacher rejected.');
            break;

        case 'suspend':
            Admin::suspendUser($userId);
            Session::flash('success', 'User suspended.');
            break;

        case 'reactivate':
            Admin::reactivateUser($userId);
            Session::flash('success', 'User reactivated.');
            break;

        default:
            Session::flash('error', 'Unknown action.');
    }
} catch (\Throwable $e) {
    error_log($e->getMessage());
    Session::flash('error', 'Action failed.');
}

Helper::redirect('/admin/teachers');
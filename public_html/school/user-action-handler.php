<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, School, Notification, Mailer, Helper, Database};

Session::start();
$schoolId = Auth::requireSchoolAdmin();
$me = Auth::user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/school');
}
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/school');
}

$userId = (int)($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

$allowedActions = ['approve','reject','suspend','reactivate','assign_class_teacher','assign_mentor'];
if (!in_array($action, $allowedActions, true)) {
    Session::flash('error', 'Invalid action.');
    Helper::redirect('/school');
}

$db = Database::connect();
$school = School::find($schoolId);

try {
    switch ($action) {
        case 'approve':
            if (School::approveUser($userId, $schoolId, (int)$me['id'])) {

                // ---- Fetch the user + name for the email ----
                $stmt = $db->prepare("SELECT id, email, role FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();

                if ($user) {
                    $displayName = 'there';
                    if ($user['role'] === 'student') {
                        $n = $db->prepare("SELECT full_name FROM students WHERE user_id = ? LIMIT 1");
                        $n->execute([$userId]);
                        $row = $n->fetch();
                        if (!empty($row['full_name'])) $displayName = $row['full_name'];
                    } elseif ($user['role'] === 'teacher') {
                        $n = $db->prepare("SELECT full_name FROM teachers WHERE user_id = ? LIMIT 1");
                        $n->execute([$userId]);
                        $row = $n->fetch();
                        if (!empty($row['full_name'])) $displayName = $row['full_name'];
                    }

                    // ---- Send welcome EMAIL ----
                    try {
                        Mailer::send(
                            $user['email'],
                            '🎉 Your Infinity Learning account is approved',
                            Mailer::approvalEmail(
                                $displayName,
                                $user['email'],
                                $user['role'],
                                $school['name'] ?? null
                            )
                        );
                    } catch (\Throwable $e) {
                        error_log('Approval email failed: ' . $e->getMessage());
                    }

                    // ---- In-app notification ----
                    Notification::push(
                        $userId,
                        'account.approved',
                        'Your account was approved 🎉',
                        'Welcome to Infinity Learning! You can now log in and access your dashboard.',
                        '/dashboard'
                    );
                }

                Session::flash('success', 'User approved. Welcome email sent.');
            } else {
                Session::flash('error', 'User not found in your school.');
            }
            break;

        case 'reject':
            // Fetch user info before or after rejection
            $stmt = $db->prepare("SELECT id, email, role FROM users WHERE id = ? AND school_id = ? LIMIT 1");
            $stmt->execute([$userId, $schoolId]);
            $user = $stmt->fetch();

            if (School::rejectUser($userId, $schoolId)) {
                if ($user) {
                    $displayName = 'there';
                    if ($user['role'] === 'student') {
                        $n = $db->prepare("SELECT full_name FROM students WHERE user_id = ? LIMIT 1");
                        $n->execute([$userId]);
                        $row = $n->fetch();
                        if (!empty($row['full_name'])) $displayName = $row['full_name'];
                    } elseif ($user['role'] === 'teacher') {
                        $n = $db->prepare("SELECT full_name FROM teachers WHERE user_id = ? LIMIT 1");
                        $n->execute([$userId]);
                        $row = $n->fetch();
                        if (!empty($row['full_name'])) $displayName = $row['full_name'];
                    }
                    try {
                        Mailer::send(
                            $user['email'],
                            'Update on your Infinity Learning account application',
                            Mailer::accountRejectedEmail($displayName, $user['role'])
                        );
                    } catch (\Throwable $e) {
                        error_log('Reject email failed: ' . $e->getMessage());
                    }
                }
                Session::flash('success', 'User rejected and email notification sent.');
            } else {
                Session::flash('error', 'User not found in your school.');
            }
            break;

        case 'suspend':
            $check = $db->prepare("SELECT id, email, role FROM users WHERE id=? AND school_id=? LIMIT 1");
            $check->execute([$userId, $schoolId]);
            $user = $check->fetch();
            if ($user) {
                $db->prepare("UPDATE users SET status='suspended' WHERE id=?")->execute([$userId]);
                $displayName = 'there';
                if ($user['role'] === 'student') {
                    $n = $db->prepare("SELECT full_name FROM students WHERE user_id = ? LIMIT 1");
                    $n->execute([$userId]);
                    $row = $n->fetch();
                    if (!empty($row['full_name'])) $displayName = $row['full_name'];
                } elseif ($user['role'] === 'teacher') {
                    $n = $db->prepare("SELECT full_name FROM teachers WHERE user_id = ? LIMIT 1");
                    $n->execute([$userId]);
                    $row = $n->fetch();
                    if (!empty($row['full_name'])) $displayName = $row['full_name'];
                }
                try {
                    Mailer::send(
                        $user['email'],
                        'Important Notice: Account Suspended — Infinity Learning',
                        Mailer::accountSuspendedEmail($displayName, $user['role'])
                    );
                } catch (\Throwable $e) {
                    error_log('Suspension email failed: ' . $e->getMessage());
                }
                Session::flash('success', 'User suspended and email notification sent.');
            } else {
                Session::flash('error', 'User not found in your school.');
            }
            break;

        case 'reactivate':
            $check = $db->prepare("SELECT id FROM users WHERE id=? AND school_id=? LIMIT 1");
            $check->execute([$userId, $schoolId]);
            if ($check->fetch()) {
                $db->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$userId]);
                Session::flash('success', 'User reactivated.');
            } else {
                Session::flash('error', 'User not found in your school.');
            }
            break;

        case 'assign_class_teacher':
            $grade = trim($_POST['class_teacher_grade'] ?? '');
            $section = trim($_POST['class_teacher_section'] ?? '');
            if (School::assignClassTeacher($userId, $schoolId, $grade, $section)) {
                Session::flash('success', $grade !== '' ? "Assigned as Class Teacher for $grade $section." : "Class Teacher assignment removed.");
            } else {
                Session::flash('error', 'Failed to assign class teacher. Please check permissions.');
            }
            break;

        case 'assign_mentor':
            $mentorUserId = !empty($_POST['mentor_user_id']) ? (int)$_POST['mentor_user_id'] : null;
            $studentIds = [];
            if (!empty($_POST['student_ids']) && is_array($_POST['student_ids'])) {
                $studentIds = array_map('intval', $_POST['student_ids']);
            } elseif ($userId > 0) {
                $studentIds = [$userId];
            }
            $assignedCount = School::assignMentor($studentIds, $mentorUserId, $schoolId);
            Session::flash('success', "Mentor assigned to $assignedCount student(s).");
            break;
    }
} catch (\Throwable $e) {
    error_log('School user action failed: ' . $e->getMessage());
    Session::flash('error', 'Action failed: ' . $e->getMessage());
}

// Redirect back to the referring list (teachers or students)
$referer = $_SERVER['HTTP_REFERER'] ?? '/school';
if (strpos($referer, '/school') === false) {
    $referer = '/school';
}
Helper::redirect($referer);
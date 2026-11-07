<?php
declare(strict_types=1);
ob_start();

define('APP_ROOT', dirname(__DIR__) . '/infinitylearning');
define('BASE_PATH', dirname(__DIR__) . '/infinitylearning');
define('VIEW_PATH', APP_ROOT . '/views');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\FutureNxt\Auth;
use App\FutureNxt\Employee;
use App\FutureNxt\Task;
use App\FutureNxt\Leave;
use App\FutureNxt\WFH;
use App\FutureNxt\DailyUpdate;
use App\FutureNxt\Notice;
use App\FutureNxt\EmailNotifier;
use App\Helper;
use App\Session;

Session::start();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
          || isset($_POST['ajax']) || isset($_GET['ajax']);

function sendResponse(bool $success, string $message, array $extra = []): void {
    global $isAjax;
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }
    // Redirect with flash message
    $redirect = $_POST['redirect_url'] ?? $_SERVER['HTTP_REFERER'] ?? ($success ? '/futurenxt/dashboard' : '/futurenxt/login');
    $param = $success ? 'success=' . urlencode($message) : 'error=' . urlencode($message);
    $delimiter = str_contains($redirect, '?') ? '&' : '?';
    header('Location: ' . $redirect . $delimiter . $param);
    exit;
}

// -------------------------------------------------------------
// PUBLIC ACTIONS (LOGIN / LOGOUT)
// -------------------------------------------------------------
if ($action === 'login') {
    $identity = trim((string)($_POST['identity'] ?? $_POST['login_identifier'] ?? ''));
    $password = trim((string)($_POST['password'] ?? ''));

    $res = Auth::login($identity, $password);
    if ($res['success']) {
        $dest = $_SESSION['fnx_redirect_after_login'] ?? '/futurenxt/dashboard';
        unset($_SESSION['fnx_redirect_after_login']);
        if ($isAjax) {
            sendResponse(true, 'Login successful!', ['redirect' => $dest]);
        } else {
            header('Location: ' . $dest);
            exit;
        }
    } else {
        sendResponse(false, $res['error']);
    }
}

if ($action === 'logout') {
    Auth::logout();
    header('Location: /futurenxt/login?logged_out=1');
    exit;
}

if ($action === 'ping') {
    // Keep session active
    sendResponse(true, 'pong');
}

// -------------------------------------------------------------
// ALL OTHER ACTIONS REQUIRE AUTHENTICATION
// -------------------------------------------------------------
if (!Auth::check()) {
    sendResponse(false, 'Unauthorized. Please log in.');
}

$currentUser = Auth::user();
if (!$currentUser) {
    Auth::logout();
    sendResponse(false, 'Session invalid or user not found. Please log in again.');
}
$currentId   = (int)$currentUser['id'];

// -------------------------------------------------------------
// PROFILE & SECURITY (AVAILABLE TO ALL USERS)
// -------------------------------------------------------------
if ($action === 'change_password') {
    $currentPass = trim((string)($_POST['current_password'] ?? ''));
    $newPass     = trim((string)($_POST['new_password'] ?? ''));
    $confirmPass = trim((string)($_POST['confirm_password'] ?? ''));

    if ($currentPass === '' || $newPass === '' || $confirmPass === '') {
        sendResponse(false, 'Please fill in all password fields.');
    }
    if ($newPass !== $confirmPass) {
        sendResponse(false, 'New password and confirmation password do not match.');
    }
    if (strlen($newPass) < 8) {
        sendResponse(false, 'New password must be at least 8 characters long.');
    }

    if (!password_verify($currentPass, $currentUser['password_hash'])) {
        sendResponse(false, 'Current password is incorrect. Please try again.');
    }

    $newHash = password_hash($newPass, PASSWORD_DEFAULT);
    $db = \App\FutureNxt\Database::connect();
    $stmt = $db->prepare("UPDATE fnx_employees SET password_hash = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$newHash, $currentId]);

    \App\FutureNxt\Audit::log('change_password', 'employee', $currentId, 'User changed account password', $currentId);

    try {
        EmailNotifier::sendPasswordChangedConfirmation($currentId, 'Yourself');
    } catch (\Throwable $e) {}

    sendResponse(true, 'Your password has been changed successfully! A security confirmation email has been dispatched to your registered email.');
}

// -------------------------------------------------------------
// PROFILE PHOTO UPLOAD (CEO / CTO / ADMIN)
// -------------------------------------------------------------
if ($action === 'upload_profile_photo') {
    if (!Auth::isAdminOrExec()) {
        sendResponse(false, 'Permission denied: Only Admin, CEO, or CTO can upload profile pictures.');
    }

    $targetId = !empty($_POST['employee_id']) ? (int)$_POST['employee_id'] : $currentId;
    if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        sendResponse(false, 'Please select a valid image file to upload.');
    }

    $res = Employee::updatePhoto($targetId, $_FILES['photo']);
    if ($res['success']) {
        if ($targetId === $currentId) {
            $_SESSION['fnx_user']['photo_path'] = $res['photo_path'];
        }
        sendResponse(true, 'Profile picture updated successfully!', ['photo_path' => $res['photo_path']]);
    } else {
        sendResponse(false, $res['error'] ?? 'Failed to update profile picture.');
    }
}

// -------------------------------------------------------------
// EMPLOYEE MANAGEMENT (ADMIN / CEO / CTO / MANAGERS)
// -------------------------------------------------------------
if ($action === 'create_employee') {
    if (!Auth::isAdminOrExec() && !Auth::isManagerOrAbove()) {
        sendResponse(false, 'Permission denied: Only Admin, CEO, CTO, and Managers can onboard employees.');
    }

    // If Manager is onboarding, they automatically become the reporting manager
    if (!Auth::isAdminOrExec()) {
        $_POST['manager_id'] = $currentId;
        // Manager can only onboard 'employee' or 'intern'
        if (!in_array($_POST['role'] ?? '', ['employee', 'intern'], true)) {
            $_POST['role'] = 'employee';
        }
    }

    $res = Employee::create($_POST, $_FILES);
    if ($res['success']) {
        sendResponse(true, "Employee {$res['emp_code']} onboarded successfully! Initial Password: {$res['initial_password']}", $res);
    } else {
        sendResponse(false, $res['error']);
    }
}

if ($action === 'get_next_emp_code') {
    $role = trim((string)($_GET['role'] ?? $_POST['role'] ?? 'employee'));
    $managerId = !empty($_GET['manager_id']) ? (int)$_GET['manager_id'] : (!empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null);
    if (!Auth::isAdminOrExec() && (Auth::role() === 'manager' || Auth::role() === 'office_manager')) {
        $managerId = $currentId;
    }
    $code = Employee::generateNextCode($role, $managerId);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'code' => $code]);
    exit;
}

if ($action === 'update_employee') {
    if (!Auth::isAdminOrExec()) {
        sendResponse(false, 'Permission denied: Only Admin, CEO, and CTO can change profile details.');
    }
    $empId = (int)($_POST['employee_id'] ?? 0);
    if (!$empId) {
        $empId = $currentId;
    }
    $res = Employee::update($empId, $_POST, $_FILES);
    if ($res['success']) {
        if ($empId === $currentId) {
            $_SESSION['fnx_user'] = Employee::findById($currentId);
        }
        sendResponse(true, 'Profile details updated successfully!');
    } else {
        sendResponse(false, $res['error']);
    }
}

if ($action === 'update_status') {
    if (!Auth::isAdminOrExec()) {
        sendResponse(false, 'Permission denied: Only Admin, CEO, and CTO can change employment status.');
    }
    $empId         = (int)($_POST['employee_id'] ?? 0);
    $status        = trim((string)($_POST['status'] ?? 'active'));
    $pipReason     = trim((string)($_POST['pip_reason'] ?? ''));
    $pipEndDate    = trim((string)($_POST['pip_end_date'] ?? ''));
    $statusRemarks = trim((string)($_POST['status_remarks'] ?? ''));

    $ok = Employee::updateStatus($empId, $status, $pipReason, $pipEndDate, $statusRemarks);
    if ($ok) {
        $msg = "Employee status changed to " . strtoupper($status) . ".";
        if ($status === 'resigned') {
            $msg .= " 15-day notice period active until " . date('d M Y', strtotime('+15 days')) . ".";
        } elseif ($status === 'suspended') {
            $msg .= " Account has been suspended and access revoked.";
        } elseif ($status === 'terminated') {
            $msg .= " Account terminated and portal access revoked.";
        }
        sendResponse(true, $msg);
    } else {
        sendResponse(false, 'Failed to update employee status.');
    }
}

// -------------------------------------------------------------
// ADMIN PASSWORD RESET VIA EMAIL OTP (NO CURRENT PASSWORD NEEDED)
// -------------------------------------------------------------
if ($action === 'admin_request_password_otp') {
    $isAjax = true;
    if (!Auth::isAdminOrExec()) {
        sendResponse(false, 'Permission denied: Only Admin/Executive Leadership can reset credentials.');
    }
    $empId = (int)($_POST['employee_id'] ?? 0);
    $res = Employee::createPasswordResetOtp($empId);
    if ($res['success']) {
        sendResponse(true, $res['message'], $res);
    } else {
        sendResponse(false, $res['error']);
    }
}

if ($action === 'admin_confirm_password_reset') {
    $isAjax = true;
    if (!Auth::isAdminOrExec()) {
        sendResponse(false, 'Permission denied: Only Admin/Executive Leadership can reset credentials.');
    }
    $empId       = (int)($_POST['employee_id'] ?? 0);
    $otp         = trim((string)($_POST['otp_code'] ?? ''));
    $newPassword = trim((string)($_POST['new_password'] ?? ''));

    $res = Employee::verifyPasswordResetOtpAndChangePassword($empId, $otp, $newPassword);
    if ($res['success']) {
        sendResponse(true, $res['message']);
    } else {
        sendResponse(false, $res['error']);
    }
}

// -------------------------------------------------------------
// RESEND ONBOARDING CREDENTIALS EMAIL (ADMIN / EXEC)
// -------------------------------------------------------------
if ($action === 'resend_onboarding_email') {
    if (!Auth::isAdminOrExec()) {
        sendResponse(false, 'Permission denied: Only Admin/Executive Leadership can resend partner credentials.');
    }
    $partnerId = (int)($_POST['partner_id'] ?? $_POST['employee_id'] ?? 0);
    $partner = Employee::findById($partnerId);
    if (!$partner) {
        sendResponse(false, 'Partner record not found.');
    }
    $customPass = trim((string)($_POST['password'] ?? 'Futurenxt@2026'));
    $sent = EmailNotifier::sendPartnerOnboarding($partnerId, $customPass);
    if ($sent) {
        sendResponse(true, "Official onboarding welcome email dispatched to {$partner['email']} with login credentials!");
    } else {
        sendResponse(false, "Failed to send onboarding email. Please check server logs.");
    }
}

// -------------------------------------------------------------
// TASK ACTIONS
// -------------------------------------------------------------
if ($action === 'create_task') {
    if (!Auth::isManagerOrAbove()) {
        sendResponse(false, 'Permission denied: Only Managers, CEO, CTO, and Admin can assign tasks.');
    }
    $assigneeIds = $_POST['assignee_ids'] ?? $_POST['assignees'] ?? [];
    if (!is_array($assigneeIds) && !empty($assigneeIds)) {
        $assigneeIds = [$assigneeIds];
    }
    $res = Task::create($_POST, $assigneeIds, $currentId);
    if ($res['success']) {
        sendResponse(true, "Task {$res['task_code']} created and assigned successfully! Official notification emails dispatched to all assigned partners.", $res);
    } else {
        sendResponse(false, $res['error']);
    }
}

if ($action === 'reassign_task') {
    if (!Auth::isAdminOrExec() && !Auth::isManagerOrAbove()) {
        sendResponse(false, 'Permission denied.');
    }
    $taskId         = (int)($_POST['task_id'] ?? 0);
    $fromEmployeeId = (int)($_POST['from_employee_id'] ?? 0);
    $toEmployeeId   = (int)($_POST['to_employee_id'] ?? 0);
    $reason         = trim((string)($_POST['reason'] ?? 'Reassigned by management'));

    $res = Task::reassign($taskId, $fromEmployeeId, $toEmployeeId, $currentId, $reason);
    if ($res['success']) {
        sendResponse(true, 'Task reassigned successfully!');
    } else {
        sendResponse(false, $res['error']);
    }
}

if ($action === 'submit_task_doc' || $action === 'submit_task_docs') {
    $taskId = (int)($_POST['task_id'] ?? 0);
    $notes  = trim((string)($_POST['submission_notes'] ?? $_POST['notes'] ?? ''));
    $file   = !empty($_FILES['documentation_pdf']['name']) 
        ? $_FILES['documentation_pdf'] 
        : (!empty($_FILES['doc_file']['name']) ? $_FILES['doc_file'] : ($_FILES['file'] ?? []));

    $res = Task::submitDocumentation($taskId, $currentId, $file, $notes);
    if ($res['success']) {
        sendResponse(true, 'Task documentation PDF uploaded and submitted for management review!');
    } else {
        sendResponse(false, $res['error']);
    }
}

if ($action === 'review_task' || $action === 'approve_task' || $action === 'reject_task') {
    if (!Auth::isAdminOrExec() && !Auth::isManagerOrAbove()) {
        sendResponse(false, 'Permission denied.');
    }
    $taskId   = (int)($_POST['task_id'] ?? 0);
    $decision = trim((string)($_POST['decision'] ?? ($action === 'reject_task' ? 'reopen' : 'approve')));
    $feedback = trim((string)($_POST['remarks'] ?? $_POST['feedback'] ?? ''));

    try {
        if ($decision === 'approve') {
            $ok = Task::approve($taskId, $currentId, $feedback ?: 'Approved by management');
            if ($ok) {
                sendResponse(true, 'Task reviewed and approved successfully!');
            } else {
                sendResponse(false, 'Failed to approve task.');
            }
        } else {
            $ok = Task::reject($taskId, $currentId, $feedback ?: 'Revision requested');
            if ($ok) {
                sendResponse(true, 'Task returned for revision.');
            } else {
                sendResponse(false, 'Failed to update task status.');
            }
        }
    } catch (\Throwable $e) {
        error_log('FutureNxt review_task error: ' . $e->getMessage());
        sendResponse(false, 'Database error while reviewing task: ' . $e->getMessage());
    }
}

// -------------------------------------------------------------
// LEAVE ACTIONS
// -------------------------------------------------------------
if ($action === 'apply_leave' || $action === 'request_leave') {
    $type      = trim((string)($_POST['leave_type'] ?? 'casual'));
    $startDate = trim((string)($_POST['start_date'] ?? ''));
    $endDate   = trim((string)($_POST['end_date'] ?? ''));
    $reason    = trim((string)($_POST['reason'] ?? ''));
    $targetEmpId = (!empty($_POST['employee_id']) && Auth::isAdminOrExec()) ? (int)$_POST['employee_id'] : $currentId;

    $res = Leave::request($targetEmpId, $type, $startDate, $endDate, $reason);
    if ($res['success']) {
        sendResponse(true, 'Leave request submitted successfully. Management will review any task conflicts.');
    } else {
        sendResponse(false, $res['error']);
    }
}

if ($action === 'review_leave') {
    if (!Auth::isAdminOrExec()) {
        sendResponse(false, 'Permission denied: Only Admin, CEO, or CTO can approve leaves.');
    }
    $leaveId = (int)($_POST['leave_id'] ?? 0);
    $rawStatus = trim((string)($_POST['status'] ?? $_POST['decision'] ?? 'approved'));
    $status  = ($rawStatus === 'approve' || $rawStatus === 'approved') ? 'approved' : 'rejected';
    $remarks = trim((string)($_POST['remarks'] ?? ''));

    $ok = Leave::review($leaveId, $status, $currentId, $remarks);
    if ($ok) {
        sendResponse(true, "Leave request marked as {$status}.");
    } else {
        sendResponse(false, 'Failed to review leave.');
    }
}

// -------------------------------------------------------------
// WORK FROM ANYWHERE (WFA) ACTIONS
// -------------------------------------------------------------
if ($action === 'request_wfa' || $action === 'apply_wfa' || $action === 'request_wfh' || $action === 'apply_wfh') {
    $startDate = trim((string)($_POST['start_date'] ?? ''));
    $endDate   = trim((string)($_POST['end_date'] ?? ''));
    $reason    = trim((string)($_POST['reason'] ?? ''));
    $targetEmpId = (!empty($_POST['employee_id']) && Auth::isAdminOrExec()) ? (int)$_POST['employee_id'] : $currentId;

    $res = WFH::request($targetEmpId, $startDate, $endDate, $reason);
    if ($res['success']) {
        sendResponse(true, 'Work From Anywhere (WFA) request submitted successfully.');
    } else {
        sendResponse(false, $res['error']);
    }
}

if ($action === 'review_wfa' || $action === 'review_wfh') {
    if (!Auth::isAdminOrExec()) {
        sendResponse(false, 'Permission denied.');
    }
    $wfhId   = (int)($_POST['wfa_id'] ?? $_POST['wfh_id'] ?? 0);
    $rawStatus = trim((string)($_POST['status'] ?? $_POST['decision'] ?? 'approved'));
    $status  = ($rawStatus === 'approve' || $rawStatus === 'approved') ? 'approved' : 'rejected';
    $remarks = trim((string)($_POST['remarks'] ?? ''));

    $ok = WFH::review($wfhId, $status, $currentId, $remarks);
    if ($ok) {
        sendResponse(true, "WFA request marked as {$status}.");
    } else {
        sendResponse(false, 'Failed to review WFA request.');
    }
}

// -------------------------------------------------------------
// DAILY WORK UPDATES (REQUIRED FOR EVERYONE)
// -------------------------------------------------------------
if ($action === 'submit_daily_update') {
    $date         = trim((string)($_POST['update_date'] ?? date('Y-m-d')));
    $workDone     = trim((string)($_POST['work_done'] ?? ''));
    $tasksSummary = trim((string)($_POST['tasks_summary'] ?? ''));
    $blockers     = trim((string)($_POST['blockers'] ?? ''));
    $planTomorrow = trim((string)($_POST['plan_tomorrow'] ?? ''));
    $hoursSpent   = !empty($_POST['hours_spent']) ? (float)$_POST['hours_spent'] : null;
    $taskIds      = !empty($_POST['task_ids']) && is_array($_POST['task_ids']) ? $_POST['task_ids'] : [];

    $targetEmpId = $currentId;
    if (!empty($_POST['employee_id']) && Auth::isAdminOrExec()) {
        $targetEmpId = (int)$_POST['employee_id'];
    }

    $res = DailyUpdate::submit(
        $targetEmpId,
        $date,
        $workDone,
        $tasksSummary,
        $blockers,
        $planTomorrow,
        $hoursSpent,
        $taskIds
    );

    if ($res['success']) {
        sendResponse(true, "Daily work update for {$date} submitted successfully!", $res);
    } else {
        sendResponse(false, $res['error']);
    }
}

if ($action === 'get_task_updates') {
    $taskId   = (int)($_GET['task_id'] ?? 0);
    $taskCode = trim((string)($_GET['task_code'] ?? ''));
    if ($taskId <= 0) {
        sendResponse(false, 'Invalid task ID');
    }
    $updates = DailyUpdate::getByTask($taskId, $taskCode);
    sendResponse(true, 'Updates retrieved', ['updates' => $updates]);
}

// -------------------------------------------------------------
// COMPANY NOTICES & EVENT BROADCASTS
// -------------------------------------------------------------
if ($action === 'create_notice') {
    if (!Auth::isAdminOrExec()) {
        sendResponse(false, 'Permission denied: Only Admin, CEO, and CTO can broadcast company notices.');
    }

    $res = Notice::create($_POST, $currentId);
    if ($res['success']) {
        sendResponse(true, 'Notice published and broadcasted successfully!');
    } else {
        sendResponse(false, $res['error']);
    }
}

if ($action === 'delete_notice') {
    if (!Auth::isAdminOrExec()) {
        sendResponse(false, 'Permission denied.');
    }
    $noticeId = (int)($_POST['notice_id'] ?? 0);
    Notice::delete($noticeId, $currentId);
    sendResponse(true, 'Notice archived successfully.');
}

if ($action === 'pin_notice') {
    if (!Auth::isAdminOrExec()) {
        sendResponse(false, 'Permission denied.');
    }
    $noticeId = (int)($_POST['notice_id'] ?? 0);
    $pin = !empty($_POST['pin']);
    Notice::pin($noticeId, $pin, $currentId);
    sendResponse(true, $pin ? 'Notice pinned to top.' : 'Notice unpinned.');
}

if ($action === 'trigger_task_reminders') {
    $res = EmailNotifier::checkAndSendTaskDeadlineReminders();
    sendResponse(true, "Task reminders checked. " . count($res['tasks_processed']) . " tasks alerted.", $res);
}

// Fallback
sendResponse(false, 'Invalid action specified.');

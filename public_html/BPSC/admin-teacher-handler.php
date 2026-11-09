<?php
/**
 * Infinity Learning - Admin Teacher Management Action Endpoint
 * Approves, Declines, or Removes Teachers
 */

define('BPSC_PORTAL', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/src/UserManager.php';
require_once __DIR__ . '/src/Auth.php';

header('Content-Type: application/json; charset=utf-8');

$currentUser = Auth::user();
// Processes admin approvals, declines, or removals for faculty onboarding requests
$action = trim($_POST['action'] ?? $_GET['action'] ?? 'list');
$teacherId = trim($_POST['teacher_id'] ?? $_GET['teacher_id'] ?? '');

switch ($action) {
    case 'approve':
        if (empty($teacherId)) {
            echo json_encode(['success' => false, 'message' => 'Missing teacher ID.']);
            exit;
        }
        $res = UserManager::approveTeacher($teacherId);
        $res['pending'] = UserManager::getPendingTeachers();
        $res['approved'] = UserManager::getApprovedTeachers();
        echo json_encode($res);
        exit;

    case 'decline':
        if (empty($teacherId)) {
            echo json_encode(['success' => false, 'message' => 'Missing teacher ID.']);
            exit;
        }
        $res = UserManager::declineTeacher($teacherId);
        $res['pending'] = UserManager::getPendingTeachers();
        $res['approved'] = UserManager::getApprovedTeachers();
        echo json_encode($res);
        exit;

    case 'remove':
        if (empty($teacherId)) {
            echo json_encode(['success' => false, 'message' => 'Missing teacher ID.']);
            exit;
        }
        $res = UserManager::removeTeacher($teacherId);
        $res['pending'] = UserManager::getPendingTeachers();
        $res['approved'] = UserManager::getApprovedTeachers();
        echo json_encode($res);
        exit;

    case 'list':
    default:
        echo json_encode([
            'success' => true,
            'pending' => UserManager::getPendingTeachers(),
            'approved' => UserManager::getApprovedTeachers()
        ]);
        exit;
}


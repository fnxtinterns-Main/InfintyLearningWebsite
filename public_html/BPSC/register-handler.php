<?php
/**
 * Infinity Learning - Registration Action Handler Endpoint
 * Processes Candidate & Teacher registrations
 */

define('BPSC_PORTAL', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/src/UserManager.php';
require_once __DIR__ . '/src/Auth.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
    || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
    || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')
    || isset($_POST['ajax']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    } else {
        header('Location: index.php');
    }
    exit;
}

$regType = trim($_POST['reg_type'] ?? 'student');

if ($regType === 'teacher') {
    $result = UserManager::registerTeacher($_POST);
} else {
    $result = UserManager::registerCandidate($_POST);
}

if (!$isAjax) {
    if ($result['success']) {
        header('Location: index.php?reg_success=' . urlencode($result['message']));
    } else {
        header('Location: index.php?reg_error=' . urlencode($result['message']));
    }
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
exit;


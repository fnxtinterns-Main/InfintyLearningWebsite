<?php
/**
 * Infinity Learning - Login Action Handler Endpoint
 * Authenticates registered users against local repository
 */

define('BPSC_PORTAL', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Auth.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
    || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
    || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')
    || isset($_POST['ajax']);

// Also support JSON payloads
if (empty($_POST)) {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $jsonData = json_decode($rawInput, true);
        if (is_array($jsonData)) {
            $_POST = $jsonData;
            $isAjax = true;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed. Use POST.'
        ]);
    } else {
        header('Location: index.php');
    }
    exit;
}

$identifier = trim($_POST['identifier'] ?? $_POST['email'] ?? $_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role = trim($_POST['role'] ?? ROLE_STUDENT);

if (empty($identifier) || empty($password)) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Please provide both your registered email/phone and password.'
        ]);
    } else {
        header('Location: index.php?error=' . urlencode('Please provide both your registered email/phone and password.'));
    }
    exit;
}

// Perform authentication attempt
$result = Auth::attempt($identifier, $password, $role);

if (!$isAjax) {
    if ($result['success']) {
        header('Location: index.php');
    } else {
        header('Location: index.php?error=' . urlencode($result['message']));
    }
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
exit;

<?php
/**
 * Infinity Learning - Logout Action Handler Endpoint
 * Destroys session and returns to login gate
 */

define('BPSC_PORTAL', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Auth.php';

Auth::logout();

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => 'Signed out of portal terminal successfully.'
    ]);
    exit;
}

header('Location: index.php');
exit;


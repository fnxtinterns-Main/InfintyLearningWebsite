<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, FeeManagement, Helper};

Session::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/school/fees');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid token.');
    Helper::redirect('/school/fees');
}

$user = Auth::user();
$userId = (int)$user['id'];
$action = $_POST['action'] ?? 'assign';

try {
    if ($action === 'assign') {
        FeeManagement::recordFee($_POST, $userId);
        Session::flash('success', 'Fee due recorded successfully.');
    } elseif ($action === 'pay') {
        $feeId = (int)($_POST['fee_id'] ?? 0);
        $amountPaid = (float)($_POST['amount_paid'] ?? 0.0);
        $mode = $_POST['payment_mode'] ?? 'upi';
        $notes = $_POST['notes'] ?? '';
        FeeManagement::updatePayment($feeId, $amountPaid, $mode, $userId, $notes);
        Session::flash('success', 'Payment recorded and digital receipt issued.');
    }
} catch (\Throwable $e) {
    error_log('Fee save error: ' . $e->getMessage());
    Session::flash('error', 'Error: ' . $e->getMessage());
}

$redirect = (Auth::role() === 'teacher') ? '/teacher/fees' : '/school/fees';
Helper::redirect($redirect);

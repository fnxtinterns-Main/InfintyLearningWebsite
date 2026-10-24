<?php
declare(strict_types=1);

use App\Auth;
use App\Aiverse;
use App\Helper;
use App\Session;

Session::start();
Auth::requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/dashboard');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid security token. Please try again.');
    Helper::redirect('/dashboard');
}

$user = Auth::user();
$studentUserId = (int)$user['id'];
$bundleId = (int)($_POST['bundle_id'] ?? 0);

if ($bundleId <= 0) {
    Session::flash('error', 'Invalid bundle selected.');
    Helper::redirect('/dashboard');
}

try {
    $result = Aiverse::enrollBundle($studentUserId, $bundleId, 'upi');
    $savings = (float)($result['total_savings'] ?? 0.0);
    $savingsText = $savings > 0 ? " (You saved ₹" . number_format($savings, 2) . " with your bundle discount!)" : "";
    Session::flash('success', "🎉 Successfully enrolled in {$result['bundle_title']}!{$savingsText} Your course access and invoice schedule are now active.");
} catch (\Throwable $e) {
    error_log('Bundle enrollment error: ' . $e->getMessage());
    Session::flash('error', 'Enrollment failed: ' . $e->getMessage());
}

$redirect = !empty($_POST['redirect_to']) ? trim($_POST['redirect_to']) : '/dashboard';
Helper::redirect($redirect);

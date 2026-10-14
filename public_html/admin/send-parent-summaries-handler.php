<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/../../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Helper, ParentSummary, Mailer, RateLimiter, Audit};

Session::start();
Auth::requireSuperAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/admin');
}
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/admin');
}

// Rate-limit: super admin can trigger at most 2 sends per hour
$me = Auth::user();
if (!RateLimiter::hit('user:' . $me['id'], 'parent_summaries_send', 2, 3600)) {
    Session::flash('error', 'You already sent parent summaries recently. Try again in an hour.');
    Helper::redirect('/admin');
}

$weekStart = $_POST['week_start'] ?? ParentSummary::defaultWeekStart();

// Validate date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekStart)) {
    $weekStart = ParentSummary::defaultWeekStart();
}

$parents = ParentSummary::groupedByParent();
if (!$parents) {
    Session::flash('warning', 'No parents with email addresses were found.');
    Helper::redirect('/admin');
}

$sent = 0;
$failed = 0;
$skipped = 0;
$notifyCount = 0;

foreach ($parents as $p) {
    $parentId = (int)$p['parent_id'];
    $parentEmail = $p['parent_email'];
    $parentName = $p['parent_name'];
    $studentIds = $p['student_ids'];

    // Filter to students who haven't been sent this week
    $toSend = [];
    foreach ($studentIds as $sid) {
        if (ParentSummary::alreadySent($parentId, $sid, $weekStart)) {
            $skipped++;
            continue;
        }
        $summary = ParentSummary::buildForStudent($sid, $weekStart);
        if ($summary) $toSend[] = $summary;
    }

    if (!$toSend) continue;

    try {
        $html = Mailer::parentSummaryEmail($parentName, $toSend, $weekStart);
        $ok = Mailer::send(
            $parentEmail,
            'Weekly progress summary for your child — Infinity Learning',
            $html,
            $parentName
        );

        foreach ($toSend as $s) {
            $sid = (int)$s['student']['id'];
            ParentSummary::recordSend($parentId, $sid, $parentEmail, $weekStart, $ok, $ok ? null : 'Mailer returned false');
        }

        if ($ok) {
            $sent++;
            $notifyCount += count($toSend);
        } else {
            $failed++;
        }
    } catch (\Throwable $e) {
        error_log('Parent summary failed for parent ' . $parentId . ': ' . $e->getMessage());
        foreach ($toSend as $s) {
            $sid = (int)$s['student']['id'];
            ParentSummary::recordSend($parentId, $sid, $parentEmail, $weekStart, false, $e->getMessage());
        }
        $failed++;
    }
}

Audit::log('parent_summaries.sent', 'platform', null, [
    'week_start'    => $weekStart,
    'parents_sent'  => $sent,
    'parents_failed'=> $failed,
    'rows_skipped'  => $skipped,
    'students'      => $notifyCount,
]);

$msg = "Parent summaries sent: $sent parent(s) — covering $notifyCount student record(s).";
if ($failed > 0) $msg .= " Failed: $failed.";
if ($skipped > 0) $msg .= " Skipped (already sent): $skipped.";

Session::flash($failed > 0 ? 'warning' : 'success', $msg);
Helper::redirect('/admin');
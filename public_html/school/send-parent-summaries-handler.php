<?php
declare(strict_types=1);
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Helper, ParentSummary, Mailer, RateLimiter, Audit, Database, School};

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

$school = School::find($schoolId);
if (!$school || empty($school['allow_weekly_summary'])) {
    Session::flash('error', 'Weekly parent summaries are not permitted for your school by the Super Admin.');
    Helper::redirect('/school');
}
if (empty($school['weekly_summary_enabled'])) {
    Session::flash('warning', 'Weekly parent summaries are currently turned OFF. Slide the toggle to "Yes" to send summaries.');
    Helper::redirect('/school');
}

if (!RateLimiter::hit('user:' . $me['id'], 'parent_summaries_send', 2, 3600)) {
    Session::flash('error', 'You already sent parent summaries recently. Try again in an hour.');
    Helper::redirect('/school');
}

$weekStart = $_POST['week_start'] ?? ParentSummary::defaultWeekStart();
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekStart)) {
    $weekStart = ParentSummary::defaultWeekStart();
}

// Restrict to parents whose child belongs to this school
$db = Database::connect();
$stmt = $db->prepare("
    SELECT DISTINCT p.id AS parent_id
    FROM parents p
    JOIN student_parents sp ON sp.parent_id = p.id
    JOIN users u ON u.id = sp.student_user_id
    WHERE u.school_id = ?
      AND p.email IS NOT NULL AND p.email <> ''
      AND u.status = 'active' AND u.role = 'student'
");
$stmt->execute([$schoolId]);
$allowedParentIds = array_column($stmt->fetchAll(), 'parent_id');
$allowedParentIds = array_map('intval', $allowedParentIds);

if (!$allowedParentIds) {
    Session::flash('warning', 'No parents with email addresses were found in your school.');
    Helper::redirect('/school');
}

$parents = ParentSummary::groupedByParent();
$parents = array_filter($parents, fn($p) => in_array((int)$p['parent_id'], $allowedParentIds, true));

$sent = 0; $failed = 0; $skipped = 0; $notifyCount = 0;

foreach ($parents as $p) {
    $parentId = (int)$p['parent_id'];
    $parentEmail = $p['parent_email'];
    $parentName = $p['parent_name'];
    $studentIds = $p['student_ids'];

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

        if ($ok) { $sent++; $notifyCount += count($toSend); } else { $failed++; }
    } catch (\Throwable $e) {
        error_log('School parent summary failed for parent ' . $parentId . ': ' . $e->getMessage());
        foreach ($toSend as $s) {
            ParentSummary::recordSend($parentId, (int)$s['student']['id'], $parentEmail, $weekStart, false, $e->getMessage());
        }
        $failed++;
    }
}

Audit::log('parent_summaries.sent', 'school', $schoolId, [
    'week_start'     => $weekStart,
    'parents_sent'   => $sent,
    'parents_failed' => $failed,
    'students'       => $notifyCount,
]);

$msg = "Parent summaries sent: $sent parent(s) — covering $notifyCount student record(s).";
if ($failed > 0) $msg .= " Failed: $failed.";
if ($skipped > 0) $msg .= " Skipped (already sent): $skipped.";

Session::flash($failed > 0 ? 'warning' : 'success', $msg);
Helper::redirect('/school');
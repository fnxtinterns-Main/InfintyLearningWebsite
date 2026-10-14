<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth, Activity, Helper};

Session::start();
Auth::requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/activities');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) { Session::flash('error','Invalid request.'); Helper::redirect('/activities'); }

$id = (int)($_POST['activity_id'] ?? 0);
$notes = trim($_POST['notes'] ?? '');
$user = Auth::user();

$activity = Activity::find($id);
if (!$activity) { Session::flash('error','Activity not found.'); Helper::redirect('/activities'); }

if (Activity::register($id, (int)$user['id'], $notes)) {
    Session::flash('success','You have been registered!');
} else {
    Session::flash('error','You are already registered for this activity.');
}
Helper::redirect('/activity?slug=' . urlencode($activity['slug']));
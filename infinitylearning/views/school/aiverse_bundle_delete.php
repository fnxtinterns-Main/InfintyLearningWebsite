<?php
declare(strict_types=1);

use App\Auth;
use App\Aiverse;
use App\Helper;
use App\Session;

Session::start();
Auth::requireSchoolAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/school/bundles');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid security token.');
    Helper::redirect('/school/bundles');
}

$bundleId = (int)($_POST['bundle_id'] ?? 0);
if ($bundleId > 0) {
    try {
        Aiverse::deleteBundle($bundleId);
        Session::flash('info', 'Course Bundle deleted successfully.');
    } catch (\Throwable $e) {
        error_log('Aiverse bundle delete error: ' . $e->getMessage());
        Session::flash('error', 'Failed to delete bundle: ' . $e->getMessage());
    }
}

Helper::redirect('/school/bundles');

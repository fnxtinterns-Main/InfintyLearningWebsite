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

$bundleId = !empty($_POST['bundle_id']) ? (int)$_POST['bundle_id'] : null;

try {
    $id = Aiverse::saveBundle($_POST, $bundleId);
    Session::flash('success', $bundleId ? 'Course Bundle updated successfully.' : 'New Course Bundle published successfully!');
} catch (\Throwable $e) {
    error_log('Aiverse bundle save error: ' . $e->getMessage());
    Session::flash('error', 'Failed to save bundle: ' . $e->getMessage());
}

Helper::redirect('/school/bundles');

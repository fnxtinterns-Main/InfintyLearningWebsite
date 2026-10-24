<?php
declare(strict_types=1);

use App\Auth;
use App\Aiverse;
use App\Helper;
use App\Session;

Session::start();
Auth::requireSchoolAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/school/banners');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid security token.');
    Helper::redirect('/school/banners');
}

$bannerId = !empty($_POST['banner_id']) ? (int)$_POST['banner_id'] : null;

try {
    $id = Aiverse::saveBanner($_POST, $bannerId);
    Session::flash('success', $bannerId ? 'Banner updated successfully.' : 'New Promo Banner published successfully!');
} catch (\Throwable $e) {
    error_log('Aiverse banner save error: ' . $e->getMessage());
    Session::flash('error', 'Failed to save banner: ' . $e->getMessage());
}

Helper::redirect('/school/banners');

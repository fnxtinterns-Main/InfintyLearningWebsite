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

$bannerId = (int)($_POST['banner_id'] ?? 0);
$isActive = !empty($_POST['is_active']);

if ($bannerId > 0) {
    try {
        Aiverse::toggleBanner($bannerId, $isActive);
        Session::flash('success', $isActive ? 'Banner enabled and now visible on student dashboard.' : 'Banner disabled.');
    } catch (\Throwable $e) {
        error_log('Aiverse banner toggle error: ' . $e->getMessage());
        Session::flash('error', 'Failed to toggle banner: ' . $e->getMessage());
    }
}

Helper::redirect('/school/banners');

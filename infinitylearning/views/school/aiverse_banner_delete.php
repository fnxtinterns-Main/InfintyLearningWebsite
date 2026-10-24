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
if ($bannerId > 0) {
    try {
        Aiverse::deleteBanner($bannerId);
        Session::flash('info', 'Promo Banner deleted successfully.');
    } catch (\Throwable $e) {
        error_log('Aiverse banner delete error: ' . $e->getMessage());
        Session::flash('error', 'Failed to delete banner: ' . $e->getMessage());
    }
}

Helper::redirect('/school/banners');

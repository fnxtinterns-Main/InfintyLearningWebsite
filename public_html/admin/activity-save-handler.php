<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth, Activity, Uploader, Helper};

Session::start();
Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/admin/activities');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/admin/activities');
}

$user = Auth::user();
$id = (int)($_POST['id'] ?? 0);

$data = [
    'title'                 => trim($_POST['title'] ?? ''),
    'category'              => $_POST['category'] ?? 'creative',
    'short_description'     => trim($_POST['short_description'] ?? ''),
    'description'           => trim($_POST['description'] ?? ''),
    'event_date'            => $_POST['event_date'] ?? '',
    'start_time'            => $_POST['start_time'] ?? '',
    'end_time'              => $_POST['end_time'] ?? '',
    'venue'                 => trim($_POST['venue'] ?? ''),
    'organizer'             => trim($_POST['organizer'] ?? ''),
    'eligibility'           => trim($_POST['eligibility'] ?? ''),
    'registration_deadline' => $_POST['registration_deadline'] ?? '',
    'registration_url'      => trim($_POST['registration_url'] ?? ''),
    'fee'                   => $_POST['fee'] ?? 0,
    'max_participants'      => $_POST['max_participants'] ?? null,
    'contact_email'         => trim($_POST['contact_email'] ?? ''),
    'contact_phone'         => trim($_POST['contact_phone'] ?? ''),
    'tags'                  => trim($_POST['tags'] ?? ''),
    'featured'              => !empty($_POST['featured']) ? 1 : 0,
    'status'                => $_POST['status'] ?? 'published',
];

if ($data['title'] === '' || $data['category'] === '') {
    Session::flash('error', 'Title and category are required.');
    Helper::redirect($id ? "/admin/activities/edit?id=$id" : '/admin/activities/new');
}

try {
    if (!empty($_FILES['cover_image']['tmp_name'])) {
        $data['cover_image'] = Uploader::image($_FILES['cover_image'], 'activities');
    }

    if ($id) {
        Activity::update($id, $data);
        Session::flash('success', 'Activity updated.');
    } else {
        Activity::create($data, (int)$user['id']);
        Session::flash('success', 'Activity published.');
    }
} catch (\Throwable $e) {
    error_log('Activity save failed: ' . $e->getMessage());
    Session::flash('error', 'Save failed: ' . $e->getMessage());
}

Helper::redirect('/admin/activities');
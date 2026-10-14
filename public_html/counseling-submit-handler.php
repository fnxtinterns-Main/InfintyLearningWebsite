<?php
declare(strict_types=1);

require_once __DIR__ . '/../infinitylearning/config/constants.php';
require_once __DIR__ . '/../infinitylearning/src/autoload.php';

use App\{Session, Auth, Counseling, Helper, Validator, Notification, Audit};

Session::start();
Auth::requireLogin();

if (Auth::role() !== 'student') {
    Helper::redirect('/counseling');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/counseling');
}
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/counseling/apply');
}

$user    = Auth::user();
$student = Auth::findStudentByUserId((int)$user['id']);
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

// ---------- Collect ----------
$data = [
    'counseling_type'       => $_POST['counseling_type'] ?? '',
    'rating_concentration'  => (int)($_POST['rating_concentration'] ?? 0),
    'rating_cognitive'      => (int)($_POST['rating_cognitive'] ?? 0),
    'rating_comprehension'  => (int)($_POST['rating_comprehension'] ?? 0),
    'rating_creativity'     => (int)($_POST['rating_creativity'] ?? 0),
    'rating_confidence'     => (int)($_POST['rating_confidence'] ?? 0),
    'rating_communication'  => (int)($_POST['rating_communication'] ?? 0),
    'full_name'             => trim($_POST['full_name'] ?? ''),
    'email'                 => trim($_POST['email'] ?? ''),
    'phone'                 => trim($_POST['phone'] ?? ''),
    'current_class'         => trim($_POST['current_class'] ?? ''),
    'city'                  => trim($_POST['city'] ?? ''),
    'state'                 => trim($_POST['state'] ?? ''),
    'reason'                => trim($_POST['reason'] ?? ''),
    'preferred_time'        => trim($_POST['preferred_time'] ?? ''),
];

// ---------- Validate ----------
$v = new Validator();
$v->required('counseling_type', $data['counseling_type'], 'Counseling type')
  ->required('full_name', $data['full_name'], 'Full name');

if (!in_array($data['counseling_type'], array_keys(Counseling::types()), true)) {
    Session::flash('error', 'Invalid counseling type.');
    Helper::redirect('/counseling/apply');
}

foreach ([
    'rating_concentration','rating_cognitive','rating_comprehension',
    'rating_creativity','rating_confidence','rating_communication',
] as $r) {
    if ($data[$r] < 1 || $data[$r] > 6) {
        Session::flash('error', 'All ratings must be between 1 and 6.');
        Helper::redirect('/counseling/apply');
    }
}

if ($v->fails()) {
    Helper::flashOld($_POST);
    Session::flash('error', implode(' ', $v->errors()));
    Helper::redirect('/counseling/apply');
}

// ---------- Save ----------
try {
    $id = Counseling::create($data, (int)$user['id'], $schoolId);

    // Notify school admin
    if ($schoolId) {
        $db = \App\Database::connect();
        $stmt = $db->prepare("SELECT admin_user_id FROM schools WHERE id=? LIMIT 1");
        $stmt->execute([$schoolId]);
        $adminId = (int)($stmt->fetchColumn() ?: 0);
        if ($adminId) {
            Notification::push(
                $adminId,
                'counseling.request',
                '🧭 New counseling request',
                ($student['full_name'] ?? 'A student') . ' requested ' . Counseling::typeLabel($data['counseling_type']) . ' counseling.',
                '/school/counseling'
            );
        }
    }

    Session::flash('success', 'Your counseling request has been submitted. Your school admin will assign a counselor soon.');
    Helper::redirect('/counseling');
} catch (\Throwable $e) {
    error_log('Counseling submit failed: ' . $e->getMessage());
    Session::flash('error', 'Could not submit. Please try again.');
    Helper::redirect('/counseling/apply');
}
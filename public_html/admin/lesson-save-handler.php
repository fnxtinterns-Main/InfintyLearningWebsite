<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth, Lesson, Uploader, Helper, Batch};

Session::start();
Auth::requireLogin();
if (!in_array(Auth::role(), ['admin','teacher'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/admin/lessons');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/admin/lessons');
}

$me   = Auth::user();
$role = Auth::role();
$id   = (int)($_POST['id'] ?? 0);

// ---------- Scope ----------
$schoolId = null;
$batchId  = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;

if ($role === 'teacher') {
    // Teachers are always scoped to their own school (if any)
    $schoolId = !empty($me['school_id']) ? (int)$me['school_id'] : null;

    // Verify the batch (if chosen) belongs to the same school / teacher
    if ($batchId) {
        $batch = Batch::find($batchId);
        if (!$batch) {
            Session::flash('error', 'Selected batch not found.');
            Helper::redirect('/admin/lessons/new');
        }
        if (!empty($me['school_id']) && (int)($batch['school_id'] ?? 0) !== (int)$me['school_id']) {
            Session::flash('error', 'You can only target batches from your own school.');
            Helper::redirect('/admin/lessons/new');
        }
    }
} elseif (Auth::isSchoolAdmin()) {
    $schoolId = (int)$me['school_id'];
} else {
    // Super admin → scope from form (empty = global)
    $schoolId = !empty($_POST['school_id']) ? (int)$_POST['school_id'] : null;

    if ($batchId) {
        $batch = Batch::find($batchId);
        if (!$batch) {
            Session::flash('error', 'Selected batch not found.');
            Helper::redirect('/admin/lessons/new');
        }
    }
}

// ---------- Field validation ----------
$data = [
    'subject_id'        => (int)($_POST['subject_id'] ?? 0),
    'batch_id'          => $batchId,
    'school_id'         => $schoolId,
    'title'             => trim($_POST['title'] ?? ''),
    'summary'           => trim($_POST['summary'] ?? ''),
    'content'           => trim($_POST['content'] ?? ''),
    'video_url'         => trim($_POST['video_url'] ?? ''),
    'difficulty'        => $_POST['difficulty'] ?? 'medium',
    'estimated_minutes' => (int)($_POST['estimated_minutes'] ?? 15),
    'published'         => isset($_POST['published']) ? (int)$_POST['published'] : 1,
];

if ($data['subject_id'] <= 0 || $data['title'] === '') {
    Session::flash('error', 'Subject and title are required.');
    Helper::redirect($id ? "/admin/lessons/edit?id=$id" : '/admin/lessons/new');
}

// ---------- Handle uploads ----------
$uploadedAttachments = [];
$uploadErrors = [];
$keepAttachments = $_POST['keep_attachments'] ?? [];
if (!is_array($keepAttachments)) $keepAttachments = [];

try {
    if (!empty($_FILES['attachments'])) {
        $uploadedAttachments = Uploader::documents($_FILES['attachments'], 'lessons');
    }
} catch (\Throwable $e) {
    Session::flash('error', 'Upload failed: ' . $e->getMessage());
    Helper::redirect($id ? "/admin/lessons/edit?id=$id" : '/admin/lessons/new');
}

// ---------- Save ----------
try {
    if ($id) {
        // Ownership check for teachers
        $existing = Lesson::find($id);
        if (!$existing) {
            Session::flash('error', 'Lesson not found.');
            Helper::redirect('/admin/lessons');
        }
        if ($role === 'teacher' && (int)$existing['created_by'] !== (int)$me['id']) {
            Session::flash('error', 'You can only edit your own lessons.');
            Helper::redirect('/admin/lessons');
        }

        $data['attachments']       = $uploadedAttachments;
        $data['keep_attachments']  = $keepAttachments;
        Lesson::update($id, $data);

        Session::flash('success', 'Lesson updated successfully.');
    } else {
        $data['attachments'] = $uploadedAttachments;
        Lesson::create($data, (int)$me['id']);

        $countFiles = count($uploadedAttachments);
        $msg = 'Lesson published successfully.';
        if ($countFiles > 0) $msg .= " ($countFiles attachment" . ($countFiles > 1 ? 's' : '') . " uploaded)";
        Session::flash('success', $msg);
    }
} catch (\Throwable $e) {
    error_log('Lesson save failed: ' . $e->getMessage());
    Session::flash('error', 'Save failed: ' . $e->getMessage());
    Helper::redirect($id ? "/admin/lessons/edit?id=$id" : '/admin/lessons/new');
}

// ---------- Redirect ----------
if ($role === 'teacher') {
    Helper::redirect('/teacher/lessons');
} else {
    Helper::redirect('/admin/lessons');
}
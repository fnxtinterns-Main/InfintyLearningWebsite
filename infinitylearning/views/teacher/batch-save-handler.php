<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../src/autoload.php';

use App\{Session, Auth, Batch, Helper};

Session::start();
Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/teacher/batches');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/teacher/batches');
}

$id = (int)($_POST['id'] ?? 0);
$me = Auth::user();

// Normalize teacher assignment:
// - Admin can pass teacher_id explicitly.
// - Teacher always owns their batch.
$teacherId = $role === 'admin' && !empty($_POST['teacher_id'])
    ? (int)$_POST['teacher_id']
    : (int)$me['id'];

$data = [
    'name'        => trim($_POST['name'] ?? ''),
    'description' => trim($_POST['description'] ?? ''),
    'subject'     => trim($_POST['subject'] ?? ''),
    'class_level' => trim($_POST['class_level'] ?? ''),
    'teacher_id'  => $teacherId,
    'capacity'    => (int)($_POST['capacity'] ?? 50),
];

if ($data['name'] === '') {
    Session::flash('error', 'Batch name is required.');
    Helper::redirect($id ? "/teacher/batch/edit?id=$id" : '/teacher/batch/new');
}

try {
    if ($id) {
        // Ownership check for teachers
        $existing = Batch::find($id);
        if (!$existing) throw new \RuntimeException('Batch not found.');
        if ($role === 'teacher' && (int)$existing['teacher_id'] !== (int)$me['id']) {
            throw new \RuntimeException('You do not own this batch.');
        }
        Batch::update($id, $data);
        Session::flash('success', 'Batch updated.');
        Helper::redirect('/teacher/batch?id=' . $id);
    } else {
        $newId = Batch::create($data, (int)$me['id']);
        Session::flash('success', 'Batch created.');
        Helper::redirect('/teacher/batch?id=' . $newId);
    }
} catch (\Throwable $e) {
    error_log('Batch save failed: ' . $e->getMessage());
    Session::flash('error', 'Save failed: ' . $e->getMessage());
    Helper::redirect($id ? "/teacher/batch/edit?id=$id" : '/teacher/batch/new');
}
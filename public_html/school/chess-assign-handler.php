<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\Session;
use App\Auth;
use App\Helper;
use App\Database;
use App\Notification;

Session::start();
$schoolId = Auth::requireSchoolAdmin();
$me = Auth::user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/school/chess');
}

$studentUserId = (int)($_POST['student_user_id'] ?? 0);
$teacherUserId = (int)($_POST['teacher_user_id'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

if ($studentUserId <= 0 || $teacherUserId <= 0) {
    Session::flash('error', 'Please select both a student and a coach.');
    Helper::redirect('/school/chess');
}

$db = Database::connect();

try {
    // Delete any existing assignment for this student
    $db->prepare("DELETE FROM chess_coach_assignments WHERE student_user_id = ? AND school_id = ?")
       ->execute([$studentUserId, $schoolId]);

    // Insert new assignment
    $stmt = $db->prepare("
        INSERT INTO chess_coach_assignments (school_id, teacher_user_id, student_user_id, notes, assigned_by)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$schoolId, $teacherUserId, $studentUserId, $notes ?: null, (int)$me['id']]);

    // Notify Teacher
    Notification::push(
        $teacherUserId,
        'chess.student_assigned',
        '♟️ New Chess Mentee Assigned',
        'A student has been assigned to you for chess coaching and 1-on-1 sessions.',
        '/teacher/chess'
    );

    // Notify Student
    Notification::push(
        $studentUserId,
        'chess.coach_assigned',
        '♟️ Your Chess Coach Has Been Assigned!',
        'Your school assigned a mentor to help you advance in Rising Rooks Chess Academy.',
        '/chess'
    );

    Session::flash('success', 'Chess coach assigned successfully!');
} catch (\Throwable $e) {
    Session::flash('error', 'Assignment failed: ' . $e->getMessage());
}

Helper::redirect('/school/chess');

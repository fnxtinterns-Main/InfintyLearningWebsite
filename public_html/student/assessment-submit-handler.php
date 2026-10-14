<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, AcademicAssessment, Helper};

Session::start();
Auth::requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/student/assessments');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid submission token.');
    Helper::redirect('/student/assessments');
}

$user = Auth::user();
$studentId = (int)$user['id'];
$assessmentId = (int)($_POST['assessment_id'] ?? 0);
$answers = $_POST['answers'] ?? [];

try {
    $res = AcademicAssessment::submitAssessment($assessmentId, $studentId, $answers);
    if ($res['is_evaluated']) {
        Session::flash('success', "Assessment submitted and evaluated! Your score: {$res['score']}");
    } else {
        Session::flash('success', 'Assessment submitted successfully! Teacher will grade your descriptive answers soon.');
    }
} catch (\Throwable $e) {
    error_log('Assessment submit error: ' . $e->getMessage());
    Session::flash('error', 'Error submitting assessment: ' . $e->getMessage());
}

Helper::redirect('/student/assessment/take?id=' . $assessmentId);

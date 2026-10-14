<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, AcademicAssessment, QuestionBank, Helper};

Session::start();
Auth::requireRole('teacher');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/teacher/assessments');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request token.');
    Helper::redirect('/teacher/assessments');
}

$user = Auth::user();
$teacherId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

$questionIds = array_map('intval', $_POST['question_ids'] ?? []);
if (empty($questionIds)) {
    Session::flash('error', 'Please select at least 1 question for the assessment.');
    Helper::redirect('/teacher/assessment/new');
}

$questionsList = [];
foreach ($questionIds as $qid) {
    $q = QuestionBank::find($qid);
    if ($q) {
        $questionsList[] = $q;
    }
}

$_POST['questions'] = $questionsList;

try {
    $id = AcademicAssessment::create($_POST, $teacherId, $schoolId);
    Session::flash('success', 'Assessment published successfully!');
    Helper::redirect('/teacher/assessments');
} catch (\Throwable $e) {
    error_log('Assessment creation failed: ' . $e->getMessage());
    Session::flash('error', 'Failed to create assessment: ' . $e->getMessage());
    Helper::redirect('/teacher/assessment/new');
}

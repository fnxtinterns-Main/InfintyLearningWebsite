<?php
/**
 * Infinity Learning - BPSC TRE 4.0 Leaderboard API Handler
 * Handles real-time candidate merit ranking by District and State
 */

define('BPSC_PORTAL', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/UserManager.php';
require_once __DIR__ . '/src/LeaderboardManager.php';

header('Content-Type: application/json; charset=utf-8');

$currentUser = Auth::user();
$currentUserId = $currentUser['id'] ?? null;
$currentUserDistrict = $currentUser['district'] ?? 'Patna';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'record_test') {
        if (!$currentUserId) {
            echo json_encode([
                'success' => false,
                'message' => 'Candidate must be logged in to record assessment scores.'
            ]);
            exit;
        }

        $paperId = trim($_POST['paper_id'] ?? 'QP_TEST');
        $paperTitle = trim($_POST['paper_title'] ?? 'BPSC CBT Assessment');
        $score = (float)($_POST['score'] ?? 0.0);
        $totalMarks = (float)($_POST['total_marks'] ?? 150.0);
        $accuracy = (float)($_POST['accuracy'] ?? 0.0);

        $res = LeaderboardManager::recordTest($currentUserId, $paperId, $paperTitle, $score, $totalMarks, $accuracy);

        // Fetch updated rankings
        $leaderboard = LeaderboardManager::getLeaderboard('district', $currentUserDistrict, $currentUserId);
        $res['leaderboard'] = $leaderboard;

        echo json_encode($res);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unsupported POST action']);
    exit;
}

// GET Handler
$scope = trim($_GET['scope'] ?? 'district');
$district = trim($_GET['district'] ?? $currentUserDistrict);

if (empty($district) || $district === 'null') {
    $district = $currentUserDistrict;
}

$data = LeaderboardManager::getLeaderboard($scope, $district, $currentUserId);

echo json_encode([
    'success' => true,
    'data' => $data
]);
exit;


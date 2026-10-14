<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Timetable};

Session::start();
$user = Auth::user();
$schoolId = 0;
if (Auth::isSchoolAdmin()) {
    $schoolId = (int)Auth::requireSchoolAdmin();
} elseif ($user && Auth::role() === 'teacher' && !empty($user['school_id'])) {
    $schoolId = (int)$user['school_id'];
} else {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$day = strtolower(trim($_GET['day'] ?? 'monday'));
$period = (int)($_GET['period'] ?? 1);

try {
    $data = Timetable::getFreeTeachersForSlot($schoolId, $day, $period);
    echo json_encode([
        'success' => true,
        'day' => ucfirst($day),
        'period' => $period,
        'free' => $data['free'],
        'busy' => $data['busy']
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

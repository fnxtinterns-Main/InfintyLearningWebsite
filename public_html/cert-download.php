<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Certificate, Helper, Database};

Session::start();
Auth::requireLogin();

$id  = (int)($_GET['id'] ?? 0);
$me  = Auth::user();
$role = Auth::role();

if ($id <= 0) { http_response_code(400); exit('Missing certificate id.'); }

$db = Database::connect();
$stmt = $db->prepare("
    SELECT c.*, u.school_id AS student_school_id
    FROM certificates c
    JOIN users u ON u.id = c.student_user_id
    WHERE c.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$cert = $stmt->fetch();
if (!$cert) { http_response_code(404); exit('Certificate not found.'); }

// ---- Access control ----
$canDownload = false;
if (Auth::isSuperAdmin()) {
    $canDownload = true;
} elseif (Auth::isSchoolAdmin()) {
    $canDownload = (int)$cert['student_school_id'] === (int)$me['school_id'];
} elseif ($role === 'student') {
    $canDownload = (int)$cert['student_user_id'] === (int)$me['id'];
} elseif ($role === 'teacher') {
    // Teacher can view certs of students in their batches
    $q = $db->prepare("
        SELECT 1 FROM batch_students bs
        JOIN batches b ON b.id = bs.batch_id
        WHERE bs.student_user_id = ? AND b.teacher_id = ?
        LIMIT 1
    ");
    $q->execute([(int)$cert['student_user_id'], (int)$me['id']]);
    $canDownload = (bool)$q->fetch();
}

if (!$canDownload) { http_response_code(403); exit('Forbidden.'); }

$path = Certificate::resolveFile($cert['file_path'] ?? $cert['certificate_url'] ?? '');
if (!$path) { http_response_code(404); exit('Certificate file missing.'); }

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($path) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
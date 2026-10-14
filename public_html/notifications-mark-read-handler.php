<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Notification};

Session::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    Notification::markRead($id, (int)Auth::user()['id']);
}

header('Content-Type: application/json');
echo json_encode(['ok' => true]);
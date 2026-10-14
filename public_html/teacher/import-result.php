<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth};

Session::start();
Auth::requireLogin();
if (!in_array(Auth::role(), ['teacher','admin'], true)) { http_response_code(403); exit('Forbidden'); }

$name = basename($_SESSION['_import_result_file'] ?? '');
$path = UPLOAD_PATH . '/tmp/' . $name;
if (!$name || !is_file($path)) { http_response_code(404); exit('No result file.'); }

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $name . '"');
readfile($path);

// Clean up after download
@unlink($path);
unset($_SESSION['_import_result_file']);
exit;
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

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="infinity-student-import-template.csv"');

$out = fopen('php://output', 'w');

// BOM so Excel opens UTF-8 correctly
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, [
    'full_name','email','phone','password','gender','age',
    'school_name','school_board','current_class','academic_year',
    'city','state','interests',
    'guardian_name','guardian_relationship','guardian_phone','guardian_email',
    'guardian_occupation','guardian_city',
]);

// Sample row
fputcsv($out, [
    'Aarav Sharma','aarav.sharma@example.com','+919812345678','','male','12',
    'City Public School','CBSE','Grade 7','2025-2026',
    'Mumbai','Maharashtra','Maths, Chess',
    'Rakesh Sharma','father','+919812345679','rakesh@example.com',
    'Engineer','Mumbai',
]);

// Another sample — different gender / board
fputcsv($out, [
    'Priya Patel','priya.patel@example.com','+919812345680','','female','14',
    'St. Xaviers','ICSE','Grade 9','2025-2026',
    'Ahmedabad','Gujarat','Drawing, Singing',
    'Meena Patel','mother','+919812345681','',
    'Doctor','Ahmedabad',
]);

fclose($out);
exit;
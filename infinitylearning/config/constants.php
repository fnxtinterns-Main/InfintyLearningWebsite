<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/src');
define('VIEW_PATH', BASE_PATH . '/views');
define('PUBLIC_PATH', BASE_PATH . '/../public_html');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

define('APP_NAME', 'Infinity Learning');

define('ROLE_STUDENT', 'student');
define('ROLE_TEACHER', 'teacher');
define('ROLE_ADMIN',   'admin');

define('STATUS_PENDING',   'pending');
define('STATUS_ACTIVE',    'active');
define('STATUS_SUSPENDED', 'suspended');
define('STATUS_REJECTED',  'rejected');

define('ACTIVITY_CATEGORIES', [
    'creative'    => 'Creative',
    'academic'    => 'Academic',
    'sports'      => 'Sports',
    'cultural'    => 'Cultural',
    'technology'  => 'Technology',
    'competition' => 'Competition',
]);
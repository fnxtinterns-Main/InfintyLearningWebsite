<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/autoload.php';

use App\{Auth, Database};

$email = 'admin@infinitylearning.net';
$phone = '+919523176706';
$pass  = 'Admin@12345';

$db = Database::connect();
$exists = Auth::findByEmail($email);
if ($exists) { echo "Admin already exists (#{$exists['id']}). Delete this file."; exit; }

$userId = Auth::createUser([
    'email' => $email, 'phone' => $phone, 'password' => $pass,
    'role' => 'admin', 'status' => 'active',
]);
$db->prepare("UPDATE users SET phone_verified_at=NOW(), email_verified_at=NOW() WHERE id=?")->execute([$userId]);
echo "Created admin #$userId — email=$email / pass=$pass\n\nDELETE THIS FILE NOW.";
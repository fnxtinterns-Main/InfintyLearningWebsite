<?php
declare(strict_types=1);

define('APP_ROOT', __DIR__ . '/infinitylearning');
define('BASE_PATH', __DIR__ . '/infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\FutureNxt\Database;
use App\FutureNxt\EmailNotifier;
use App\FutureNxt\Employee;

echo "====================================================\n";
echo "FutureNxt Partner Onboarding Email Dispatcher\n";
echo "====================================================\n\n";

$db = Database::connect();
$stmt = $db->query("SELECT id, emp_code, full_name, email, role, designation FROM fnx_employees ORDER BY id ASC");
$partners = $stmt->fetchAll();

if (empty($partners)) {
    echo "No partner records found in fnx_employees.\n";
    exit(1);
}

echo "Found " . count($partners) . " partner accounts.\n";
echo "Synchronizing passwords and dispatching official onboarding emails...\n\n";

$defaultPass = 'Futurenxt@2026';
$defaultHash = password_hash($defaultPass, PASSWORD_DEFAULT);

$successCount = 0;
$failCount = 0;

foreach ($partners as $p) {
    $id = (int)$p['id'];
    $code = $p['emp_code'];
    $name = $p['full_name'];
    $email = $p['email'];
    $role = $p['role'];

    // Determine password to set & send
    if ($code === 'FNX-ADMIN' || $id === 1) {
        $sendPass = 'Inflearning@99';
        // Ensure Admin has valid password hash
        $adminHash = password_hash('Inflearning@99', PASSWORD_DEFAULT);
        $uStmt = $db->prepare("UPDATE fnx_employees SET password_hash = ? WHERE id = ?");
        $uStmt->execute([$adminHash, $id]);
    } else {
        $sendPass = $defaultPass;
        $uStmt = $db->prepare("UPDATE fnx_employees SET password_hash = ? WHERE id = ?");
        $uStmt->execute([$defaultHash, $id]);
    }

    echo "Processing [{$code}] {$name} ({$email})...\n";
    echo "  Password set to: {$sendPass}\n";

    $sent = EmailNotifier::sendPartnerOnboarding($id, $sendPass);

    if ($sent) {
        echo "  [SUCCESS] Onboarding email dispatched to {$email}\n\n";
        $successCount++;
    } else {
        echo "  [FAILED] Could not send email to {$email}\n\n";
        $failCount++;
    }
}

echo "----------------------------------------------------\n";
echo "Summary: {$successCount} sent successfully, {$failCount} failed.\n";
echo "====================================================\n";

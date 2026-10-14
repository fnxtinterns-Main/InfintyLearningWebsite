<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Database, Helper, Uploader};

Session::start();

try {
    $schoolId = Auth::requireSchoolAdmin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Helper::redirect('/school/branding');
    }

    if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
        Session::flash('error', 'Invalid token.');
        Helper::redirect('/school/branding');
    }

    $primaryColor = trim($_POST['brand_primary_color'] ?? '#0A192F');
    $secondaryColor = trim($_POST['brand_secondary_color'] ?? '#BE9420');
    $tagline = trim($_POST['brand_tagline'] ?? '');

    $crestLogo = trim($_POST['crest_logo'] ?? '');
    if (!empty($_FILES['crest_file']['tmp_name'])) {
        try {
            $uploadedCrest = Uploader::image($_FILES['crest_file'], 'branding');
            if ($uploadedCrest) {
                $crestLogo = $uploadedCrest;
            }
        } catch (\Throwable $e) {
            error_log('Crest upload error: ' . $e->getMessage());
        }
    }

    $seal = trim($_POST['official_seal'] ?? '');
    if (!empty($_FILES['seal_file']['tmp_name'])) {
        try {
            $uploadedSeal = Uploader::image($_FILES['seal_file'], 'branding');
            if ($uploadedSeal) {
                $seal = $uploadedSeal;
            }
        } catch (\Throwable $e) {
            error_log('Seal upload error: ' . $e->getMessage());
        }
    }

    $signature = trim($_POST['principal_signature'] ?? '');
    if (!empty($_FILES['signature_file']['tmp_name'])) {
        try {
            $uploadedSig = Uploader::image($_FILES['signature_file'], 'branding');
            if ($uploadedSig) {
                $signature = $uploadedSig;
            }
        } catch (\Throwable $e) {
            error_log('Signature upload error: ' . $e->getMessage());
        }
    }

    $db = Database::connect();
    // Ensure columns exist
    try {
        $db->exec("
            ALTER TABLE `schools`
              ADD COLUMN IF NOT EXISTS `brand_primary_color` varchar(20) DEFAULT '#0A192F',
              ADD COLUMN IF NOT EXISTS `brand_secondary_color` varchar(20) DEFAULT '#BE9420',
              ADD COLUMN IF NOT EXISTS `brand_tagline` varchar(255) DEFAULT NULL,
              ADD COLUMN IF NOT EXISTS `crest_logo` varchar(500) DEFAULT NULL,
              ADD COLUMN IF NOT EXISTS `principal_signature` varchar(500) DEFAULT NULL,
              ADD COLUMN IF NOT EXISTS `official_seal` varchar(500) DEFAULT NULL;
        ");
    } catch (\Throwable $e) {}

    $stmt = $db->prepare("
        UPDATE schools
        SET brand_primary_color = ?,
            brand_secondary_color = ?,
            brand_tagline = ?,
            crest_logo = ?,
            official_seal = ?,
            principal_signature = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $primaryColor,
        $secondaryColor,
        $tagline ?: null,
        $crestLogo ?: null,
        $seal ?: null,
        $signature ?: null,
        $schoolId
    ]);

    Helper::redirect('/school/branding?saved=1');
} catch (\Throwable $e) {
    error_log('Branding save error: ' . $e->getMessage());
    http_response_code(500);
    exit('Error updating branding: ' . htmlspecialchars($e->getMessage()));
}

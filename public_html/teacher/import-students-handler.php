<?php
declare(strict_types=1);

// Find the app folder regardless of depth
$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'   // subfolder (admin/, teacher/, school/)
    : __DIR__ . '/../infinitylearning';     // public_html/ root

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Batch, Helper, Database, Audit, Importer};

Session::start();
Auth::requireLogin();
$role = Auth::role();
if (!in_array($role, ['teacher','admin'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/teacher/batches');
}
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/teacher/batches');
}

$stage   = $_POST['stage'] ?? 'preview';
$batchId = (int)($_POST['batch_id'] ?? 0);

if (!$batchId) {
    Session::flash('error', 'Please select a batch.');
    Helper::redirect('/teacher/batch/import');
}

$batch = Batch::find($batchId);
if (!$batch) {
    Session::flash('error', 'Batch not found.');
    Helper::redirect('/teacher/batches');
}

if ($role === 'teacher' && (int)$batch['teacher_id'] !== (int)Auth::user()['id']) {
    Session::flash('error', 'You do not own this batch.');
    Helper::redirect('/teacher/batches');
}

// =============================================================
// STAGE 1 — PREVIEW: parse CSV, validate, stash in session
// =============================================================
if ($stage === 'preview') {
    if (empty($_FILES['csv']['tmp_name']) || ($_FILES['csv']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        Session::flash('error', 'Please choose a valid CSV file.');
        Helper::redirect('/teacher/batch/import?batch_id=' . $batchId);
    }

    $file = $_FILES['csv'];
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        Session::flash('error', 'CSV file too large (max 2MB).');
        Helper::redirect('/teacher/batch/import?batch_id=' . $batchId);
    }

    // Save to a temp file
    $tempName = 'import_' . bin2hex(random_bytes(8)) . '.csv';
    $tmpDir   = UPLOAD_PATH . '/tmp';
    if (!is_dir($tmpDir)) {
        @mkdir($tmpDir, 0755, true);
    }
    $tempPath = $tmpDir . '/' . $tempName;

    if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
        Session::flash('error', 'Could not save uploaded file.');
        Helper::redirect('/teacher/batch/import?batch_id=' . $batchId);
    }

    // Parse + validate
    $parsed = Importer::parseAndValidateCsv($tempPath, $batchId);

    $_SESSION['_import_preview'] = [
        'batch_id'  => $batchId,
        'temp_file' => $tempName,
        'rows'      => $parsed['rows'],
        'summary'   => $parsed['summary'],
    ];

    Helper::redirect('/teacher/batch/import?batch_id=' . $batchId);
}

// =============================================================
// STAGE 2 — COMMIT: create users + enroll
// =============================================================
if ($stage === 'commit') {
    $tempName = basename((string)($_POST['temp_file'] ?? ''));
    $tempPath = UPLOAD_PATH . '/tmp/' . $tempName;

    if (!is_file($tempPath)) {
        Session::flash('error', 'Temporary file expired. Please re-upload.');
        Helper::redirect('/teacher/batch/import?batch_id=' . $batchId);
    }

    $parsed = Importer::parseAndValidateCsv($tempPath, $batchId);
    $db     = Database::connect();

    $created  = 0;
    $enrolled = 0;
    $skipped  = 0;
    $results  = [];   // for the downloadable results CSV

    foreach ($parsed['rows'] as $row) {
        if (!in_array($row['status'], ['new', 'existing'], true)) {
            $skipped++;
            continue;
        }

        $d = $row['data'];

        try {
            $db->beginTransaction();

            $userId = 0;

            if ($row['status'] === 'new') {
                $plainPassword = $d['password'] !== ''
                    ? $d['password']
                    : Importer::generatePassword();

                $userId = Auth::createUser([
                    'email'    => $d['email'],
                    'phone'    => $d['phone'],
                    'password' => $plainPassword,
                    'role'     => 'student',
                    'status'   => 'active',
                ]);

                $db->prepare("
                    INSERT INTO students
                        (user_id, full_name, gender, age, school_name, school_board,
                         current_class, academic_year, city, state, interests)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?)
                ")->execute([
                    $userId,
                    $d['full_name'],
                    $d['gender'] !== '' ? $d['gender'] : null,
                    $d['age'] !== '' ? (int)$d['age'] : null,
                    $d['school_name']     ?: null,
                    $d['school_board']    ?: null,
                    $d['current_class']   ?: null,
                    $d['academic_year']   ?: null,
                    $d['city']            ?: null,
                    $d['state']           ?: null,
                    $d['interests']       ?: null,
                ]);

                // Parent record (only if we have a name + phone)
                if ($d['guardian_name'] !== '' && $d['guardian_phone'] !== '') {
                    $db->prepare("
                        INSERT INTO parents
                            (full_name, email, phone, relationship, occupation, city, state)
                        VALUES (?,?,?,?,?,?,?)
                    ")->execute([
                        $d['guardian_name'],
                        $d['guardian_email']     ?: null,
                        $d['guardian_phone'],
                        $d['guardian_relationship'] ?: 'guardian',
                        $d['guardian_occupation'] ?: null,
                        $d['guardian_city']       ?: null,
                        $d['state']               ?: null,
                    ]);
                    $parentId = (int)$db->lastInsertId();
                    $db->prepare("
                        INSERT INTO student_parents (student_user_id, parent_id, is_primary)
                        VALUES (?, ?, 1)
                    ")->execute([$userId, $parentId]);
                }

                $created++;
                $results[] = [
                    'row'       => $row['row'],
                    'full_name' => $d['full_name'],
                    'email'     => $d['email'],
                    'phone'     => $d['phone'],
                    'password'  => $plainPassword,
                    'status'    => 'created',
                ];
            } else {
                // existing user
                $userId = (int)($row['existing_user_id'] ?? 0);
                $results[] = [
                    'row'       => $row['row'],
                    'full_name' => $d['full_name'],
                    'email'     => $d['email'],
                    'phone'     => $d['phone'],
                    'password'  => '(existing — unchanged)',
                    'status'    => 'enrolled',
                ];
            }

            if ($userId > 0 && Batch::addStudent($batchId, $userId)) {
                $enrolled++;
            }

            $db->commit();

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $skipped++;
            $results[] = [
                'row'       => $row['row'],
                'full_name' => $d['full_name'] ?? '',
                'email'     => $d['email'] ?? '',
                'phone'     => $d['phone'] ?? '',
                'password'  => '',
                'status'    => 'failed: ' . $e->getMessage(),
            ];
            error_log('Import row failed: ' . $e->getMessage());
        }
    }

    // Write results CSV for download
    $resultName = 'import_result_' . date('Ymd_His') . '.csv';
    $resultPath = UPLOAD_PATH . '/tmp/' . $resultName;
    $out = fopen($resultPath, 'w');
    if ($out) {
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        fputcsv($out, ['row','full_name','email','phone','password','status']);
        foreach ($results as $r) {
            fputcsv($out, $r);
        }
        fclose($out);
    }

    // Clean up the source CSV
    @unlink($tempPath);

    Audit::log('batch.import', 'batch', $batchId, [
        'created'  => $created,
        'enrolled' => $enrolled,
        'skipped'  => $skipped,
    ]);

    Session::flash(
        'success',
        "Import finished — $created new account(s) created, $enrolled enrolled, $skipped skipped. " .
        "Download the results file to see auto-generated passwords."
    );

    $_SESSION['_import_result_file'] = $resultName;
    Helper::redirect('/teacher/batch?id=' . $batchId);
}

// Fallback
Helper::redirect('/teacher/batch/import?batch_id=' . $batchId);
<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Helper, Database, Validator, Audit, Uploader};

Session::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/profile');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/profile');
}

$loggedInUser = Auth::user();
$loggedInRole = $loggedInUser['role'];

// Determine target user to update
$targetUserId = !empty($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : (int)$loggedInUser['id'];
$isManagingStudent = false;

if ($targetUserId !== (int)$loggedInUser['id']) {
    if (!in_array($loggedInRole, ['admin', 'school'], true)) {
        Session::flash('error', 'Unauthorized action.');
        Helper::redirect('/profile');
    }
    $targetUser = Auth::findById($targetUserId);
    if (!$targetUser || ($loggedInRole === 'school' && (int)$targetUser['school_id'] !== (int)$loggedInUser['school_id'])) {
        Session::flash('error', 'Student not found or unauthorized.');
        Helper::redirect('/profile');
    }
    $user = $targetUser;
    $role = $targetUser['role'];
    $isManagingStudent = ($role === 'student');
} else {
    $user = $loggedInUser;
    $role = $loggedInRole;
}

$redirectUrl = $isManagingStudent ? "/profile?user_id={$user['id']}" : '/profile';

// ---------- Validate ----------
$v = new Validator();
$v->required('full_name', $_POST['full_name'] ?? '', 'Full name');
if ($v->fails()) {
    Session::flash('error', implode(' ', $v->errors()));
    Helper::redirect($redirectUrl);
}

$db = Database::connect();
\App\School::ensureSchema();

// ---------- Handle optional image & signature uploads ----------
$imagePath = null;
$sigPath = null;
try {
    if (!empty($_FILES['profile_image']['tmp_name'])) {
        $imagePath = Uploader::image($_FILES['profile_image'], 'profiles');
    }
    if (!empty($_FILES['signature_image']['tmp_name'])) {
        $sigPath = Uploader::image($_FILES['signature_image'], 'signatures');
    }
    if (!empty($_FILES['principal_signature']['tmp_name'])) {
        $sigPath = Uploader::image($_FILES['principal_signature'], 'signatures');
    }
} catch (\Throwable $e) {
    Session::flash('error', $e->getMessage());
    Helper::redirect($redirectUrl);
}

// ---------- Common normalized inputs ----------
$gender = $_POST['gender'] ?? '';
if (!in_array($gender, ['male','female','other','prefer_not_to_say',''], true)) $gender = '';

$fullName  = trim($_POST['full_name']);
$schoolName = trim($_POST['school_name'] ?? '') ?: null;
$city       = trim($_POST['city'] ?? '') ?: null;
$state      = trim($_POST['state'] ?? '') ?: null;

try {
    $db->beginTransaction();

    if ($role === 'student') {
        // ---- Update students ----
        $sql = "UPDATE students SET
                    full_name = ?, gender = ?, age = ?, school_name = ?,
                    school_board = ?, current_class = ?, academic_year = ?,
                    registration_number = ?, roll_number = ?,
                    city = ?, state = ?, interests = ?"
             . ($imagePath ? ", profile_image = ?" : "")
             . " WHERE user_id = ?";

        $params = [
            $fullName,
            $gender !== '' ? $gender : null,
            ($_POST['age'] ?? '') !== '' ? (int)$_POST['age'] : null,
            $schoolName,
            trim($_POST['school_board'] ?? '') ?: null,
            trim($_POST['current_class'] ?? '') ?: null,
            trim($_POST['academic_year'] ?? '') ?: null,
            trim($_POST['registration_number'] ?? '') ?: null,
            trim($_POST['roll_number'] ?? '') ?: null,
            $city,
            $state,
            trim($_POST['interests'] ?? '') ?: null,
        ];
        if ($imagePath) $params[] = $imagePath;
        $params[] = $user['id'];
        $db->prepare($sql)->execute($params);

        // ---- Update (or create) parent record ----
        $gName = trim($_POST['guardian_name'] ?? '');
        if ($gName !== '') {
            $q = $db->prepare("
                SELECT p.id FROM parents p
                JOIN student_parents sp ON sp.parent_id = p.id
                WHERE sp.student_user_id = ? AND sp.is_primary = 1
                LIMIT 1
            ");
            $q->execute([(int)$user['id']]);
            $existing = $q->fetch();

            $parentData = [
                'full_name'    => $gName,
                'email'        => trim($_POST['guardian_email'] ?? '') ?: null,
                'phone'        => Helper::normalizePhone($_POST['guardian_phone'] ?? '') ?: null,
                'relationship' => in_array($_POST['guardian_relationship'] ?? '', ['father','mother','guardian','other'], true)
                                    ? $_POST['guardian_relationship'] : 'guardian',
                'occupation'   => trim($_POST['guardian_occupation'] ?? '') ?: null,
                'city'         => trim($_POST['guardian_city'] ?? '') ?: null,
                'state'        => $state,
            ];

            if ($existing) {
                $db->prepare("
                    UPDATE parents SET full_name=?, email=?, phone=?, relationship=?,
                                       occupation=?, city=?, state=?
                    WHERE id=?
                ")->execute([
                    $parentData['full_name'], $parentData['email'], $parentData['phone'],
                    $parentData['relationship'], $parentData['occupation'],
                    $parentData['city'], $parentData['state'], $existing['id'],
                ]);
            } else {
                $db->prepare("
                    INSERT INTO parents (full_name, email, phone, relationship, occupation, city, state)
                    VALUES (?,?,?,?,?,?,?)
                ")->execute([
                    $parentData['full_name'], $parentData['email'], $parentData['phone'],
                    $parentData['relationship'], $parentData['occupation'],
                    $parentData['city'], $parentData['state'],
                ]);
                $parentId = (int)$db->lastInsertId();
                $db->prepare("
                    INSERT INTO student_parents (student_user_id, parent_id, is_primary)
                    VALUES (?, ?, 1)
                ")->execute([(int)$user['id'], $parentId]);
            }
        }

    } elseif ($role === 'teacher') {
        // ---- Update teachers ----
        $sql = "UPDATE teachers SET
                    full_name = ?, gender = ?, school_name = ?,
                    city = ?, state = ?, expert_field = ?, years_experience = ?,
                    additional_expertise = ?, bio = ?"
             . ($imagePath ? ", profile_image = ?" : "")
             . ($sigPath ? ", signature_image = ?" : "")
             . " WHERE user_id = ?";

        $params = [
            $fullName,
            $gender !== '' ? $gender : null,
            $schoolName,
            $city,
            $state,
            trim($_POST['expert_field'] ?? '') ?: null,
            ($_POST['years_experience'] ?? '') !== '' ? (int)$_POST['years_experience'] : null,
            trim($_POST['additional_expertise'] ?? '') ?: null,
            trim($_POST['bio'] ?? '') ?: null,
        ];
        if ($imagePath) $params[] = $imagePath;
        if ($sigPath)   $params[] = $sigPath;
        $params[] = $user['id'];
        $db->prepare($sql)->execute($params);

    } elseif ($role === 'school') {
        // ---- Update school principal info ----
        $schoolId = (int)($user['school_id'] ?? 0);
        if ($schoolId > 0) {
            $princName = trim($_POST['principal_name'] ?? '');
            $sql = "UPDATE schools SET name = COALESCE(NULLIF(?, ''), name)";
            $params = [$fullName];
            if ($princName !== '') {
                $sql .= ", principal_name = ?";
                $params[] = $princName;
            }
            if ($sigPath) {
                $sql .= ", principal_signature = ?";
                $params[] = $sigPath;
            }
            $sql .= " WHERE id = ?";
            $params[] = $schoolId;
            $db->prepare($sql)->execute($params);
        }
    }

    $db->commit();
    Audit::log('profile.updated', 'user', (int)$user['id']);
    Session::flash('success', 'Profile updated successfully.');
    Helper::redirect($redirectUrl);

} catch (\Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Profile update failed: ' . $e->getMessage());
    Session::flash('error', 'Could not save profile: ' . $e->getMessage());
    Helper::redirect($redirectUrl);
}
<?php
declare(strict_types=1);
$appRoot = is_dir(__DIR__ . '/../infinitylearning')
    ? __DIR__ . '/../infinitylearning'
    : (is_dir(__DIR__ . '/infinitylearning') ? __DIR__ . '/infinitylearning' : dirname(__DIR__) . '/infinitylearning');
if (!defined('APP_ROOT')) define('APP_ROOT', $appRoot);

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, OTP, Auth, SMS, Mailer, Helper, RateLimiter, Audit, Database, School};

Session::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/verify-otp');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/verify-otp');
}

$action = $_POST['action'] ?? '';
$phone  = $_SESSION['_pending_phone'] ?? '';
$role   = $_SESSION['_pending_role'] ?? '';
$data   = $_SESSION['_pending_registration'] ?? [];

if (!$phone || !$data) Helper::redirect('/register');

// ---------------- RESEND ----------------
if ($action === 'resend') {
    if (!OTP::canResend($phone, 'registration')) {
        Session::flash('error', 'Please wait before requesting a new OTP.');
        Helper::redirect('/verify-otp');
    }
    if (!RateLimiter::hit($phone, 'otp_resend', 50, 3600)) {
        Session::flash('error', 'Too many resend attempts.');
        Helper::redirect('/verify-otp');
    }
    $otp = OTP::generate(6);
    OTP::create($phone, 'registration', $otp);
    SMS::sendOTP($phone, $otp);
    if (!empty($data['email'])) {
        Mailer::send(
            $data['email'],
            'Your Infinity Learning verification code',
            Mailer::otpEmail($data['full_name'] ?? 'there', $otp, 'registration')
        );
    }
    Session::flash('success', 'A new OTP has been sent to your phone and email.');
    Helper::redirect('/verify-otp');
}

// ---------------- VERIFY ----------------
if ($action === 'verify') {
    if (!RateLimiter::hit($phone, 'otp_verify', 50, 900)) {
        Session::flash('error', 'Too many attempts. Try again later.');
        Helper::redirect('/verify-otp');
    }

    $otp = trim($_POST['otp'] ?? '');
    if (!preg_match('/^\d{6}$/', $otp)) {
        Session::flash('error', 'Invalid OTP format.');
        Helper::redirect('/verify-otp');
    }

    if (!OTP::verifyAndConsume($phone, 'registration', $otp)) {
        Session::flash('error', 'Invalid or expired OTP.');
        Helper::redirect('/verify-otp');
    }

    $db = Database::connect();
    
    // Ensure any database schema migrations run OUTSIDE the transaction
    // to prevent MySQL from triggering an implicit commit on DDL statements
    School::ensureSchema();

    try {
        $db->beginTransaction();

        $schoolId  = (int)($data['school_id'] ?? 0);
        $isAiverse = !empty($_SESSION['_pending_is_aiverse']) || !empty($data['is_aiverse']) || School::isAiverse($schoolId);
        $status    = $isAiverse ? 'active' : 'pending';

        $userId = Auth::createUser([
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'password'  => $data['password'],
            'role'      => $role,
            'school_id' => $schoolId,
            'status'    => $status,
        ]);

        if ($role === 'student') {
            // Check student table columns dynamically
            $studentCols = $db->query("SHOW COLUMNS FROM students")->fetchAll(\PDO::FETCH_COLUMN);
            $hasDistrict = in_array('district', $studentCols, true);
            $hasPincode  = in_array('pincode', $studentCols, true);
            $hasRegNo    = in_array('registration_number', $studentCols, true);

            $cols = ['user_id', 'full_name', 'gender', 'age', 'school_name', 'school_board', 'current_class', 'academic_year'];
            $vals = [
                $userId,
                $data['full_name'],
                $data['gender'] !== '' ? $data['gender'] : null,
                $data['age'] !== null && $data['age'] !== '' ? (int)$data['age'] : null,
                $isAiverse ? 'AIVERSE' : null,
                $data['school_board'] ?: ($isAiverse ? 'AI Academy' : null),
                $data['current_class'] ?: ($isAiverse ? 'AIverse Cohort 2026' : null),
                $data['academic_year'] ?: ($isAiverse ? '2026-2027' : null),
            ];

            if ($hasRegNo) {
                $cols[] = 'registration_number';
                $vals[] = $data['registration_number'] ?? null;
            }

            if ($hasDistrict && $hasPincode) {
                $cols = array_merge($cols, ['city', 'district', 'pincode', 'state', 'interests']);
                $vals = array_merge($vals, [
                    $data['city'] ?: null,
                    $data['district'] ?? null,
                    $data['pincode'] ?? null,
                    $data['state'] ?: null,
                    $data['interests'] ?: ($isAiverse ? 'Artificial Intelligence, Machine Learning' : null),
                ]);
            } else {
                $cityCombined = trim(($data['city'] ?? '') . (!empty($data['district']) ? ' (Dist: ' . $data['district'] . ')' : '') . (!empty($data['pincode']) ? ' PIN: ' . $data['pincode'] : ''));
                $cols = array_merge($cols, ['city', 'state', 'interests']);
                $vals = array_merge($vals, [
                    $cityCombined ?: null,
                    $data['state'] ?: null,
                    $data['interests'] ?: ($isAiverse ? 'Artificial Intelligence, Machine Learning' : null),
                ]);
            }

            $colList = implode(', ', $cols);
            $placeholders = implode(', ', array_fill(0, count($cols), '?'));
            $stmt = $db->prepare("INSERT INTO students ({$colList}) VALUES ({$placeholders})");
            $stmt->execute($vals);

            if (!$isAiverse && !empty($data['guardian_name']) && !empty($data['guardian_phone'])) {
                $db->prepare("
                    INSERT INTO parents (full_name, email, phone, relationship, occupation, city, state)
                    VALUES (?,?,?,?,?,?,?)
                ")->execute([
                    $data['guardian_name'], $data['guardian_email'] ?: null,
                    $data['guardian_phone'], $data['guardian_relationship'] ?: 'guardian',
                    $data['guardian_occupation'] ?: null, $data['guardian_city'] ?: null,
                    $data['state'] ?: null,
                ]);
                $parentId = (int)$db->lastInsertId();
                $db->prepare("
                    INSERT INTO student_parents (student_user_id, parent_id, is_primary)
                    VALUES (?, ?, 1)
                ")->execute([$userId, $parentId]);
            }
        } else {
            $teacherCols = $db->query("SHOW COLUMNS FROM teachers")->fetchAll(\PDO::FETCH_COLUMN);
            $hasTeacherCode = in_array('teacher_code', $teacherCols, true);
            $hasTeacherReg  = in_array('registration_number', $teacherCols, true);

            $schoolName = $_SESSION['_pending_school_name'] ?? null;
            $teacherCode = School::generateTeacherCode($schoolName);

            $tCols = ['user_id', 'full_name', 'gender', 'school_name', 'city', 'state', 'expert_field', 'years_experience', 'additional_expertise', 'bio'];
            $tVals = [
                $userId,
                $data['full_name'],
                $data['gender'] !== '' ? $data['gender'] : null,
                $schoolName,
                $data['city'] ?: null,
                $data['state'] ?: null,
                $data['expert_field'] ?: null,
                $data['years_experience'] !== null && $data['years_experience'] !== '' ? (int)$data['years_experience'] : null,
                $data['additional_expertise'] ?: null,
                $data['bio'] ?: null,
            ];

            if ($hasTeacherCode) {
                $tCols[] = 'teacher_code';
                $tVals[] = $teacherCode;
            }
            if ($hasTeacherReg) {
                $tCols[] = 'registration_number';
                $tVals[] = $data['registration_number'] ?? null;
            }

            $tColList = implode(', ', $tCols);
            $tPlaceholders = implode(', ', array_fill(0, count($tCols), '?'));
            $stmt = $db->prepare("INSERT INTO teachers ({$tColList}) VALUES ({$tPlaceholders})");
            $stmt->execute($tVals);
        }

        $db->commit();

        Audit::log('registration.completed', 'user', $userId, ['role' => $role, 'school_id' => $schoolId, 'is_aiverse' => $isAiverse]);

        unset(
            $_SESSION['_pending_registration'],
            $_SESSION['_pending_phone'],
            $_SESSION['_pending_role'],
            $_SESSION['_pending_school_id'],
            $_SESSION['_pending_school_name'],
            $_SESSION['_pending_is_aiverse']
        );

        if ($isAiverse) {
            Session::flash('success', 'Registration successful! Welcome to AIVERSE. Please log in to explore your AI courses.');
        } else {
            Session::flash('success', 'Registration successful! Your school admin will review and approve your account.');
        }
        Helper::redirect('/login');

    } catch (\Throwable $e) {
        if ($db && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log('Registration failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        Session::flash('error', 'Registration error: ' . $e->getMessage());
        Helper::redirect('/verify-otp');
    }
}
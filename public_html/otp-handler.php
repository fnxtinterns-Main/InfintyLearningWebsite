<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, OTP, Auth, SMS, Mailer, Helper, RateLimiter, Audit, Database};

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
    if (!RateLimiter::hit($phone, 'otp_resend', 3, 3600)) {
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
    if (!RateLimiter::hit($phone, 'otp_verify', 10, 900)) {
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
    try {
        $db->beginTransaction();

        $schoolId = (int)($data['school_id'] ?? 0);
        $status   = 'pending';

        $userId = Auth::createUser([
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'password'  => $data['password'],
            'role'      => $role,
            'school_id' => $schoolId,
            'status'    => $status,
        ]);

        if ($role === 'student') {
            $stmt = $db->prepare("
                INSERT INTO students
                    (user_id, full_name, gender, age, school_name, school_board,
                     current_class, academic_year, city, state, interests)
                VALUES (?,?,?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $userId, $data['full_name'],
                $data['gender'] !== '' ? $data['gender'] : null,
                $data['age'] !== null && $data['age'] !== '' ? (int)$data['age'] : null,
                null, $data['school_board'] ?: null, $data['current_class'] ?: null,
                $data['academic_year'] ?: null, $data['city'] ?: null,
                $data['state'] ?: null, $data['interests'] ?: null,
            ]);

            if (!empty($data['guardian_name']) && !empty($data['guardian_phone'])) {
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
            $stmt = $db->prepare("
                INSERT INTO teachers
                    (user_id, full_name, gender, school_name, city, state,
                     expert_field, years_experience, additional_expertise, bio)
                VALUES (?,?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $userId, $data['full_name'],
                $data['gender'] !== '' ? $data['gender'] : null,
                null, $data['city'] ?: null, $data['state'] ?: null,
                $data['expert_field'] ?: null,
                $data['years_experience'] !== null && $data['years_experience'] !== ''
                    ? (int)$data['years_experience'] : null,
                $data['additional_expertise'] ?: null,
                $data['bio'] ?: null,
            ]);
        }

        $db->commit();

        Audit::log('registration.completed', 'user', $userId, ['role' => $role, 'school_id' => $schoolId]);

        unset(
            $_SESSION['_pending_registration'],
            $_SESSION['_pending_phone'],
            $_SESSION['_pending_role'],
            $_SESSION['_pending_school_id'],
            $_SESSION['_pending_school_name']
        );

        Session::flash('success', 'Registration successful! Your school admin will review and approve your account.');
        Helper::redirect('/login');

    } catch (\Throwable $e) {
        $db->rollBack();
        error_log('Registration failed: ' . $e->getMessage());
        Session::flash('error', 'Registration failed. Please try again.');
        Helper::redirect('/verify-otp');
    }
}
<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Validator, Auth, OTP, SMS, Mailer, Helper, RateLimiter, Audit, Database, School};

Session::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/register');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request. Please try again.');
    Helper::redirect('/register');
}

$ip = Helper::clientIp();
if (!RateLimiter::hit($ip, 'register', 50, 3600)) {
    Session::flash('error', 'Too many registration attempts. Please try later.');
    Helper::redirect('/register');
}

$role = $_POST['role'] ?? 'student';
if (!in_array($role, ['student','teacher'], true)) {
    Session::flash('error', 'Invalid role.');
    Helper::redirect('/register');
}

$data = [
    'role'                  => $role,
    'school_id'             => (int)($_POST['school_id'] ?? 0),
    'full_name'             => trim($_POST['full_name'] ?? ''),
    'email'                 => strtolower(trim($_POST['email'] ?? '')),
    'phone'                 => Helper::normalizePhone($_POST['phone'] ?? ''),
    'password'              => $_POST['password'] ?? '',
    'password_confirm'      => $_POST['password_confirm'] ?? '',
    'gender'                => $_POST['gender'] ?? '',
    'age'                   => $_POST['age'] ?? null,
    'city'                  => trim($_POST['city'] ?? ''),
    'state'                 => trim($_POST['state'] ?? ''),
    'district'              => trim($_POST['district'] ?? ''),
    'pincode'               => trim($_POST['pincode'] ?? ''),
    'school_board'          => trim($_POST['school_board'] ?? ''),
    'academic_year'         => trim($_POST['academic_year'] ?? ''),
    'current_class'         => trim($_POST['current_class'] ?? ''),
    'interests'             => trim($_POST['interests'] ?? ''),
    'guardian_name'         => trim($_POST['guardian_name'] ?? ''),
    'guardian_relationship' => $_POST['guardian_relationship'] ?? 'guardian',
    'guardian_phone'        => Helper::normalizePhone($_POST['guardian_phone'] ?? ''),
    'guardian_email'        => strtolower(trim($_POST['guardian_email'] ?? '')),
    'guardian_occupation'   => trim($_POST['guardian_occupation'] ?? ''),
    'guardian_city'         => trim($_POST['guardian_city'] ?? ''),
    'expert_field'          => trim($_POST['expert_field'] ?? ''),
    'years_experience'      => $_POST['years_experience'] ?? null,
    'additional_expertise'  => trim($_POST['additional_expertise'] ?? ''),
    'bio'                   => trim($_POST['bio'] ?? ''),
    'registration_number'   => trim($_POST['registration_number'] ?? ''),
];

// Validate school
if ($data['school_id'] <= 0) {
    Helper::flashOld($_POST);
    Session::flash('error', 'Please select your school or program.');
    Helper::redirect('/register?type=' . $role);
}
$school = School::find($data['school_id']);
if (!$school || $school['status'] !== 'active') {
    Helper::flashOld($_POST);
    Session::flash('error', 'Selected school or program is not available.');
    Helper::redirect('/register?type=' . $role);
}

$isAiverse = School::isAiverse($data['school_id']);
$data['is_aiverse'] = $isAiverse ? 1 : 0;

// Validate fields
$v = new Validator();
$v->required('full_name', $data['full_name'], 'Full name')
  ->minLength('full_name', $data['full_name'], 3, 'Full name')
  ->required('email', $data['email'], 'Email')
  ->email('email', $data['email'])
  ->required('phone', $data['phone'], 'Phone')
  ->phone('phone', $data['phone'])
  ->required('password', $data['password'], 'Password')
  ->password('password', $data['password'])
  ->matches('password_confirm', $data['password_confirm'], $data['password'])
  ->required('registration_number', $data['registration_number'], 'Registration number');

if ($role === 'student') {
    if ($isAiverse) {
        $v->required('state', $data['state'], 'State')
          ->required('district', $data['district'], 'District')
          ->required('city', $data['city'], 'City')
          ->required('pincode', $data['pincode'], 'Pin Code');
    } else {
        if ($data['gender'] !== '') {
            $v->in('gender', $data['gender'], ['male','female','other','prefer_not_to_say'], 'gender');
        }
        $v->required('age', $data['age'], 'Age')
          ->required('current_class', $data['current_class'], 'Current class')
          ->required('guardian_name', $data['guardian_name'], 'Parent/guardian name')
          ->required('guardian_phone', $data['guardian_phone'], 'Parent/guardian phone')
          ->phone('guardian_phone', $data['guardian_phone'])
          ->in('guardian_relationship', $data['guardian_relationship'], ['father','mother','guardian','other'], 'relationship');
        if ($data['guardian_email'] !== '') {
            $v->email('guardian_email', $data['guardian_email']);
        }
    }
} else {
    $v->required('expert_field', $data['expert_field'], 'Expert field');
}

if ($v->fails()) {
    Helper::flashOld($_POST);
    Session::flash('error', implode(' ', $v->errors()));
    Helper::redirect('/register?type=' . $role);
}

// Duplicate checks
if (Auth::findByEmail($data['email'])) {
    Helper::flashOld($_POST);
    Session::flash('error', 'Email already registered.');
    Helper::redirect('/register?type=' . $role);
}

$db = Database::connect();
$stmt = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
$stmt->execute([$data['phone']]);
if ($stmt->fetch()) {
    Helper::flashOld($_POST);
    Session::flash('error', 'Phone number already registered.');
    Helper::redirect('/register?type=' . $role);
}

// Store pending
$_SESSION['_pending_registration'] = $data;
$_SESSION['_pending_phone']        = $data['phone'];
$_SESSION['_pending_role']         = $role;
$_SESSION['_pending_school_id']    = $data['school_id'];
$_SESSION['_pending_school_name']  = $school['name'];
$_SESSION['_pending_is_aiverse']    = $isAiverse;

// Generate OTP once
$otp = OTP::generate(6);

// Create the OTP record keyed by phone (authoritative)
OTP::create($data['phone'], 'registration', $otp);

// Send on BOTH channels
$phoneSent = SMS::sendOTP($data['phone'], $otp);
$emailSent = Mailer::send(
    $data['email'],
    'Your Infinity Learning verification code',
    Mailer::otpEmail($data['full_name'], $otp, 'registration')
);

// Optional: also send to guardian email if provided
if (!empty($data['guardian_email'])) {
    Mailer::send(
        $data['guardian_email'],
        'Your child is signing up on Infinity Learning',
        Mailer::otpEmail($data['guardian_name'] ?: 'Parent', $otp, 'registration')
    );
}

if (!$phoneSent && !$emailSent) {
    Session::flash('error', 'Could not send OTP. Please try again.');
    Helper::redirect('/register?type=' . $role);
}

Audit::log('registration.initiated', 'user', null, [
    'email' => $data['email'], 'role' => $role, 'school_id' => $data['school_id'],
]);

Helper::redirect('/verify-otp');
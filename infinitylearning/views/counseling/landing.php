<?php
use App\Helper;
use App\Session;
use App\Auth;
use App\Counseling;

$pageTitle = 'Counseling — Infinity Learning';
$error = Session::flash('error');
$user = Auth::user();

require VIEW_PATH . '/layouts/header.php';

$types = Counseling::types();
?>

<section class="section">
    <div class="section-head text-center">
        <span class="kicker">🧭 COUNSELING</span>
        <h2>Guidance for every step of your journey</h2>
        <p>
            Talk to a trained mentor about academics, career, admissions, or your psychometric profile.
            Fill a short form and your school admin will assign the right counselor for you.
        </p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error" style="max-width:720px;margin:0 auto 1.5rem">
            <?= Helper::e($error) ?>
        </div>
    <?php endif; ?>

    <div class="counseling-cards">
        <?php foreach ($types as $slug => $t): ?>
            <div class="counseling-card" style="border-top:4px solid <?= Helper::e($t['color']) ?>">
                <div class="counseling-card-icon" style="background:<?= Helper::e($t['color']) ?>22;color:<?= Helper::e($t['color']) ?>">
                    <?= $t['icon'] ?>
                </div>
                <h3><?= Helper::e($t['label']) ?></h3>
                <p><?= Helper::e($t['desc']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
        <?php if ($user && $user['role'] === 'student'): ?>
            <a href="/counseling/apply" class="btn btn-primary btn-lg">
                Start a counseling request →
            </a>
            <p class="muted mt-2" style="font-size:.88rem">
                It takes 2 minutes. Your school admin will assign a counselor within 48 hours.
            </p>
        <?php elseif ($user): ?>
            <div class="alert alert-info" style="max-width:560px;margin:0 auto">
                Only <strong>students</strong> can request counseling. If you're a teacher and want to help,
                ask your school admin to assign you counseling requests.
            </div>
        <?php else: ?>
            <a href="/login" class="btn btn-primary btn-lg">
                Log in as a student to start →
            </a>
            <p class="muted mt-2" style="font-size:.88rem">
                New here? <a href="/register?type=student">Sign up as a student</a> first.
            </p>
        <?php endif; ?>
    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
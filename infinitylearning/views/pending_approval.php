<?php
use App\Auth;
use App\Helper;
use App\School;

Auth::requireLogin();
$user = Auth::user();
$school = !empty($user['school_id']) ? School::find((int)$user['school_id']) : null;

$pageTitle = 'Awaiting Approval — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section" style="max-width:720px;margin:2rem auto">
    <div class="card" style="text-align:center;padding:3rem 2rem">
        <div style="font-size:4rem;line-height:1;margin-bottom:.5rem">⏳</div>
        <h1 style="margin-bottom:.5rem">Almost there!</h1>
        <p class="muted" style="max-width:460px;margin:0 auto">
            Hi <strong><?= Helper::e($user['email']) ?></strong>, your account is
            <strong>waiting for approval</strong> from your school admin.
        </p>

        <?php if ($school): ?>
            <div class="card mt-3" style="background:var(--surface-2);border:none;max-width:420px;margin-left:auto;margin-right:auto;text-align:left">
                <p class="muted" style="font-size:.8rem;margin-bottom:.5rem">Your school</p>
                <div style="font-family:var(--font-display);font-weight:600;font-size:1.15rem">
                    🏫 <?= Helper::e($school['name']) ?>
                </div>
                <?php if ($school['city'] || $school['state']): ?>
                    <div class="muted" style="font-size:.85rem;margin-top:.25rem">
                        <?= Helper::e(trim(($school['city'] ?? '') . ', ' . ($school['state'] ?? ''), ', ')) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($school['contact_email'])): ?>
                    <div class="muted" style="font-size:.85rem;margin-top:.75rem">
                        Contact: <?= Helper::e($school['contact_email']) ?>
                        <?php if (!empty($school['contact_phone'])): ?> · <?= Helper::e($school['contact_phone']) ?><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <p class="muted" style="font-size:.9rem;margin-top:1.5rem;max-width:520px;margin-left:auto;margin-right:auto">
            Once your school admin approves you, you'll have full access to lessons, activities,
            attendance and more.
        </p>

        <div class="flex center mt-3">
            <a href="/logout" class="btn btn-secondary">Logout</a>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
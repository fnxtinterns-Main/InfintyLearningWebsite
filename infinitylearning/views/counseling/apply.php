<?php
use App\Helper;
use App\Session;
use App\Auth;
use App\Counseling;

Auth::requireLogin();
if (Auth::role() !== 'student') {
    Helper::redirect('/counseling');
}

$pageTitle = 'Start Counseling — Infinity Learning';
$error = Session::flash('error');
$user  = Auth::user();

// Pre-fill from student profile
$student = Auth::findStudentByUserId((int)$user['id']);

$types = Counseling::types();

require VIEW_PATH . '/layouts/header.php';
?>

<section class="section" style="max-width:760px;margin:0 auto">
    <a href="/counseling" class="muted">← Back to counseling</a>

    <div class="card mt-2">
        <div class="section-head text-left" style="margin-bottom:1.5rem">
            <span class="kicker">🧭 COUNSELING REQUEST</span>
            <h2 style="margin-bottom:.35rem">Start a counseling request</h2>
            <p class="muted">Fill in your details below. Your school admin will assign a counselor to you.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= Helper::e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/counseling-submit-handler.php" id="counselingForm">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">

            <!-- ================= TYPE ================= -->
            <div class="form-group">
                <label>Which counseling do you want? *</label>
                <select name="counseling_type" required id="counselingType">
                    <option value="">— Select a type —</option>
                    <?php foreach ($types as $slug => $t): ?>
                        <option value="<?= Helper::e($slug) ?>" <?= Helper::old('counseling_type') === $slug ? 'selected' : '' ?>>
                            <?= $t['icon'] ?>  <?= Helper::e($t['label']) ?> — <?= Helper::e($t['desc']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>You can only submit one type per request. Need a different type? Submit another request.</small>
            </div>

            <!-- ================= RATINGS ================= -->
            <div class="divider"></div>
            <h3 style="font-size:1rem;margin-bottom:.35rem">Rate yourself honestly (1 = low, 6 = high)</h3>
            <p class="muted" style="font-size:.85rem;margin-bottom:1rem">
                There are no right or wrong answers — this helps us understand how best to help you.
            </p>

            <?php
            $ratings = [
                'rating_concentration' => ['Concentration', '📊', 'How well can you focus on a task?'],
                'rating_cognitive'     => ['Cognitive Skills', '🧠', 'How quickly do you grasp new ideas?'],
                'rating_comprehension' => ['Comprehension', '📖', 'How well do you understand what you read or hear?'],
                'rating_creativity'    => ['Creativity', '🎨', 'How easily do you come up with new ideas?'],
                'rating_confidence'    => ['Confidence', '💪', 'How confident do you feel about your abilities?'],
                'rating_communication' => ['Communication', '💬', 'How well do you express yourself to others?'],
            ];
            foreach ($ratings as $name => [$label, $icon, $hint]): ?>
                <div class="rating-row">
                    <div class="rating-label">
                        <span class="rating-icon"><?= $icon ?></span>
                        <div>
                            <strong><?= $label ?></strong>
                            <div class="muted" style="font-size:.78rem"><?= $hint ?></div>
                        </div>
                    </div>
                    <div class="rating-slider">
                        <input type="range"
                               name="<?= $name ?>"
                               min="1" max="6" step="1"
                               value="<?= Helper::old($name) ?: '3' ?>"
                               class="rating-input"
                               oninput="this.nextElementSibling.textContent = this.value">
                        <span class="rating-value">3</span>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- ================= BASIC INFO ================= -->
            <div class="divider"></div>
            <h3 style="font-size:1rem;margin-bottom:.5rem">Your details</h3>

            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required
                       value="<?= Helper::old('full_name') ?: Helper::e($student['full_name'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email"
                           value="<?= Helper::old('email') ?: Helper::e($user['email']) ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone"
                           value="<?= Helper::old('phone') ?: Helper::e($user['phone']) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Current Class</label>
                    <input type="text" name="current_class"
                           value="<?= Helper::old('current_class') ?: Helper::e($student['current_class'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Preferred time to talk</label>
                    <input type="text" name="preferred_time" placeholder="e.g. Weekday evenings"
                           value="<?= Helper::old('preferred_time') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city"
                           value="<?= Helper::old('city') ?: Helper::e($student['city'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state"
                           value="<?= Helper::old('state') ?: Helper::e($student['state'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Reason / What would you like to discuss?</label>
                <textarea name="reason" rows="4"
                          placeholder="Tell your counselor briefly what you're hoping to get out of this."><?= Helper::old('reason') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                Submit counseling request →
            </button>
            <p class="muted text-center mt-2" style="font-size:.82rem">
                Your request goes to your school admin, who will assign a counselor to you.
            </p>
        </form>
    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
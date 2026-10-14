<?php
use App\Helper;

$pageTitle = 'Contact — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">📬 CONTACT US</span>
                <h1>We'd love to hear<br>from you</h1>
                <p>
                    Whether you're a school looking to onboard, a teacher wanting to know more,
                    or a parent with a question — here's how to reach us.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     CONTACT CARDS
     ============================================================ -->
<section class="section">
    <div class="container">
        <div class="grid grid-3">

            <div class="card">
                <div class="icon" style="font-size:2rem;margin-bottom:.75rem">📧</div>
                <h3>General enquiries</h3>
                <p>For anything about the platform, onboarding or partnerships.</p>
                <p style="margin-top:1rem">
                    <a href="mailto:info@infintylearning.net" class="btn btn-primary btn-sm">
                        info@infintylearning.net
                    </a>
                </p>
            </div>

            <div class="card">
                <div class="icon" style="font-size:2rem;margin-bottom:.75rem">🏫</div>
                <h3>Schools</h3>
                <p>If your school is already on Infinity Learning, please contact your <strong>school admin</strong> first — they can help with most questions.</p>
                <p style="margin-top:1rem">
                    <a href="/register?type=teacher" class="btn btn-secondary btn-sm">
                        Onboard your school →
                    </a>
                </p>
            </div>

            <div class="card">
                <div class="icon" style="font-size:2rem;margin-bottom:.75rem">🔒</div>
                <h3>Privacy &amp; data requests</h3>
                <p>To access, correct or delete your personal data, or to raise a privacy concern.</p>
                <p style="margin-top:1rem">
                    <a href="mailto:info@infintylearning.net" class="btn btn-secondary btn-sm">
                        info@infintylearning.net
                    </a>
                </p>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     OFFICE / RESPONSE TIME
     ============================================================ -->
<section class="section-tint section">
    <div class="container">
        <div class="section-head">
            <span class="kicker">⏱ RESPONSE TIME</span>
            <h2>What to expect when you reach out</h2>
        </div>

        <div class="grid grid-2" style="max-width:900px;margin:0 auto">
            <div class="card">
                <h3>📅 Weekdays</h3>
                <p>We reply to most enquiries within <strong>1 business day</strong>.</p>
            </div>
            <div class="card">
                <h3>📅 Weekends &amp; holidays</h3>
                <p>Replies may take a little longer. Urgent data-privacy requests are prioritised.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     CTA
     ============================================================ -->
<section class="cta-final">
    <div class="container">
        <div class="cta-final-inner">
            <span class="kicker" style="background:rgba(255,255,255,.15);color:#fff">💬 STILL HAVE QUESTIONS?</span>
            <h2>Let's get your school started</h2>
            <p>Onboarding takes less than 10 minutes. We'll help you every step of the way.</p>
            <div class="cta-final-buttons">
                <a href="mailto:info@infintylearning.net" class="btn btn-primary btn-xl">Email us →</a>
                <a href="/about" class="btn btn-ghost btn-xl" style="background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.3)">Learn about us</a>
            </div>
        </div>
    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
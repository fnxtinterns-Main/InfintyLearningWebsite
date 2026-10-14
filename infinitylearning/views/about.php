<?php
use App\Helper;

$pageTitle = 'About — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">🌱 ABOUT US</span>
                <h1>Education that reaches<br>every learner, infinitely</h1>
                <p>
                    Infinity Learning is a unified education platform that brings together
                    academics, activities, competitions, achievements and counseling —
                    one login, one experience, for schools, teachers, students and parents.
                </p>
            </div>
            <div class="page-hero-stats">
                <div class="page-hero-stat">
                    <div class="page-hero-stat-n">4</div>
                    <div class="page-hero-stat-l">User roles</div>
                </div>
                <div class="page-hero-stat">
                    <div class="page-hero-stat-n">∞</div>
                    <div class="page-hero-stat-l">Learning paths</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     MISSION
     ============================================================ -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="kicker">🎯 OUR MISSION</span>
            <h2>Make world-class learning accessible to every student</h2>
            <p>
                Whether a child studies in a metropolitan CBSE school or a small-town state board,
                they deserve the same quality of structure, mentorship and recognition.
                Infinity Learning exists to close that gap.
            </p>
        </div>

        <div class="grid grid-3">
            <div class="feature">
                <div class="icon">🎓</div>
                <h3>Structured learning</h3>
                <p>Lessons, chapters and topics organised so every student always knows what to study next.</p>
            </div>
            <div class="feature">
                <div class="icon">🏆</div>
                <h3>Recognition that matters</h3>
                <p>Every participation, every achievement — auto-certified as a downloadable PDF.</p>
            </div>
            <div class="feature">
                <div class="icon">🧭</div>
                <h3>Guidance for every step</h3>
                <p>Academic, career, admission and psychometric counseling — right inside the platform.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     WHO IT'S FOR
     ============================================================ -->
<section class="section-tint section">
    <div class="container">
        <div class="section-head">
            <span class="kicker">👥 WHO IT'S FOR</span>
            <h2>Built for every role in the school ecosystem</h2>
        </div>

        <div class="grid grid-4">
            <div class="card">
                <h3>🎓 Students</h3>
                <p>Learn at their own pace, participate in events, earn certificates, and track their growth over time.</p>
            </div>
            <div class="card">
                <h3>👩‍🏫 Teachers</h3>
                <p>Create batches, publish lessons, mark attendance, assign activities and record results — all in one place.</p>
            </div>
            <div class="card">
                <h3>🏫 Schools</h3>
                <p>Onboard teachers and students, manage content, approve accounts and view school-wide analytics.</p>
            </div>
            <div class="card">
                <h3>👨‍👩‍👧 Parents</h3>
                <p>Follow their child's learning journey, attendance, achievements and certificates.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     WHAT MAKES US DIFFERENT
     ============================================================ -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="kicker">✨ WHAT MAKES US DIFFERENT</span>
            <h2>One platform, not five</h2>
            <p>Most schools stitch together five different tools. We replace all of them with one.</p>
        </div>

        <div class="grid grid-2">
            <div class="card card-featured">
                <h3>🌐 Global + school-scoped content</h3>
                <p>
                    Schools publish their own lessons and activities. The platform also provides
                    global content available to everyone — so a small school benefits from the
                    same library a large one does.
                </p>
            </div>
            <div class="card">
                <h3>🤖 AI-ready foundation</h3>
                <p>
                    The platform's data model already supports personalised learning, adaptive practice
                    and knowledge profiling — ready for the next generation of features.
                </p>
            </div>
            <div class="card">
                <h3>🧭 Counseling built-in</h3>
                <p>
                    Four types of counseling — academic, career, admission, psychometric — routed
                    directly to the right teacher through the school admin.
                </p>
            </div>
            <div class="card">
                <h3>📜 Automatic certificates</h3>
                <p>
                    Winners, runner-ups and participants receive branded PDF certificates the moment
                    results are recorded. No more manual certificate work.
                </p>
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
            <span class="kicker" style="background:rgba(255,255,255,.15);color:#fff">🚀 GET STARTED</span>
            <h2>Ready to bring Infinity Learning to your school?</h2>
            <p>
                Onboard your school, invite teachers, and give every student the structured learning
                journey they deserve.
            </p>
            <div class="cta-final-buttons">
                <a href="/register?type=teacher" class="btn btn-primary btn-xl">Get started free →</a>
                <a href="/contact" class="btn btn-ghost btn-xl" style="background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.3)">Talk to us</a>
            </div>
        </div>
    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
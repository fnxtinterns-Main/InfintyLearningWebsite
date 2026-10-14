<?php
use App\Activity;
use App\Helper;
use App\Auth;
use App\Database;

$pageTitle = 'Infinity Learning — Learn. Grow. Achieve';

// Fetch upcoming activities (real data, respects scope)
$upcomingActivities = Activity::upcoming(3, Auth::check() ? Auth::schoolId() : null);

// ============================================================
// STAT BAND — fixed numbers
// (Change these any time — no DB needed)
// ============================================================
$stats = [
    'schools'      => 10,
    'students'     => 5000,
    'activities'   => 30,
    'certificates' => 50,
];

// Partner logos — auto-detect from folder
$partnerDir = PUBLIC_PATH . '/assets/img/partners';
$partners = [];
if (is_dir($partnerDir)) {
    foreach (scandir($partnerDir) as $file) {
        if (preg_match('/\.(png|jpg|jpeg|svg|webp)$/i', $file)) {
            $partners[] = [
                'file' => $file,
                'name' => ucfirst(pathinfo($file, PATHINFO_FILENAME)),
            ];
        }
    }
}

require VIEW_PATH . '/layouts/header.php';
?>

<!-- ============================================================
     1. HERO
     ============================================================ -->
<section class="hero-home">
    <div class="container">
        <div class="hero-home-grid">

            <div class="hero-home-text">
                <span class="kicker">✨ INFINITY LEARNING</span>

                <h1 class="hero-home-title">
                    Learn. Grow. Achieve — <br>
                    for every
                    <span class="rotating-word-wrap">
                        <span class="rotating-word" data-words='["student","learner","achiever","winner","creator"]'>student</span>
                    </span>
                </h1>

                <p class="hero-home-sub">
                    One unified platform for academics, AI-powered learning, activities,
                    competitions, and achievements — built for schools, teachers,
                    and the students who will shape tomorrow.
                </p>

                <div class="hero-home-cta">
                    <a href="/register?type=student" class="btn btn-primary btn-lg">
                        Start Learning Free →
                    </a>
                    <a href="/activities" class="btn btn-secondary btn-lg">
                        Explore Activities
                    </a>
                </div>

                <!-- Trust row -->
                <div class="hero-trust">
                    <div class="hero-trust-avatars">
                        <span class="avatar avatar-initial" data-role="student">A</span>
                        <span class="avatar avatar-initial" data-role="teacher">M</span>
                        <span class="avatar avatar-initial" data-role="student">K</span>
                        <span class="avatar avatar-initial" data-role="student">S</span>
                        <span class="avatar avatar-initial" data-role="teacher">R</span>
                    </div>
                    <div class="hero-trust-text">
                        <strong><?= number_format($stats['students']) ?>+</strong>
                        students &amp; teachers already growing with us
                    </div>
                </div>
            </div>

            <div class="hero-home-art">
                <div class="hero-glow"></div>

                <img class="oldman-float" src="/assets/img/oldman.png" alt="Meet your mentor"
                     fetchpriority="high" decoding="async"
                     onerror="this.style.display='none'">

                <!-- Floating mini cards -->
                <div class="hero-mini-card hero-mini-1">
                    <span class="hero-mini-icon">📊</span>
                    <div>
                        <div class="hero-mini-num">92%</div>
                        <div class="hero-mini-lbl">Attendance</div>
                    </div>
                </div>

                <div class="hero-mini-card hero-mini-2">
                    <span class="hero-mini-icon">🏆</span>
                    <div>
                        <div class="hero-mini-num">7</div>
                        <div class="hero-mini-lbl">Achievements</div>
                    </div>
                </div>

                <div class="hero-mini-card hero-mini-3">
                    <span class="hero-mini-icon">📚</span>
                    <div>
                        <div class="hero-mini-num">24</div>
                        <div class="hero-mini-lbl">Lessons done</div>
                    </div>
                </div>

                <div class="hero-mini-card hero-mini-4">
                    <span class="hero-mini-icon">🎯</span>
                    <div>
                        <div class="hero-mini-num">4</div>
                        <div class="hero-mini-lbl">Upcoming</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     2. ORANGE STAT BAND
     ============================================================ -->
<section class="stat-band">
    <div class="container">
        <div class="stat-band-grid">
            <div class="stat-band-item">
                <div class="stat-band-n" data-count="<?= (int)$stats['schools'] ?>">0</div>
                <div class="stat-band-l">Schools</div>
            </div>
            <div class="stat-band-item">
                <div class="stat-band-n" data-count="<?= (int)$stats['students'] ?>">0</div>
                <div class="stat-band-l">Students</div>
            </div>
            <div class="stat-band-item">
                <div class="stat-band-n" data-count="<?= (int)$stats['activities'] ?>">0</div>
                <div class="stat-band-l">Activities</div>
            </div>
            <div class="stat-band-item">
                <div class="stat-band-n" data-count="<?= (int)$stats['certificates'] ?>">0</div>
                <div class="stat-band-l">Certificates issued</div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     3. OUR PARTNERS
     ============================================================ -->
<?php
// Filter out the Infinity logo before rendering
$partners = array_filter($partners, function ($p) {
    return stripos($p['file'], 'infinity') === false;
});
?>
<?php if ($partners): ?>
<section class="section-sm partner-section">
    <div class="container">
        <p class="partner-heading">OUR PARTNERS</p>

        <div class="partner-ticker">
            <div class="partner-track">
                <?php foreach ($partners as $p): ?>
                    <div class="partner-logo">
                        <img src="/assets/img/partners/<?= Helper::e($p['file']) ?>"
                             alt="<?= Helper::e($p['name']) ?>"
                             loading="lazy">
                    </div>
                <?php endforeach; ?>
                <?php foreach ($partners as $p): ?>
                    <div class="partner-logo" aria-hidden="true">
                        <img src="/assets/img/partners/<?= Helper::e($p['file']) ?>" alt="">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<!-- ============================================================
     4. THE JOURNEY — 5 numbered steps
     ============================================================ -->
<section class="section-lg">
    <div class="container">
        <div class="section-head">
            <span class="kicker">📚 OUR LEARNING MODEL</span>
            <h2>From classroom to certificate</h2>
            <p>Every stage builds on the last. Every student follows the same path to mastery.</p>
        </div>

        <div class="journey">
            <?php
            $steps = [
                ['Learn',       'Structured lessons across all subjects with PDFs, videos and study notes.',                     '📘'],
                ['Practice',    'Adaptive practice that meets each student where they are.',                                    '✍️'],
                ['Compete',     'Inter-school activities, competitions and events — scoped to your school.',                    '🏆'],
                ['Get Certified', 'Auto-generated PDF certificates for every achievement and every participation.',             '🎓'],
                ['Grow',        'Progress tracked over time. Parents and teachers see the full journey.',                       '📈'],
            ];
            foreach ($steps as $i => $step): ?>
                <div class="journey-step reveal">
                    <div class="journey-num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
                    <div class="journey-body">
                        <h3><span style="margin-right:.5rem"><?= $step[2] ?></span><?= Helper::e($step[0]) ?></h3>
                        <p><?= Helper::e($step[1]) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     5. MOCKUP DASHBOARD (tinted bg)
     ============================================================ -->
<section class="section-lg section-tint">
    <div class="container">
        <div class="mockup-grid">

            <div class="mockup-wrap reveal-scale">
                <div class="mockup-dashboard">
                    <div class="mockup-header">
                        <div class="mockup-header-avatar avatar-initial" data-role="student">A</div>
                        <div class="mockup-header-text">
                            <strong>Aarav Sharma</strong>
                            <span>Grade 7 · City Public School</span>
                        </div>
                        <span class="badge badge-success">Active</span>
                    </div>

                    <div class="mockup-stats">
                        <div class="mockup-stat">
                            <span class="mockup-stat-icon">✅</span>
                            <div class="mockup-stat-num">92%</div>
                            <div class="mockup-stat-lbl">Attendance</div>
                        </div>
                        <div class="mockup-stat">
                            <span class="mockup-stat-icon">📚</span>
                            <div class="mockup-stat-num">24</div>
                            <div class="mockup-stat-lbl">Lessons</div>
                        </div>
                        <div class="mockup-stat">
                            <span class="mockup-stat-icon">🏆</span>
                            <div class="mockup-stat-num">7</div>
                            <div class="mockup-stat-lbl">Achievements</div>
                        </div>
                    </div>

                    <div class="mockup-section">
                        <div class="mockup-section-title">🎯 Next up</div>
                        <div class="mockup-activity">
                            <div class="mockup-activity-icon">🎨</div>
                            <div class="mockup-activity-body">
                                <strong>Drawing Competition</strong>
                                <span>Tomorrow · 10:00 AM</span>
                            </div>
                            <span class="badge badge-primary">Soon</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mockup-text">
                <span class="kicker">📊 FOR TEACHERS &amp; PARENTS</span>
                <h2>Every student's growth, tracked in one place</h2>
                <p>
                    From attendance to achievements, from lessons completed to competitions won —
                    all visible on a single dashboard your teachers, students and parents can trust.
                </p>

                <ul class="feature-list">
                    <li><span class="feature-check">✓</span> Real-time attendance with monthly trends</li>
                    <li><span class="feature-check">✓</span> Lesson progress and study material per subject</li>
                    <li><span class="feature-check">✓</span> Activity participation and certificates</li>
                    <li><span class="feature-check">✓</span> Parent visibility into everything their child does</li>
                </ul>

                <a href="/register?type=student" class="btn btn-primary btn-lg">
                    Create a free account →
                </a>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     6. UPCOMING ACTIVITIES
     ============================================================ -->
<?php if ($upcomingActivities): ?>
<section class="section-lg">
    <div class="container">
        <div class="section-head">
            <span class="kicker">🏆 UPCOMING EVENTS</span>
            <h2>Something exciting is always happening</h2>
            <p>Creative, academic, sports and technology events — scoped to your school and beyond.</p>
        </div>

        <div class="grid grid-3">
            <?php foreach ($upcomingActivities as $a):
                $cover = $a['cover_image'] ?: '/assets/img/placeholder-activity.png';
            ?>
                <a class="activity-card reveal" href="/activity?slug=<?= urlencode($a['slug']) ?>">
                    <img class="activity-cover" src="<?= Helper::e($cover) ?>" alt=""
                         onerror="this.style.display='none'">
                    <div class="activity-body">
                        <span class="badge badge-primary"><?= ucfirst($a['category']) ?></span>
                        <h3><?= Helper::e($a['title']) ?></h3>
                        <div class="activity-meta">
                            <?php if ($a['event_date']): ?>
                                <span>📅 <?= date('d M Y', strtotime($a['event_date'])) ?></span>
                            <?php endif; ?>
                            <?php if ($a['venue']): ?>
                                <span>📍 <?= Helper::e($a['venue']) ?></span>
                            <?php endif; ?>
                        </div>
                        <p><?= Helper::e(mb_substr($a['short_description'] ?? '', 0, 110)) ?></p>
                        <div class="actions">
                            <span class="btn btn-secondary btn-sm">View details →</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-3">
            <a href="/activities" class="btn btn-secondary">View all activities →</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     7. CERTIFICATE SHOWCASE
     ============================================================ -->
<section class="section-lg section-tint">
    <div class="container">
        <div class="cert-showcase-grid">

            <div class="cert-showcase-text">
                <span class="kicker kicker-brand">🎓 ACHIEVEMENTS THAT MATTER</span>
                <h2>Certificates your students will treasure</h2>
                <p>
                    Every time a student wins, completes or simply participates — we generate a
                    beautiful PDF certificate automatically. Ready to download, share and print.
                </p>

                <ul class="feature-list">
                    <li><span class="feature-check">✓</span> Auto-generated on every result</li>
                    <li><span class="feature-check">✓</span> Winner, runner-up and participation variants</li>
                    <li><span class="feature-check">✓</span> Branded with your school's name</li>
                    <li><span class="feature-check">✓</span> Downloadable as PDF instantly</li>
                </ul>
            </div>

            <div class="cert-showcase-art reveal-scale">
                <div class="mockup-cert">
                    <div class="mockup-cert-border">
                        <div class="mockup-cert-ribbon">CERTIFICATE</div>
                        <div class="mockup-cert-sub">OF ACHIEVEMENT</div>
                        <div class="mockup-cert-line"></div>
                        <div class="mockup-cert-name">Aarav Sharma</div>
                        <div class="mockup-cert-desc">has successfully participated in the Math Whizz Championship</div>
                        <div class="mockup-cert-footer">
                            <span>🏆 1st Place</span>
                            <span><?= date('d M Y') ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     8. TESTIMONIAL (dark)
     ============================================================ -->
<section class="section-dark quote-section">
    <div class="container">
        <div class="quote-inner">
            <div class="quote-mark">"</div>
            <blockquote class="quote-text">
                Infinity Learning has transformed how we run our school.
                Attendance, activities and certificates — everything in one place.
                Our teachers save hours every week.
            </blockquote>
            <div class="quote-author">
                <div class="avatar avatar-initial" data-role="teacher" style="width:48px;height:48px;font-size:1.1rem">R</div>
                <div>
                    <strong>Rakesh Verma</strong>
                    <span class="muted">Principal · City Public School</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     9. FINAL CTA
     ============================================================ -->
<section class="cta-final">
    <div class="container">
        <div class="cta-final-inner">
            <span class="kicker" style="background:rgba(255,255,255,.15);color:#fff">🚀 READY WHEN YOU ARE</span>
            <h2>Ready to transform your school?</h2>
            <p>
                Join schools across the country using Infinity Learning to run academics,
                activities and achievements — all from one beautiful platform.
            </p>
            <div class="cta-final-buttons">
                <a href="/register?type=teacher" class="btn btn-primary btn-xl">Get started free →</a>
                <a href="/activities" class="btn btn-ghost btn-xl" style="background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.3)">See it in action</a>
            </div>
        </div>
    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
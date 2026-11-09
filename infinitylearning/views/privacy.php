<?php
use App\Helper;

$pageTitle = 'Privacy Policy — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>

<?php /* =====================================================
         DRAFT — LEGAL REVIEW REQUIRED
         This privacy policy reflects the platform's actual
         data flows as implemented. It must be reviewed by
         a lawyer before public launch, especially the
         "Children's Data" section (India DPDP Act 2023).
       ===================================================== */ ?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">🔒 PRIVACY</span>
                <h1>Privacy Policy</h1>
                <p>
                    This policy explains what information Infinity Learning collects,
                    how we use it, and the choices you have.
                </p>
                <p class="muted" style="font-size:.85rem;margin-top:.75rem">
                    Last updated: <?= date('d M Y') ?>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container-narrow">

        <!-- 1 -->
        <div class="card">
            <h2 class="detail-h2">1. Who we are</h2>
            <p>
                Infinity Learning ("we", "us", "the Platform") is an education-management platform
                used by schools, teachers, students and parents.
            </p>
            <p style="margin-top:1rem">
                Because the Platform supports two different ways of using it, the responsibility for
                your personal data depends on how you joined:
            </p>

            <div class="grid grid-2" style="margin-top:1.25rem">
                <div class="card" style="background:var(--surface-2);border:none">
                    <h3 style="font-size:1rem">If you joined through a school</h3>
                    <p style="font-size:.92rem">
                        Your <strong>school is the data controller</strong>. Infinity Learning acts as a
                        <strong>data processor</strong> on the school's behalf. Requests to access, correct
                        or delete your data should go to your school admin first.
                    </p>
                </div>
                <div class="card" style="background:var(--surface-2);border:none">
                    <h3 style="font-size:1rem">If you joined independently</h3>
                    <p style="font-size:.92rem">
                        If you registered without a school (or under the "Independent / AIVERSE" tier),
                        Infinity Learning is the <strong>data controller</strong> for your account and
                        is responsible for its protection.
                    </p>
                </div>
            </div>
        </div>

        <!-- 2 -->
        <div class="card mt-3">
            <h2 class="detail-h2">2. What we collect</h2>
            <p>We collect only what is necessary to run the Platform:</p>

            <h3 style="font-size:1rem;margin-top:1.25rem">Account &amp; identity</h3>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>Full name, email address, phone number</li>
                <li>Password (stored only as a secure Argon2id hash — never in plain text)</li>
                <li>Role (student, teacher, or administrator)</li>
                <li>School affiliation (if any)</li>
            </ul>

            <h3 style="font-size:1rem;margin-top:1.25rem">Student profile (for students)</h3>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>Age, gender, current class, academic year</li>
                <li>School board, city, state</li>
                <li>Parent / guardian name, phone, email, occupation</li>
                <li>Interests (self-declared)</li>
            </ul>

            <h3 style="font-size:1rem;margin-top:1.25rem">Teacher profile (for teachers)</h3>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>Expert field, years of experience, additional expertise</li>
                <li>Short biography (optional)</li>
            </ul>

            <h3 style="font-size:1rem;margin-top:1.25rem">Activity on the Platform</h3>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>Lessons viewed and progress through them</li>
                <li>Attendance records (marked by teachers)</li>
                <li>Activity registrations and results</li>
                <li>Certificates earned</li>
                <li>Counseling requests and counselor notes</li>
                <li>Notifications received</li>
                <li>Uploaded files (profile pictures, lesson attachments)</li>
            </ul>

            <h3 style="font-size:1rem;margin-top:1.25rem">Technical data</h3>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>IP address, browser type, session identifiers</li>
                <li>Audit logs of administrative actions</li>
            </ul>
        </div>

        <!-- 3 -->
        <div class="card mt-3">
            <h2 class="detail-h2">3. Why we collect it</h2>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>To create and manage your account</li>
                <li>To deliver lessons, activities and certificates</li>
                <li>To keep attendance and progress records for the school</li>
                <li>To enable counseling between students and teachers</li>
                <li>To send you transactional emails (OTP, password resets, approvals, notifications)</li>
                <li>To protect against fraud and abuse</li>
                <li>To improve the Platform</li>
            </ul>
        </div>

        <!-- 4 -->
        <div class="card mt-3">
            <h2 class="detail-h2">4. Who can see your information</h2>

            <h3 style="font-size:1rem;margin-top:1rem">If you joined through a school</h3>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li><strong>Your school admin</strong> — sees all information about you within the school</li>
                <li><strong>Your teachers</strong> — see your name, class, attendance, submissions and results in batches they teach</li>
                <li><strong>Yourself</strong> — can see your own profile, lessons, attendance, certificates and counseling</li>
                <li><strong>Infinity Learning platform administrators</strong> — access only for technical support and security</li>
                <li><strong>Your parent/guardian</strong> — if you're a student, we may share your progress with them via email summaries</li>
            </ul>

            <h3 style="font-size:1rem;margin-top:1.25rem">If you joined independently</h3>
            <p>
                Only you and Infinity Learning platform administrators can see your information.
                No school or teacher has access.
            </p>

            <p style="margin-top:1rem">
                We do <strong>not</strong> sell your personal data. We do <strong>not</strong> share it
                with advertisers.
            </p>
        </div>

        <!-- 5 -->
        <div class="card mt-3">
            <h2 class="detail-h2">5. Children's data</h2>
            <p>
                Many of our users are students under 18. We take this responsibility seriously.
            </p>
            <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.75rem">
                <li>Students under 18 must register under a school or with verifiable parental/guardian consent</li>
                <li>We collect only the minimum information needed to deliver the educational service</li>
                <li>We do <strong>not</strong> use children's data for behavioural advertising or tracking</li>
                <li>Parents and guardians may request access to, correction of, or deletion of their child's data by contacting us or the school admin</li>
                <li>Schools act as the primary guardian of their students' educational data</li>
            </ul>
        </div>

        <!-- 6 -->
        <div class="card mt-3">
            <h2 class="detail-h2">6. Definite Data Retention Limits &amp; Schedules</h2>
            <p>We retain personal data only for as long as necessary to fulfill the educational purpose or statutory obligations:</p>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li><strong>Active User Accounts:</strong> Maintained for the duration of active enrollment or school association.</li>
                <li><strong>Inactive / Dormant Accounts:</strong> Accounts with no activity for <strong>12 consecutive months</strong> are flagged; after <strong>24 months</strong> of inactivity, profile records are permanently purged unless required by institutional board affiliation.</li>
                <li><strong>Statutory Financial &amp; Fee Ledgers:</strong> Invoiced fee receipts, tuition transaction IDs, and ledger entries are retained for <strong>7 years</strong> to comply with Indian taxation (GST) and corporate audit statutes.</li>
                <li><strong>Audit &amp; Security Logs:</strong> Administrative access and security audit logs are retained for <strong>12 months</strong>, after which they are automatically rotated and purged.</li>
                <li><strong>Certificates &amp; Transcripts:</strong> Official student completion certificates and accredited credentials are preserved permanently as tamper-evident academic records.</li>
                <li><strong>Account Deletion Requests (RTBF):</strong> When a verified deletion request is received via our <a href="/data-request" style="color:var(--il-brand,#7C2D5C); font-weight:700;">Data Request Portal</a>, active database entries are deleted within <strong>30 days</strong>.</li>
            </ul>
        </div>

        <!-- 6A: AI Model Training Shield -->
        <div class="card mt-3" style="background:#FFF9F4; border:1.5px solid #F3DECB;">
            <h2 class="detail-h2" style="color:#B45309; display:flex; align-items:center; gap:8px;">
                <span>🛡️</span> Strict Prohibition on AI Model Training
            </h2>
            <p style="font-size:14px; line-height:1.65; color:#374151;">
                Infinity Learning maintains a strict zero-tolerance policy against the unauthorized use of student and teacher work for artificial intelligence training.
            </p>
            <ul style="margin-left:1.25rem; line-height:1.8; font-size:13.5px; color:#4B5563; margin-top:.5rem;">
                <li>Your student essays, homework submissions, doubts, tests, and audio/video live classes are <strong>NEVER used to train, test, or fine-tune public foundation AI models</strong> (e.g. OpenAI, Anthropic, Google Gemini, or open weights models).</li>
                <li>No educational records or personal intellectual property are scraped or ingested into external machine learning corpuses.</li>
                <li>Where synthetic or AI-assisted educational illustrations are generated on the platform, they are clearly tagged with an <strong>[AI Generated]</strong> disclosure label.</li>
            </ul>
        </div>

        <!-- 7 -->
        <div class="card mt-3">
            <h2 class="detail-h2">7. Your Statutory Rights &amp; Self-Service Portal</h2>
            <p>Subject to the <em>Digital Personal Data Protection Act (DPDP 2023)</em>, GDPR, and regional privacy frameworks, you have the right to:</p>
            <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.75rem">
                <li><strong>Right of Access &amp; Summary:</strong> Request an export of all personal and academic data held about you in a standardized format.</li>
                <li><strong>Right to Correction &amp; Erasure:</strong> Rectify inaccurate demographic details or request permanent account erasure.</li>
                <li><strong>Right to Grievance Redressal:</strong> Direct complaints to our appointed Grievance Officer with guaranteed acknowledgement within 48 hours.</li>
                <li><strong>Right to Withdraw Consent:</strong> Revoke marketing consents at any time via <a href="/unsubscribe" style="color:var(--il-brand,#7C2D5C); font-weight:700;">Unsubscribe</a> or toggle cookie preferences in our footer.</li>
            </ul>
            <div style="margin-top:1.25rem; background:var(--surface-2); padding:16px; border-radius:8px;">
                <p style="margin:0 0 10px; font-weight:700; font-size:14px;">Submit an instant privacy request:</p>
                <a href="/data-request" class="btn btn-primary" style="padding:8px 18px; font-size:13px; text-decoration:none;">Open Data Request Portal →</a>
            </div>
        </div>

        <!-- 8 -->
        <div class="card mt-3">
            <h2 class="detail-h2">8. Subprocessors &amp; Data Transfers</h2>
            <p>
                To provide secure, resilient cloud learning, we partner with thoroughly audited infrastructure vendors. 
                All user data is stored within certified data centers in <strong>Mumbai, India</strong>.
            </p>
            <p style="margin-top:1rem">
                View our complete, up-to-date schedule of vendors, security certifications, and processing roles:
                <br>
                👉 <a href="/subprocessors" style="color:var(--il-brand,#7C2D5C); font-weight:700; text-decoration:underline;">View Authorized Subprocessors Schedule</a> · 
                <a href="/dpa" style="color:var(--il-brand,#7C2D5C); font-weight:700; text-decoration:underline;">Institutional DPA</a>
            </p>
        </div>

        <!-- 9 -->
        <div class="card mt-3">
            <h2 class="detail-h2">9. Security</h2>
            <p>We protect your data with:</p>
            <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.75rem">
                <li>Encrypted transport (HTTPS) across the Platform</li>
                <li>Argon2id password hashing</li>
                <li>CSRF protection on all forms</li>
                <li>Rate limiting on sensitive endpoints</li>
                <li>Audit logging of administrative actions</li>
                <li>Role-based access control</li>
            </ul>
            <p style="margin-top:1rem">
                No system is perfectly secure. If you believe your account has been compromised,
                email <a href="mailto:info@infintylearning.net">info@infintylearning.net</a> immediately.
            </p>
        </div>

        <!-- 10 -->
        <div class="card mt-3">
            <h2 class="detail-h2">10. Cookies &amp; sessions</h2>
            <p>
                We use a single session cookie to keep you logged in. It is HTTP-only, secure
                (when served over HTTPS), and does not track you across other websites. We do not
                use third-party advertising cookies.
            </p>
        </div>

        <!-- 11 -->
        <div class="card mt-3">
            <h2 class="detail-h2">11. Changes to this policy</h2>
            <p>
                We may update this policy from time to time. Material changes will be announced
                on the Platform. Continued use after changes means you accept the updated policy.
            </p>
        </div>

        <!-- 12 -->
        <div class="card mt-3">
            <h2 class="detail-h2">12. Grievance Redressal &amp; Data Protection Officer</h2>
            <p>
                In accordance with the <em>Digital Personal Data Protection Act 2023</em> and applicable rules, 
                our designated Grievance &amp; Data Protection Officer can be reached directly:
            </p>
            <div style="background:var(--surface-2); padding:16px; border-radius:8px; margin-top:12px; font-size:13.5px; line-height:1.7;">
                <strong>Grievance Officer:</strong> Legal &amp; Data Protection Cell<br>
                <strong>Organization:</strong> Infinity Learning &amp; FutureNxt Technologies<br>
                <strong>Email:</strong> <a href="mailto:info@infintylearning.net" style="color:var(--il-brand,#7C2D5C); font-weight:700;">info@infintylearning.net</a><br>
                <strong>Response Turnaround:</strong> Initial acknowledgement within 48 hours; resolution within 30 days.<br>
                <strong>Direct Portal Submission:</strong> <a href="/data-request" style="color:var(--il-brand,#7C2D5C); font-weight:700;">Open Data Rights Form</a>
            </div>
        </div>

    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
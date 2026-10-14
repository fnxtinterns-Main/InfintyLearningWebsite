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
            <h2 class="detail-h2">6. How long we keep your data</h2>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li><strong>Active accounts:</strong> as long as your account remains active</li>
                <li><strong>Suspended or rejected accounts:</strong> retained for a reasonable period to prevent re-registration abuse</li>
                <li><strong>Attendance and academic records:</strong> retained for the duration of the school relationship plus a reasonable period afterwards, as required for educational record-keeping</li>
                <li><strong>Audit logs:</strong> retained for a minimum of 12 months for security and accountability purposes</li>
                <li><strong>Certificates:</strong> retained permanently as they are part of a student's academic record</li>
            </ul>
            <p style="margin-top:1rem">
                When data is no longer needed, we securely delete or anonymise it.
            </p>
        </div>

        <!-- 7 -->
        <div class="card mt-3">
            <h2 class="detail-h2">7. Your rights</h2>
            <p>Subject to applicable law, you have the right to:</p>
            <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.75rem">
                <li><strong>Access</strong> — ask what data we hold about you</li>
                <li><strong>Correct</strong> — fix inaccurate data</li>
                <li><strong>Delete</strong> — request deletion of your account and data (subject to legal retention requirements)</li>
                <li><strong>Object</strong> — object to certain processing</li>
                <li><strong>Portability</strong> — request a copy of your data in a portable format</li>
                <li><strong>Withdraw consent</strong> — where processing is based on consent, you may withdraw it at any time</li>
            </ul>
            <p style="margin-top:1rem">
                To exercise these rights, email
                <a href="mailto:privacy@infintylearning.net">privacy@infintylearning.net</a>
                or contact your school admin. We aim to respond within 30 days.
            </p>
        </div>

        <!-- 8 -->
        <div class="card mt-3">
            <h2 class="detail-h2">8. Third-party services</h2>
            <p>We use the following third-party services to operate the Platform:</p>
            <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.75rem">
                <li><strong>Hosting provider</strong> — for servers and storage</li>
                <li><strong>Email delivery (SMTP)</strong> — to send transactional emails</li>
                <li><strong>SMS provider</strong> — to send OTP codes (when configured)</li>
                <li><strong>Database infrastructure</strong> — for storing structured data</li>
            </ul>
            <p style="margin-top:1rem">
                Each provider is bound by its own privacy and security commitments.
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
                email <a href="mailto:privacy@infintylearning.net">privacy@infintylearning.net</a> immediately.
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
            <h2 class="detail-h2">12. Contact us</h2>
            <p>
                For any privacy-related question:<br>
                <strong>Email:</strong> <a href="mailto:privacy@infintylearning.net">privacy@infintylearning.net</a><br>
                <strong>Website:</strong> <a href="https://infintylearning.net">infintylearning.net</a>
            </p>
        </div>

    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
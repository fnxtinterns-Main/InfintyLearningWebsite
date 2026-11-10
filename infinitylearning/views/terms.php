<?php
use App\Helper;

$pageTitle = 'Terms of Service — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>

<?php /* =====================================================
         DRAFT — LEGAL REVIEW REQUIRED
         Reflects how the platform is intended to be used.
         A lawyer should review before public launch,
         especially governing law and liability clauses.
       ===================================================== */ ?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">📄 LEGAL</span>
                <h1>Terms of Service</h1>
                <p>
                    These terms govern your use of Infinity Learning. By creating an account
                    or using the Platform, you agree to them.
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

        <div class="card">
            <h2 class="detail-h2">1. Acceptance of terms</h2>
            <p>
                By accessing or using Infinity Learning, you agree to be bound by these Terms of
                Service, our <a href="/privacy">Privacy Policy &amp; Statutory DPDP Notice</a>, and our <a href="/refund-policy">Cancellation &amp; Refund Policy</a>. If you do not agree,
                please do not use the Platform.
            </p>
            <p style="margin-top:.75rem; font-size:13.5px; color:#475569; line-height:1.6;">
                These terms incorporate statutory rights and protections under India's <strong>Digital Personal Data Protection Act, 2023 ("DPDP Act")</strong>, the Information Technology Act 2000, and the Consumer Protection (E-Commerce) Rules 2020.
            </p>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">2. Eligibility &amp; Parental Consent (DPDP Act § 9)</h2>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>You must be at least 13 years old to create an account.</li>
                <li><strong>Children under 18:</strong> Pursuant to Section 9 of the DPDP Act 2023, students under 18 years of age must register with verifiable consent from a parent or lawful guardian. By registering a minor student, the parent/guardian affirms that they have reviewed and consented to these Terms and our Privacy Policy.</li>
                <li><strong>No Child Profiling or Targeted Ads:</strong> Infinity Learning strictly prohibits tracking, behavioural monitoring, profiling, or targeted advertising directed at minor students.</li>
                <li>If you register on behalf of a school, you confirm you are authorised to do so.</li>
            </ul>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">3. Your account</h2>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>You are responsible for keeping your password confidential.</li>
                <li>You are responsible for all activity that happens under your account.</li>
                <li>You must provide accurate information during registration.</li>
                <li>You must not share your account with anyone else.</li>
                <li>If your account is created by a school, the school admin may suspend or delete it.</li>
            </ul>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">4. Acceptable use</h2>
            <p>You agree <strong>not</strong> to:</p>
            <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.75rem">
                <li>Upload malicious code, viruses or harmful files</li>
                <li>Attempt to access other users' accounts or private data</li>
                <li>Use the Platform to harass, threaten or bully anyone</li>
                <li>Post content that is illegal, hateful, obscene or infringing</li>
                <li>Interfere with the Platform's operation or security</li>
                <li>Scrape, copy or resell Platform content without written permission</li>
            </ul>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">5. Content You Upload &amp; AI Training Shield</h2>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>You retain full ownership and intellectual property of content you upload (lessons, assignments, notes, student doubts, files).</li>
                <li>By uploading, you grant Infinity Learning a limited, non-exclusive licence strictly to store, process, and display that content to authorized users in your school or cohort.</li>
                <li><strong>No AI Model Training:</strong> Infinity Learning shall <strong>NEVER</strong> use, sell, or license your uploaded coursework, questions, student submissions, or notes to train, fine-tune, or benchmark external machine learning or foundation AI models.</li>
                <li>You confirm you own or have obtained the necessary rights to any content you upload and that it does not infringe any third-party copyright.</li>
            </ul>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">6. Subscriptions, Auto-Renewal &amp; Easy Cancellation</h2>
            <p>For independent courses, bundles, chess coaching, and recurring platform tiers:</p>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li><strong>Auto-Renewal Disclosure:</strong> If you enroll in a recurring subscription plan, your subscription will automatically renew at the conclusion of each billing period (monthly, quarterly, or annually) at the then-current standard rate, unless cancelled prior to the renewal date.</li>
                <li><strong>Advance Notification:</strong> We provide transparent billing receipts and advance email notices prior to annual subscription renewals.</li>
                <li><strong>Easy 1-Click Cancellation:</strong> We do NOT employ dark patterns, forced phone calls, or deceptive retention mazes. You may cancel your subscription at any time directly through your dashboard settings or by submitting a cancellation request to <a href="mailto:info@infintylearning.net" style="color:var(--il-brand,#7C2D5C); font-weight:700;">info@infintylearning.net</a>.</li>
                <li><strong>Post-Cancellation Access:</strong> Upon cancellation, you retain full uninterrupted access to your enrolled courses and resources until the end of your prepaid billing period.</li>
                <li><strong>Refunds:</strong> All fee refunds and grace periods are governed strictly by our transparent <a href="/refund-policy" style="color:var(--il-brand,#7C2D5C); font-weight:700; text-decoration:underline;">Cancellation &amp; Refund Policy</a>.</li>
            </ul>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">7. DMCA &amp; Copyright Infringement Takedown Procedure</h2>
            <p>
                Infinity Learning respects intellectual property rights and adheres to the notice and takedown principles 
                of the Digital Millennium Copyright Act (17 U.S.C. § 512) and the Indian Copyright Act 1957.
            </p>
            <p style="margin-top:.75rem">
                If you believe your copyrighted work has been uploaded or displayed without authorization, submit a formal written notice to our Designated Copyright Agent:
            </p>
            <div style="background:var(--surface-2); padding:16px; border-radius:8px; margin:12px 0; font-size:13.5px; line-height:1.7;">
                <strong>Designated Copyright Agent:</strong> Legal &amp; Compliance Office<br>
                <strong>Email:</strong> <a href="mailto:info@infintylearning.net" style="color:var(--il-brand,#7C2D5C); font-weight:700;">info@infintylearning.net</a><br>
                <strong>Address:</strong> Infinity Learning / FutureNxt Technologies, India
            </div>
            <p style="font-size:13.5px; color:#475569; line-height:1.6; margin-top:.5rem;">
                Your notice must include: (a) identification of the copyrighted work claimed to be infringed; (b) the exact URL or location on the Platform; (c) your contact information (name, address, phone, email); (d) a statement of good-faith belief that the use is unauthorized; and (e) a physical or electronic signature of the copyright owner or authorized representative.
            </p>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">8. Platform Content &amp; Open Source Licenses</h2>
            <p>
                Platform design, branding, and proprietary codebase are owned by Infinity Learning. Open source components and web fonts utilized on the platform are used pursuant to their respective permissive licenses (including MIT, Apache 2.0, and SIL Open Font License).
                <br>
                👉 <a href="/open-source-licenses" style="color:var(--il-brand,#7C2D5C); font-weight:700; text-decoration:underline;">View Open Source Software &amp; Font Disclosures</a>.
            </p>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">7. Certificates</h2>
            <p>
                Certificates issued through the Platform reflect the activities, competitions and
                results recorded by teachers or admins. Infinity Learning is not responsible for
                the accuracy of information entered by school staff.
            </p>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">8. Availability</h2>
            <p>
                We aim to keep the Platform available at all times, but we do not guarantee
                uninterrupted service. We may suspend or modify the Platform for maintenance,
                security or improvement without prior notice.
            </p>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">9. Termination</h2>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>You may stop using the Platform at any time.</li>
                <li>School admins may suspend or remove users within their school.</li>
                <li>We may suspend or terminate accounts that violate these terms or applicable law.</li>
                <li>On termination, some data may be retained as described in the <a href="/privacy">Privacy Policy</a>.</li>
            </ul>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">10. Limitation of liability</h2>
            <p>
                To the maximum extent permitted by law, Infinity Learning is not liable for
                indirect, incidental, special or consequential damages arising from your use of the
                Platform. Our total liability to you will not exceed the amount you (or your school)
                paid to use the Platform in the 12 months preceding the claim.
            </p>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">11. Changes to these terms</h2>
            <p>
                We may update these terms from time to time. Material changes will be announced
                on the Platform. Continued use after changes means you accept the updated terms.
            </p>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">12. Data Principal Rights &amp; DPBI Redressal</h2>
            <p>
                In accordance with Sections 11, 12, 13, and 14 of the Digital Personal Data Protection Act, 2023:
            </p>
            <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.5rem;">
                <li>You have the right to access, rectify, or request erasure of your digital personal data via our <a href="/data-request" style="color:var(--il-brand,#7C2D5C); font-weight:700;">Data Rights Portal</a>.</li>
                <li><strong>Right to Nominate (Section 14):</strong> You may nominate a designated representative to exercise your data rights in the event of death or incapacity.</li>
                <li><strong>Grievance Escalation to DPBI (Section 13(3)):</strong> If any privacy complaint submitted to our Grievance Officer (<a href="mailto:info@infintylearning.net">info@infintylearning.net</a>) is not resolved within 30 days, or if you are dissatisfied with the response, you have the statutory right to file a complaint directly with the <strong>Data Protection Board of India (DPBI)</strong>.</li>
            </ul>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">13. Governing law</h2>
            <p>
                These terms are governed by the laws of India. Any dispute will be subject to the
                exclusive jurisdiction of the courts of Jharkhand, India, without prejudice to the statutory powers of the Data Protection Board of India under the DPDP Act 2023.
            </p>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">14. Contact</h2>
            <p>
                For questions about these terms or compliance:<br>
                <strong>Email:</strong> <a href="mailto:info@infintylearning.net">info@infintylearning.net</a><br>
                <strong>Website:</strong> <a href="https://infintylearning.net">infintylearning.net</a>
            </p>
        </div>

    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
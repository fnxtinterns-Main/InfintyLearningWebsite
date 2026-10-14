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
                Service and our <a href="/privacy">Privacy Policy</a>. If you do not agree,
                please do not use the Platform.
            </p>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">2. Eligibility</h2>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>You must be at least 13 years old to create an account.</li>
                <li>If you are under 18, you must register through a school or with verifiable parental/guardian consent.</li>
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
            <h2 class="detail-h2">5. Content you upload</h2>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li>You retain ownership of content you upload (lessons, assignments, files).</li>
                <li>By uploading, you grant Infinity Learning a licence to store, process and display that content to the intended audience (your school, your batch, or the Platform).</li>
                <li>You confirm you have the right to upload the content and that it does not infringe anyone else's rights.</li>
                <li>You are responsible for ensuring uploaded files comply with data protection and copyright law.</li>
            </ul>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">6. Platform content</h2>
            <p>
                Content provided by Infinity Learning (global lessons, platform design, code,
                branding) is owned by us or our licensors and may not be reproduced without
                permission.
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
            <h2 class="detail-h2">12. Governing law</h2>
            <p>
                These terms are governed by the laws of India. Any dispute will be subject to the
                exclusive jurisdiction of the courts of Jharkhand, India.
            </p>
        </div>

        <div class="card mt-3">
            <h2 class="detail-h2">13. Contact</h2>
            <p>
                For questions about these terms:<br>
                <strong>Email:</strong> <a href="mailto:info@infintylearning.net">info@infintylearning.net</a><br>
                <strong>Website:</strong> <a href="https://infintylearning.net">infintylearning.net</a>
            </p>
        </div>

    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
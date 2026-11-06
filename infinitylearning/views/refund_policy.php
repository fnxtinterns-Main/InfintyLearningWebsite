<?php
use App\Helper;

$pageTitle = 'Cancellation & Refund Policy — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">💳 BILLING &amp; SUBSCRIPTIONS</span>
                <h1>Cancellation &amp; Refund Policy</h1>
                <p>
                    Clear, fair, and transparent guidelines regarding course cancellations, 
                    program enrollments, and refund processing across Infinity Learning.
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

        <!-- 1. Overview & Scope -->
        <div class="card">
            <h2 class="detail-h2">1. Overview &amp; Scope</h2>
            <p>
                At <strong>Infinity Learning</strong> ("we", "us", "the Platform"), we are committed to providing 
                enriching, high-quality educational experiences for schools, teachers, students, and parents. 
                This Cancellation and Refund Policy applies to all paid services, course bundles, specialized 
                academies (including Rising Rooks Chess Academy), counseling sessions, and subscription plans 
                purchased through or hosted on <a href="https://infintylearning.net">infintylearning.net</a>.
            </p>
            <p style="margin-top:1rem">
                By purchasing, enrolling in, or subscribing to any paid service on Infinity Learning, you agree 
                to the terms outlined in this policy as well as our <a href="/terms">Terms of Service</a> and 
                <a href="/privacy">Privacy Policy</a>.
            </p>
        </div>

        <!-- 2. Dual Service Model Distinction -->
        <div class="card mt-3">
            <h2 class="detail-h2">2. Platform Transactions vs. School Tuition Fees</h2>
            <p>
                Because Infinity Learning operates both as a direct educational academy and as an institutional 
                management partner for schools, please note the key distinction regarding payments:
            </p>

            <div class="grid grid-2" style="margin-top:1.25rem">
                <div class="card" style="background:var(--surface-2);border:none">
                    <h3 style="font-size:1rem;color:var(--il-brand,#7C2D5C)">A. Direct Platform Purchases</h3>
                    <p style="font-size:.92rem;line-height:1.6;margin-top:.5rem">
                        Payments for independent AIverse courses, specialized workshops, 1-on-1 counseling, 
                        and Rising Rooks Chess Academy coaching are processed directly by Infinity Learning. 
                        <strong>This policy governs all such transactions in full.</strong>
                    </p>
                </div>
                <div class="card" style="background:var(--surface-2);border:none">
                    <h3 style="font-size:1rem;color:var(--il-brand,#7C2D5C)">B. Institutional School Tuition</h3>
                    <p style="font-size:.92rem;line-height:1.6;margin-top:.5rem">
                        Tuition, admission, and transport fees collected by or invoiced through your enrolled 
                        school's administrative portal are governed solely by your school's official fee policies. 
                        Infinity Learning provides the tracking ERP and does not hold or refund school tuition fees directly.
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. Cancellation Terms -->
        <div class="card mt-3">
            <h2 class="detail-h2">3. Cancellation Guidelines</h2>
            <p>You may request cancellation under the following conditions depending on the service category:</p>

            <div style="margin-top:1rem; display:flex; flex-direction:column; gap:16px;">
                <div style="border-left:3px solid var(--il-brand,#7C2D5C); padding-left:14px;">
                    <h3 style="font-size:1rem; margin-bottom:4px;">📘 Self-Paced Courses &amp; Digital Learning Bundles</h3>
                    <p style="font-size:.92rem; line-height:1.6; margin:0;">
                        Cancellations must be requested within <strong>48 hours</strong> of purchase, provided 
                        you have accessed or completed less than <strong>15%</strong> of the video content, 
                        study material, or chapter assessments.
                    </p>
                </div>

                <div style="border-left:3px solid var(--il-brand,#7C2D5C); padding-left:14px;">
                    <h3 style="font-size:1rem; margin-bottom:4px;">🧑‍🏫 Live Batches, Bootcamps &amp; Masterclasses</h3>
                    <p style="font-size:.92rem; line-height:1.6; margin:0;">
                        Full cancellation is available if requested at least <strong>48 hours prior</strong> to the 
                        first scheduled live session. Once the live batch commences, enrollment is locked and non-refundable.
                    </p>
                </div>

                <div style="border-left:3px solid var(--il-brand,#7C2D5C); padding-left:14px;">
                    <h3 style="font-size:1rem; margin-bottom:4px;">💬 1-on-1 Counseling &amp; Expert Mentorship</h3>
                    <p style="font-size:.92rem; line-height:1.6; margin:0;">
                        Scheduled counseling sessions can be cancelled or rescheduled free of charge if requested at least 
                        <strong>24 hours prior</strong> to the scheduled appointment. Cancellations made under 24 hours 
                        or missed sessions ("no-shows") without advance notice are non-refundable.
                    </p>
                </div>

                <div style="border-left:3px solid var(--il-brand,#7C2D5C); padding-left:14px;">
                    <h3 style="font-size:1rem; margin-bottom:4px;">♟️ Competitions, Tournaments &amp; Special Events</h3>
                    <p style="font-size:.92rem; line-height:1.6; margin:0;">
                        For events with an entry fee, cancellation requests are accepted up to <strong>48 hours prior</strong> 
                        to tournament start or bracket generation. Once event pairings or brackets are released, registration 
                        fees are strictly non-refundable.
                    </p>
                </div>

                <div style="border-left:3px solid var(--il-brand,#7C2D5C); padding-left:14px;">
                    <h3 style="font-size:1rem; margin-bottom:4px;">🏫 Institutional School Subscriptions (B2B)</h3>
                    <p style="font-size:.92rem; line-height:1.6; margin:0;">
                        Annual or term-based school partner subscriptions are governed by their respective Institutional 
                        Service Agreement, typically requiring a <strong>30-day written notice</strong> prior to the 
                        commencement of the next academic billing cycle.
                    </p>
                </div>
            </div>
        </div>

        <!-- 4. Refund Eligibility -->
        <div class="card mt-3">
            <h2 class="detail-h2">4. Refund Eligibility &amp; Criteria</h2>
            <p>
                A refund may be approved under the following qualifying circumstances:
            </p>
            <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.75rem">
                <li><strong>Eligible Timely Cancellation:</strong> Cancellation requested within the designated window specified in Section 3.</li>
                <li><strong>Duplicate Billing:</strong> An accidental duplicate transaction or technical payment gateway glitch resulting in multiple charges for the same service.</li>
                <li><strong>Service Non-Delivery:</strong> Where Infinity Learning is unable to deliver a scheduled course, live batch, or mentorship session due to operational reasons.</li>
                <li><strong>Verified Technical Failure:</strong> Prolonged, continuous technical platform failure attributable to our servers that prevented access to a purchased course for more than 48 hours and could not be remedied.</li>
            </ul>
        </div>

        <!-- 5. Non-Refundable Items -->
        <div class="card mt-3">
            <h2 class="detail-h2">5. Non-Refundable Situations</h2>
            <p>Refunds will <strong>not</strong> be granted under the following scenarios:</p>
            <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.75rem">
                <li>Services, tutoring, or 1-on-1 counseling sessions that have already been conducted or attended.</li>
                <li>Courses where more than 15% of the modules have been completed or significant course assets downloaded.</li>
                <li>Issued official digital certificates with cryptographic QR code validation.</li>
                <li>Failure to attend a live class, workshop, or competition without required prior notice.</li>
                <li>Incompatibility or deficiencies in user-side hardware, personal internet bandwidth, or local devices.</li>
                <li>Account suspension, ban, or termination resulting from academic dishonesty, harassment, or violations of our <a href="/terms">Terms of Service</a>.</li>
                <li>Institutional school tuition and transport fees (must be directed to your school administration).</li>
            </ul>
        </div>

        <!-- 6. How to Request a Refund -->
        <div class="card mt-3">
            <h2 class="detail-h2">6. How to Request a Cancellation or Refund</h2>
            <p>
                To initiate a cancellation or refund request, please email our support team at 
                <a href="mailto:info@infintylearning.net" style="font-weight:700;">info@infintylearning.net</a> 
                with the subject line: <code>[Refund Request] - [Your Full Name] - [Order / Student ID]</code>.
            </p>
            <p style="margin-top:.75rem">Please include the following details to expedite your request:</p>
            <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.5rem">
                <li>Registered Student / User Full Name and registered Phone Number</li>
                <li>Registered Email Address</li>
                <li>Course / Workshop / Service Name</li>
                <li>Date of Transaction and Payment Receipt or Transaction ID</li>
                <li>Detailed reason for the cancellation / refund request</li>
            </ul>
        </div>

        <!-- 7. Processing & Timeline -->
        <div class="card mt-3">
            <h2 class="detail-h2">7. Refund Processing &amp; Timelines</h2>
            <ul style="margin-left:1.25rem;line-height:1.9">
                <li><strong>Review Window:</strong> Our billing and academic team will review your request within <strong>2 to 3 business days</strong>.</li>
                <li><strong>Original Payment Method:</strong> Approved refunds will be credited back exclusively to the original payment source (Credit/Debit Card, Net Banking, or UPI ID).</li>
                <li><strong>Settlement Time:</strong> Once initiated, banking and payment gateways typically take <strong>5 to 7 business days</strong> to reflect the amount in your account.</li>
                <li><strong>Deductions:</strong> Standard payment gateway transaction fees or applicable government taxes may be non-refundable where mandated by payment intermediaries.</li>
            </ul>
        </div>

        <!-- 8. Modifications -->
        <div class="card mt-3">
            <h2 class="detail-h2">8. Modifications to this Policy</h2>
            <p>
                Infinity Learning reserves the right to modify, amend, or update this Cancellation &amp; Refund Policy 
                at any time. Any changes will become effective immediately upon posting to this URL. Continued use of 
                the Platform after updates constitutes acceptance of the modified policy.
            </p>
        </div>

        <!-- 9. Contact & Grievance -->
        <div class="card mt-3">
            <h2 class="detail-h2">9. Grievance Redressal &amp; Support Contact</h2>
            <p>
                If you have any questions, concerns, or unresolved issues regarding this policy, please reach out to us:
            </p>
            <p style="margin-top:.75rem;line-height:1.8">
                <strong>Infinity Learning Support Desk</strong><br>
                📧 <strong>Email:</strong> <a href="mailto:info@infintylearning.net">info@infintylearning.net</a><br>
                📞 <strong>Phone:</strong> <a href="tel:9801506511">+91 98015 06511</a><br>
                🌐 <strong>Website:</strong> <a href="https://infintylearning.net">infintylearning.net</a><br>
                📍 <strong>Jurisdiction:</strong> Subject to the exclusive jurisdiction of the courts of Jharkhand, India.
            </p>
        </div>

    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

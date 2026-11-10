<?php
use App\Helper;

$pageTitle = 'Privacy Policy & Statutory DPDP Notice — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">🔒 DPDP ACT 2023 COMPLIANT</span>
                <h1>Privacy Policy &amp; Data Protection Notice</h1>
                <p>
                    Statutory Privacy Notice under the <em>Digital Personal Data Protection Act, 2023 (DPDP Act)</em>, 
                    Information Technology Act 2000, and global privacy standards.
                </p>
                <p class="muted" style="font-size:.85rem;margin-top:.75rem">
                    Last updated: <?= date('d M Y') ?> · Effective Date: 11 August 2023 (As amended)
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container-narrow">

        <!-- Statutory Language Toggle (Section 5(3) DPDP Act 2023) -->
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px; background:var(--surface-2, #F8FAFC); padding:14px 18px; border-radius:12px; border:1px solid #E2E8F0;">
            <div style="font-size:13.5px; font-weight:700; color:var(--text-color, #1e293b); display:flex; align-items:center; gap:8px;">
                <span>🌐</span> <span>Statutory Notice Language / भाषा (DPDP Act § 5(3)):</span>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="button" id="dpdpBtnEn" onclick="switchDpdpLanguage('en')" class="btn btn-primary" style="padding:7px 16px; font-size:13px; font-weight:700; border-radius:8px; cursor:pointer;">
                    English (Standard)
                </button>
                <button type="button" id="dpdpBtnHi" onclick="switchDpdpLanguage('hi')" class="btn btn-outline" style="padding:7px 16px; font-size:13px; font-weight:700; border-radius:8px; cursor:pointer; background:#fff;">
                    हिन्दी (Eighth Schedule / 8वीं अनुसूची)
                </button>
            </div>
        </div>

        <!-- ==================================================================== -->
        <!-- ENGLISH STATUTORY PRIVACY POLICY                                    -->
        <!-- ==================================================================== -->
        <div id="dpdpNoticeEn">

            <!-- 1: Data Fiduciary & Relationship -->
            <div class="card">
                <h2 class="detail-h2">1. Who We Are (Data Fiduciary Identification)</h2>
                <p>
                    <strong>Infinity Learning</strong> (operated by FutureNxt Technologies, registered in India) 
                    acts as a <strong>Data Fiduciary</strong> under Section 2(i) of the <em>Digital Personal Data Protection Act, 2023 ("DPDP Act")</em> 
                    for independent candidates, direct subscribers, and platform visitors.
                </p>
                <p style="margin-top:1rem">
                    Because the Platform supports both institutional and direct learning, the responsibility for
                    your personal data is determined by your enrollment channel:
                </p>

                <div class="grid grid-2" style="margin-top:1.25rem">
                    <div class="card" style="background:var(--surface-2, #F8FAFC);border:none;padding:16px;">
                        <h3 style="font-size:1rem;margin:0 0 8px;">If you joined through an Affiliated School</h3>
                        <p style="font-size:.92rem;line-height:1.6;margin:0;">
                            Your <strong>school is the primary Data Fiduciary</strong> (Data Controller). Infinity Learning acts as a
                            <strong>Data Processor</strong> on the school's written instructions. Demands to access, rectify, or purge 
                            educational records may be routed through your school administrator or directly via our portal.
                        </p>
                    </div>
                    <div class="card" style="background:var(--surface-2, #F8FAFC);border:none;padding:16px;">
                        <h3 style="font-size:1rem;margin:0 0 8px;">If you joined Independently or via BPSC Portal</h3>
                        <p style="font-size:.92rem;line-height:1.6;margin:0;">
                            If you registered independently for online courses, mock tests, or BPSC TRE preparation,
                            Infinity Learning is the direct <strong>Data Fiduciary</strong> responsible for the secure processing 
                            and governance of your digital personal data.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2: What We Collect -->
            <div class="card mt-3">
                <h2 class="detail-h2">2. What Digital Personal Data We Collect</h2>
                <p>
                    Pursuant to Section 5(1)(i) of the DPDP Act 2023, we collect only the minimal digital personal data 
                    necessary for authentic educational delivery, verifiable examination integrity, and statutory compliance:
                </p>

                <h3 style="font-size:1rem;margin-top:1.25rem">Account &amp; Identity Data</h3>
                <ul style="margin-left:1.25rem;line-height:1.9">
                    <li>Full legal name, email address, mobile phone number</li>
                    <li>Credentials: Password stored strictly as an irreversible Argon2id cryptographic hash (never in plain text)</li>
                    <li>Designation / Role: Student/Candidate, Teacher/Faculty, or Institutional Administrator</li>
                    <li>School affiliation or target competitive examination (e.g., BPSC TRE cadre level)</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem">Student &amp; Candidate Profile</h3>
                <ul style="margin-left:1.25rem;line-height:1.9">
                    <li>Age, gender, current academic class or target teaching grade</li>
                    <li>State, city, and examination district of Bihar / India</li>
                    <li>Parent or legal guardian contact details (for students under 18 years of age)</li>
                    <li>Self-declared academic subjects and preparation streams</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem">Teacher &amp; Faculty Profile</h3>
                <ul style="margin-left:1.25rem;line-height:1.9">
                    <li>Academic qualifications, subject specialization, STET/TET credential references</li>
                    <li>Years of teaching experience, short biographical brief (for teacher verification)</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem">Academic &amp; Learning Activity</h3>
                <ul style="margin-left:1.25rem;line-height:1.9">
                    <li>Lessons completed, mock test scorecards, time-per-question analytics, and answer logs</li>
                    <li>Proctored CBT session logs (tab switch alerts and session timestamps for exam honesty)</li>
                    <li>Attendance markings, assignments, doubts, counseling exchanges, and course completion certificates</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem">Technical &amp; Audit Telemetry</h3>
                <ul style="margin-left:1.25rem;line-height:1.9">
                    <li>IP address, browser user-agent, session identifiers, and CSRF protection tokens</li>
                    <li>Consent timestamps, terms acceptance logs, and administrative audit trails</li>
                </ul>
            </div>

            <!-- 3: Purpose of Processing -->
            <div class="card mt-3">
                <h2 class="detail-h2">3. Purpose of Processing (Specified Purposes — Section 5)</h2>
                <p>Digital personal data is processed solely for the following specified, lawful purposes:</p>
                <ul style="margin-left:1.25rem;line-height:1.9">
                    <li>To verify user identity and authenticate access across web and mobile terminals</li>
                    <li>To provide curriculum-aligned lessons, syllabus trackers, notes, and digital library resources</li>
                    <li>To administer proctored online computer-based tests (CBT), grade test responses, and issue scorecards</li>
                    <li>To track attendance, issue verified completion certificates, and deliver academic counseling</li>
                    <li>To dispatch essential transactional messages (OTP login codes, password resets, enrollment confirmations)</li>
                    <li>To detect, prevent, and mitigate security incidents, academic fraud, or unlawful intrusions</li>
                    <li>To maintain statutory accounts, billing ledgers, and tax invoices required under Indian law</li>
                </ul>
            </div>

            <!-- 4: Unbundled Consent & Withdrawal -->
            <div class="card mt-3">
                <h2 class="detail-h2">4. Notice, Consent &amp; Right to Withdraw (Sections 5 &amp; 6)</h2>
                <p>
                    Under Section 6 of the DPDP Act 2023, consent must be free, specific, informed, unconditional, and unambiguous with clear affirmative action:
                </p>
                <ul style="margin-left:1.25rem;line-height:1.9">
                    <li><strong>No Dark Patterns:</strong> All consent checkboxes on registration forms are unchecked by default. Essential educational service terms are unbundled from optional marketing communications.</li>
                    <li><strong>Right to Withdraw (Section 6(4)):</strong> You have the unconditional right to withdraw your consent at any time. Withdrawal does not affect the lawfulness of processing undertaken prior to withdrawal.</li>
                    <li><strong>Easy Opt-Out:</strong> Marketing emails contain a 1-click unsubscribe link. You may also withdraw marketing consent at any time via our <a href="/unsubscribe" style="color:var(--il-brand,#7C2D5C); font-weight:700;">Unsubscribe Center</a> or by submitting a request through our <a href="/data-request" style="color:var(--il-brand,#7C2D5C); font-weight:700;">Data Rights Portal</a>.</li>
                </ul>
            </div>

            <!-- 5: Children's Data Protection (Section 9) -->
            <div class="card mt-3" style="border-left:4px solid #F59E0B;">
                <h2 class="detail-h2" style="color:#B45309; display:flex; align-items:center; gap:8px;">
                    <span>👶</span> 5. Processing of Children's Personal Data (Section 9 DPDP Act 2023)
                </h2>
                <p style="font-size:14px; line-height:1.65; color:#1F2937;">
                    The Digital Personal Data Protection Act, 2023 defines a "child" as any individual who has not completed eighteen (18) years of age. Infinity Learning enforces strict safeguards under Section 9:
                </p>
                <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:10px; padding:18px; margin-top:12px;">
                    <ul style="margin-left:1.25rem; line-height:1.85; font-size:13.5px; color:#92400E; margin-bottom:0;">
                        <li><strong>Verifiable Parental Consent (§ 9(1)):</strong> Before processing any digital personal data of a minor student, we require verifiable consent from the parent or lawful guardian. Independent minor registrations require affirmative parental consent recording parent details, timestamp, and IP address.</li>
                        <li><strong>Zero Behavioural Monitoring or Tracking (§ 9(3)):</strong> Infinity Learning does <strong>NOT</strong> engage in tracking, behavioural monitoring, or profiling of children. No telemetry or analytics are ever used to profile minor students.</li>
                        <li><strong>Absolute Prohibition on Targeted Advertising (§ 9(3)):</strong> We do <strong>NOT</strong> serve targeted advertisements to children, nor do we share children's personal data with marketing networks or ad tech brokers.</li>
                        <li><strong>Child Well-Being Safeguard (§ 9(2)):</strong> We do not undertake any processing of personal data that causes or is likely to cause any detrimental effect on the physical, mental, or moral well-being of a child.</li>
                        <li><strong>Parental Rights:</strong> Parents or lawful guardians can review, update, or demand permanent erasure of their child's data at any time through our <a href="/data-request" style="color:#B45309; font-weight:700; text-decoration:underline;">Data Rights Portal</a>.</li>
                    </ul>
                </div>
            </div>

            <!-- 6: Retention & Erasure -->
            <div class="card mt-3">
                <h2 class="detail-h2">6. Definite Retention Schedules &amp; Automatic Erasure (Section 8(7))</h2>
                <p>
                    In accordance with Section 8(7) of the DPDP Act 2023, personal data is erased once the specified purpose 
                    has been fulfilled or when consent is withdrawn:
                </p>
                <ul style="margin-left:1.25rem;line-height:1.9">
                    <li><strong>Active Accounts:</strong> Retained for the duration of active enrollment, exam preparation, or institutional subscription.</li>
                    <li><strong>Dormant Accounts:</strong> Accounts inactive for <strong>12 consecutive months</strong> receive notice; after <strong>24 months</strong> of continuous inactivity, accounts are permanently purged unless retention is mandated by academic boards.</li>
                    <li><strong>Statutory Financial Records:</strong> Payment transaction receipts and tax invoices are retained for <strong>7 years</strong> in compliance with Indian GST, Income Tax, and corporate audit statutes.</li>
                    <li><strong>Security &amp; Audit Logs:</strong> Access and proctoring telemetry are rotated and purged after <strong>12 months</strong>.</li>
                    <li><strong>Right to be Forgotten (RTBF):</strong> Verified erasure requests submitted through our Data Rights Portal are executed within <strong>30 calendar days</strong>.</li>
                </ul>
            </div>

            <!-- 6A: AI Model Training Shield -->
            <div class="card mt-3" style="background:#FFF9F4; border:1.5px solid #F3DECB;">
                <h2 class="detail-h2" style="color:#B45309; display:flex; align-items:center; gap:8px;">
                    <span>🛡️</span> Strict Prohibition on Artificial Intelligence Model Training
                </h2>
                <p style="font-size:14px; line-height:1.65; color:#374151;">
                    Infinity Learning maintains an uncompromising policy protecting student and educator intellectual property:
                </p>
                <ul style="margin-left:1.25rem; line-height:1.8; font-size:13.5px; color:#4B5563; margin-top:.5rem;">
                    <li>Your assignments, mock answers, essays, study notes, and counseling discussions are <strong>NEVER used to train, test, or fine-tune public foundation AI models</strong> (e.g. OpenAI, Anthropic, Google Gemini, or open-source LLMs).</li>
                    <li>No user-created curriculum or test responses are harvested or sold for machine learning corpus compilation.</li>
                    <li>Any synthetic illustrations generated on the platform are transparently labeled with an <strong>[AI Generated]</strong> disclosure badge.</li>
                </ul>
            </div>

            <!-- 7: Statutory Rights under DPDP Act 2023 -->
            <div class="card mt-3">
                <h2 class="detail-h2">7. Your Statutory Rights (Sections 11, 12, 13 &amp; 14 DPDP Act 2023)</h2>
                <p>
                    As a <strong>Data Principal</strong> under the <em>Digital Personal Data Protection Act, 2023</em>, you possess full legal entitlement to exercise the following statutory rights:
                </p>
                <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.75rem">
                    <li>
                        <strong>Right of Access &amp; Summary (Section 11):</strong> You have the right to obtain a summary of personal data being processed, the processing activities undertaken, and the identities of all other Data Fiduciaries and Processors with whom your data has been shared.
                    </li>
                    <li>
                        <strong>Right to Correction &amp; Erasure (Section 12):</strong> You have the right to correct inaccurate or misleading personal data, complete incomplete data, and request the permanent erasure of personal data that is no longer necessary for the specified purpose.
                    </li>
                    <li>
                        <strong>Right to Grievance Redressal (Section 13):</strong> You have the right to have readily available means of grievance redressal provided by our Data Fiduciary in respect of any act or omission regarding your data.
                    </li>
                    <li>
                        <strong>Right to Nominate (Section 14 DPDP Act 2023):</strong> You have the statutory right to nominate any individual who shall, in the event of your death or physical/mental incapacity, exercise your rights as a Data Principal. You can record, update, or revoke your nominee designation at any time.
                    </li>
                    <li>
                        <strong>Right to Withdraw Consent (Section 6(4)):</strong> You can withdraw previously granted consent at any time without negative consequences on previously lawful processing.
                    </li>
                </ul>

                <div style="margin-top:1.25rem; background:var(--surface-2, #F8FAFC); padding:18px; border-radius:10px; border:1px solid #E2E8F0;">
                    <p style="margin:0 0 10px; font-weight:700; font-size:14px; color:#0F172A;">
                        Exercise any statutory right or submit a nomination:
                    </p>
                    <a href="/data-request" class="btn btn-primary" style="padding:9px 20px; font-size:13.5px; text-decoration:none; font-weight:700; display:inline-block;">
                        Open Data Rights &amp; Nomination Portal →
                    </a>
                </div>
            </div>

            <!-- 8: Subprocessors & Data Localization -->
            <div class="card mt-3">
                <h2 class="detail-h2">8. Data Localization &amp; Authorized Subprocessors (Section 8(6))</h2>
                <p>
                    All digital personal data collected by Infinity Learning is stored and processed exclusively in 
                    certified, Tier-IV data centers located within <strong>Mumbai, India</strong>. We strictly honor 
                    Indian data localization guidelines.
                </p>
                <p style="margin-top:1rem">
                    For transparency, review our verified infrastructure partners, data processing roles, and security compliance:
                    <br>
                    👉 <a href="/subprocessors" style="color:var(--il-brand,#7C2D5C); font-weight:700; text-decoration:underline;">Authorized Subprocessors Schedule</a> · 
                    <a href="/dpa" style="color:var(--il-brand,#7C2D5C); font-weight:700; text-decoration:underline;">Data Processing Agreement (DPA)</a>
                </p>
            </div>

            <!-- 9: Security Safeguards -->
            <div class="card mt-3">
                <h2 class="detail-h2">9. Technical &amp; Organizational Security Safeguards (Section 8(5))</h2>
                <p>
                    Under Section 8(5) of the DPDP Act 2023, Infinity Learning implements reasonable security safeguards 
                    to prevent personal data breaches:
                </p>
                <ul style="margin-left:1.25rem;line-height:1.9;margin-top:.75rem">
                    <li>End-to-end transport layer security (TLS 1.3 / HTTPS encryption in transit)</li>
                    <li>Argon2id cryptographic password hashing with unique salt per user</li>
                    <li>Mandatory CSRF tokens on all mutating web requests and form submissions</li>
                    <li>Automated IP-based rate limiting against credential-stuffing and brute-force attacks</li>
                    <li>Strict role-based access controls (RBAC) separating student, teacher, and administrator domains</li>
                    <li>Detailed, tamper-evident audit logs of administrative and authentication activities</li>
                </ul>
            </div>

            <!-- 10: Cookies & Local Storage -->
            <div class="card mt-3">
                <h2 class="detail-h2">10. Cookies, Sessions &amp; Tracking Disclosures</h2>
                <p>
                    We operate on a privacy-first architecture. We utilize only essential, HTTP-only, secure session cookies 
                    strictly required to maintain your login session and CSRF security. We do <strong>NOT</strong> utilize 
                    third-party behavioural tracking cookies or cross-site advertising pixels.
                </p>
            </div>

            <!-- 11: Grievance Redressal & DPBI Escalation -->
            <div class="card mt-3" style="border:1.5px solid #CBD5E1;">
                <h2 class="detail-h2" style="color:#0F172A;">11. Grievance Redressal Officer &amp; Escalation to Data Protection Board of India (DPBI)</h2>
                <p>
                    Pursuant to Section 5(1)(iii) and Section 13 of the <em>Digital Personal Data Protection Act, 2023</em>, 
                    Infinity Learning has appointed a designated Grievance Redressal Officer to address all queries, 
                    concerns, and rights requests:
                </p>

                <div style="background:var(--surface-2, #F8FAFC); padding:18px; border-radius:10px; margin-top:12px; font-size:13.5px; line-height:1.75; border:1px solid #E2E8F0;">
                    <strong>Designated Grievance &amp; Data Protection Officer:</strong> Legal &amp; Data Compliance Cell<br>
                    <strong>Data Fiduciary Entity:</strong> FutureNxt Technologies / Infinity Learning<br>
                    <strong>Email:</strong> <a href="mailto:info@infintylearning.net" style="color:var(--il-brand,#7C2D5C); font-weight:700;">info@infintylearning.net</a><br>
                    <strong>Jurisdiction &amp; Address:</strong> Dhanbad, Jharkhand &amp; Mumbai, Maharashtra, India<br>
                    <strong>Response Timeline:</strong> Formal acknowledgement within <strong>48 hours</strong>; complete investigation and final resolution within <strong>30 calendar days</strong>.
                </div>

                <!-- Statutory DPBI Notice (§ 13(3)) -->
                <div style="background:#EFF6FF; border:1.5px solid #BFDBFE; border-radius:10px; padding:18px; margin-top:16px;">
                    <h3 style="margin:0 0 8px; font-size:14.5px; font-weight:800; color:#1E40AF; display:flex; align-items:center; gap:8px;">
                        <span>⚖️</span> Statutory Right of Appeal to the Data Protection Board of India (DPBI)
                    </h3>
                    <p style="font-size:13px; line-height:1.65; color:#1E3A8A; margin:0;">
                        In accordance with <strong>Section 13(3) of the Digital Personal Data Protection Act, 2023</strong>, 
                        if your grievance is not resolved by our Grievance Officer within thirty (30) days from submission, 
                        or if you are dissatisfied with the response or resolution provided, you have the statutory right 
                        to file an official complaint or appeal directly before the <strong>Data Protection Board of India (DPBI)</strong> 
                        at its official government portal (<a href="https://www.meity.gov.in" target="_blank" rel="noopener" style="color:#1E40AF; font-weight:700; text-decoration:underline;">dpbi.gov.in / MeitY</a>).
                    </p>
                </div>
            </div>

        </div>


        <!-- ==================================================================== -->
        <!-- HINDI STATUTORY PRIVACY POLICY (EIGHTH SCHEDULE — SECTION 5(3))     -->
        <!-- ==================================================================== -->
        <div id="dpdpNoticeHi" style="display:none;">

            <div class="card" style="border-left:4px solid var(--il-brand, #7C2D5C);">
                <div style="display:inline-block; background:#F3E8FF; color:#6B21A8; font-weight:800; font-size:11px; padding:4px 10px; border-radius:6px; margin-bottom:10px;">
                    भारत का संविधान — आठवीं अनुसूची भाषा (धारा 5(3) DPDP अधिनियम 2023)
                </div>
                <h2 class="detail-h2" style="font-size:22px; color:#0F172A;">
                    डिजिटल व्यक्तिगत डेटा संरक्षण अधिनियम, 2023 — सांविधिक गोपनीयता सूचना
                </h2>
                <p style="font-size:14px; line-height:1.7; color:#334155;">
                    यह सांविधिक सूचना <strong>डिजिटल व्यक्तिगत डेटा संरक्षण अधिनियम, 2023 (DPDP Act 2023)</strong> 
                    की धारा 5 के अंतर्गत जारी की गई है। यह बताती है कि इन्फिनिटी लर्निंग (Infinity Learning / FutureNxt Technologies) 
                    आपका व्यक्तिगत डेटा किस प्रकार एकत्र, उपयोग और सुरक्षित रखती है।
                </p>
            </div>

            <!-- 1. डेटा न्यासी (Data Fiduciary) -->
            <div class="card mt-3">
                <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:10px;">1. डेटा न्यासी की पहचान (Data Fiduciary)</h3>
                <p style="font-size:13.5px; line-height:1.7; color:#475569;">
                    इन्फिनिटी लर्निंग (फ्यूचरनेक्स्ट टेक्नोलॉजीज) भारत में डिजिटल व्यक्तिगत डेटा संरक्षण अधिनियम की धारा 2(i) 
                    के तहत <strong>डेटा न्यासी (Data Fiduciary)</strong> के रूप में कार्य करती है। स्वतंत्र छात्र, अभ्यर्थी और शिक्षक 
                    सीधे हमारे माध्यम से अपने अधिकारों का प्रयोग कर सकते हैं। स्कूल से जुड़े विद्यार्थियों के लिए उनका स्कूल 
                    प्राथमिक डेटा नियंत्रक होता है।
                </p>
            </div>

            <!-- 2. कौन सा डेटा एकत्र किया जाता है -->
            <div class="card mt-3">
                <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:10px;">2. हम कौन सा व्यक्तिगत डेटा एकत्र करते हैं (धारा 5(1))</h3>
                <ul style="margin-left:1.25rem; line-height:1.85; font-size:13.5px; color:#475569;">
                    <li><strong>पहचान विवरण:</strong> पूरा नाम, ईमेल पता, मोबाइल नंबर, पद/भूमिका (छात्र, शिक्षक, एडमिन)।</li>
                    <li><strong>पासवर्ड सुरक्षा:</strong> पासवर्ड कभी भी सादे टेक्स्ट में नहीं रखा जाता, यह पूरी तरह से सुरक्षित Argon2id क्रिप्टोग्राफिक हैश के रूप में रहता है।</li>
                    <li><strong>अकादमिक और परीक्षा विवरण:</strong> कक्षा, परीक्षा स्तर (BPSC TRE), मॉक टेस्ट परिणाम, ओएमआर/सीबीटी उत्तर और प्रमाणपत्र।</li>
                    <li><strong>नाबालिगों के लिए माता-पिता की जानकारी:</strong> 18 वर्ष से कम आयु के विद्यार्थियों के अभिभावक का नाम व संपर्क।</li>
                    <li><strong>तकनीकी विवरण:</strong> सुरक्षित लॉगिन और ऑडिट के लिए आईपी एड्रेस और सेशन कुकीज़।</li>
                </ul>
            </div>

            <!-- 3. डेटा संग्रह का उद्देश्य -->
            <div class="card mt-3">
                <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:10px;">3. डेटा उपयोग का निर्दिष्ट उद्देश्य (Specified Purposes)</h3>
                <p style="font-size:13.5px; line-height:1.7; color:#475569;">
                    डेटा का उपयोग केवल निम्नलिखित वैध शैक्षणिक उद्देश्यों के लिए किया जाता है:
                </p>
                <ul style="margin-left:1.25rem; line-height:1.85; font-size:13.5px; color:#475569;">
                    <li>लॉगिन प्रमाणीकरण और ओटीपी सत्यापन।</li>
                    <li>पाठ्यक्रम, अध्ययन सामग्री, लाइव मॉक टेस्ट और सीबीटी परीक्षा का संचालन।</li>
                    <li>सत्यापित अंकपत्र, प्रगति रिपोर्ट और डिजिटल प्रमाणपत्र जारी करना।</li>
                    <li>धोखाधड़ी, अनधिकृत पहुंच और सिस्टम दुरुपयोग की रोकथाम।</li>
                    <li>भारतीय कानून और टैक्स नियमों (GST) के अनुसार वैध रसीदें तैयार करना।</li>
                </ul>
            </div>

            <!-- 4. बच्चों का डेटा (धारा 9) -->
            <div class="card mt-3" style="background:#FFFBEB; border:1.5px solid #FDE68A;">
                <h3 style="font-size:16px; font-weight:800; color:#B45309; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
                    <span>👶</span> 4. बच्चों के व्यक्तिगत डेटा का प्रसंस्करण (धारा 9 DPDP अधिनियम 2023)
                </h3>
                <p style="font-size:13.5px; line-height:1.7; color:#92400E;">
                    अधिनियम के तहत 18 वर्ष से कम आयु के प्रत्येक व्यक्ति को "बच्चा (Child)" माना गया है:
                </p>
                <ul style="margin-left:1.25rem; line-height:1.85; font-size:13.5px; color:#92400E;">
                    <li><strong>माता-पिता की सत्यापन योग्य सहमति (धारा 9(1)):</strong> नाबालिग छात्र के डेटा प्रसंस्करण से पूर्व माता-पिता या कानूनी अभिभावक की स्पष्ट सहमति अनिवार्य है।</li>
                    <li><strong>ट्रैकिंग एवं बिहेवियरल प्रोफाइलिंग पर पूर्ण रोक (धारा 9(3)):</strong> बच्चों की किसी भी प्रकार की ट्रैकिंग, बिहेवियरल निगरानी या प्रोफाइलिंग नहीं की जाती है।</li>
                    <li><strong>लक्षित विज्ञापनों (Targeted Ads) पर पूर्ण प्रतिबंध (धारा 9(3)):</strong> बच्चों को कोई लक्षित विज्ञापन नहीं दिखाए जाते हैं और न ही उनका डेटा किसी विज्ञापन नेटवर्क को दिया जाता है।</li>
                    <li><strong>बच्चे की भलाई की सुरक्षा (धारा 9(2)):</strong> ऐसा कोई डेटा प्रसंस्करण नहीं किया जाता जिसका बच्चे के शारीरिक या मानसिक स्वास्थ्य पर प्रतिकूल प्रभाव पड़े।</li>
                </ul>
            </div>

            <!-- 5. डेटा प्रिंसिपल के सांविधिक अधिकार -->
            <div class="card mt-3">
                <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:10px;">5. आपके सांविधिक अधिकार (धारा 6, 11, 12, 13 एवं 14)</h3>
                <ul style="margin-left:1.25rem; line-height:1.85; font-size:13.5px; color:#475569;">
                    <li><strong>सूचना एवं सारांश प्राप्त करने का अधिकार (धारा 11):</strong> अपने सभी डिजिटल डेटा की प्रतिलिपि देखने और डाउनलोड करने का अधिकार।</li>
                    <li><strong>सुधार और विलोपन का अधिकार (धारा 12):</strong> गलत विवरण को ठीक कराने या अपनी प्रोफाइल स्थायी रूप से मिटाने (राइट टू बी फॉरगॉटन) का अधिकार।</li>
                    <li><strong>नामांकन का अधिकार (धारा 14 DPDP अधिनियम):</strong> मृत्यु या असमर्थता की स्थिति में अपने डेटा अधिकारों का प्रयोग करने हेतु किसी अन्य व्यक्ति को नामांकित (Nominate) करने का अधिकार।</li>
                    <li><strong>सहमति वापस लेने का अधिकार (धारा 6(4)):</strong> किसी भी समय बिना किसी बाधा के दी गई सहमति वापस लेने की सुविधा।</li>
                    <li><strong>शिकायत निवारण का अधिकार (धारा 13):</strong> डेटा संबंधी समस्याओं के त्वरित समाधान का अधिकार।</li>
                </ul>

                <div style="margin-top:16px; background:#F8FAFC; padding:14px; border-radius:8px; border:1px solid #E2E8F0;">
                    <a href="/data-request" class="btn btn-primary" style="padding:8px 18px; font-size:13px; text-decoration:none; font-weight:700;">
                        डेटा अधिकार अनुरोध या नामांकन फॉर्म खोलें →
                    </a>
                </div>
            </div>

            <!-- 6. शिकायत निवारण एवं भारतीय डेटा संरक्षण बोर्ड (DPBI) -->
            <div class="card mt-3" style="background:#EFF6FF; border:1.5px solid #BFDBFE;">
                <h3 style="font-size:16px; font-weight:800; color:#1E40AF; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
                    <span>⚖️</span> 6. शिकायत निवारण एवं भारतीय डेटा संरक्षण बोर्ड (DPBI) में शिकायत (धारा 13(3))
                </h3>
                <p style="font-size:13.5px; line-height:1.7; color:#1E3A8A;">
                    <strong>शिकायत निवारण अधिकारी (Grievance Officer):</strong> विधिक एवं डेटा संरक्षण प्रकोष्ठ<br>
                    <strong>संस्था:</strong> फ्यूचरनेक्स्ट टेक्नोलॉजीज / इन्फिनिटी लर्निंग<br>
                    <strong>ईमेल:</strong> <a href="mailto:info@infintylearning.net" style="color:#1E40AF; font-weight:700;">info@infintylearning.net</a><br>
                    <strong>निवारण समय सीमा:</strong> 48 घंटों में पावती और अधिकतम <strong>30 दिनों</strong> के भीतर पूर्ण समाधान।
                </p>
                <div style="margin-top:12px; padding-top:12px; border-top:1px dashed #93C5FD; font-size:13px; color:#1E3A8A; line-height:1.6;">
                    <strong>भारतीय डेटा संरक्षण बोर्ड में अपील का अधिकार (धारा 13(3)):</strong> यदि हमारी संस्था 30 दिनों में आपकी शिकायत का समाधान नहीं करती है, या आप दिए गए समाधान से असंतुष्ट हैं, तो आपको <strong>भारतीय डेटा संरक्षण बोर्ड (Data Protection Board of India - DPBI)</strong> के समक्ष आधिकारिक शिकायत या अपील दर्ज करने का पूर्ण वैधानिक अधिकार प्राप्त है।
                </div>
            </div>

        </div>

    </div>
</section>

<script>
function switchDpdpLanguage(lang) {
    const enNotice = document.getElementById('dpdpNoticeEn');
    const hiNotice = document.getElementById('dpdpNoticeHi');
    const btnEn = document.getElementById('dpdpBtnEn');
    const btnHi = document.getElementById('dpdpBtnHi');
    
    if (lang === 'hi') {
        if (enNotice) enNotice.style.display = 'none';
        if (hiNotice) hiNotice.style.display = 'block';
        if (btnHi) {
            btnHi.className = 'btn btn-primary';
            btnHi.style.background = '';
        }
        if (btnEn) {
            btnEn.className = 'btn btn-outline';
            btnEn.style.background = '#fff';
        }
    } else {
        if (enNotice) enNotice.style.display = 'block';
        if (hiNotice) hiNotice.style.display = 'none';
        if (btnEn) {
            btnEn.className = 'btn btn-primary';
            btnEn.style.background = '';
        }
        if (btnHi) {
            btnHi.className = 'btn btn-outline';
            btnHi.style.background = '#fff';
        }
    }
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

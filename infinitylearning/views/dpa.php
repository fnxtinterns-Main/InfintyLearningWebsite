<?php
use App\Helper;

$pageTitle = 'Data Processing Agreement (DPA) — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">📜 INSTITUTIONAL COMPLIANCE</span>
                <h1>Data Processing Agreement (DPA)</h1>
                <p>
                    Standard contractual clauses governing the processing of educational and student 
                    personal data between Educational Institutions (Data Controllers) and Infinity Learning (Data Processor).
                </p>
                <p class="muted" style="font-size:.85rem;margin-top:.75rem">
                    Effective: <?= date('d M Y') ?> · Digital Personal Data Protection Act 2023 (DPDP) &amp; Global Privacy Standards
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container-narrow">

        <div class="card" style="padding:28px; margin-bottom:24px;">
            <h2 class="detail-h2" style="margin-top:0;">1. Scope &amp; Purpose</h2>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0 0 12px;">
                This Data Processing Agreement ("DPA") supplements the <a href="/terms" style="color:#7C2D5C; font-weight:700;">Terms of Service</a> between the Educational Institution ("School", "Customer", or "Data Controller") and Infinity Learning ("Platform", "We", or "Data Processor").
            </p>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0;">
                This DPA applies to all processing of student, teacher, and parent personal data carried out by Infinity Learning on behalf of the School in connection with the delivery of school ERP, timetable management, attendance, LMS, and assessment features.
            </p>
        </div>

        <div class="card" style="padding:28px; margin-bottom:24px;">
            <h2 class="detail-h2" style="margin-top:0;">2. Roles &amp; Responsibilities</h2>
            <ul style="margin:0 0 14px 20px; font-size:13.5px; color:#475569; line-height:1.75;">
                <li><strong>The School as Data Controller:</strong> The School determines the lawful basis for processing, enrolls authorized students, teachers, and batches, and maintains primary guardianship over educational records in compliance with applicable education board standards.</li>
                <li><strong>Infinity Learning as Data Processor:</strong> Infinity Learning processes data solely upon the documented instructions of the School and for the sole purpose of operating the Platform.</li>
                <li><strong>Prohibition on Secondary Commercial Use:</strong> Infinity Learning shall NEVER monetize, sell, license, or exploit school data for commercial advertising, marketing, or behavioral tracking.</li>
            </ul>
        </div>

        <div class="card" style="padding:28px; margin-bottom:24px;">
            <h2 class="detail-h2" style="margin-top:0;">3. Prevention of Unauthorized AI Model Training</h2>
            <div style="background:#FFF9F4; border:1px solid #F3DECB; border-left:4px solid #FF7A18; border-radius:8px; padding:16px 18px; margin-bottom:14px;">
                <h3 style="margin:0 0 6px; font-size:15px; font-weight:800; color:#111827;">Strict AI Model Training Shield</h3>
                <p style="margin:0; font-size:13.5px; color:#4B5563; line-height:1.6;">
                    Infinity Learning guarantees that student homework submissions, doubt queries, teacher examination questions, assessment papers, and personally identifiable records are <strong>NEVER used to train, fine-tune, or benchmark public foundation models or external third-party artificial intelligence systems</strong> without explicit, unbundled written consent from the School.
                </p>
            </div>
        </div>

        <div class="card" style="padding:28px; margin-bottom:24px;">
            <h2 class="detail-h2" style="margin-top:0;">4. Technical &amp; Organizational Security Measures</h2>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0 0 14px;">
                Infinity Learning implements industry-standard safeguards to protect data against unauthorized disclosure or loss:
            </p>
            <ul style="margin:0 0 14px 20px; font-size:13.5px; color:#475569; line-height:1.75;">
                <li><strong>Encryption:</strong> Enforced TLS 1.3 across all HTTP web traffic and database connection sockets; cryptographic Argon2id password hashing.</li>
                <li><strong>Role-Based Access Controls (RBAC):</strong> Strict tenancy isolation ensuring schools, teachers, and students access only data pertinent to their specific cohorts.</li>
                <li><strong>PII Masking:</strong> Aadhaar numbers, PAN cards, banking UPI IDs, and identity documents are masked in administrative views with controlled eye-toggle reveals.</li>
                <li><strong>Audit Trails:</strong> Real-time audit logging of all sensitive administrative modifications, approvals, and credential updates.</li>
            </ul>
        </div>

        <div class="card" style="padding:28px; margin-bottom:24px;">
            <h2 class="detail-h2" style="margin-top:0;">5. Data Retention, Portability &amp; Return</h2>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0 0 14px;">
                Upon termination of services or written request by the School, Infinity Learning shall:
            </p>
            <ul style="margin:0 0 14px 20px; font-size:13.5px; color:#475569; line-height:1.75;">
                <li>Export all academic marks, student records, fee ledgers, and attendance archives in standardized, machine-readable formats (CSV/SQL/PDF).</li>
                <li>Permanently and securely shred operational database records from active clusters within 60 days of contract termination, retaining only those archives required by statutory record-keeping laws.</li>
            </ul>
        </div>

        <div class="card" style="padding:28px;">
            <h2 class="detail-h2" style="margin-top:0;">6. Inquiries &amp; Executing a Custom DPA</h2>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0 0 14px;">
                Educational institutions and school boards requiring a countersigned enterprise DPA customized to their state education regulatory guidelines may contact our compliance team:
            </p>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0;">
                <strong>Data Protection Officer / Legal:</strong> <a href="mailto:info@infintylearning.net" style="color:#7C2D5C; font-weight:700;">info@infintylearning.net</a><br>
                <strong>Website:</strong> <a href="https://infintylearning.net">infintylearning.net</a>
            </p>
        </div>

    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

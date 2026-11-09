<?php
use App\Helper;

$pageTitle = 'Authorized Subprocessors — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">🌐 SECURITY &amp; ARCHITECTURE</span>
                <h1>Authorized Subprocessors</h1>
                <p>
                    A comprehensive, transparent list of third-party vendors and infrastructure 
                    providers engaged by Infinity Learning to process platform data.
                </p>
                <p class="muted" style="font-size:.85rem;margin-top:.75rem">
                    Last updated: <?= date('d M Y') ?> · In effect for all schools, partners, and users
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container-narrow">

        <div class="card" style="padding:28px; margin-bottom:24px;">
            <h2 class="detail-h2" style="margin-top:0;">1. About Our Subprocessors</h2>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0 0 12px;">
                Infinity Learning ("we", "us", "the Platform") partners with industry-leading infrastructure and software providers to ensure high availability, data redundancy, and operational security. Each subprocessor is vetted against strict data protection benchmarks (including India DPDP Act 2023, ISO 27001, and SOC 2 compliance).
            </p>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0;">
                Subprocessors are granted access only to the minimum data strictly necessary to fulfill their respective infrastructure functions and are contractually prohibited from using platform data for any independent commercial or secondary AI training purpose.
            </p>
        </div>

        <div class="card" style="padding:28px; margin-bottom:24px;">
            <h2 class="detail-h2" style="margin-top:0;">2. Core Infrastructure &amp; Hosting Partners</h2>
            
            <div style="overflow-x:auto;">
                <table class="table" style="width:100%; border-collapse:collapse; margin-top:14px; font-size:13.5px;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:2px solid #E2E8F0; text-align:left;">
                            <th style="padding:12px 14px;">Entity Name</th>
                            <th style="padding:12px 14px;">Purpose / Role</th>
                            <th style="padding:12px 14px;">Data Processing Location</th>
                            <th style="padding:12px 14px;">Applicable Security Safeguards</th>
                        </tr>
                    </thead>
                    <tbody style="line-height:1.6;">
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:14px; font-weight:700; color:#0F172A;">Amazon Web Services (AWS)</td>
                            <td style="padding:14px; color:#475569;">Cloud Compute (EC2), Web Server (Nginx 1.30), Encrypted Persistent Storage (EBS)</td>
                            <td style="padding:14px; color:#0F172A;">Mumbai, India (ap-south-1)</td>
                            <td style="padding:14px; color:#065F46; font-weight:600;">ISO 27001, SOC 1/2/3, PCI-DSS, In-region Data Residency</td>
                        </tr>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:14px; font-weight:700; color:#0F172A;">MariaDB Enterprise Stack</td>
                            <td style="padding:14px; color:#475569;">Relational Database Storage, User Auth (Argon2id), Student Academic Records, ERP</td>
                            <td style="padding:14px; color:#0F172A;">Mumbai, India (Self-Hosted on Dedicated Cloud Node)</td>
                            <td style="padding:14px; color:#065F46; font-weight:600;">At-Rest &amp; In-Transit Encryption, Daily Automated Backups</td>
                        </tr>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:14px; font-weight:700; color:#0F172A;">Upstash Redis</td>
                            <td style="padding:14px; color:#475569;">Stateless session caching, rate limiting, and real-time socket acceleration</td>
                            <td style="padding:14px; color:#0F172A;">AWS Mumbai low-latency cluster</td>
                            <td style="padding:14px; color:#065F46; font-weight:600;">TLS 1.3 encrypted transport, zero persistent PII storage</td>
                        </tr>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:14px; font-weight:700; color:#0F172A;">Hostinger Business Email</td>
                            <td style="padding:14px; color:#475569;">Transactional Email Delivery (Account verification, login OTPs, fee receipts, alerts)</td>
                            <td style="padding:14px; color:#0F172A;">Global Secure Data Centers</td>
                            <td style="padding:14px; color:#065F46; font-weight:600;">Enforced SPF, DKIM, DMARC, TLS 1.3 SMTP authentication</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="padding:28px; margin-bottom:24px;">
            <h2 class="detail-h2" style="margin-top:0;">3. Third-Party Software &amp; Client-Side Audits</h2>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0 0 14px;">
                Infinity Learning maintains a strict <strong>zero cross-site tracking</strong> policy:
            </p>
            <ul style="margin:0 0 16px 20px; font-size:13.5px; color:#475569; line-height:1.75;">
                <li><strong>No Advertising SDKs:</strong> We do NOT embed Meta Pixel, TikTok Pixel, Google Ads remarketing, or third-party behavioral cookie trackers.</li>
                <li><strong>No Data Harvesting:</strong> We do NOT sell, lease, or monetize educational analytics or behavioral student profiles.</li>
                <li><strong>Public Site Chatbot:</strong> The conversational support agent hosted via AI Auto Agent operates only on public, unauthenticated platform information. No logged-in student academic records, exam scores, or parent banking details are ever transmitted to this service.</li>
            </ul>
        </div>

        <div class="card" style="padding:28px;">
            <h2 class="detail-h2" style="margin-top:0;">4. Subprocessor Updates &amp; Notifications</h2>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0 0 14px;">
                We periodically update this schedule when infrastructure enhancements require onboarding new audited providers. School administrators enrolled under our <a href="/dpa" style="color:#7C2D5C; font-weight:700; text-decoration:underline;">Data Processing Agreement (DPA)</a> will receive prior written notice via registered administrative email.
            </p>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0;">
                For questions regarding vendor risk management, contact our security office at <a href="mailto:info@infintylearning.net" style="color:#7C2D5C; font-weight:700;">info@infintylearning.net</a>.
            </p>
        </div>

    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

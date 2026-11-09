<?php
use App\Helper;

$pageTitle = 'Open Source Software & Licensing Disclosures — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-inner">
            <div class="page-hero-text">
                <span class="kicker">⚖️ OPEN SOURCE DISCLOSURES</span>
                <h1>Open Source Software &amp; Font Licenses</h1>
                <p>
                    Infinity Learning is built with the help of leading open source projects, 
                    permissive typography, and secure community software. We proudly acknowledge 
                    and respect their copyright holders and license terms.
                </p>
                <p class="muted" style="font-size:.85rem;margin-top:.75rem">
                    Compliance Audit Date: <?= date('d M Y') ?>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container-narrow">

        <div class="card" style="padding:28px; margin-bottom:24px;">
            <h2 class="detail-h2" style="margin-top:0;">1. Typography &amp; Web Font Licenses</h2>
            <div style="overflow-x:auto;">
                <table class="table" style="width:100%; border-collapse:collapse; margin-top:14px; font-size:13.5px;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:2px solid #E2E8F0; text-align:left;">
                            <th style="padding:10px 14px;">Font Family</th>
                            <th style="padding:10px 14px;">Creator / Foundry</th>
                            <th style="padding:10px 14px;">License Type</th>
                            <th style="padding:10px 14px;">Usage</th>
                        </tr>
                    </thead>
                    <tbody style="line-height:1.6;">
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:12px 14px; font-weight:700;">Plus Jakarta Sans</td>
                            <td style="padding:12px 14px;">Tokotype, Gumpita Rahayu</td>
                            <td style="padding:12px 14px; color:#065F46; font-weight:600;">SIL Open Font License 1.1</td>
                            <td style="padding:12px 14px;">Primary platform UI &amp; portal body typeface</td>
                        </tr>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:12px 14px; font-weight:700;">Fraunces</td>
                            <td style="padding:12px 14px;">Undercase Type, Phaedra Charles &amp; Flavia Zimbardi</td>
                            <td style="padding:12px 14px; color:#065F46; font-weight:600;">SIL Open Font License 1.1</td>
                            <td style="padding:12px 14px;">Editorial branding, hero headings, and titles</td>
                        </tr>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:12px 14px; font-weight:700;">JetBrains Mono</td>
                            <td style="padding:12px 14px;">JetBrains</td>
                            <td style="padding:12px 14px; color:#065F46; font-weight:600;">Apache License 2.0</td>
                            <td style="padding:12px 14px;">OTP displays, numerical timers, and partner codes</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="padding:28px; margin-bottom:24px;">
            <h2 class="detail-h2" style="margin-top:0;">2. Core Open Source Software &amp; Frameworks</h2>
            <div style="overflow-x:auto;">
                <table class="table" style="width:100%; border-collapse:collapse; margin-top:14px; font-size:13.5px;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:2px solid #E2E8F0; text-align:left;">
                            <th style="padding:10px 14px;">Software / Package</th>
                            <th style="padding:10px 14px;">License</th>
                            <th style="padding:10px 14px;">Notice &amp; Copyright Attribution</th>
                        </tr>
                    </thead>
                    <tbody style="line-height:1.6;">
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:12px 14px; font-weight:700;">PHP Engine</td>
                            <td style="padding:12px 14px; color:#065F46; font-weight:600;">The PHP License, version 3.01</td>
                            <td style="padding:12px 14px;">Copyright © 1999–2026 The PHP Group. All rights reserved.</td>
                        </tr>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:12px 14px; font-weight:700;">Nginx High Performance Proxy</td>
                            <td style="padding:12px 14px; color:#065F46; font-weight:600;">2-Clause BSD-like License</td>
                            <td style="padding:12px 14px;">Copyright © 2002–2026 Igor Sysoev &amp; Nginx, Inc.</td>
                        </tr>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:12px 14px; font-weight:700;">MariaDB Database Server</td>
                            <td style="padding:12px 14px; color:#065F46; font-weight:600;">GNU GPL version 2</td>
                            <td style="padding:12px 14px;">Copyright © MariaDB Corporation Ab &amp; Foundation.</td>
                        </tr>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:12px 14px; font-weight:700;">PHPMailer</td>
                            <td style="padding:12px 14px; color:#065F46; font-weight:600;">GNU LGPL version 2.1</td>
                            <td style="padding:12px 14px;">Copyright © 2004–2026 Marcus Bointon, Andy Prevost.</td>
                        </tr>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:12px 14px; font-weight:700;">Chart.js Visualizer</td>
                            <td style="padding:12px 14px; color:#065F46; font-weight:600;">MIT License</td>
                            <td style="padding:12px 14px;">Copyright © 2014–2026 Chart.js Contributors.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="padding:28px;">
            <h2 class="detail-h2" style="margin-top:0;">3. Image &amp; Media Asset Licensing</h2>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0 0 12px;">
                Platform visual assets, logos, and illustrations are custom-commissioned original artwork owned by Infinity Learning and FutureNxt Technologies, or derived from permissively licensed open assets with required attributions.
            </p>
            <p style="font-size:14px; color:#334155; line-height:1.65; margin:0;">
                All AI-assisted educational imagery is created under valid platform subscriptions, free of restrictive third-party copyright claims, and clearly labeled in accordance with our transparent content guidelines.
            </p>
        </div>

    </div>
</section>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

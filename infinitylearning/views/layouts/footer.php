</main>
<footer class="footer">
    <style>
        /* Footer-specific styles — inline to bypass CSS file caching */
        footer.footer{
            border-top:1px solid #F3DECB;
            background:#FFFFFF;
            padding:48px 1.25rem 24px;
            margin-top:64px;
            color:#7A5C4A;
            font-size:.85rem;
            text-align:left;
        }
        footer.footer *{ box-sizing:border-box; }
        footer.footer .container{ max-width:1160px; margin:0 auto; padding:0; }

        footer.footer .footer-cols{
            display:grid;
            grid-template-columns:1.4fr 1fr 1fr;
            gap:48px;
            max-width:1100px;
            margin:0 auto 40px;
            text-align:left;
            list-style:none;
            padding:0;
        }
        footer.footer .footer-col{ text-align:left; }
        footer.footer .footer-col ul,
        footer.footer .footer-links,
        footer.footer .footer-contact{
            list-style:none;
            padding:0;
            margin:0;
        }
        footer.footer .footer-col li{
            list-style:none;
            padding:0;
            margin:0 0 8px 0;
        }

        footer.footer .footer-brand{
            display:flex;
            flex-direction:column;
            align-items:flex-start;
            line-height:1;
            gap:4px;
            margin-bottom:14px;
        }
        footer.footer .footer-brand-infinity{
            font-family:'Fraunces', Georgia, serif;
            font-weight:700;
            font-size:1.6rem;
            letter-spacing:-.03em;
            background:linear-gradient(135deg,#7C2D5C 0%,#A0386B 100%);
            -webkit-background-clip:text;
            background-clip:text;
            color:transparent;
        }
        footer.footer .footer-brand-learning{
            font-size:.62rem;
            font-weight:700;
            letter-spacing:.42em;
            color:#7C2D5C;
            padding-left:2px;
        }
        footer.footer .footer-tagline{
            font-family:'Fraunces', Georgia, serif;
            font-weight:500;
            font-style:italic;
            color:#2B1B12;
            font-size:1rem;
            margin:0 0 12px 0;
            line-height:1.4;
        }
        footer.footer .footer-blurb{
            color:#7A5C4A;
            font-size:.9rem;
            line-height:1.6;
            margin:0 0 20px 0;
            max-width:400px;
        }

        footer.footer .footer-contact{
            display:flex;
            flex-direction:column;
            gap:8px;
        }
        footer.footer .footer-contact li{
            display:flex;
            align-items:center;
            gap:8px;
            font-size:.9rem;
            color:#7A5C4A;
            margin:0;
        }
        footer.footer .footer-contact-icon{
            font-size:1rem;
            flex-shrink:0;
            line-height:1;
        }
        footer.footer .footer-contact a{
            color:#FF7A18;
            text-decoration:none;
            font-weight:600;
        }
        footer.footer .footer-contact a:hover{
            text-decoration:underline;
            color:#B85000;
        }

        footer.footer .footer-powered{
            margin-top:18px;
            padding-top:14px;
            border-top:1px solid #F3DECB;
        }
        footer.footer .footer-powered-label{
            display:block;
            font-size:.65rem;
            letter-spacing:.08em;
            text-transform:uppercase;
            font-weight:700;
            color:#A07B65;
            margin-bottom:8px;
        }
        footer.footer .footer-powered-logos{
            display:flex;
            align-items:center;
            gap:10px;
            flex-wrap:wrap;
        }
        footer.footer .footer-partner-badge{
            display:inline-flex;
            align-items:center;
            gap:8px;
            background:#FFF7ED;
            border:1px solid #FED7AA;
            padding:4px 10px 4px 6px;
            border-radius:24px;
        }
        footer.footer .footer-partner-img{
            height:28px;
            width:28px;
            border-radius:50%;
            object-fit:cover;
            background:#FFFFFF;
            box-shadow:0 1px 3px rgba(0,0,0,0.1);
            display:block;
        }
        footer.footer .footer-partner-img.futurenxt-img{
            border-radius:4px;
            width:auto;
            height:22px;
            padding:1px 4px;
            object-fit:contain;
        }
        footer.footer .footer-partner-name{
            font-size:.8rem;
            font-weight:700;
            color:#7C2D5C;
        }
        footer.footer .footer-powered-amp{
            color:#A07B65;
            font-weight:700;
            font-size:.85rem;
        }

        footer.footer .footer-col-heading{
            font-size:.78rem;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.1em;
            color:#2B1B12;
            margin:0 0 14px 0;
            padding:0;
        }
        footer.footer .footer-links{
            display:flex;
            flex-direction:column;
            gap:9px;
        }
        footer.footer .footer-links a{
            color:#7A5C4A;
            text-decoration:none;
            font-size:.92rem;
            font-weight:600;
            line-height:1.3;
            display:inline-block;
        }
        footer.footer .footer-links a:hover{
            color:#FF7A18;
        }

        footer.footer .footer-bottom{
            border-top:1px solid #F3DECB;
            padding-top:20px;
            max-width:1100px;
            margin:0 auto;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:16px;
            flex-wrap:wrap;
            color:#7A5C4A;
            font-size:.82rem;
            text-align:left;
        }
        footer.footer .footer-bottom p{
            margin:0;
            padding:0;
        }
        footer.footer .footer-bottom-links{
            display:flex;
            gap:8px;
            align-items:center;
            flex-wrap:wrap;
        }
        footer.footer .footer-bottom-links a{
            color:#7A5C4A;
            text-decoration:none;
            font-weight:600;
        }
        footer.footer .footer-bottom-links a:hover{
            color:#FF7A18;
        }
        footer.footer .footer-dot{
            opacity:.5;
            color:#7A5C4A;
        }

        @media(max-width:900px){
            footer.footer .footer-cols{
                grid-template-columns:1fr 1fr;
                gap:32px;
            }
            footer.footer .footer-col-brand{
                grid-column:1 / -1;
            }
        }
        @media(max-width:560px){
            footer.footer{
                padding:32px 1rem 20px;
            }
            footer.footer .footer-cols{
                grid-template-columns:1fr;
                gap:28px;
                text-align:center;
            }
            footer.footer .footer-col-brand{ text-align:center; }
            footer.footer .footer-brand{ align-items:center; }
            footer.footer .footer-contact li{ justify-content:center; }
            footer.footer .footer-col-heading,
            footer.footer .footer-col{ text-align:center; }
            footer.footer .footer-bottom{ justify-content:center; text-align:center; }
            footer.footer .footer-powered-logos{ justify-content:center; }
        }

        /* Dark mode */
        @media(prefers-color-scheme:dark){
            footer.footer{
                background:#1A120C;
                border-top-color:#3A2A1F;
                color:#A8917E;
            }
            footer.footer .footer-brand-learning{ color:#D8B4C8; }
            footer.footer .footer-tagline{ color:#F5E6DA; }
            footer.footer .footer-blurb{ color:#A8917E; }
            footer.footer .footer-col-heading{ color:#F5E6DA; }
            footer.footer .footer-links a{ color:#A8917E; }
            footer.footer .footer-links a:hover{ color:#FF9F45; }
            footer.footer .footer-contact li{ color:#A8917E; }
            footer.footer .footer-contact a{ color:#FF9F45; }
            footer.footer .footer-contact a:hover{ color:#FFB871; }
            footer.footer .footer-powered{ border-top-color:#3A2A1F; }
            footer.footer .footer-powered-label{ color:#8C7565; }
            footer.footer .footer-partner-badge{
                background:#261A12;
                border-color:#4A3222;
            }
            footer.footer .footer-partner-name{ color:#FFD199; }
            footer.footer .footer-powered-amp{ color:#8C7565; }
            footer.footer .footer-bottom{
                border-top-color:#3A2A1F;
                color:#A8917E;
            }
            footer.footer .footer-bottom-links a{ color:#A8917E; }
            footer.footer .footer-bottom-links a:hover{ color:#FF9F45; }
        }
    </style>

    <div class="container">
        <div class="footer-cols">

            <!-- Column 1: Brand + tagline + contact -->
            <div class="footer-col footer-col-brand">
                <div class="footer-brand">
                    <span class="footer-brand-infinity">Infinity</span>
                    <span class="footer-brand-learning">L E A R N I N G</span>
                </div>
                <p class="footer-tagline">
                    Learn. Grow. Achieve — Infinitely.
                </p>
                <p class="footer-blurb">
                    One unified platform for academics, activities, competitions and
                    achievements — built for schools, teachers, students and parents.
                </p>
                <ul class="footer-contact">
                    <li>
                        <span class="footer-contact-icon">📞</span>
                        <a href="tel:9801506511">+91 98015 06511</a>
                    </li>
                    <li>
                        <span class="footer-contact-icon">📧</span>
                        <a href="mailto:info@infintylearning.net">info@infintylearning.net</a>
                    </li>
                    <li>
                        <span class="footer-contact-icon">🌐</span>
                        <a href="https://infintylearning.net" target="_blank" rel="noopener">infintylearning.net</a>
                    </li>
                </ul>

                <div class="footer-powered">
                    <span class="footer-powered-label">POWERED BY</span>
                    <div class="footer-powered-logos">
                        <div class="footer-partner-badge" title="Agents Of Change">
                            <img src="/assets/img/agents-of-change.jpg" alt="Agents Of Change" class="footer-partner-img">
                            <span class="footer-partner-name">Agents Of Change</span>
                        </div>
                        <span class="footer-powered-amp">&amp;</span>
                        <div class="footer-partner-badge" title="FutureNXT">
                            <img src="/assets/img/futurenxt-logo.jpg" alt="FutureNXT" class="footer-partner-img futurenxt-img">
                            <span class="footer-partner-name">FutureNXT</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Platform links -->
            <div class="footer-col">
                <h4 class="footer-col-heading">Platform</h4>
                <ul class="footer-links">
                    <li><a href="/counseling">Counseling</a></li>
                    <li><a href="/activities">Activities</a></li>
                    <li><a href="/lessons">Lessons</a></li>
                    <li><a href="/dashboard">Dashboard</a></li>
                    <li><a href="/my-certificates">My Certificates</a></li>
                </ul>
            </div>

            <!-- Column 3: Company / legal links -->
            <div class="footer-col">
                <h4 class="footer-col-heading">Company</h4>
                <ul class="footer-links">
                    <li><a href="/about">About</a></li>
                    <li><a href="/contact">Contact</a></li>
                    <li><a href="/privacy">Privacy Policy</a></li>
                    <li><a href="/terms">Terms of Service</a></li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom">
            <p>© <?= date('Y') ?> Infinity Learning · Powered by <strong>Agents Of Change</strong> &amp; <strong>FutureNXT</strong>. All rights reserved.</p>
            <p class="footer-bottom-links">
                <a href="/privacy">Privacy</a>
                <span class="footer-dot">·</span>
                <a href="/terms">Terms</a>
                <span class="footer-dot">·</span>
                <a href="/about">About</a>
                <span class="footer-dot">·</span>
                <a href="/contact">Contact</a>
            </p>
        </div>
    </div>
</footer>
<script src="/assets/js/app.js?v=3.0"></script>
</body>
</html>
<?php \App\Helper::clearOld(); ?>
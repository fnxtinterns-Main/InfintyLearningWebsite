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
                        <span class="footer-contact-icon">📧</span>
                        <a href="mailto:info@infintylearning.net">info@infintylearning.net</a>
                    </li>
                    <li>
                        <span class="footer-contact-icon">🌐</span>
                        <a href="https://infintylearning.net" target="_blank" rel="noopener">infintylearning.net</a>
                    </li>
                </ul>
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
            <p>© <?= date('Y') ?> Infinity Learning. All rights reserved.</p>
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
<script src="/assets/js/app.js"></script>
</body>
</html>
<?php \App\Helper::clearOld(); ?>
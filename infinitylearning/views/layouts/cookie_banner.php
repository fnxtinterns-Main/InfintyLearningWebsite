<!-- Cookie Consent Banner & Privacy Guard (WCAG 2.1 AA Compliant, Zero Dark Patterns) -->
<div id="ilCookieBanner" role="dialog" aria-modal="true" aria-labelledby="ilCookieTitle" aria-describedby="ilCookieDesc" style="display:none; position:fixed; bottom:20px; left:20px; right:20px; max-width:680px; margin:0 auto; z-index:999999; background:#FFFFFF; border:1.5px solid #E2E8F0; border-radius:14px; box-shadow:0 20px 35px -5px rgba(0,0,0,0.18), 0 10px 10px -5px rgba(0,0,0,0.06); padding:22px 24px; font-family:'Plus Jakarta Sans',-apple-system,BlinkMacSystemFont,sans-serif; color:#0F172A;">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:12px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <span style="font-size:24px;" aria-hidden="true">🍪</span>
            <h3 id="ilCookieTitle" style="margin:0; font-size:16.5px; font-weight:800; color:#0F172A;">Your Privacy &amp; Cookie Choices</h3>
        </div>
        <button type="button" onclick="ilRejectNonEssentialCookies()" aria-label="Close and use necessary cookies only" style="background:none; border:none; font-size:22px; line-height:1; color:#64748B; cursor:pointer; padding:2px 6px; border-radius:4px;" onfocus="this.style.outline='2px solid #7C2D5C'" onblur="this.style.outline='none'">&times;</button>
    </div>

    <p id="ilCookieDesc" style="font-size:13.5px; line-height:1.6; color:#334155; margin:0 0 16px;">
        We use essential cookies to keep you safely logged in and ensure secure operation. We do <strong>not</strong> sell your data or use cross-site behavioral ad trackers. With your permission, we also use privacy-first diagnostic tools to improve educational tools. You can customize your preferences anytime.
        Read our <a href="/privacy" style="color:#7C2D5C; font-weight:700; text-decoration:underline;">Privacy Policy</a> and <a href="/terms" style="color:#7C2D5C; font-weight:700; text-decoration:underline;">Terms</a>.
    </p>

    <!-- Preferences Expansion Accordion (Optional) -->
    <div id="ilCookiePrefs" style="display:none; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:14px 16px; margin-bottom:16px;">
        <div style="margin-bottom:12px;">
            <label style="display:flex; align-items:center; justify-content:space-between; font-size:13px; font-weight:700; color:#0F172A;">
                <span>🔒 Strictly Necessary Cookies</span>
                <span style="font-size:11px; background:#E2E8F0; color:#475569; padding:3px 8px; border-radius:4px;">Always Active</span>
            </label>
            <p style="margin:4px 0 0; font-size:12px; color:#64748B; line-height:1.5;">
                Essential for core authentication, session management (`il_session`), CSRF tokens, and security defense.
            </p>
        </div>
        <div>
            <label style="display:flex; align-items:center; justify-content:space-between; font-size:13px; font-weight:700; color:#0F172A; cursor:pointer;">
                <span>📊 Anonymous Educational Analytics</span>
                <input type="checkbox" id="ilPrefAnalytics" style="width:16px; height:16px; accent-color:#7C2D5C; cursor:pointer;">
            </label>
            <p style="margin:4px 0 0; font-size:12px; color:#64748B; line-height:1.5;">
                Anonymous performance metrics to help teachers improve lesson delivery. Blocked by default until opted-in.
            </p>
        </div>
    </div>

    <!-- Actions: Equal prominence (No Dark Patterns) -->
    <div style="display:flex; align-items:center; justify-content:flex-end; flex-wrap:wrap; gap:10px;">
        <button type="button" onclick="ilToggleCookiePrefs()" id="ilBtnPrefs" style="background:#FFFFFF; border:1.5px solid #CBD5E1; color:#334155; padding:9px 16px; font-size:13px; font-weight:700; border-radius:8px; cursor:pointer; transition:all 0.15s;" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='#FFFFFF'" onfocus="this.style.outline='2px solid #7C2D5C'">
            Cookie Preferences
        </button>
        <button type="button" onclick="ilRejectNonEssentialCookies()" id="ilBtnReject" style="background:#F1F5F9; border:1.5px solid #CBD5E1; color:#0F172A; padding:9px 18px; font-size:13px; font-weight:700; border-radius:8px; cursor:pointer; transition:all 0.15s;" onmouseover="this.style.background='#E2E8F0'" onmouseout="this.style.background='#F1F5F9'" onfocus="this.style.outline='2px solid #7C2D5C'">
            Reject Non-Essential
        </button>
        <button type="button" onclick="ilAcceptAllCookies()" id="ilBtnAccept" style="background:#7C2D5C; border:1.5px solid #7C2D5C; color:#FFFFFF; padding:9px 20px; font-size:13px; font-weight:700; border-radius:8px; cursor:pointer; transition:all 0.15s; box-shadow:0 2px 4px rgba(124,45,92,0.2);" onmouseover="this.style.background='#5E2045'" onmouseout="this.style.background='#7C2D5C'" onfocus="this.style.outline='2px solid #0F172A'">
            Accept All
        </button>
    </div>
</div>

<script>
(function() {
    const CONSENT_KEY = 'il_cookie_consent_v2';

    window.ilCheckCookieConsent = function() {
        const consent = localStorage.getItem(CONSENT_KEY);
        if (!consent) {
            const banner = document.getElementById('ilCookieBanner');
            if (banner) banner.style.display = 'block';
        } else {
            try {
                const prefs = JSON.parse(consent);
                if (prefs.analytics) {
                    window.ilEnableAnalytics && window.ilEnableAnalytics();
                }
            } catch(e) {}
        }
    };

    window.ilAcceptAllCookies = function() {
        const payload = {
            strictly_necessary: true,
            analytics: true,
            timestamp: new Date().toISOString()
        };
        localStorage.setItem(CONSENT_KEY, JSON.stringify(payload));
        document.cookie = "il_consent=all; path=/; max-age=31536000; SameSite=Lax; Secure";
        const banner = document.getElementById('ilCookieBanner');
        if (banner) banner.style.display = 'none';
        window.ilEnableAnalytics && window.ilEnableAnalytics();
    };

    window.ilRejectNonEssentialCookies = function() {
        const payload = {
            strictly_necessary: true,
            analytics: false,
            timestamp: new Date().toISOString()
        };
        localStorage.setItem(CONSENT_KEY, JSON.stringify(payload));
        document.cookie = "il_consent=essential; path=/; max-age=31536000; SameSite=Lax; Secure";
        const banner = document.getElementById('ilCookieBanner');
        if (banner) banner.style.display = 'none';
    };

    window.ilSaveCookiePrefs = function() {
        const analytics = document.getElementById('ilPrefAnalytics')?.checked || false;
        const payload = {
            strictly_necessary: true,
            analytics: analytics,
            timestamp: new Date().toISOString()
        };
        localStorage.setItem(CONSENT_KEY, JSON.stringify(payload));
        document.cookie = "il_consent=" + (analytics ? "custom_analytics" : "essential") + "; path=/; max-age=31536000; SameSite=Lax; Secure";
        const banner = document.getElementById('ilCookieBanner');
        if (banner) banner.style.display = 'none';
        if (analytics && window.ilEnableAnalytics) window.ilEnableAnalytics();
    };

    window.ilToggleCookiePrefs = function() {
        const prefs = document.getElementById('ilCookiePrefs');
        const btnPrefs = document.getElementById('ilBtnPrefs');
        if (!prefs) return;
        if (prefs.style.display === 'none') {
            prefs.style.display = 'block';
            btnPrefs.innerText = 'Save Preferences';
            btnPrefs.onclick = window.ilSaveCookiePrefs;
            btnPrefs.style.background = '#7C2D5C';
            btnPrefs.style.color = '#FFFFFF';
        } else {
            prefs.style.display = 'none';
            btnPrefs.innerText = 'Cookie Preferences';
            btnPrefs.onclick = window.ilToggleCookiePrefs;
            btnPrefs.style.background = '#FFFFFF';
            btnPrefs.style.color = '#334155';
        }
    };

    window.ilOpenCookieBanner = function() {
        const banner = document.getElementById('ilCookieBanner');
        if (banner) {
            banner.style.display = 'block';
            banner.focus();
        }
    };

    // Keyboard support: Escape closes modal with essential selection
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const banner = document.getElementById('ilCookieBanner');
            if (banner && banner.style.display !== 'none') {
                ilRejectNonEssentialCookies();
            }
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ilCheckCookieConsent);
    } else {
        ilCheckCookieConsent();
    }
})();
</script>

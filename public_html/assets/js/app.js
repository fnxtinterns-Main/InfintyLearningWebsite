document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // 1. NAVBAR — scroll state (shrinks + adds shadow)
    // ============================================================
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        let isScrolled = false;
        const updateNav = () => {
            const y = window.scrollY || 0;
            if (!isScrolled && y > 24) {
                isScrolled = true;
                navbar.classList.add('scrolled');
            } else if (isScrolled && y < 8) {
                isScrolled = false;
                navbar.classList.remove('scrolled');
            }
        };
        updateNav();
        window.addEventListener('scroll', updateNav, { passive: true });

        // Prevent navbar from capturing mouse wheel and shifting inner container
        navbar.addEventListener('wheel', function (e) {
            navbar.scrollLeft = 0;
            navbar.scrollTop = 0;
            if (e.deltaY !== 0) {
                window.scrollBy({ top: e.deltaY, left: 0, behavior: 'auto' });
                e.preventDefault();
            }
        }, { passive: false });
    }

    // ============================================================
    // 2. MOBILE NAV TOGGLE
    // ============================================================
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks  = document.querySelector('.nav-links');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => navLinks.classList.toggle('open'));
    }

    // ============================================================
    // 3. AUTO-FOCUS OTP INPUT
    // ============================================================
    const otp = document.querySelector('input[name="otp"]');
    if (otp) otp.focus();

    // ============================================================
    // 4. PASSWORD MATCH — client-side confirm validation
    // ============================================================
    const pw  = document.querySelector('input[name="password"]');
    const pwc = document.querySelector('input[name="password_confirm"]');
    if (pw && pwc) {
        const check = () => pwc.setCustomValidity(pwc.value !== pw.value ? 'Passwords do not match' : '');
        pwc.addEventListener('input', check);
        pw.addEventListener('input', check);
    }

    const newPw  = document.querySelector('input[name="new_password"]');
    const newPwC = document.querySelector('input[name="new_password_confirm"]');
    if (newPw && newPwC) {
        const check = () => newPwC.setCustomValidity(newPwC.value !== newPw.value ? 'Passwords do not match' : '');
        newPwC.addEventListener('input', check);
        newPw.addEventListener('input', check);
    }

    // ============================================================
    // 5. AUTO-HIDE ALERTS
    // ============================================================
    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => {
            a.style.transition = 'opacity .4s';
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 400);
        }, 6000);
    });

    // ============================================================
    // 6. REVEAL ANIMATIONS ON SCROLL
    // ============================================================
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('is-visible');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal, .reveal-up, .reveal-scale, .reveal-fade').forEach(el => {
        io.observe(el);
    });

    // ============================================================
    // 7. DROPDOWN MENUS
    // ============================================================
    const menus = document.querySelectorAll('.user-menu');

    menus.forEach(menu => {
        const trigger = menu.querySelector('button[aria-haspopup="true"]');
        if (!trigger) return;

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const wasOpen = menu.classList.contains('open');

            menus.forEach(m => {
                m.classList.remove('open');
                const t = m.querySelector('button[aria-haspopup="true"]');
                if (t) t.setAttribute('aria-expanded', 'false');
            });

            if (!wasOpen) {
                menu.classList.add('open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.user-menu')) {
            menus.forEach(m => {
                m.classList.remove('open');
                const t = m.querySelector('button[aria-haspopup="true"]');
                if (t) t.setAttribute('aria-expanded', 'false');
            });
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            menus.forEach(m => {
                m.classList.remove('open');
                const t = m.querySelector('button[aria-haspopup="true"]');
                if (t) t.setAttribute('aria-expanded', 'false');
            });
        }
    });

    // ============================================================
    // 8. NOTIFICATION CLICK → mark as read & dismiss from panel
    // ============================================================
    document.querySelectorAll('.notif-item').forEach(item => {
        item.addEventListener('click', function (e) {
            const nid = this.dataset.nid;
            const link = this.getAttribute('href');
            if (nid) {
                try {
                    fetch('/notifications-mark-read-handler.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + encodeURIComponent(nid),
                        keepalive: true
                    });
                } catch (err) {}
            }
            // Animate and remove from the list
            this.style.transition = 'all 0.25s ease';
            this.style.opacity = '0';
            this.style.transform = 'translateX(20px)';
            setTimeout(() => {
                const list = item.closest('.notif-list');
                item.remove();
                // Decrement or remove badge
                const badge = document.querySelector('.notif-badge');
                if (badge) {
                    let count = parseInt(badge.textContent.trim(), 10) || 0;
                    count = Math.max(0, count - 1);
                    if (count > 0) {
                        badge.textContent = count;
                    } else {
                        badge.remove();
                    }
                }
                if (list && list.children.length === 0) {
                    const parent = list.parentElement;
                    if (parent && !parent.querySelector('.notif-empty')) {
                        const empty = document.createElement('div');
                        empty.className = 'notif-empty';
                        empty.innerHTML = '<div style="font-size:1.8rem">🔔</div><p class="muted" style="font-size:.85rem">No notifications yet.</p>';
                        parent.appendChild(empty);
                    }
                    const markAllForm = document.querySelector('.notif-mark-all-form');
                    if (markAllForm) markAllForm.remove();
                }
            }, 250);
        });
    });

    // ============================================================
    // 8b. AUTO LOGOUT ON SCREEN LOCK / INACTIVITY (Item 17)
    // ============================================================
    let screenHiddenTime = null;
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            screenHiddenTime = Date.now();
        } else if (document.visibilityState === 'visible' && screenHiddenTime) {
            const secondsHidden = (Date.now() - screenHiddenTime) / 1000;
            // Screen locked / off for 15+ minutes -> auto logout
            if (secondsHidden >= 900) {
                window.location.href = '/logout';
            }
            screenHiddenTime = null;
        }
    });

    let idleTimeout;
    function resetIdleTimer() {
        clearTimeout(idleTimeout);
        // 25 minutes of inactivity -> auto logout
        idleTimeout = setTimeout(function() {
            if (document.querySelector('.user-menu')) {
                window.location.href = '/logout';
            }
        }, 1500000);
    }
    ['mousemove', 'keydown', 'touchstart', 'scroll', 'click'].forEach(evt => {
        window.addEventListener(evt, resetIdleTimer, { passive: true });
    });
    resetIdleTimer();

    // ============================================================
    // 9. SHOW / HIDE PASSWORD TOGGLE
    // ============================================================
    (function attachPasswordToggles() {
        const EYE_OPEN = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        const EYE_OFF  = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';

        document.querySelectorAll('input[type="password"]').forEach(function (input) {
            if (input.dataset.pwToggled === '1') return;
            input.dataset.pwToggled = '1';

            const wrapper = document.createElement('div');
            wrapper.className = 'pw-wrap';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pw-toggle';
            btn.setAttribute('aria-label', 'Show password');
            btn.setAttribute('tabindex', '-1');
            btn.innerHTML = EYE_OPEN;

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const showing = input.type === 'text';
                if (showing) {
                    input.type = 'password';
                    btn.innerHTML = EYE_OPEN;
                    btn.setAttribute('aria-label', 'Show password');
                    btn.classList.remove('is-on');
                } else {
                    input.type = 'text';
                    btn.innerHTML = EYE_OFF;
                    btn.setAttribute('aria-label', 'Hide password');
                    btn.classList.add('is-on');
                }
                input.focus();
            });

            wrapper.appendChild(btn);
        });
    })();

    // ============================================================
    // 10. BUTTON LOADING STATE
    // ============================================================
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function () {
            const submit = form.querySelector('button[type="submit"]:not([data-no-loading])');
            if (submit && !submit.disabled) {
                submit.classList.add('is-loading');
                setTimeout(() => submit.classList.remove('is-loading'), 15000);
            }
        });
    });

    // ============================================================
    // 11. IMAGE ERROR FALLBACK
    // ============================================================
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function () {
            this.style.background = 'var(--il-grad-soft)';
            this.style.minHeight = '60px';
            this.alt = this.alt || 'Image unavailable';
        }, { once: true });
    });

    // ============================================================
    // 12. HOMEPAGE — ROTATING WORD
    // ============================================================
    const rotating = document.querySelector('.rotating-word');
    if (rotating) {
        let words = [];
        try {
            words = JSON.parse(rotating.dataset.words || '[]');
        } catch (e) {
            words = ['student', 'learner', 'achiever', 'winner'];
        }
        if (!Array.isArray(words) || !words.length) {
            words = ['student', 'learner', 'achiever', 'winner'];
        }

        let idx = 0;
        rotating.textContent = words[0];

        setInterval(() => {
            rotating.classList.add('is-swapping');
            setTimeout(() => {
                idx = (idx + 1) % words.length;
                rotating.textContent = words[idx];
                rotating.classList.remove('is-swapping');
            }, 400);
        }, 2400);
    }

    // ============================================================
    // 13. HOMEPAGE — COUNTERS (stat band)
    // ============================================================
    const counters = document.querySelectorAll('[data-count]');
    if (counters.length) {
        const animateCounter = (el) => {
            const target = parseInt(el.dataset.count, 10) || 0;
            const duration = 1400;
            const start = performance.now();

            const step = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const value = Math.floor(eased * target);
                el.textContent = value.toLocaleString();
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    el.textContent = target.toLocaleString();
                }
            };
            requestAnimationFrame(step);
        };

        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && !entry.target.dataset.counted) {
                    entry.target.dataset.counted = '1';
                    animateCounter(entry.target);
                }
            });
        }, { threshold: 0.4 });

        counters.forEach((el) => counterObserver.observe(el));
    }

    // ============================================================
    // 14. HOMEPAGE — PARTNER TICKER (hide if empty)
    // ============================================================
    const ticker = document.querySelector('.partner-track');
    if (ticker && ticker.children.length < 2) {
        const section = ticker.closest('.partner-section');
        if (section) section.style.display = 'none';
    }

});
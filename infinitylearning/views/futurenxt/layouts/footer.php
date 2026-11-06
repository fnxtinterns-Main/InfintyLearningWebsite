<?php
declare(strict_types=1);
?>
    <!-- Global Footer -->
    <footer class="fnx-footer">
        <div class="fnx-footer-inner">
            <div class="fnx-footer-copy">
                &copy; <?= date('Y') ?> <strong>FutureNxt Technologies</strong> · Corporate Enterprise Operations Portal · All Rights Reserved.
            </div>
            <div class="fnx-footer-links">
                <a href="/futurenxt/dashboard">Dashboard</a>
                <a href="/futurenxt/tasks">Tasks</a>
                <a href="/futurenxt/daily-updates">Daily Updates</a>
                <a href="/futurenxt/leaves">Leaves &amp; WFH</a>
                <a href="/futurenxt/profile">My Profile</a>
                <a href="https://infintylearning.net" target="_blank">Infinity Learning</a>
            </div>
        </div>
    </footer>

    <!-- Profile Dropdown & Modal Toggle Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const trigger = document.getElementById('fnxProfileTrigger');
        const menu = document.getElementById('fnxProfileMenu');
        
        if (trigger && menu) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                    menu.classList.remove('show');
                }
            });
        }
    });

    function fnxOpenModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function fnxCloseModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.fnx-modal-backdrop').forEach(function(m) {
                m.style.display = 'none';
            });
            document.body.style.overflow = 'auto';
        }
    });

    // Password Visibility Eye Toggle
    function fnxTogglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '🙈';
            btn.title = 'Hide password';
        } else {
            input.type = 'password';
            btn.innerHTML = '👁️';
            btn.title = 'Show password';
        }
    }

    // Masked Sensitive Details Toggle (Banking / Aadhaar / PAN)
    function fnxToggleMask(elemId, btn) {
        const el = document.getElementById(elemId);
        if (!el) return;
        const isMasked = el.getAttribute('data-masked') === '1';
        if (isMasked) {
            el.innerText = el.getAttribute('data-real');
            el.setAttribute('data-masked', '0');
            btn.innerHTML = '🙈';
            btn.title = 'Hide detail';
        } else {
            el.innerText = el.getAttribute('data-mask');
            el.setAttribute('data-masked', '1');
            btn.innerHTML = '👁️';
            btn.title = 'Show detail';
        }
    }

    // Auto-logout prevention: Keep user session alive indefinitely
    setInterval(function() {
        fetch('/futurenxt-handler.php?action=ping', { credentials: 'same-origin' }).catch(function() {});
    }, 240000); // Every 4 minutes
    </script>
</body>
</html>

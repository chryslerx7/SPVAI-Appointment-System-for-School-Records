    </main>

    <script src="../assets/js/jquery-3.1.1.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script>
    // Admin responsive navigation (P10-003): off-canvas sidebar on tablet/mobile.
    // Mirrors the student navigation model. Desktop is unaffected
    // (sidebar stays visible via md: classes).
    (function() {
        if (window.__spvaiAdminNav) return;
        window.__spvaiAdminNav = true;

        var sidebar = document.getElementById('admin-sidebar');
        var toggle = document.getElementById('admin-menu-toggle');
        var closeBtn = document.getElementById('admin-menu-close');
        var backdrop = document.getElementById('admin-menu-backdrop');
        if (!sidebar || !toggle || !backdrop) return;

        var HIDDEN_SIDEBAR = '-translate-x-full';
        var isMobileView = function() {
            return window.matchMedia('(max-width: 767px)').matches;
        };

        function openMenu() {
            sidebar.classList.remove(HIDDEN_SIDEBAR);
            backdrop.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            if (closeBtn) closeBtn.focus();
        }

        function closeMenu(returnFocus) {
            sidebar.classList.add(HIDDEN_SIDEBAR);
            backdrop.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
            if (returnFocus) toggle.focus();
        }

        function isOpen() {
            return !sidebar.classList.contains(HIDDEN_SIDEBAR);
        }

        toggle.addEventListener('click', function() {
            if (isOpen()) {
                closeMenu(false);
            } else {
                openMenu();
            }
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                closeMenu(true);
            });
        }

        backdrop.addEventListener('click', function() {
            closeMenu(false);
        });

        // Close the panel after choosing a link on tablet/mobile; harmless on desktop.
        sidebar.addEventListener('click', function(e) {
            var link = e.target.closest('a');
            if (link && isMobileView()) {
                closeMenu(false);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isOpen() && isMobileView()) {
                closeMenu(true);
            }
        });

        // Reset mobile-only state when resizing up to desktop.
        window.addEventListener('resize', function() {
            if (!isMobileView()) {
                backdrop.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
    })();
    </script>
</body>
</html>

(() => {
    const STORAGE_KEY = 'itik-theme';
    const root = document.documentElement;
    const toggleBtn = document.getElementById('theme-toggle');

    function getCurrentTheme() {
        return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function applyTheme(theme, persist) {
        const normalized = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', normalized);

        if (persist) {
            try {
                window.localStorage.setItem(STORAGE_KEY, normalized);
            } catch (e) {
                // Abaikan jika localStorage diblokir
            }
        }

        syncButton(normalized);
    }

    function syncButton(theme) {
        if (!toggleBtn) {
            return;
        }

        const isDark = theme === 'dark';
        toggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        toggleBtn.setAttribute(
            'aria-label',
            isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'
        );

        const labelEl = toggleBtn.querySelector('.theme-toggle-label');
        if (labelEl) {
            labelEl.textContent = isDark ? 'Mode Terang' : 'Mode Gelap';
        }
    }

    syncButton(getCurrentTheme());

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const nextTheme = getCurrentTheme() === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme, true);
        });
    }

    if (window.matchMedia) {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', (event) => {
            let tersimpan = null;
            try {
                tersimpan = window.localStorage.getItem(STORAGE_KEY);
            } catch (e) {
                tersimpan = null;
            }

            if (tersimpan !== 'dark' && tersimpan !== 'light') {
                applyTheme(event.matches ? 'dark' : 'light', false);
            }
        });
    }
})();

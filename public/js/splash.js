(() => {
    const overlay = document.getElementById('login-splash-overlay');
    if (!overlay) {
        return;
    }

    const prefersReducedMotion = Boolean(
        window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    );

    if (prefersReducedMotion) {
        overlay.remove();
        return;
    }

    let sudahDihapus = false;
    let fallbackTimer = null;

    function hapusOverlay() {
        if (sudahDihapus) {
            return;
        }
        sudahDihapus = true;
        if (fallbackTimer !== null) {
            window.clearTimeout(fallbackTimer);
        }
        overlay.remove();
        window.dispatchEvent(new CustomEvent('itik:splash-done'));
    }

    overlay.addEventListener('animationend', (event) => {
        if (event.target === overlay && event.animationName === 'splashOverlayFadeOut') {
            hapusOverlay();
        }
    });

    // Batas waktu cadangan agar overlay tidak macet jika animationend tidak terpanggil
    fallbackTimer = window.setTimeout(hapusOverlay, 2600);
})();

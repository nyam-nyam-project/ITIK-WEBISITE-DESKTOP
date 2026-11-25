(() => {
    const prefersReducedMotion = Boolean(
        window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    );

    function formatNumber(value, decimals, format, suffix) {
        if (format === 'id') {
            return (
                new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                }).format(value) + suffix
            );
        }

        return value.toFixed(decimals) + suffix;
    }

    function animateCountUp(el) {
        const raw = el.dataset.countUp;
        if (raw === undefined || raw === '') {
            return;
        }

        const target = parseFloat(raw);
        if (Number.isNaN(target)) {
            return;
        }

        const decimals = parseInt(el.dataset.decimals || '0', 10);
        const format = el.dataset.format || '';
        const suffix = el.dataset.suffix || '';

        if (prefersReducedMotion || target === 0) {
            el.textContent = formatNumber(target, decimals, format, suffix);
            return;
        }

        const duration = 650;
        const startTime = performance.now();
        el.textContent = formatNumber(0, decimals, format, suffix);

        function tick(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = target * eased;

            el.textContent = formatNumber(current, decimals, format, suffix);

            if (progress < 1) {
                window.requestAnimationFrame(tick);
            } else {
                el.textContent = formatNumber(target, decimals, format, suffix);
            }
        }

        window.requestAnimationFrame(tick);
    }

    function animateProgressBar(el) {
        const raw = el.dataset.progressTarget;
        if (raw === undefined || raw === '') {
            return;
        }

        const target = parseFloat(raw);
        if (Number.isNaN(target)) {
            return;
        }

        if (prefersReducedMotion || target <= 0) {
            el.value = target;
            return;
        }

        const duration = 700;
        const startTime = performance.now();
        el.value = 0;

        function tick(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.value = target * eased;

            if (progress < 1) {
                window.requestAnimationFrame(tick);
            } else {
                el.value = target;
            }
        }

        window.requestAnimationFrame(tick);
    }

    function animateSvgRing(el) {
        const rawTarget = el.dataset.ringTarget;
        if (rawTarget === undefined || rawTarget === '') {
            return;
        }

        const targetOffset = parseFloat(rawTarget);
        const circumference = parseFloat(el.getAttribute('stroke-dasharray') || '0');
        if (Number.isNaN(targetOffset)) {
            return;
        }

        if (prefersReducedMotion || circumference <= 0) {
            el.setAttribute('stroke-dashoffset', String(targetOffset));
            return;
        }

        el.setAttribute('stroke-dashoffset', String(circumference));
        el.getBoundingClientRect();

        window.requestAnimationFrame(() => {
            el.setAttribute('stroke-dashoffset', String(targetOffset));
        });
    }

    function jalankanSemuaAnimasi() {
        document.querySelectorAll('[data-count-up]').forEach(animateCountUp);
        document.querySelectorAll('progress[data-progress-target]').forEach(animateProgressBar);
        document.querySelectorAll('[data-ring-target]').forEach(animateSvgRing);
    }

    const splashOverlay = document.getElementById('login-splash-overlay');
    if (splashOverlay && !prefersReducedMotion) {
        window.addEventListener('itik:splash-done', jalankanSemuaAnimasi, { once: true });
    } else {
        jalankanSemuaAnimasi();
    }
})();

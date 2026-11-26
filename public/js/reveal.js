(() => {
    const elements = Array.from(document.querySelectorAll('[data-reveal]'));
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (!document.documentElement.classList.contains('js') || motion.matches || !('IntersectionObserver' in window) || elements.length === 0) {
        return;
    }

    let observer;
    const pending = new Set();
    const timers = new Set();

    function finish(element) {
        element.classList.remove('reveal-pending');
        element.classList.add('reveal-visible');
        element.removeAttribute('data-reveal-step');
        pending.delete(element);
    }

    function showAll() {
        observer?.disconnect();
        timers.forEach(timer => window.clearTimeout(timer));
        timers.clear();
        pending.forEach(finish);
        document.removeEventListener('focusin', revealFocused);
        motion.removeEventListener('change', motionChanged);
    }

    function reveal(element, step = 0) {
        observer.unobserve(element);
        element.dataset.revealStep = String(step);
        element.classList.add('reveal-visible');
        const timer = window.setTimeout(() => {
            finish(element);
            timers.delete(timer);
        }, 320 + step * 40);
        timers.add(timer);
    }

    function revealFocused(event) {
        const element = event.target.closest('[data-reveal]');
        if (element && pending.has(element)) {
            observer.unobserve(element);
            finish(element);
        }
    }

    function motionChanged(event) {
        if (event.matches) {
            showAll();
        }
    }

    function start() {
        try {
            observer = new IntersectionObserver(entries => {
                try {
                    entries.filter(entry => entry.isIntersecting)
                        .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)
                        .forEach((entry, index) => reveal(entry.target, Math.min(index, 3)));
                } catch {
                    showAll();
                }
            }, { rootMargin: '0px 0px -16px 0px', threshold: 0 });

            document.addEventListener('focusin', revealFocused);
            motion.addEventListener('change', motionChanged);
            elements.forEach(element => {
                if (element.contains(document.activeElement)) {
                    return;
                }
                pending.add(element);
                element.classList.add('reveal-pending');
                observer.observe(element);
            });
        } catch {
            showAll();
        }
    }

    if (document.getElementById('login-splash-overlay')) {
        let started = false;
        const startOnce = () => {
            if (!started) {
                started = true;
                if (!motion.matches) {
                    start();
                }
            }
        };
        window.addEventListener('itik:splash-done', startOnce, { once: true });
        window.setTimeout(startOnce, 2800);
    } else {
        start();
    }
})();

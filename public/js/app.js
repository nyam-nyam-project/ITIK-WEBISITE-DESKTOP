(() => {
    document.documentElement.classList.add('js');

    const appSidebar = document.getElementById('app-sidebar');
    const appNavToggle = document.getElementById('nav-toggle');
    if (appNavToggle && appSidebar) {
        appNavToggle.addEventListener('click', function () {
            const open = appSidebar.classList.toggle('is-open');
            appNavToggle.setAttribute('aria-expanded', String(open));
            appNavToggle.textContent = open ? 'Tutup menu' : 'Menu';
        });
    }

    document.addEventListener('submit', function (event) {
        const message = event.target.getAttribute('data-confirm');
        if (message && !window.confirm(message)) event.preventDefault();
    });

    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[data-alert]');
        if (link) {
            window.alert(link.dataset.alert);
            event.preventDefault();
        }
    });
})();

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('guruSidebar');

    if (!sidebar || !window.bootstrap) {
        return;
    }

    sidebar.querySelectorAll('.nav-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                bootstrap.Offcanvas.getOrCreateInstance(sidebar).hide();
            }
        });
    });
});

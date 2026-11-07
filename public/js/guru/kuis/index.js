function switchView(mode) {
    const viewBab = document.getElementById('view-bab');
    const viewAll = document.getElementById('view-all');
    const btnBab = document.getElementById('btn-view-bab');
    const btnAll = document.getElementById('btn-view-all');
    if (!viewBab || !viewAll) return;
    if (mode === 'bab') {
        viewBab.classList.remove('u-display-none');
        viewBab.classList.add('u-display-block');
        viewAll.classList.remove('u-display-block');
        viewAll.classList.add('u-display-none');
        btnBab.classList.add('active');
        btnAll.classList.remove('active');
    } else {
        viewBab.classList.remove('u-display-block');
        viewBab.classList.add('u-display-none');
        viewAll.classList.remove('u-display-none');
        viewAll.classList.add('u-display-block');
        btnAll.classList.add('active');
        btnBab.classList.remove('active');
    }
}

document.querySelectorAll('[data-view]').forEach(button => {
    button.addEventListener('click', function() {
        switchView(button.dataset.view);
    });
});
